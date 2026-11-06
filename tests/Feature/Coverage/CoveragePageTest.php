<?php

declare(strict_types=1);

namespace Tests\Feature\Coverage;

use DOMDocument;
use DOMElement;
use DOMNode;
use DOMXPath;
use Tests\TestCase;

final class CoveragePageTest extends TestCase
{
    private const VISIBLE = '[not(ancestor-or-self::template)]';

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_page_shows_the_rf1_sections_in_order(): void
    {
        $text = $this->visibleText();

        $expected = [
            'Inicio',
            'Encuentra el internet ideal para tu zona',
            'La disponibilidad, velocidad y precio dependen de tu ubicación y de la tecnología disponible.',
            'Consulta tu cobertura',
            'Comparte tu ubicación y descubre qué planes están disponibles en tu zona.',
            '¿Estás en el domicilio donde quieres el servicio?',
            'Usamos tu ubicación para consultar la cobertura y no la guardamos. Si abres WhatsApp, prepararemos un mensaje con tu ubicación; tú decides si lo envías',
            'Compartir ubicación',
            'Internet por Fibra Óptica',
            'Alta velocidad',
            'Conexión estable',
            'Ideal para hogar y negocio',
            'Consultar en mi zona',
            'Internet por Antena',
            'Cobertura en zonas alejadas',
            'Instalación según factibilidad',
            'Planes según ubicación',
            'Consultar en mi zona',
            '¿Cómo saber qué opción tienes?',
            'Comparte tu ubicación',
            'Validamos la tecnología disponible',
            'Te mostramos planes y precio para tu zona',
            'La cobertura está sujeta a validación técnica del domicilio.',
            '¿No sabes qué tecnología llega a tu casa?',
            'Nuestro equipo te ayuda',
            'Contáctanos por WhatsApp',
        ];

        $position = 0;

        foreach ($expected as $fragment) {
            $found = mb_strpos($text, $fragment, $position);

            $this->assertNotFalse($found, "Falta o está fuera de orden: {$fragment}");

            $position = $found + mb_strlen($fragment);
        }
    }

    public function test_navigation_shows_the_options_in_order_with_the_logo_and_whatsapp_button(): void
    {
        $xpath = $this->page();
        $nav = $this->single($xpath, '//header//nav');

        $options = [];

        foreach ($xpath->query('.//li', $nav) as $item) {
            $options[] = $this->clean($item->textContent);
        }

        $this->assertSame(['Inicio', 'Servicios', 'Planes', 'Cobertura', 'Soporte', 'Contacto'], $options);

        $logo = $this->single($xpath, '//header//img');
        $this->assertSame('Econofibra JLINK', $logo->getAttribute('alt'));
        $this->assertFileExists(public_path(ltrim((string) parse_url($logo->getAttribute('src'), PHP_URL_PATH), '/')));

        $button = $this->single($xpath, '//header//button');
        $this->assertSame('Contáctanos', $this->clean($button->textContent));
    }

    public function test_every_option_has_a_link_and_only_the_active_one_is_marked(): void
    {
        $xpath = $this->page();
        $links = [];

        foreach ($xpath->query('//header//nav//a') as $link) {
            $this->assertInstanceOf(DOMElement::class, $link);
            $links[$this->clean($link->textContent)] = $link;
        }

        $this->assertSame(['Inicio', 'Servicios', 'Planes', 'Cobertura', 'Soporte', 'Contacto'], array_keys($links));
        $this->assertSame(url('/'), $links['Inicio']->getAttribute('href'));
        $this->assertStringEndsWith('/#planes', $links['Planes']->getAttribute('href'));
        $this->assertStringEndsWith('/cobertura', $links['Cobertura']->getAttribute('href'));
        $this->assertStringEndsWith('/servicios', $links['Servicios']->getAttribute('href'));
        $this->assertStringEndsWith('/soporte', $links['Soporte']->getAttribute('href'));
        $this->assertStringEndsWith('/contacto', $links['Contacto']->getAttribute('href'));

        $this->assertSame('page', $links['Cobertura']->getAttribute('aria-current'));
        $this->assertCount(1, $xpath->query('//header//*[@aria-current]'));

        $this->assertSame(url('/'), $this->single($xpath, '//header//a[.//img]')->getAttribute('href'));
    }

    public function test_confirmation_starts_unanswered_and_the_share_button_inactive(): void
    {
        $xpath = $this->page();

        $options = $xpath->query('//form//input[@type="radio"]');

        $this->assertCount(2, $options);

        foreach ($options as $option) {
            $this->assertInstanceOf(DOMElement::class, $option);
            $this->assertFalse($option->hasAttribute('checked'));
        }

        $labels = [];

        foreach ($xpath->query('//form//label[.//input[@type="radio"]]') as $label) {
            $labels[] = $this->clean($label->textContent);
        }

        $this->assertSame(['Sí', 'No'], $labels);

        $share = $this->single($xpath, '//form//button[contains(., "Compartir ubicación")]');
        $this->assertTrue($share->hasAttribute('disabled'));
    }

    public function test_form_gives_the_browser_the_endpoint_and_the_configured_timeouts(): void
    {
        config([
            'coverage.location_timeout_seconds' => 33,
            'coverage.server_timeout_seconds' => 44,
        ]);

        $form = $this->single($this->page(), '//form[@id="consulta"]');

        $this->assertSame('/api/coverage/check', $form->getAttribute('data-coverage-endpoint'));
        $this->assertSame('33', $form->getAttribute('data-coverage-location-timeout'));
        $this->assertSame('44', $form->getAttribute('data-coverage-server-timeout'));
    }

    public function test_form_gives_the_browser_the_configured_whatsapp_number(): void
    {
        config(['coverage.whatsapp_number' => '+00 000 000 0000']);

        $form = $this->single($this->page(), '//form[@id="consulta"]');

        $this->assertSame('+00 000 000 0000', $form->getAttribute('data-coverage-whatsapp-number'));
    }

    public function test_form_gives_the_browser_no_whatsapp_number_when_none_is_configured(): void
    {
        config(['coverage.whatsapp_number' => null]);

        $form = $this->single($this->page(), '//form[@id="consulta"]');

        $this->assertSame('', $form->getAttribute('data-coverage-whatsapp-number'));
    }

    public function test_whatsapp_fallback_area_starts_hidden_and_empty(): void
    {
        $area = $this->single($this->page(), '//*[@data-whatsapp-fallback]');

        $this->assertTrue($area->hasAttribute('hidden'));
        $this->assertSame('', $this->clean($area->textContent));
    }

    // El aviso es fijo: sin este espacio tapa el botón final a 360 px y no se puede pulsar (RF-4, RF-44).
    public function test_page_reserves_room_below_the_final_button_while_the_fallback_is_visible(): void
    {
        $css = file_get_contents(resource_path('css/app.css'));

        $this->assertMatchesRegularExpression(
            '/:has\(\.whatsapp-fallback:not\(\[hidden\]\)\)\s+\.final-call__inner\s*\{[^}]*padding-bottom:/',
            $css,
        );
    }

    public function test_form_has_no_address_fields(): void
    {
        $xpath = $this->page();

        $this->assertCount(0, $xpath->query('//input[not(@type="radio")] | //textarea | //select'));

        $text = $this->allText();

        foreach (['Código postal', 'Colonia', 'Calle y número', 'Ingresa tu dirección', 'Consultar disponibilidad', 'para tu domicilio'] as $removed) {
            $this->assertStringNotContainsString($removed, $text);
        }
    }

    public function test_privacy_notice_comes_before_the_share_button(): void
    {
        $text = $this->visibleText();

        $notice = mb_strpos($text, 'no la guardamos');
        $share = mb_strpos($text, 'Compartir ubicación');

        $this->assertNotFalse($notice);
        $this->assertNotFalse($share);
        $this->assertLessThan($share, $notice);
    }

    public function test_results_zone_is_hidden_and_right_below_the_coverage_bar(): void
    {
        $xpath = $this->page();

        $results = $this->single($xpath, '//*[@id="resultados"]');
        $this->assertTrue($results->hasAttribute('hidden'));

        $previous = $results->previousElementSibling;
        $this->assertInstanceOf(DOMElement::class, $previous);
        $this->assertCount(1, $xpath->query('.//form[@id="consulta"]', $previous));
        $this->assertSame('', $this->clean($results->textContent));
    }

    public function test_both_technology_cards_lead_to_the_form(): void
    {
        $xpath = $this->page();

        $links = $xpath->query('//main//a[contains(., "Consultar en mi zona")]');

        $this->assertCount(2, $links);

        foreach ($links as $link) {
            $this->assertInstanceOf(DOMElement::class, $link);
            $this->assertSame('#consulta', $link->getAttribute('href'));
        }

        $this->single($xpath, '//form[@id="consulta"]');
    }

    public function test_temporary_images_exist_on_the_server(): void
    {
        $xpath = $this->page();
        $sources = [];

        foreach ($xpath->query('//main//img') as $image) {
            $this->assertInstanceOf(DOMElement::class, $image);
            $sources[] = ltrim((string) parse_url($image->getAttribute('src'), PHP_URL_PATH), '/');
        }

        $this->assertCount(3, $sources);

        foreach ($sources as $source) {
            $this->assertStringStartsWith('images/temporal/', $source);
            $this->assertStringEndsWith('.svg', $source);
            $this->assertFileExists(public_path($source));
        }
    }

    public function test_templates_carry_the_literal_texts_of_results_and_messages(): void
    {
        $text = $this->templateText();

        foreach ([
            'Datos de ejemplo: estas zonas, planes y precios no son reales',
            'Precio e instalación de tu zona, sujetos a validación técnica del domicilio',
            'Contratar',
            'Consultar de nuevo',
            'Recomendada',
            'No pudimos procesar tu ubicación',
            'WhatsApp no disponible por ahora',
            'Si WhatsApp no se abrió, escríbenos al',
        ] as $literal) {
            $this->assertStringContainsString($literal, $text);
        }

        $this->assertStringContainsString('Para cambiar tu respuesta, usa Consultar de nuevo', $this->allText());
    }

    public function test_every_message_has_its_template(): void
    {
        $xpath = $this->page();

        foreach ([
            'tpl-sample-notice',
            'tpl-technologies',
            'tpl-technology',
            'tpl-plan',
            'tpl-scenario-d',
            'tpl-scenario-e-review',
            'tpl-scenario-e-location',
            'tpl-not-at-address',
            'tpl-location-denied',
            'tpl-location-unavailable',
            'tpl-location-unsupported',
            'tpl-rejected',
            'tpl-system-error',
            'tpl-new-query',
            'tpl-whatsapp-unavailable',
            'tpl-whatsapp-fallback',
        ] as $id) {
            $this->single($xpath, "//template[@id='{$id}']");
        }
    }

    public function test_interface_has_no_written_prices_plans_or_speeds(): void
    {
        $text = $this->allText();

        $this->assertDoesNotMatchRegularExpression('/\$\s*\d/', $text);
        $this->assertDoesNotMatchRegularExpression('/\d\s*(Mbps|Gbps|megas)/i', $text);
    }

    public function test_no_text_claims_the_address_was_verified_or_guarantees_service(): void
    {
        $text = mb_strtolower($this->allText());

        foreach (['verificado', 'verificamos', 'domicilio confirmado', 'garantiza', 'garantizado'] as $claim) {
            $this->assertStringNotContainsString($claim, $text);
        }
    }

    private function page(): DOMXPath
    {
        $html = $this->get('/cobertura')->assertOk()->getContent();

        $document = new DOMDocument();
        $previous = libxml_use_internal_errors(true);
        $document->loadHTML('<?xml encoding="utf-8"?>'.$html);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        foreach (['script', 'style'] as $tag) {
            foreach (iterator_to_array($document->getElementsByTagName($tag)) as $node) {
                $node->parentNode?->removeChild($node);
            }
        }

        return new DOMXPath($document);
    }

    private function single(DOMXPath $xpath, string $query): DOMElement
    {
        $nodes = $xpath->query($query);

        $this->assertCount(1, $nodes, "Se esperaba un solo elemento: {$query}");

        $node = $nodes->item(0);
        $this->assertInstanceOf(DOMElement::class, $node);

        return $node;
    }

    private function visibleText(): string
    {
        return $this->textOf($this->page()->query('//body//text()'.self::VISIBLE));
    }

    private function templateText(): string
    {
        return $this->textOf($this->page()->query('//template//text()'));
    }

    private function allText(): string
    {
        return $this->textOf($this->page()->query('//body//text()'));
    }

    /**
     * @param  iterable<DOMNode>  $nodes
     */
    private function textOf(iterable $nodes): string
    {
        $text = '';

        foreach ($nodes as $node) {
            $text .= $node->textContent;
        }

        return $this->clean($text);
    }

    private function clean(string $text): string
    {
        return trim((string) preg_replace('/\s+/u', ' ', $text));
    }
}
