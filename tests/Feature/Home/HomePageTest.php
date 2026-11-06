<?php

declare(strict_types=1);

namespace Tests\Feature\Home;

use App\Infrastructure\Persistence\Eloquent\PlanCardModel;
use Database\Seeders\PlanCardSeeder;
use DOMDocument;
use DOMElement;
use DOMXPath;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

final class HomePageTest extends TestCase
{
    use RefreshDatabase;

    private const NOTICE = 'Disponibilidad y precio sujetos a la cobertura de tu zona';

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        config(['site.social' => ['facebook' => null, 'youtube' => null]]);
    }

    public function test_root_shows_the_home_page_without_redirecting(): void
    {
        $this->seed(PlanCardSeeder::class);

        $this->get('/')->assertOk()->assertViewIs('pages.home.index');
    }

    public function test_page_shows_the_rf2_sections_in_order(): void
    {
        $this->seed(PlanCardSeeder::class);

        $text = $this->text($this->page());
        $position = 0;

        foreach ([
            'Inicio',
            'Contacto',
            'Contáctanos',
            'Conectando tu mundo con mayor velocidad',
            'Internet de fibra óptica confiable, estable y rápido para tu hogar o negocio.',
            '¡Contrata Ahora!',
            'Alta Velocidad',
            'Conexión Estable',
            'Soporte Técnico',
            'Cobertura en tu Zona',
            'Nuestros Planes',
            'Elige el plan ideal para ti',
            'Hogar Conectado',
            'Gaming',
            'Negocios',
            'Premium',
            self::NOTICE,
            '¿Por qué elegir Econofibra JLINK?',
            'Internet de calidad',
            'Atención personalizada',
            'Instalación profesional',
            'Soporte técnico siempre contigo',
            '¡Estamos listos para conectarte!',
            'Contáctanos por WhatsApp',
            'Tu mejor conexión, nuestro compromiso',
        ] as $fragment) {
            $found = mb_strpos($text, $fragment, $position);

            $this->assertNotFalse($found, "Falta o está fuera de orden: {$fragment}");

            $position = $found + mb_strlen($fragment);
        }
    }

    public function test_navigation_has_the_shared_options_destinations_and_active_home(): void
    {
        $xpath = $this->page();

        $options = [];

        foreach ($xpath->query('//header//nav//li') as $item) {
            $options[] = $this->clean($item->textContent);
        }

        $this->assertSame(['Inicio', 'Servicios', 'Cobertura', 'Soporte', 'Contacto'], $options);

        $links = [];

        foreach ($xpath->query('//header//nav//a') as $link) {
            $this->assertInstanceOf(DOMElement::class, $link);
            $links[$this->clean($link->textContent)] = $link;
        }

        $this->assertSame(['Inicio', 'Servicios', 'Cobertura', 'Soporte', 'Contacto'], array_keys($links));
        $this->assertSame(url('/'), $links['Inicio']->getAttribute('href'));
        $this->assertStringEndsWith('/cobertura', $links['Cobertura']->getAttribute('href'));
        $this->assertStringEndsWith('/servicios', $links['Servicios']->getAttribute('href'));
        $this->assertStringEndsWith('/soporte', $links['Soporte']->getAttribute('href'));
        $this->assertStringEndsWith('/contacto', $links['Contacto']->getAttribute('href'));

        $this->assertSame('page', $links['Inicio']->getAttribute('aria-current'));
        $this->assertCount(1, $xpath->query('//header//*[@aria-current]'));

        $this->assertSame(url('/'), $this->single($xpath, '//header//a[.//img]')->getAttribute('href'));
        $this->assertSame('Contáctanos', $this->clean($this->single($xpath, '//header//button[@data-whatsapp="contact"]')->textContent));
    }

    public function test_hero_uses_the_real_photo_with_a_description_and_real_text(): void
    {
        $xpath = $this->page();

        $image = $this->single($xpath, '//main//section[.//h1]//img');
        $path = ltrim((string) parse_url($image->getAttribute('src'), PHP_URL_PATH), '/');

        $this->assertSame('images/hero-principal.jpeg', $path);
        $this->assertFileEquals(base_path('docs/referencias/hero-principal.jpeg'), public_path($path));
        $this->assertNotSame('', trim($image->getAttribute('alt')));

        $this->assertSame('Conectando tu mundo con mayor velocidad', $this->clean($this->single($xpath, '//h1')->textContent));
    }

    public function test_cards_come_from_the_database_with_their_icon_in_position_order(): void
    {
        $this->card(['name' => 'Tercera', 'speed_mbps' => 75, 'phrase' => 'Frase tres', 'icon_key' => 'cohete', 'position' => null]);
        $this->card(['name' => 'Segunda', 'speed_mbps' => 60, 'phrase' => 'Frase dos', 'icon_key' => 'maletin', 'position' => 2]);
        $this->card(['name' => 'Primera', 'speed_mbps' => 40, 'phrase' => 'Frase uno', 'icon_key' => 'control', 'position' => 1]);

        $xpath = $this->page();
        $cards = $xpath->query('//*[@data-plan-card]');

        $this->assertCount(3, $cards);

        $expected = [
            ['Primera', '40 Mbps', 'Frase uno', 'control'],
            ['Segunda', '60 Mbps', 'Frase dos', 'maletin'],
            ['Tercera', '75 Mbps', 'Frase tres', 'cohete'],
        ];

        foreach ($expected as $index => [$name, $speed, $phrase, $icon]) {
            $card = $cards->item($index);
            $text = $this->clean($card->textContent);

            $this->assertStringContainsString($name, $text);
            $this->assertStringContainsString($speed, $text);
            $this->assertStringContainsString($phrase, $text);
            $this->assertCount(1, $xpath->query(".//*[@data-plan-icon='{$icon}']", $card));
            $this->assertCount(1, $xpath->query('.//a[contains(., "Contratar")]', $card));
        }
    }

    public function test_incomplete_cards_are_left_out(): void
    {
        $this->card(['name' => 'Completa']);
        $this->card(['name' => 'Sin velocidad', 'speed_mbps' => 0]);
        $this->card(['name' => 'Sin frase', 'phrase' => '   ']);

        $text = $this->text($this->page());

        $this->assertStringContainsString('Completa', $text);
        $this->assertStringNotContainsString('Sin velocidad', $text);
        $this->assertStringNotContainsString('Sin frase', $text);
    }

    public function test_an_unknown_icon_key_uses_the_default_icon(): void
    {
        $this->card(['icon_key' => 'desconocido']);

        $this->single($this->page(), '//*[@data-plan-card]//*[@data-plan-icon="conectividad"]');
    }

    public function test_a_change_in_the_database_is_shown_on_the_next_load(): void
    {
        $card = $this->card(['name' => 'Antes', 'speed_mbps' => 50, 'icon_key' => 'casa']);

        $this->assertStringContainsString('Antes', $this->text($this->page()));

        $card->update(['name' => 'Después', 'speed_mbps' => 80, 'icon_key' => 'cohete', 'is_popular' => true]);

        $xpath = $this->page();
        $text = $this->text($xpath);

        $this->assertStringContainsString('Después', $text);
        $this->assertStringContainsString('80 Mbps', $text);
        $this->assertStringNotContainsString('Antes', $text);
        $this->single($xpath, '//*[@data-plan-card]//*[@data-plan-icon="cohete"]');
        $this->single($xpath, '//*[contains(@class, "plan-card--popular")]');
    }

    public function test_only_marked_cards_are_highlighted(): void
    {
        $this->card(['name' => 'Normal']);
        $this->card(['name' => 'Destacada uno', 'is_popular' => true]);
        $this->card(['name' => 'Destacada dos', 'is_popular' => true]);

        $xpath = $this->page();

        $highlighted = $xpath->query('//*[@data-plan-card][contains(@class, "plan-card--popular")]');

        $this->assertCount(2, $highlighted);

        foreach ($highlighted as $card) {
            $this->assertStringContainsString('Más Popular', $card->textContent);
            $this->assertStringNotContainsString('Normal', $card->textContent);
        }

        $this->assertSame(2, mb_substr_count($this->text($xpath), 'Más Popular'));
    }

    public function test_only_the_buttons_of_normal_cards_have_an_arrow(): void
    {
        $this->card(['name' => 'Normal']);
        $this->card(['name' => 'Destacada', 'is_popular' => true]);

        $xpath = $this->page();

        $this->assertSame(2, $xpath->query('//*[contains(@class, "plan-card__action")]')->length);
        $this->assertSame(1, $xpath->query('//*[contains(@class, "plan-card__action")]/*[local-name() = "svg"]')->length);
        $this->assertSame(0, $xpath->query('//*[contains(@class, "plan-card--popular")]//*[contains(@class, "plan-card__action")]/*[local-name() = "svg"]')->length);
    }

    public function test_no_marked_card_means_no_popular_label(): void
    {
        $this->card();

        $this->assertStringNotContainsString('Más Popular', $this->text($this->page()));
    }

    public function test_the_notice_is_shown_with_cards(): void
    {
        $this->card();

        $this->assertStringContainsString(self::NOTICE, $this->text($this->page()));
    }

    public function test_without_cards_the_section_keeps_title_notice_and_coverage_button(): void
    {
        $this->assertPlansFallback();
    }

    public function test_without_complete_cards_the_section_keeps_title_notice_and_coverage_button(): void
    {
        $this->card(['speed_mbps' => null]);

        $this->assertPlansFallback();
    }

    public function test_a_read_failure_shows_the_rest_of_the_page(): void
    {
        Schema::drop('plan_cards');

        $this->assertPlansFallback();
    }

    public function test_page_has_no_prices_recommendation_or_availability_claims(): void
    {
        $this->seed(PlanCardSeeder::class);

        $text = mb_strtolower($this->text($this->page()));

        $this->assertDoesNotMatchRegularExpression('/\$\s*\d/', $text);

        foreach ([
            '/mes',
            'costo',
            'precio de instalación',
            'recomendada',
            'te recomendamos',
            'disponible en tu domicilio',
            'disponible en tu zona',
            'datos de ejemplo',
        ] as $forbidden) {
            $this->assertStringNotContainsString($forbidden, $text);
        }
    }

    public function test_hire_buttons_go_to_the_coverage_bar_without_whatsapp(): void
    {
        $this->seed(PlanCardSeeder::class);

        $links = $this->page()->query('//main//a[contains(., "Contratar") or contains(., "¡Contrata Ahora!")]');

        $this->assertCount(5, $links);

        foreach ($links as $link) {
            $this->assertInstanceOf(DOMElement::class, $link);
            $this->assertStringEndsWith('/cobertura#barra-consulta', $link->getAttribute('href'));
            $this->assertFalse($link->hasAttribute('data-whatsapp'));
        }
    }

    public function test_the_coverage_page_has_the_target_of_the_hire_buttons(): void
    {
        $xpath = $this->parse($this->get('/cobertura')->assertOk()->getContent());

        $this->single($xpath, '//section[@id="barra-consulta"][.//h2[contains(., "Consulta tu cobertura")]]');
    }

    public function test_footer_has_logo_on_a_white_tab_slogan_and_whatsapp_button(): void
    {
        $xpath = $this->page();

        $footer = $this->single($xpath, '//footer');

        $this->assertStringContainsString('Tu mejor conexión, nuestro compromiso', $this->clean($footer->textContent));
        $this->assertSame('Econofibra JLINK', $this->single($xpath, '//footer//*[contains(@class, "logo-tab")]//img')->getAttribute('alt'));

        $button = $this->single($xpath, '//footer//button[@data-whatsapp="contact"]');
        $this->assertSame('Contáctanos por WhatsApp', $button->getAttribute('aria-label'));
    }

    public function test_plans_announce_the_configured_starting_price(): void
    {
        config(['site.plans_from_price' => 350]);

        $this->get('/')->assertOk()->assertSee('Planes desde $350 pesos');
    }

    public function test_plans_hide_the_starting_price_when_it_is_not_configured(): void
    {
        config(['site.plans_from_price' => null]);

        $this->get('/')->assertOk()->assertDontSee('Planes desde');
    }

    public function test_footer_hides_the_networks_that_are_not_configured(): void
    {
        $this->assertCount(0, $this->page()->query('//footer//a'));
    }

    public function test_footer_shows_only_the_configured_network_in_a_new_tab(): void
    {
        config(['site.social' => ['facebook' => 'https://facebook.com/econofibra', 'youtube' => 'http://youtube.com/x']]);

        $link = $this->single($this->page(), '//footer//a');

        $this->assertSame('https://facebook.com/econofibra', $link->getAttribute('href'));
        $this->assertSame('_blank', $link->getAttribute('target'));
        $this->assertStringContainsString('noopener', $link->getAttribute('rel'));
        $this->assertSame('Facebook', $link->getAttribute('aria-label'));
    }

    public function test_footer_shows_both_networks_when_configured(): void
    {
        config(['site.social' => ['facebook' => 'https://facebook.com/econofibra', 'youtube' => 'https://youtube.com/@econofibra']]);

        $labels = [];

        foreach ($this->page()->query('//footer//a') as $link) {
            $this->assertInstanceOf(DOMElement::class, $link);
            $labels[] = $link->getAttribute('aria-label');
        }

        $this->assertSame(['Facebook', 'YouTube'], $labels);
    }

    public function test_page_does_not_mount_the_location_query(): void
    {
        $xpath = $this->page();

        $this->assertCount(0, $xpath->query('//form | //*[@data-coverage-form] | //*[@data-coverage-share]'));
    }

    public function test_page_gives_the_browser_the_configured_whatsapp_number(): void
    {
        config(['coverage.whatsapp_number' => '+00 000 000 0000']);

        $element = $this->single($this->page(), '//*[@data-whatsapp-number]');

        $this->assertSame('+00 000 000 0000', $element->getAttribute('data-whatsapp-number'));
    }

    public function test_whatsapp_fallback_area_starts_hidden_and_empty(): void
    {
        $area = $this->single($this->page(), '//*[@data-whatsapp-fallback]');

        $this->assertTrue($area->hasAttribute('hidden'));
        $this->assertSame('', trim($area->textContent));
    }

    public function test_page_has_the_whatsapp_templates(): void
    {
        $xpath = $this->page();

        $this->assertSame(
            'WhatsApp no disponible por ahora',
            $this->clean($this->single($xpath, "//template[@id='tpl-whatsapp-unavailable']")->textContent),
        );
        $this->assertStringContainsString(
            'Si WhatsApp no se abrió, escríbenos al',
            $this->clean($this->single($xpath, "//template[@id='tpl-whatsapp-fallback']")->textContent),
        );
    }

    public function test_page_reserves_room_below_the_footer_while_the_fallback_is_visible(): void
    {
        $css = (string) file_get_contents(resource_path('css/app.css'));

        $this->assertMatchesRegularExpression(
            '/:has\(\.whatsapp-fallback:not\(\[hidden\]\)\)\s+\.site-footer\s*\{[^}]*padding-bottom:/',
            $css,
        );
    }

    public function test_footer_icons_keep_their_size_next_to_a_long_note(): void
    {
        $css = (string) file_get_contents(resource_path('css/app.css'));

        $this->assertMatchesRegularExpression('/\.social-icon\s*\{[^}]*flex-shrink:\s*0;/', $css);
    }

    public function test_hero_gradient_follows_the_text_at_every_desktop_width(): void
    {
        $css = (string) file_get_contents(resource_path('css/app.css'));

        $this->assertMatchesRegularExpression('/\.home-hero::after\s*\{[^}]*--hero-text-left:\s*max\(0px,/', $css);
    }

    public function test_hero_indicators_have_only_the_two_separators_of_the_mockup(): void
    {
        $css = (string) file_get_contents(resource_path('css/app.css'));

        $this->assertMatchesRegularExpression('/\.home-hero__benefits li \+ li \+ li\s*\{[^}]*border-left:/', $css);
        $this->assertDoesNotMatchRegularExpression('/\.home-hero__benefits li \+ li\s*\{[^}]*border-left:/', $css);
    }

    private function assertPlansFallback(): void
    {
        $xpath = $this->page();
        $text = $this->text($xpath);

        $this->assertStringContainsString('Nuestros Planes', $text);
        $this->assertStringNotContainsString('Elige el plan ideal para ti', $text);
        $this->assertStringContainsString(self::NOTICE, $text);
        $this->assertStringContainsString('¿Por qué elegir Econofibra JLINK?', $text);
        $this->assertCount(0, $xpath->query('//*[@data-plan-card]'));

        $link = $this->single($xpath, '//section[@id="planes"]//a[contains(., "Consultar en mi zona")]');
        $this->assertStringEndsWith('/cobertura#barra-consulta', $link->getAttribute('href'));
    }

    /** @param array<string, mixed> $values */
    private function card(array $values = []): PlanCardModel
    {
        return PlanCardModel::query()->create($values + [
            'name' => 'Tarjeta',
            'speed_mbps' => 50,
            'phrase' => 'Una frase',
            'icon_key' => 'casa',
            'position' => 1,
            'is_popular' => false,
        ]);
    }

    private function page(): DOMXPath
    {
        return $this->parse($this->get('/')->assertOk()->getContent());
    }

    private function parse(string $html): DOMXPath
    {
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

    private function text(DOMXPath $xpath): string
    {
        $text = '';

        foreach ($xpath->query('//body//text()') as $node) {
            $text .= $node->textContent;
        }

        return $this->clean($text);
    }

    private function clean(string $text): string
    {
        return trim((string) preg_replace('/\s+/u', ' ', $text));
    }
}
