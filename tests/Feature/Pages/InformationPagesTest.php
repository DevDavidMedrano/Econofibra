<?php

declare(strict_types=1);

namespace Tests\Feature\Pages;

use DOMDocument;
use DOMElement;
use DOMXPath;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

final class InformationPagesTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        config(['site.social' => ['facebook' => null, 'youtube' => null]]);
    }

    /**
     * @return array<string, array{string, string, list<string>}>
     */
    public static function pages(): array
    {
        return [
            'servicios' => ['/servicios', 'Servicios', [
                'Internet por Fibra Óptica',
                'Internet por Antena',
                'La disponibilidad de cada tecnología depende de tu zona',
                'Consultar en mi zona',
            ]],
            'soporte' => ['/soporte', 'Soporte', [
                'Soporte Técnico',
                'Revisa que los cables',
                'Reinicia el módem',
                'Pide ayuda por WhatsApp',
            ]],
            'contacto' => ['/contacto', 'Contacto', [
                'Escríbenos por WhatsApp',
                'Consulta tu cobertura',
            ]],
        ];
    }

    /**
     * @param  list<string>  $fragments
     */
    #[DataProvider('pages')]
    public function test_page_responds_with_its_key_texts_and_active_option(string $path, string $option, array $fragments): void
    {
        $xpath = $this->page($path);
        $text = $this->text($xpath);

        foreach ($fragments as $fragment) {
            $this->assertStringContainsString($fragment, $text);
        }

        $active = $this->single($xpath, '//header//nav//a[@aria-current="page"]');

        $this->assertSame($option, $this->clean($active->textContent));
        $this->assertStringEndsWith($path, $active->getAttribute('href'));
    }

    /**
     * @param  list<string>  $fragments
     */
    #[DataProvider('pages')]
    public function test_page_writes_no_prices_or_contact_data_and_has_no_form(string $path, string $option, array $fragments): void
    {
        $xpath = $this->page($path);

        $this->assertDoesNotMatchRegularExpression('/\$\s*\d|\bMXN\b|\bMbps\b|\/\s*mes\b/iu', $this->text($xpath));
        $this->assertCount(0, $xpath->query('//a[starts-with(@href, "tel:") or starts-with(@href, "mailto:")]'));
        $this->assertCount(0, $xpath->query('//form'));
    }

    /**
     * @param  list<string>  $fragments
     */
    #[DataProvider('pages')]
    public function test_page_shares_the_whatsapp_state_and_the_footer(string $path, string $option, array $fragments): void
    {
        config(['coverage.whatsapp_number' => '+00 000 000 0000']);

        $xpath = $this->page($path);

        $this->assertSame('+00 000 000 0000', $this->single($xpath, '//main')->getAttribute('data-whatsapp-number'));
        $this->assertGreaterThanOrEqual(2, $xpath->query('//button[@data-whatsapp="contact"]')->length);
        $this->single($xpath, '//footer');
        $this->single($xpath, '//template[@id="tpl-whatsapp-unavailable"]');
        $this->single($xpath, '//*[@data-whatsapp-fallback]');
    }

    public function test_services_buttons_lead_to_the_coverage_bar(): void
    {
        $links = $this->page('/servicios')->query('//main//a[contains(., "Consultar en mi zona")]');

        $this->assertCount(2, $links);

        foreach ($links as $link) {
            $this->assertInstanceOf(DOMElement::class, $link);
            $this->assertStringEndsWith('/cobertura#barra-consulta', $link->getAttribute('href'));
        }
    }

    public function test_contact_links_to_coverage_and_hides_the_networks_that_are_not_configured(): void
    {
        $xpath = $this->page('/contacto');

        $this->assertStringEndsWith('/cobertura', $this->single($xpath, '//main//a')->getAttribute('href'));
        $this->assertStringNotContainsString('Facebook', $this->text($xpath));
        $this->assertStringNotContainsString('YouTube', $this->text($xpath));
    }

    public function test_contact_shows_only_the_configured_network(): void
    {
        config(['site.social.facebook' => 'https://facebook.example/econofibra']);

        $xpath = $this->page('/contacto');
        $link = $this->single($xpath, '//main//a[contains(., "Facebook")]');

        $this->assertSame('https://facebook.example/econofibra', $link->getAttribute('href'));
        $this->assertStringNotContainsString('YouTube', $this->text($xpath));
    }

    private function page(string $path): DOMXPath
    {
        $document = new DOMDocument;
        $previous = libxml_use_internal_errors(true);
        $document->loadHTML('<?xml encoding="utf-8"?>'.$this->get($path)->assertOk()->getContent());
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        return new DOMXPath($document);
    }

    public function test_contact_and_footer_offer_the_configured_phones_as_call_links(): void
    {
        config(['site.phones' => '8680000001, 123, 8680000002']);

        $xpath = $this->page('/contacto');

        foreach (['//main', '//footer'] as $area) {
            $links = $xpath->query($area.'//a[starts-with(@href, "tel:")]');

            $this->assertCount(2, $links);
            $this->assertInstanceOf(DOMElement::class, $links->item(0));
            $this->assertSame('tel:+528680000001', $links->item(0)->getAttribute('href'));
            $this->assertSame('868 000 0001', $this->clean($links->item(0)->textContent));
        }
    }

    public function test_contact_hides_the_phones_when_none_is_configured(): void
    {
        config(['site.phones' => null]);

        $xpath = $this->page('/contacto');

        $this->assertCount(0, $xpath->query('//a[starts-with(@href, "tel:")]'));
        $this->assertStringNotContainsString('Llámanos', $this->text($xpath));
    }

    private function single(DOMXPath $xpath, string $query): DOMElement
    {
        $nodes = $xpath->query($query);

        $this->assertCount(1, $nodes, "Se esperaba un solo elemento: {$query}");

        $node = $nodes->item(0);
        $this->assertInstanceOf(DOMElement::class, $node);

        return $node;
    }

    private function text(DOMXPath $xpath): string
    {
        $text = '';

        foreach ($xpath->query('//body//text()[not(ancestor::template)]') as $node) {
            $text .= $node->textContent;
        }

        return $this->clean($text);
    }

    private function clean(string $text): string
    {
        return trim((string) preg_replace('/\s+/u', ' ', $text));
    }
}
