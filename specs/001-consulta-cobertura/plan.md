# Plan 001 — Consulta de cobertura

Spec: specs/001-consulta-cobertura/spec.md

## Creación de Laravel (T1)
La carpeta no está vacía, así que el proyecto se crea en una carpeta temporal y se copia sin tocar lo existente.

```powershell
$raiz = "C:\Pagina web ISP"
$tmp  = Join-Path $env:TEMP "econofibra-laravel"
$protegidos = "AGENTS.md","MEMORY.md",".mcp.json","docs\constitution.md"
$antes = $protegidos | ForEach-Object { Get-FileHash (Join-Path $raiz $_) }

composer create-project laravel/laravel $tmp --no-interaction
robocopy $tmp $raiz /E /XD vendor node_modules .git .claude docs specs /XF .gitignore AGENTS.md MEMORY.md CLAUDE.md .mcp.json
# robocopy termina bien con códigos de salida de 0 a 7.
```

Después:
1. Combinar `.gitignore`: se conserva el archivo actual con su orden (`.env.*` antes de `!.env.example`) y se añaden al final las líneas de `$tmp\.gitignore` que falten. Debe seguir `.claude/settings.local.json`.
2. Ejecutar `composer install` y `npm install` en la raíz.
3. Comparar los hashes con `$antes` y borrar `$tmp`.
4. Revisar `phpunit.xml`: `DB_CONNECTION=sqlite` y `DB_DATABASE=:memory:` activos, y añadir la suite `Integration` (`tests/Integration`).
5. En `.env` y `.env.example`: `DB_CONNECTION=mysql`, `DB_HOST=127.0.0.1`, `DB_PORT=3306`, `DB_DATABASE=econofibra`, `DB_USERNAME=root`, `DB_PASSWORD=` vacío, solo en `.env` de desarrollo; `SESSION_DRIVER=file`, `CACHE_STORE=file`, `QUEUE_CONNECTION=sync`.
6. La base `econofibra` se crea a mano con `CREATE DATABASE econofibra CHARACTER SET utf8mb4;` cuando MySQL esté encendido. Solo hace falta en T10.

## Archivos
| Archivo | Capa | Acción | RF |
|---|---|---|---|
| `composer.json`, `package.json`, `vite.config.js`, `phpunit.xml`, `.gitignore`, `.env.example` | Base | Crear o combinar | Todos |
| `config/coverage.php` | Infrastructure | Crear | RF-42 y umbral, tiempos, moneda |
| `app/Providers/CoverageServiceProvider.php` | Infrastructure | Crear | Enlaza contratos y umbral |
| `bootstrap/app.php` | Presentation | Modificar: registrar `routes/api.php` | RF-12 |
| `app/Domain/Coverage/ValueObjects/GeoPoint.php` | Domain | Crear | RF-12 |
| `app/Domain/Coverage/ValueObjects/PrecisionMargin.php` | Domain | Crear | RF-11, RF-12 |
| `app/Domain/Coverage/ValueObjects/ZoneBoundary.php` | Domain | Crear | RF-13, RF-33 |
| `app/Domain/Coverage/ValueObjects/TechnologyType.php`, `TechnologyStatus.php`, `CoverageScenario.php`, `CoverageReason.php` (enums) | Domain | Crear | RF-13, RF-22, RF-23 |
| `app/Domain/Coverage/Entities/CoverageZone.php`, `CoverageResult.php`, `ZoneMatch.php`, `TechnologyOffer.php` | Domain | Crear | RF-13, RF-33, RF-50 |
| `app/Domain/Coverage/Services/PrecisionPolicy.php` | Domain | Crear | RF-11 |
| `app/Domain/Coverage/Services/ZoneLocator.php` | Domain | Crear | RF-13, RF-33 |
| `app/Domain/Coverage/Services/CoverageResolver.php` | Domain | Crear | RF-13, RF-14, RF-18 a RF-24 |
| `app/Domain/Coverage/Services/TechnologyRecommender.php` | Domain | Crear | RF-21 |
| `app/Domain/Coverage/Repositories/CoverageZoneRepository.php`, `ZoneOfferRepository.php`, `SampleDataRepository.php` | Domain | Crear | RF-16, RF-26 |
| `app/Domain/Plans/Entities/InternetPlan.php` | Domain | Crear | RF-15 |
| `app/Domain/Plans/ValueObjects/Money.php` | Domain | Crear | RF-15, RF-35 |
| `app/Domain/Plans/Services/PlanCompletenessPolicy.php` | Domain | Crear | RF-14, RF-15 |
| `app/Application/Coverage/UseCases/CheckCoverageUseCase.php` | Application | Crear | RF-11, RF-13, RF-16, RF-26, RF-50 |
| `app/Application/Coverage/DTOs/CheckCoverageInput.php`, `CheckCoverageOutput.php` | Application | Crear | RF-6, RF-50 |
| `app/Infrastructure/Persistence/Eloquent/*Model.php` (zona, tecnología, estado, plan, precio) | Infrastructure | Crear | RF-16, RF-45 |
| `app/Infrastructure/Persistence/Repositories/Eloquent*Repository.php` | Infrastructure | Crear | RF-16, RF-17, RF-26 |
| `database/migrations/*` (5 tablas) | Infrastructure | Crear | RF-16, RF-45 |
| `database/seeders/TechnologySeeder.php`, `CoverageSampleSeeder.php`, `DatabaseSeeder.php` | Infrastructure | Crear | RF-46 |
| `app/Http/Requests/CheckCoverageRequest.php` | Presentation | Crear | RF-6, RF-12, RF-16 |
| `app/Http/Controllers/CoverageCheckController.php`, `CoveragePageController.php` | Presentation | Crear | RF-1, RF-13 |
| `app/Http/Resources/CoverageResultResource.php` | Presentation | Crear | RF-15, RF-26, RF-35, RF-50 |
| `routes/web.php`, `routes/api.php` | Presentation | Modificar o crear | RF-1, RF-13 |
| `resources/views/layouts/app.blade.php` | Presentation | Crear | RF-1, RF-5 |
| `resources/views/pages/coverage/index.blade.php` y `partials/*` | Presentation | Crear | RF-1 a RF-5, RF-28, RF-29, RF-48, RF-52 a RF-54 |
| `resources/css/app.css` | Presentation | Modificar | RF-4, RF-5, RF-54 |
| `public/images/temporal/*.svg` | Presentation | Crear | RF-52 |
| `resources/js/coverage/flow.js` | Presentation | Crear | RF-7, RF-8, RF-37 a RF-40, RF-47, RF-49, RF-55 |
| `resources/js/coverage/geolocation.js` | Presentation | Crear | RF-9, RF-10 |
| `resources/js/coverage/api.js` | Presentation | Crear | RF-6, RF-27, RF-32, RF-40 |
| `resources/js/coverage/presenter.js` | Presentation | Crear | RF-18 a RF-24, RF-26, RF-34 a RF-36 |
| `resources/js/coverage/whatsapp.js` | Presentation | Crear | RF-25, RF-41, RF-43, RF-44, RF-51 |
| `resources/js/coverage/page.js`, `resources/js/app.js` | Presentation | Crear o modificar | Une los módulos con la página |
| `tests/Unit`, `tests/Integration`, `tests/Feature`, `tests/js` | Pruebas | Crear | Ver Estrategia de tests |

### Tablas
| Tabla | Columnas principales |
|---|---|
| `coverage_zones` | `name`, `boundary` (JSON), `is_sample` |
| `technologies` | `code` (`fiber`, `antenna`), `name` |
| `zone_technologies` | `zone_id`, `technology_id`, `status`, `is_sample`; única por zona y tecnología |
| `plans` | `technology_id`, `name` y `speed_mbps` (ambas anulables), `is_sample` |
| `zone_plan_prices` | `zone_id`, `plan_id`, `price_cents` y `installation_cost_cents` (enteros con signo, anulables), `is_sample`; única por zona y plan |

Las columnas anulables permiten guardar planes incompletos, que el dominio filtra. La base de datos no guarda ninguna ubicación de clientes.

### Contrato del servidor
`POST /api/coverage/check` con cuerpo JSON. Las coordenadas nunca van en la URL, para que no lleguen a los registros de acceso.

Entrada: `latitude`, `longitude`, `accuracy_meters` (opcional), `address_confirmed`.

Salida 200:
```json
{
  "scenario": "A|B|C|D|E",
  "reason": "low_precision|ambiguous_location|pending_review|null",
  "zone_name": "string|null",
  "sample_data": true,
  "technologies": [{
    "code": "fiber", "name": "Fibra óptica", "recommended": true,
    "plans": [{ "name": "", "speed_mbps": 0, "price_label": "", "installation_label": "" }]
  }]
}
```
Salida 422 en una consulta rechazada (RF-12). Salida 500 en un fallo (RF-27).

## Funciones puras
Ninguna regla de esta spec depende de la fecha: no hay vigencias ni plazos, por lo que ninguna función recibe "hoy" y el dominio no usa reloj. Los dos tiempos de espera son del navegador y no son reglas de negocio. `created_at` y `updated_at` quedan en UTC según la skill `local-dates`.

### PHP (dominio)
- `PrecisionPolicy::isLow(PrecisionMargin $margin, int $thresholdMeters): bool`. Devuelve verdadero si el margen falta o es mayor que el umbral.
- `ZoneBoundary::contains(GeoPoint $point): bool`. Un punto sobre el borde cuenta como dentro.
- `ZoneBoundary::distanceInMetersTo(GeoPoint $point): float`.
- `ZoneLocator::locate(GeoPoint $point, PrecisionMargin $margin, array $zones): ZoneMatch`. Devuelve `none`, `single` o `ambiguous`:
  - Dos o más zonas contienen el punto: `ambiguous`.
  - Una zona lo contiene y otra está a una distancia igual o menor que el margen: `ambiguous`.
  - Ninguna lo contiene y alguna está a una distancia igual o menor que el margen: `ambiguous`.
  - Una zona lo contiene y ninguna otra está al alcance: `single`.
  - Ninguna lo contiene ni está al alcance: `none`.
- `PlanCompletenessPolicy::isComplete(InternetPlan $plan): bool`. Exige nombre, velocidad, precio mayor que cero y costo de instalación igual o mayor que cero.
- `CoverageResolver::resolve(?CoverageZone $zone, array $offers): CoverageResult`. Aplica los pasos 3 a 5 de RF-13 sobre los estados y planes de la zona.
- `CoverageResolver::lowPrecision(): CoverageResult` y `CoverageResolver::ambiguous(): CoverageResult`. Devuelven el Escenario E con su motivo.
- `TechnologyRecommender::recommend(array $confirmed): ?TechnologyType`. Fibra si está confirmada; si no, antena; `null` si no hay ninguna.

### JavaScript (presentación)
- `flow.reduce(state, event)`: máquina de estados de la consulta, sin DOM ni red. Selectores `canShare(state)`, `optionsEnabled(state)` y `canRetry(state)`.
- `presenter.toViewModel(response)`: indica qué bloques y textos se muestran según escenario y motivo.
- `whatsapp.buildMessage({ purpose, location, zoneName, planName })` y `whatsapp.buildUrl(number, message)`.
- `whatsapp.mapLink(location)`: `https://www.google.com/maps?q=<lat>,<lng>`.

## Decisiones
- **Pertenencia a una zona:** cada zona guarda sus polígonos como JSON de pares latitud y longitud, y el dominio aplica el algoritmo de cruce de rayos en PHP. Alternativa descartada: funciones espaciales de MySQL (`ST_Contains`) porque sacan la regla del dominio, exigen MySQL en las pruebas y SQLite no las tiene.
- **Margen contra el límite de zona:** el dominio calcula la distancia mínima del punto a cada segmento del polígono, en una proyección plana local centrada en el punto (equirrectangular, radio 6 371 008.8 m), y la compara con el margen, con el empate como alcanzada. El cálculo solo importa hasta 100 m, donde el error es despreciable. Alternativa descartada: distancia a un centroide o círculo de la zona, porque no representa el límite real, y fórmulas esféricas por segmento, porque añaden complejidad sin mejorar el resultado a esa escala.
- **Forma de las zonas:** una zona admite uno o varios polígonos sin huecos y no cruza el antimeridiano. Alternativa descartada: GeoJSON completo con huecos, porque aún no hay datos reales que lo pidan. Si el KML real trae huecos, será un cambio de `ZoneBoundary`.
- **Carga de zonas:** se leen todas las zonas en cada consulta. Alternativa descartada: índice o caja envolvente, porque una empresa local tendrá pocas zonas y se puede añadir después.
- **Pruebas sobre SQLite en memoria:** la geometría está en PHP y no se usa SQL propio de MySQL, así que `php artisan test` funciona con MySQL apagado. Alternativa descartada: MySQL en las pruebas, porque se arranca a mano y haría fallar las pruebas por el entorno. La compatibilidad con MySQL se verifica una vez en T10 con `migrate:fresh --seed`.
- **Tailwind CSS:** viene integrado con Vite en Laravel y sus variables de tema fijan los colores en un solo sitio. Alternativa descartada: CSS modular, porque obliga a escribir a mano las reglas de distribución y las variantes de 360 px y 1280 px.
- **JavaScript sin framework, con módulos ES y Vite:** cumple el stack de AGENTS.md. Alternativa descartada: Alpine o Vue, porque añaden una dependencia que la página no necesita.
- **Vitest para la lógica de JavaScript (`npm test`):** muchos RF ocurren solo en el navegador y la constitución exige prueba primero en la lógica. `php artisan test` no puede ejecutarlos. Alternativa descartada: verificarlos solo a mano en navegador, porque no se puede repetir con cada cambio.
- **Un solo caso de uso, `CheckCoverageUseCase`:** los planes, precios y recomendación son pasos de una misma consulta. Alternativa descartada: los casos de uso separados que sugiere AGENTS.md, porque en esta spec no tienen otro uso. Se pueden extraer cuando exista otra entrada.
- **`routes/api.php` registrado a mano en `bootstrap/app.php`:** la ruta queda sin sesión, de modo que la ubicación no pasa por ella. Alternativa descartada: `php artisan install:api`, porque instala autenticación que no se usa; y una ruta `web`, porque guarda la entrada en sesión al fallar la validación.
- **Espera de 20 s con temporizador propio:** el parámetro `timeout` de `getCurrentPosition` no cuenta el tiempo con el aviso de permiso abierto, y la spec exige contarlo. Se usa `setTimeout` junto con `maximumAge: 0` y `enableHighAccuracy: true`. Alternativa descartada: usar solo `timeout`.
- **Posiciones y respuestas tardías:** cada consulta lleva un identificador en `flow`; lo que llegue con otro identificador se ignora. La espera del servidor usa `AbortController`. Alternativa descartada: confiar solo en cancelar, porque la API de ubicación no se puede cancelar.
- **Textos fijos en Blade, datos por `<template>`:** el JavaScript clona plantillas y usa `textContent`, lo que evita inyección de HTML con nombres de la base de datos. Alternativa descartada: armar HTML con cadenas.
- **Dinero en centavos enteros:** evita errores de redondeo. Alternativa descartada: decimales de coma flotante. `Money` no se formatea en el dominio: lo hace `CoverageResultResource` con `config('coverage.currency_symbol')` (`$`) y `config('coverage.price_suffix')` (`/mes`). La spec no fija moneda ni periodicidad, por lo que quedan configurables.
- **Imágenes temporales en SVG propio:** logotipo, mapa con marcadores, fibra y antena, hechas para este proyecto en `public/images/temporal/`. No requieren licencia ni descarga. Alternativa descartada: recortes del mockup, porque no consta que sean libres de uso; y bancos de fotos, porque exigen revisar cada licencia.
- **Tipografía local con `@fontsource/poppins`:** se acerca al mockup y no depende de servicios externos. Alternativa descartada: fuentes cargadas desde un CDN.
- **Rutas:** la página vive en `/cobertura` y `/` redirige ahí mientras no exista la página principal. Alternativa descartada: servirla en `/`, porque esa ruta será de la página principal.
- **Enlace de mapa de Google Maps:** se abre directamente en la app del celular del equipo. Alternativa descartada: OpenStreetMap, porque la apertura en móvil es menos directa.

## Estrategia de tests
`php artisan test` no depende de MySQL. `npm test` cubre la lógica de JavaScript. Ambos deben quedar en verde al cerrar cada tarea que los use. En la lógica, la prueba se escribe antes que el código.

| Carpeta | Herramienta | Qué prueba |
|---|---|---|
| `tests/Unit/Domain/Coverage`, `tests/Unit/Domain/Plans` | `php artisan test`, sin base de datos | Geometría, precisión, resolución de escenarios, plan completo, recomendación |
| `tests/Unit/Architecture/DomainIsolationTest.php` | `php artisan test` | Que `app/Domain` no use `Illuminate`, `Carbon` ni `App\Infrastructure` |
| `tests/Integration/Persistence` | `php artisan test`, SQLite en memoria | Repositorios, marca de ejemplo, escenarios de los datos de ejemplo |
| `tests/Feature/Coverage` | `php artisan test`, SQLite en memoria | Endpoint, validación, privacidad, página, configuración |
| `tests/js` | `npm test` (Vitest) | `flow`, `presenter`, `whatsapp`, `api` y `geolocation` con dobles de las API del navegador |
| Navegador | MCP de Chrome DevTools | RF de interfaz, móvil y escritorio, y comparación con el mockup |

### Datos de ejemplo para verificar
Zonas cuadradas de 0.01 grados de lado (unos 1.1 km). Las coordenadas son ficticias.

| Zona de ejemplo | Centro (lat, lng) | Contenido | Resultado esperado |
|---|---|---|---|
| Ejemplo Fibra | 20.000, -100.000 | Fibra con 2 planes | A |
| Ejemplo Antena | 20.000, -99.950 | Antena con 2 planes | B |
| Ejemplo Ambas | 20.000, -99.900 | Fibra y antena, 2 planes cada una; uno con instalación en cero | C |
| Ejemplo Sin Servicio | 20.050, -100.000 | Tecnologías no disponibles | D con zona |
| Ejemplo Revisión | 20.050, -99.950 | Fibra pendiente de revisión | E (`pending_review`) |
| Ejemplo Norte y Ejemplo Sur | 20.050, -99.900 y 20.053, -99.900 | Superpuestas, fibra | E (`ambiguous_location`) en 20.051, -99.900 |

Puntos adicionales: 21.000, -101.000 da D sin zona. 20.000, -99.9945 queda a unos 52 m de Ejemplo Fibra: con margen 40 da D y con margen 60 da E por ubicación ambigua. Cualquier centro con margen 150 da E por precisión baja.

### Simular la ubicación con Chrome DevTools
La página se sirve con `npm run build` y `php artisan serve` en `http://127.0.0.1:8000/cobertura`, que el navegador trata como contexto seguro. Tras cargar la página, con `evaluate_script`:

```js
// Posición con margen a elegir
Geolocation.prototype.getCurrentPosition = (ok) =>
  ok({ coords: { latitude: 20, longitude: -100, accuracy: 30 }, timestamp: Date.now() });

// Permiso negado (code 1), posición no disponible (code 2)
Geolocation.prototype.getCurrentPosition = (_ok, fail) => fail({ code: 1 });

// Sin respuesta: comprueba el tiempo máximo de 20 s
Geolocation.prototype.getCurrentPosition = () => {};

// Navegador sin ubicación
Object.defineProperty(navigator, 'geolocation', { value: undefined, configurable: true });
```

No se añade ningún gancho de pruebas al código de producción. La sustitución se pierde al recargar y hay que repetirla. La vista móvil se comprueba con la emulación del MCP a 360 x 800 y el escritorio a 1280 x 800. El fallo del servidor se provoca apagando MySQL, y la pérdida de conexión con la emulación de red sin conexión.

## Cobertura de RF
| RF | Parte del plan | Prueba |
|---|---|---|
| RF-1 | Vista Blade | `CoveragePageTest`, navegador |
| RF-2 | Vista Blade | `CoveragePageTest` |
| RF-3 | Vista Blade | `CoveragePageTest`, navegador |
| RF-4 | `app.css` | Navegador a 360 y 1280 px |
| RF-5 | Vista y `app.css` | Comparación con el mockup en navegador |
| RF-6 | `api.js`, `CheckCoverageRequest` | `api.test.js`, `CheckCoverageApiTest` |
| RF-7 | `flow.js` | `flow.test.js`, navegador |
| RF-8 | `flow.js` | `flow.test.js`, navegador |
| RF-9 | `geolocation.js` | `geolocation.test.js` |
| RF-10 | `geolocation.js`, `flow.js`, plantillas | `geolocation.test.js`, navegador |
| RF-11 | `PrecisionPolicy`, caso de uso | `PrecisionPolicyTest`, `CheckCoverageApiTest` |
| RF-12 | `CheckCoverageRequest`, `GeoPoint`, `PrecisionMargin` | `CheckCoverageApiTest`, `GeoPointTest` |
| RF-13 | Caso de uso, `ZoneLocator`, `CoverageResolver` | `CoverageResolverTest`, `CheckCoverageApiTest` |
| RF-14 | `CoverageResolver`, `PlanCompletenessPolicy` | `CoverageResolverTest` |
| RF-15 | `CoverageResolver`, `PlanCompletenessPolicy` | `PlanCompletenessPolicyTest`, `CoverageResolverTest` |
| RF-16 | Repositorios, `CheckCoverageRequest` | `CheckCoverageApiTest` |
| RF-17 | Repositorios | `CheckCoverageApiTest` |
| RF-18 | `CoverageResolver`, `presenter.js` | `CoverageResolverTest`, `presenter.test.js`, navegador |
| RF-19 | `CoverageResolver`, `presenter.js` | `CoverageResolverTest`, `presenter.test.js`, navegador |
| RF-20 | `CoverageResolver`, `presenter.js` | `CoverageResolverTest`, `presenter.test.js`, navegador |
| RF-21 | `TechnologyRecommender` | `TechnologyRecommenderTest`, navegador |
| RF-22 | `CoverageResolver`, `presenter.js` | `CoverageResolverTest`, `presenter.test.js`, navegador |
| RF-23 | `CoverageResolver`, `presenter.js` | `CoverageResolverTest`, `presenter.test.js`, navegador |
| RF-24 | `CoverageResolver`, `presenter.js` | `CoverageResolverTest`, `presenter.test.js` |
| RF-25 | `whatsapp.js` | `whatsapp.test.js`, navegador |
| RF-26 | `SampleDataRepository`, `presenter.js` | `EloquentRepositoriesTest`, `presenter.test.js`, navegador |
| RF-27 | `api.js`, `flow.js`, plantillas | `api.test.js`, `flow.test.js`, navegador |
| RF-28 | Vista Blade | `CoveragePageTest`, navegador |
| RF-29 | Vista Blade | `CoveragePageTest` |
| RF-30 | Caso de uso, ruta sin sesión | `LocationPrivacyTest` |
| RF-31 | Textos de plantillas | `CoveragePageTest`, revisión de textos en navegador |
| RF-32 | `api.js`, plantillas | `api.test.js`, navegador |
| RF-33 | `ZoneLocator` | `ZoneLocatorTest`, `CheckCoverageApiTest` |
| RF-34 | `presenter.js`, plantillas | `presenter.test.js`, navegador |
| RF-35 | `CoverageResultResource` | `CheckCoverageApiTest` |
| RF-36 | `presenter.js`, plantillas | `presenter.test.js`, navegador |
| RF-37 | `flow.js` | `flow.test.js`, navegador |
| RF-38 | `flow.js` | `flow.test.js` |
| RF-39 | `flow.js` | `flow.test.js`, navegador |
| RF-40 | `flow.js`, `api.js` | `flow.test.js` |
| RF-41 | `whatsapp.js` | `whatsapp.test.js` |
| RF-42 | `config/coverage.php` | `CoverageConfigTest` |
| RF-43 | `whatsapp.js`, vista | `whatsapp.test.js`, navegador |
| RF-44 | `whatsapp.js`, plantillas | `whatsapp.test.js`, navegador |
| RF-45 | Migraciones, modelos | `EloquentRepositoriesTest` |
| RF-46 | `CoverageSampleSeeder` | `SampleDataSeederTest` |
| RF-47 | `flow.js` | `flow.test.js`, revisión de almacenamiento en navegador |
| RF-48 | Vista Blade | `CoveragePageTest` |
| RF-49 | `flow.js`, plantillas | `flow.test.js`, navegador |
| RF-50 | Caso de uso, `CoverageResultResource` | `CheckCoverageApiTest` |
| RF-51 | `whatsapp.js` | `whatsapp.test.js` |
| RF-52 | Vista, imágenes temporales | Comparación con el mockup en navegador |
| RF-53 | Vista Blade | `CoveragePageTest`, navegador |
| RF-54 | `app.css` | Navegador a 360 px |
| RF-55 | `flow.js` | `flow.test.js` |
