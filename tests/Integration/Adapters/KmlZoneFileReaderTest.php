<?php

declare(strict_types=1);

namespace Tests\Integration\Adapters;

use App\Domain\Coverage\Repositories\ZoneFileContent;
use App\Domain\Coverage\Repositories\ZoneFileRejected;
use App\Infrastructure\Adapters\KmlZoneFileReader;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class KmlZoneFileReaderTest extends TestCase
{
    private const RING = '-100,20,0 -99.9,20,0 -99.9,20.1,0 -100,20,0';

    private const TRIANGLE = [[20.0, -100.0], [20.0, -99.9], [20.1, -99.9]];

    public function test_it_reads_a_named_polygon_as_latitude_and_longitude_without_the_closing_vertex(): void
    {
        $content = $this->read($this->kml($this->placemark('Centro')));

        $this->assertCount(1, $content->entries);
        $this->assertSame('Centro', $content->entries[0]->name);
        $this->assertSame([self::TRIANGLE], $content->entries[0]->polygons);
        $this->assertSame(0, $content->ignoredGeometries);
    }

    public function test_it_reads_coordinates_without_height_and_on_several_lines(): void
    {
        $polygon = $this->polygon("\n   -100,20\n\t-99.9,20   -99.9,20.1\n   -100,20\n");

        $content = $this->read($this->kml("<Placemark><name>Centro</name>{$polygon}</Placemark>"));

        $this->assertSame([self::TRIANGLE], $content->entries[0]->polygons);
    }

    public function test_it_keeps_a_ring_that_comes_without_the_closing_vertex(): void
    {
        $polygon = $this->polygon('-100,20 -99.9,20 -99.9,20.1');

        $content = $this->read($this->kml("<Placemark><name>Centro</name>{$polygon}</Placemark>"));

        $this->assertSame([self::TRIANGLE], $content->entries[0]->polygons);
    }

    public function test_it_finds_placemarks_inside_nested_folders(): void
    {
        $body = '<Document><Folder><name>Capa</name><Folder>'.$this->placemark('Norte').'</Folder></Folder>'
            .$this->placemark('Sur').'</Document>';

        $this->assertSame(['Norte', 'Sur'], $this->names($this->read($this->kml($body))));
    }

    public function test_several_polygons_of_an_element_form_one_entry(): void
    {
        $multi = '<MultiGeometry>'.$this->polygon(self::RING)
            .$this->polygon('-98,20 -97.9,20 -97.9,20.1 -98,20').'</MultiGeometry>';

        $content = $this->read($this->kml("<Placemark><name>Dos partes</name>{$multi}</Placemark>"));

        $this->assertCount(1, $content->entries);
        $this->assertCount(2, $content->entries[0]->polygons);
    }

    #[DataProvider('kmlVersions')]
    public function test_it_accepts_any_kml_namespace(string $root): void
    {
        $xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>{$root}".$this->placemark('Centro').'</kml>';

        $this->assertCount(1, $this->read($xml)->entries);
    }

    /** @return array<string, array{string}> */
    public static function kmlVersions(): array
    {
        return [
            '2.2' => ['<kml xmlns="http://www.opengis.net/kml/2.2">'],
            '2.1 of Google' => ['<kml xmlns="http://earth.google.com/kml/2.1">'],
            'no namespace' => ['<kml>'],
        ];
    }

    public function test_it_counts_the_points_and_lines_it_ignores(): void
    {
        $point = '<Placemark><name>Torre</name><Point><coordinates>-100,20,0</coordinates></Point></Placemark>';
        $line = '<Placemark><name>Ruta</name><LineString><coordinates>-100,20 -99,20</coordinates></LineString></Placemark>';
        $mixed = '<Placemark><name>Mixta</name><MultiGeometry>'.$this->polygon(self::RING)
            .'<Point><coordinates>-100,20</coordinates></Point></MultiGeometry></Placemark>';

        $content = $this->read($this->kml($point.$line.$mixed));

        $this->assertSame(['Mixta'], $this->names($content));
        $this->assertSame(3, $content->ignoredGeometries);
    }

    public function test_a_polygon_without_a_name_is_delivered_with_an_empty_name(): void
    {
        $content = $this->read($this->kml('<Placemark>'.$this->polygon(self::RING).'</Placemark>'));

        $this->assertSame('', $content->entries[0]->name);
    }

    public function test_it_decodes_the_predefined_xml_escapes_of_a_name(): void
    {
        $content = $this->read($this->kml($this->placemark('Norte &amp; Sur')));

        $this->assertSame('Norte & Sur', $content->entries[0]->name);
    }

    public function test_it_ignores_a_network_link_and_loads_the_polygons_of_the_file(): void
    {
        $link = '<NetworkLink><name>Remota</name><Link><href>http://127.0.0.1:9/zonas.kml</href></Link></NetworkLink>';

        $this->assertSame(['Centro'], $this->names($this->read($this->kml($link.$this->placemark('Centro')))));
    }

    public function test_a_file_with_only_a_network_link_has_no_polygons(): void
    {
        $link = '<NetworkLink><Link><href>http://127.0.0.1:9/zonas.kml</href></Link></NetworkLink>';

        $this->assertRejected($this->kml($link), 'no contiene polígonos');
    }

    public function test_it_ignores_an_href_inside_a_placemark(): void
    {
        $style = '<Style><IconStyle><Icon><href>http://127.0.0.1:9/i.png</href></Icon></IconStyle></Style>';
        $body = "<Placemark><name>Centro</name>{$style}".$this->polygon(self::RING).'</Placemark>';

        $this->assertCount(1, $this->read($this->kml($body))->entries);
    }

    public function test_it_rejects_a_doctype(): void
    {
        $xml = '<?xml version="1.0"?><!DOCTYPE kml><kml>'.$this->placemark('Centro').'</kml>';

        $this->assertRejected($xml, 'DOCTYPE');
    }

    public function test_it_rejects_a_doctype_written_in_utf_16(): void
    {
        $xml = '<?xml version="1.0" encoding="UTF-16"?><!DOCTYPE kml><kml>'.$this->placemark('Centro').'</kml>';

        $this->assertRejected("\xFF\xFE".mb_convert_encoding($xml, 'UTF-16LE', 'UTF-8'), 'DOCTYPE');
    }

    public function test_it_rejects_an_internal_entity(): void
    {
        $xml = '<?xml version="1.0"?><!DOCTYPE kml [<!ENTITY nombre "Centro">]><kml>'
            .$this->placemark('&nombre;').'</kml>';

        $this->assertRejected($xml, 'DOCTYPE');
    }

    public function test_it_rejects_an_entity_expansion_bomb(): void
    {
        $entities = '<!ENTITY a "aaaaaaaaaa">';

        foreach (range('b', 'k') as $letter) {
            $previous = chr(ord($letter) - 1);
            $entities .= "<!ENTITY {$letter} \"".str_repeat("&{$previous};", 10).'">';
        }

        $xml = "<?xml version=\"1.0\"?><!DOCTYPE kml [{$entities}]><kml>".$this->placemark('&k;').'</kml>';

        // libxml may stop it while parsing, so the reason depends on its version.
        $this->assertRejected($xml, 'El archivo');
    }

    public function test_it_rejects_an_external_entity(): void
    {
        $secret = (string) tempnam(sys_get_temp_dir(), 'kml');
        file_put_contents($secret, 'Secreto');
        $uri = 'file:///'.ltrim(str_replace('\\', '/', $secret), '/');
        $xml = "<?xml version=\"1.0\"?><!DOCTYPE kml [<!ENTITY x SYSTEM \"{$uri}\">]><kml>"
            .$this->placemark('&x;').'</kml>';

        try {
            $rejected = $this->assertRejected($xml, 'DOCTYPE');
            $this->assertStringNotContainsString('Secreto', $rejected->getMessage());
        } finally {
            unlink($secret);
        }
    }

    public function test_it_rejects_an_undeclared_entity(): void
    {
        $this->assertRejected($this->kml($this->placemark('&nombre;')), 'no es un KML legible');
    }

    public function test_it_rejects_a_kmz_with_the_advice_to_export_as_kml(): void
    {
        $this->assertRejected("PK\x03\x04contenido comprimido", 'exporta');
    }

    #[DataProvider('unreadableFiles')]
    public function test_it_rejects_what_is_not_a_readable_kml(string $contents): void
    {
        $this->assertRejected($contents, 'no es un KML legible');
    }

    /** @return array<string, array{string}> */
    public static function unreadableFiles(): array
    {
        return [
            'empty' => [''],
            'plain text' => ['esto no es xml'],
            'cut xml' => ['<?xml version="1.0"?><kml><Placemark>'],
            'other xml' => ['<?xml version="1.0"?><gpx><Placemark><name>Centro</name></Placemark></gpx>'],
        ];
    }

    public function test_it_rejects_a_file_without_polygons(): void
    {
        $point = '<Placemark><name>Torre</name><Point><coordinates>-100,20</coordinates></Point></Placemark>';

        $this->assertRejected($this->kml($point), 'no contiene polígonos');
    }

    public function test_it_rejects_a_polygon_with_a_hole_and_names_the_element(): void
    {
        $polygon = '<Polygon><outerBoundaryIs><LinearRing><coordinates>'.self::RING
            .'</coordinates></LinearRing></outerBoundaryIs><innerBoundaryIs><LinearRing><coordinates>'
            .'-99.99,20.01 -99.98,20.01 -99.98,20.02 -99.99,20.01</coordinates></LinearRing></innerBoundaryIs></Polygon>';

        $rejected = $this->assertRejected($this->kml("<Placemark><name>Con hueco</name>{$polygon}</Placemark>"), 'hueco');

        $this->assertStringContainsString('Con hueco', $rejected->getMessage());
    }

    #[DataProvider('brokenCoordinates')]
    public function test_it_rejects_unreadable_coordinates_and_names_the_element(string $coordinates): void
    {
        $body = '<Placemark><name>Rota</name>'.$this->polygon($coordinates).'</Placemark>';

        $this->assertStringContainsString('Rota', $this->assertRejected($this->kml($body), 'coordenadas')->getMessage());
    }

    /** @return array<string, array{string}> */
    public static function brokenCoordinates(): array
    {
        return [
            'text' => ['a,b c,d e,f'],
            'one value' => ['-100 -99.9 -99.8'],
            'four values' => ['-100,20,0,1 -99.9,20,0,1 -99.9,20.1,0,1'],
            'empty value' => ['-100, -99.9,20 -99.9,20.1'],
        ];
    }

    public function test_a_polygon_without_an_outer_ring_is_rejected(): void
    {
        $body = '<Placemark><name>Vacía</name><Polygon></Polygon></Placemark>';

        $this->assertStringContainsString('Vacía', $this->assertRejected($this->kml($body), 'contorno')->getMessage());
    }

    public function test_the_file_size_limit_accepts_the_exact_size_and_rejects_one_byte_more(): void
    {
        $xml = $this->kml($this->placemark('Centro'));
        $padded = $xml.str_repeat(' ', 2048 - strlen($xml));

        $this->assertCount(1, (new KmlZoneFileReader(2, 10_000))->read($padded)->entries);

        $this->expectException(ZoneFileRejected::class);
        $this->expectExceptionMessage('pesa más de');

        (new KmlZoneFileReader(2, 10_000))->read($padded.' ');
    }

    public function test_the_vertex_limit_of_the_file_counts_all_the_polygons(): void
    {
        $xml = $this->kml($this->placemark('Uno').$this->placemark('Dos'));

        $this->assertCount(2, (new KmlZoneFileReader(2048, 6))->read($xml)->entries);

        $this->expectException(ZoneFileRejected::class);
        $this->expectExceptionMessage('más de 5 vértices');

        (new KmlZoneFileReader(2048, 5))->read($xml);
    }

    public function test_it_leaves_the_xml_error_handling_as_it_was(): void
    {
        $previous = libxml_use_internal_errors(false);

        try {
            $this->assertRejected('<kml>', 'no es un KML legible');
            $this->assertFalse(libxml_use_internal_errors(false));
        } finally {
            libxml_use_internal_errors($previous);
        }
    }

    private function read(string $contents): ZoneFileContent
    {
        return (new KmlZoneFileReader(2048, 10_000))->read($contents);
    }

    /** @return list<string> */
    private function names(ZoneFileContent $content): array
    {
        return array_map(fn ($entry): string => $entry->name, $content->entries);
    }

    private function assertRejected(string $contents, string $reason): ZoneFileRejected
    {
        try {
            $this->read($contents);
        } catch (ZoneFileRejected $rejected) {
            $this->assertStringContainsString($reason, $rejected->getMessage());

            return $rejected;
        }

        $this->fail('The file was not rejected.');
    }

    private function kml(string $body): string
    {
        return '<?xml version="1.0" encoding="UTF-8"?><kml xmlns="http://www.opengis.net/kml/2.2">'.$body.'</kml>';
    }

    private function placemark(string $name): string
    {
        return "<Placemark><name>{$name}</name>".$this->polygon(self::RING).'</Placemark>';
    }

    private function polygon(string $coordinates): string
    {
        return "<Polygon><outerBoundaryIs><LinearRing><coordinates>{$coordinates}</coordinates></LinearRing></outerBoundaryIs></Polygon>";
    }
}
