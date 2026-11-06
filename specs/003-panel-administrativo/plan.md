# Plan 003 — Panel administrativo

Spec: specs/003-panel-administrativo/spec.md

## Archivos
| Archivo | Capa | Acción | RF |
|---|---|---|---|
| `app/Domain/Admin/ValueObjects/AdminEmail.php`, `AdminPassword.php` | Domain | Crear | RF-2, RF-4 |
| `app/Domain/Admin/Repositories/AdminAccountRepository.php` | Domain | Crear | RF-2 |
| `app/Domain/Coverage/ValueObjects/ZoneName.php` | Domain | Crear | RF-8, RF-10, RF-21 |
| `app/Domain/Coverage/ValueObjects/ZoneBoundary.php` | Domain | Modificar: método público `polygons()` para guardar el límite | RF-8 |
| `app/Domain/Coverage/Services/ZonePolygonPolicy.php` | Domain | Crear | RF-9 |
| `app/Domain/Coverage/Services/SampleCleanupPolicy.php`, `ValueObjects/SampleSnapshot.php`, `SampleBlocker.php` | Domain | Crear | RF-17, RF-19 |
| `app/Domain/Coverage/Repositories/ZoneFileReader.php`, `ZoneFileRejected.php`, `ZoneFileContent.php`, `ZoneFileEntry.php` | Domain | Crear: contrato del lector y datos leídos | RF-8, RF-9 |
| `app/Domain/Coverage/Repositories/ZoneCatalogRepository.php` | Domain | Crear: lista, importación, renombrar, eliminar, estado | RF-8, RF-10, RF-11 |
| `app/Domain/Coverage/Repositories/SampleDataRepository.php` | Domain | Modificar: `counts()`, `blockers()`, `deleteAllUnlessBlocked()` | RF-16, RF-17, RF-19 |
| `app/Domain/Plans/ValueObjects/PlanSpeed.php`, `Money.php` | Domain | Crear y modificar: `Money::fromDecimalString()` | RF-12, RF-13 |
| `app/Domain/Plans/Repositories/PlanCatalogRepository.php` | Domain | Crear: planes y precios por zona | RF-12, RF-13 |
| `app/Domain/Shared/AdminChangeRejected.php` | Domain | Crear: motivo legible del rechazo | RF-14 |
| `app/Application/Admin/UseCases/SetAdminAccountUseCase.php` | Application | Crear | RF-2 |
| `app/Application/Coverage/UseCases/{ImportZones,RenameZone,DeleteZone,SetZoneTechnologyStatus,ListZones}UseCase.php`, `DTOs/` | Application | Crear | RF-8 a RF-11, RF-14 |
| `app/Application/Plans/UseCases/{SavePlan,DeletePlan,SetZonePrice,RemoveZonePrice,ListPlans}UseCase.php` | Application | Crear | RF-12 a RF-14 |
| `app/Application/Coverage/UseCases/{GetSampleSummary,DeleteSampleData}UseCase.php` | Application | Crear | RF-16, RF-17, RF-19 |
| `app/Infrastructure/Persistence/Eloquent/AdminUserModel.php` | Infrastructure | Crear: autenticable sobre `users`; sustituye a `app/Models/User.php` | RF-2 |
| `app/Models/User.php`, `database/factories/UserFactory.php` | Infrastructure | Eliminar | RF-2 |
| `config/auth.php`, `config/session.php`, `config/admin.php`, `.env.example` | Infrastructure | Modificar y crear | RF-4, RF-5, RF-7, RF-9 |
| `app/Infrastructure/Adapters/KmlZoneFileReader.php` | Infrastructure | Crear | RF-8, RF-9, RF-22 |
| `app/Infrastructure/Persistence/Repositories/EloquentAdminAccountRepository.php`, `EloquentZoneCatalogRepository.php`, `EloquentPlanCatalogRepository.php` | Infrastructure | Crear | RF-2, RF-8, RF-10 a RF-14, RF-21 |
| `app/Infrastructure/Persistence/Repositories/EloquentSampleDataRepository.php` | Infrastructure | Modificar | RF-16, RF-17, RF-19 |
| `app/Infrastructure/Persistence/Eloquent/CoverageZoneModel.php` | Infrastructure | Modificar: `saving` calcula `name_key` | RF-21 |
| `database/migrations/2026_11_05_000007_add_admin_columns_to_coverage_zones_table.php` | Infrastructure | Crear: `name_key` único y `boundary_replaced` | RF-19, RF-21 |
| `app/Providers/AdminServiceProvider.php`, `bootstrap/providers.php` | Infrastructure | Crear y modificar: contratos, límites, proxies, esquema HTTPS | RF-7, RF-9 |
| `app/Console/Commands/SetAdminAccountCommand.php` | Presentation | Crear: `php artisan admin:account {correo}` | RF-2 |
| `bootstrap/app.php` | Presentation | Modificar: `ForceHttps`, redirección de invitados, respuesta 419, `AdminChangeRejected` | RF-3, RF-6, RF-7, RF-14 |
| `app/Http/Middleware/ForceHttps.php`, `EndIdleAdminSession.php` | Presentation | Crear | RF-5, RF-7 |
| `routes/web.php` | Presentation | Modificar: grupo `/panel` | RF-1, RF-3 |
| `app/Http/Controllers/Admin/{Login,Zone,ZoneImport,ZoneTechnology,ZonePrice,Plan,SampleData}Controller.php` | Presentation | Crear | RF-1, RF-4, RF-5, RF-8, RF-10 a RF-13, RF-16, RF-17, RF-19 |
| `app/Http/Requests/Admin/*.php`, incluido `ConfirmedActionRequest.php` | Presentation | Crear | RF-4, RF-14, RF-20 |
| `resources/views/layouts/admin.blade.php`, `pages/admin/**`, `errors/413.blade.php` | Presentation | Crear | RF-1, RF-9, RF-16, RF-18, RF-19 |
| `resources/css/app.css` | Presentation | Modificar: clases `.admin-*` | RF-18 |

## Funciones puras
Ninguna regla de dominio depende de la fecha, así que ninguna recibe "hoy". El bloqueo y la inactividad se miden en Presentation con el reloj de Laravel.
- `AdminEmail::normalize(string): string`: quita espacios y pasa a minúsculas; se usa al entrar, al contar intentos y en consola.
- `AdminPassword::__construct(string)`: rechaza menos de 12 caracteres.
- `ZoneName::__construct(string)`: exige texto válido. `ZoneName::key(): string`: SHA-256 de `mb_strtolower(trim($nombre))`, que conserva los acentos.
- `ZonePolygonPolicy::violation(array $vertices, int $maxVertices): ?string`: aplica primero las reglas baratas (cantidad, rango, repetidos, salto de longitud mayor de 180°), luego el área por la fórmula del polígono y al final el cruce de lados no contiguos. Dos lados no contiguos que se tocan cuentan como cruce.
- `Money::fromDecimalString(string): Money`: convierte sin `float`, con `^\d{1,6}(\.\d{1,2})?$`.
- `PlanSpeed::__construct(int)`: de 1 a 100 000.
- `SampleCleanupPolicy::blockers(SampleSnapshot): list<SampleBlocker>`: aplica la definición de dato real dependiente, con lo que debe corregirse en cada caso.

## Decisiones
- El panel vive en `/panel`, sin enlaces públicos. Alternativa descartada: `/admin`, porque es la primera dirección que prueban los robots; no sustituye a la autenticación.
- Autenticación nativa de Laravel sobre la tabla `users`, sin paquetes. Alternativa descartada: Breeze o Fortify, porque traen registro y recuperación que la spec excluye.
- `users` se conserva como está; `name` lleva el valor fijo "Administrador" y `password_reset_tokens` queda sin uso. `AdminUserModel` sustituye a `App\Models\User` y `config/auth.php` apunta a él. Alternativa descartada: dejar `App\Models\User`, porque Eloquent quedaría fuera de Infrastructure (constitución IV).
- Con cuenta existente, `EloquentAdminAccountRepository` actualiza esa fila, sea cual sea su correo, y nunca inserta otra (RF-2).
- La contraseña se pide con `secret()` en la consola. Alternativa descartada: pasarla como argumento, porque quedaría en el historial del shell.
- Cierre de sesiones con el middleware `auth.session`, que invalida la sesión si cambia el hash de la contraseña. Alternativa descartada: borrar filas de `sessions`, porque `.env.example` usa `SESSION_DRIVER=file`.
- Inactividad con `EndIdleAdminSession`, que guarda en la sesión la última actividad, con el valor de `config/admin.php`. Alternativa descartada: `SESSION_LIFETIME=30`, porque depende del `.env` de cada servidor, afecta a todo el sitio y no se puede probar con el driver `array`.
- Bloqueo con `RateLimiter`, clave `correo normalizado|ip`, comprobado antes de `Auth::attempt` y limpiado al entrar. El formulario no valida el formato del correo, para no dar un mensaje distinto (RF-4).
- `ForceHttps` se registra con `append` en la pila global, con 301 para GET y HEAD y 308 para lo demás. Alternativa descartada: `prepend`, porque correría antes de `TrustProxies` y crearía un bucle de redirección detrás de un proxy. `URL::forceScheme('https')` cubre los enlaces generados.
- `config/session.php` usa `env('SESSION_SECURE_COOKIE', env('APP_ENV') === 'production')`, para que un `.env` incompleto no deje la cookie insegura. `.env.example` lo documenta comentado.
- Proxies con `TRUSTED_PROXIES` en `config/admin.php` y `TrustProxies::at()` en el proveedor, solo si tiene valor.
- La protección CSRF actúa antes que `auth`, así que un POST con la sesión vencida da 419. En `bootstrap/app.php`, `respond()` convierte ese 419 en redirección a la entrada si la ruta es del panel y no hay sesión (RF-3); con sesión sigue siendo 419 (RF-6). Se usa `respond()` porque Laravel convierte la excepción antes de los `render()`.
- `name_key` guarda el SHA-256 de la clave, con índice único. Alternativa descartada: índice único sobre `name`, porque las colaciones de MySQL ignoran acentos y las de SQLite no pliegan mayúsculas no ASCII, y un índice con colación `utf8mb4_bin` no existe en SQLite. La migración añade la columna nula, la rellena y la hace obligatoria.
- `boundary_replaced` es un booleano que la importación activa al reemplazar un límite. Alternativa descartada: comparar con el límite del seeder, porque se rompería al cambiar el seeder.
- Lectura del KML con `DOMDocument` y `LIBXML_NONET`, sin `LIBXML_NOENT` ni `LIBXML_DTDLOAD`. Tras cargar se rechaza si `$dom->doctype !== null`. Alternativa descartada: buscar `<!DOCTYPE` en los bytes, porque otra codificación como UTF-16 lo evade.
- XPath por `local-name()`, para aceptar cualquier versión de KML y `Folder` anidados. Solo se recorren `Placemark` y `Polygon`, así que `NetworkLink` y `href` nunca se leen. El anillo se convierte de `longitud,latitud[,altura]` a `[latitud, longitud]` y se quita el vértice de cierre. `innerBoundaryIs` marca hueco. Un archivo que empieza por `PK` se rechaza con el consejo de RF-9 para KMZ.
- Límites en `config/admin.php`: 2 048 KB, 1 000 vértices por polígono y 10 000 por archivo. El total del archivo se comprueba antes que la regla de cruces, que es cuadrática. Un archivo mayor que `post_max_size` se corta antes de iniciar la sesión, por eso `errors/413.blade.php` explica el motivo en vez de un mensaje en el formulario.
- Toda la importación va en una transacción del repositorio; el borrado de ejemplo también, y dentro de ella se toma la instantánea, se aplica la política y se borra (`deleteAllUnlessBlocked`), para que RF-19 se compruebe en ese momento.
- Las actualizaciones de estado y precio tocan solo el valor y no `is_sample`; un registro nuevo es real por defecto (RF-16).
- Confirmación con una casilla `confirmation` dentro del formulario, exigida con la regla `accepted` en `ConfirmedActionRequest`. Alternativa descartada: `confirm()` de JavaScript con campo oculto, porque el campo iría siempre y el servidor no probaría nada. El panel no necesita JavaScript.
- Los repositorios convierten `QueryException` en `AdminChangeRejected`, y `bootstrap/app.php` lo responde con `back()->withErrors()`. Así un nombre repetido por carrera o un fallo de guardado siguen RF-14 sin lógica en los controladores.
- El layout carga solo `resources/css/app.css`, sin `app.js`. Alternativa descartada: una hoja aparte, porque añadiría otra entrada a Vite.

## Estrategia de tests
Con `php artisan test`; en la lógica, la prueba va primero. Verificación reducida (constitución IX): las pruebas Feature usan `withoutVite()` y las de tiempo usan `travelTo` con fecha fija.
- `tests/Unit/Domain/Admin/AdminCredentialsTest.php`: RF-2, RF-4.
- `tests/Unit/Domain/Coverage/ZoneNameTest.php`, `ZonePolygonPolicyTest.php`, `SampleCleanupPolicyTest.php`: RF-9, RF-19, RF-21.
- `tests/Unit/Domain/Plans/MoneyTest.php` (ampliada), `PlanSpeedTest.php`: RF-12, RF-13.
- `tests/Integration/Adapters/KmlZoneFileReaderTest.php`: RF-8, RF-9, RF-22, incluidos DOCTYPE en UTF-16, entidades, `NetworkLink`, `Folder`, `MultiGeometry`, huecos y orden de coordenadas.
- `tests/Integration/Persistence/AdminCatalogRepositoriesTest.php`: RF-8, RF-10 a RF-13, RF-21, incluida la unicidad en la base de datos y la migración con zonas existentes.
- `tests/Feature/Admin/AdminAccessTest.php`: RF-1, RF-3 a RF-6, con los minutos 14:59 y 15:00 del bloqueo y 29:59 y 30:00 de la inactividad.
- `tests/Feature/Admin/AdminAccountCommandTest.php`: RF-2, incluidos el correo distinto y la sesión abierta que se cierra.
- `tests/Feature/Admin/ProductionSecurityTest.php`: RF-7.
- `tests/Feature/Admin/ZoneAdminTest.php`, `PlanAdminTest.php`: RF-8 a RF-15, RF-20.
- `tests/Feature/Admin/SampleDataAdminTest.php`: RF-16, RF-17, RF-19, RF-20.
- `tests/Integration/Persistence/EloquentRepositoriesTest.php`: su ayudante `zone()` inserta con `DB::table` y debe añadir `name_key`.

La protección CSRF se salta en el entorno `testing`: las pruebas de RF-6 y del 419 de RF-3 cambian `app()['env']` a otro valor.

## Cobertura de RF
| RF | Parte del plan | Prueba |
|---|---|---|
| RF-1 | Grupo `/panel`, sin enlaces públicos | `AdminAccessTest` |
| RF-2 | `SetAdminAccountCommand`, `SetAdminAccountUseCase`, `auth.session` | `AdminAccountCommandTest`, `AdminCredentialsTest` |
| RF-3 | `auth`, `respond()` del 419 | `AdminAccessTest` |
| RF-4 | `LoginRequest`, `RateLimiter`, `AdminEmail` | `AdminAccessTest` |
| RF-5 | `EndIdleAdminSession`, cierre de sesión | `AdminAccessTest` |
| RF-6 | Protección CSRF de Laravel | `AdminAccessTest` |
| RF-7 | `ForceHttps`, `config/session.php` | `ProductionSecurityTest` |
| RF-8 | `ImportZonesUseCase`, `KmlZoneFileReader` | `KmlZoneFileReaderTest`, `ZoneAdminTest` |
| RF-9 | `KmlZoneFileReader`, `ZonePolygonPolicy` | `KmlZoneFileReaderTest`, `ZonePolygonPolicyTest`, `ZoneAdminTest` |
| RF-10 | `RenameZoneUseCase`, `DeleteZoneUseCase` | `ZoneAdminTest` |
| RF-11 | `SetZoneTechnologyStatusUseCase` | `ZoneAdminTest` |
| RF-12 | `SavePlanUseCase`, `DeletePlanUseCase` | `PlanAdminTest`, `PlanSpeedTest` |
| RF-13 | `SetZonePriceUseCase`, `RemoveZonePriceUseCase` | `PlanAdminTest`, `MoneyTest` |
| RF-14 | `AdminChangeRejected`, Requests | `ZoneAdminTest`, `PlanAdminTest` |
| RF-15 | Sin caché: la consulta lee la base de datos | `PlanAdminTest` (cambio de precio y consulta siguiente) |
| RF-16 | `GetSampleSummaryUseCase`, vistas | `SampleDataAdminTest` |
| RF-17 | `deleteAllUnlessBlocked` | `SampleDataAdminTest` |
| RF-18 | `layouts/admin`, `.admin-*` | Repaso básico a 360 y 1280 px |
| RF-19 | `SampleCleanupPolicy` | `SampleCleanupPolicyTest`, `SampleDataAdminTest` |
| RF-20 | `ConfirmedActionRequest` | `ZoneAdminTest`, `PlanAdminTest`, `SampleDataAdminTest` |
| RF-21 | `ZoneName`, `name_key` | `ZoneNameTest`, `AdminCatalogRepositoriesTest` |
| RF-22 | `KmlZoneFileReader` | `KmlZoneFileReaderTest` |
