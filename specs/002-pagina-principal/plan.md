# Plan 002 — Página principal

Spec: specs/002-pagina-principal/spec.md

## Archivos
| Archivo | Capa | Acción | RF |
|---|---|---|---|
| `app/Domain/Plans/Entities/PlanCard.php` | Domain | Crear | RF-8, RF-9 |
| `app/Domain/Plans/ValueObjects/PlanCardIcon.php` (enum `casa`, `control`, `maletin`, `cohete`) | Domain | Crear | RF-8 |
| `app/Domain/Plans/Services/PlanCardCatalogPolicy.php` | Domain | Crear | RF-8 |
| `app/Domain/Plans/Repositories/PlanCardRepository.php`, `PlanCardsUnavailable.php` | Domain | Crear | RF-9, RF-15 |
| `app/Application/Plans/UseCases/GetPlanCardsUseCase.php` | Application | Crear | RF-8, RF-15 |
| `app/Infrastructure/Persistence/Eloquent/PlanCardModel.php` | Infrastructure | Crear | RF-9 |
| `app/Infrastructure/Persistence/Repositories/EloquentPlanCardRepository.php` | Infrastructure | Crear | RF-9, RF-10, RF-15 |
| `app/Providers/CoverageServiceProvider.php` | Infrastructure | Modificar: enlace del repositorio | RF-9 |
| `database/migrations/2026_11_05_000006_create_plan_cards_table.php` | Infrastructure | Crear | RF-9, RF-16 |
| `database/seeders/PlanCardSeeder.php`, `DatabaseSeeder.php` | Infrastructure | Crear y modificar | RF-11, RF-31 |
| `config/site.php`, `.env.example` (`SITE_FACEBOOK_URL`, `SITE_YOUTUBE_URL`) | Infrastructure | Crear y modificar | RF-21 |
| `public/images/hero-principal.jpeg` | Presentation | Copiar de `docs/referencias/`, sin mover el original | RF-5 |
| `routes/web.php` | Presentation | Modificar: `/` con nombre `home`, sin redirección | RF-1 |
| `app/Http/Controllers/HomePageController.php` | Presentation | Crear | RF-1, RF-8, RF-21, RF-25 |
| `app/Http/Support/SocialLinks.php` | Presentation | Crear | RF-21 |
| `resources/views/partials/navigation.blade.php` | Presentation | Mover desde `pages/coverage/partials/`, con parámetro `active` | RF-3, RF-4 |
| `resources/views/partials/whatsapp-support.blade.php` | Presentation | Crear: aviso fijo y plantillas `tpl-whatsapp-*`, sacados de cobertura | RF-26, RF-27 |
| `resources/views/pages/home/index.blade.php`, `partials/{hero,plans,plan-card,advantages,footer}.blade.php` | Presentation | Crear | RF-2, RF-5 a RF-8, RF-12 a RF-15, RF-17 a RF-21, RF-24 |
| `resources/views/pages/coverage/index.blade.php`, `partials/check-bar.blade.php`, `partials/templates.blade.php` | Presentation | Modificar: navegación y aviso compartidos, `id="barra-consulta"` en la barra | RF-3, RF-17 |
| `resources/views/components/icon.blade.php` | Presentation | Modificar: iconos de tarjetas, ventajas y redes | RF-2, RF-8, RF-21 |
| `resources/css/app.css` | Presentation | Modificar | RF-6, RF-7, RF-13, RF-19, RF-20, RF-22, RF-23, RF-26 a RF-29, RF-32 |
| `resources/js/home/page.js`, `resources/js/app.js` | Presentation | Crear y modificar | RF-25 a RF-27, RF-29 |
| `resources/js/coverage/flow.js`, `resources/js/coverage/page.js` | Presentation | Modificar: evento `leave` | RF-30, RF-33 |

## Funciones puras
No hay reglas que dependan de la fecha, así que ninguna recibe "hoy".
- `PlanCardCatalogPolicy::visibleCards(array $cards): array`: quita las tarjetas incompletas (valores vacíos o con solo espacios, velocidad nula o no positiva) y ordena por posición, sin posición al final, nombre e id.
- `PlanCardIcon::tryFrom(string $key)`: `null` significa icono por defecto.
- `SocialLinks::configured(array $urls): array`: conserva las direcciones `https://` cuyo host tiene dominio.
- `reduce(state, { type: 'leave' })` en `flow.js`: devuelve `{ ...initialState, queryId: state.queryId }`. Conservar `queryId` evita que `dispatch` lance otra consulta, y las respuestas tardías se descartan porque la fase ya no coincide.
- `buildMessage({ purpose: 'contact' })` y `buildUrl()` de `whatsapp.js`, reutilizadas para RF-25.

## Decisiones
- Tarjetas en una tabla `plan_cards` propia, sin `is_sample` ni relación con `plans`. Alternativa descartada: añadir columnas a `plans`, porque mezclaría el catálogo real con los planes de ejemplo por zona y rompería RF-16.
- `PlanCardSeeder` no hace nada si ya existe una tarjeta. Alternativa descartada: `updateOrCreate` por nombre, porque sobrescribiría o duplicaría tarjetas editadas.
- Un fallo de lectura se convierte en `PlanCardsUnavailable` en el repositorio y el caso de uso devuelve una lista vacía. Alternativa descartada: capturarlo en el controlador, porque sería una regla fuera de Application.
- Salida de `/cobertura` con el evento `pagehide`, que despacha `leave`, oculta el aviso de WhatsApp y deja pintado el estado inicial antes de que el navegador guarde la página; `pageshow` repinta los controles. Alternativa descartada: `visibilitychange`, porque también salta al abrir WhatsApp e incumpliría RF-30.
- La imagen del hero es un `<img>` con `alt`. Alternativa descartada: fondo CSS, porque no admite descripción alternativa (RF-5).
- `id="barra-consulta"` en la sección para RF-17. Alternativa descartada: reutilizar `#consulta`, porque ese id está en el formulario y dejaría fuera de la vista el título de la barra.
- Aparición de tarjetas con `IntersectionObserver`, y solo con `prefers-reduced-motion: no-preference`; sin JavaScript se ven igual. Alternativa descartada: animación CSS al cargar, porque a 360 px terminaría antes de llegar a las tarjetas.
- `home/page.js` aparte de `mountCoveragePage`, que no se monta sin formulario. Lee el número de `data-whatsapp-number`, y en el botón de icono del footer inserta el texto de RF-26 después del botón, no dentro.
- Direcciones de redes en `config/site.php`; el número de WhatsApp sigue en `config/coverage.php`. Alternativa descartada: añadirlas a `coverage.php`, porque no tienen relación con la cobertura.

## Estrategia de tests
Con `php artisan test` y `npm test`; en la lógica, la prueba va primero.
- `tests/Unit/Domain/Plans/PlanCardCatalogPolicyTest.php`, `PlanCardIconTest.php`: RF-8.
- `tests/Unit/Http/SocialLinksTest.php`: RF-21.
- `tests/Integration/Persistence/EloquentPlanCardRepositoryTest.php`: RF-9, RF-10, RF-15.
- `tests/Integration/Persistence/PlanCardSeederTest.php`: RF-11, RF-16, RF-31.
- `tests/Feature/Home/HomePageTest.php`: RF-1 a RF-5, RF-8, RF-12 a RF-15, RF-17 a RF-21, RF-24.
- `tests/Feature/Coverage/CoveragePageTest.php`: se sustituye `test_home_redirects_to_the_coverage_page` por una prueba de RF-1 en `HomePageTest`, y `test_only_the_active_option_has_a_link` pasa a exigir enlaces en "Inicio", "Planes" y "Cobertura", con `aria-current` solo en esta última.
- `tests/js/coverage/flow.test.js`, `tests/js/coverage/page.test.js`: RF-30, RF-33, incluida la respuesta tardía tras `leave`.
- `tests/js/home/page.test.js`: RF-25, RF-26, RF-27.
- Chrome DevTools a 360 y 1280 px: diseño, contraste, animaciones, Atrás con bfcache y aviso de WhatsApp.

## Cobertura de RF
| RF | Parte del plan | Prueba |
|---|---|---|
| RF-1 | `routes/web.php`, `HomePageController` | `HomePageTest` |
| RF-2 | `pages/home` | `HomePageTest`, navegador |
| RF-3, RF-4 | `partials/navigation` | `HomePageTest`, `CoveragePageTest`, navegador |
| RF-5 | `hero`, imagen pública | `HomePageTest`, navegador |
| RF-6, RF-7 | Vistas y `app.css` | Comparación en navegador a 1280 px |
| RF-8 | `PlanCardCatalogPolicy`, `PlanCardIcon`, `plan-card` | `PlanCardCatalogPolicyTest`, `PlanCardIconTest`, `HomePageTest` |
| RF-9, RF-10 | `EloquentPlanCardRepository` | `EloquentPlanCardRepositoryTest`, `HomePageTest` |
| RF-11, RF-31 | `PlanCardSeeder` | `PlanCardSeederTest` |
| RF-12, RF-13, RF-14 | `plans`, `plan-card` | `HomePageTest`, navegador |
| RF-15 | `GetPlanCardsUseCase`, `plans` | `EloquentPlanCardRepositoryTest`, `HomePageTest` |
| RF-16 | Tabla `plan_cards` propia | `PlanCardSeederTest`, suite de la spec 001 |
| RF-17 | Enlaces a `/cobertura#barra-consulta` | `HomePageTest`, navegador |
| RF-18, RF-19, RF-20 | `footer` | `HomePageTest`, navegador |
| RF-21 | `SocialLinks`, `config/site.php`, `footer` | `SocialLinksTest`, `HomePageTest` |
| RF-22, RF-23 | `app.css` | Navegador a 360 y 1280 px |
| RF-24 | `home/page.js` sin geolocalización | `HomePageTest`, navegador |
| RF-25, RF-26, RF-27 | `home/page.js`, `whatsapp-support` | `tests/js/home/page.test.js`, navegador |
| RF-28 | `app.css` | Contraste medido en navegador a 1280 px |
| RF-29, RF-32 | `app.css`, `home/page.js` | Navegador, con y sin reducción de movimiento |
| RF-30, RF-33 | `flow.js`, `page.js` | `flow.test.js`, `page.test.js`, navegador |
