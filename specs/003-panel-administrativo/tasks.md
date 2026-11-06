# Tareas 003 — Panel administrativo

- [x] T1: Acceso protegido: cuenta por consola, entrada, salida, bloqueo, inactividad, HTTPS y cookie, con una página inicial del panel vacía.
  - RF: RF-1, RF-2, RF-3, RF-4, RF-5, RF-6, RF-7
  - Hecho cuando: `AdminAccessTest`, `AdminAccountCommandTest`, `ProductionSecurityTest` y `AdminCredentialsTest` pasan, y `app/Models/User.php` ya no existe.

- [x] T2: Zonas: migración de `name_key` y `boundary_replaced`, lector KML, importación, renombrado, eliminación con confirmación y estado por tecnología.
  - RF: RF-8, RF-9, RF-10, RF-11, RF-14, RF-15, RF-20, RF-21, RF-22
  - Hecho cuando: `ZoneNameTest`, `ZonePolygonPolicyTest`, `KmlZoneFileReaderTest` y `ZoneAdminTest` pasan, y un KML de prueba crea una zona que la consulta de cobertura reconoce.

- [x] T3: Planes, precios por zona y datos de ejemplo: alta, edición y eliminación de planes, asignación de precios, contadores y borrado con bloqueo.
  - RF: RF-12, RF-13, RF-14, RF-15, RF-16, RF-17, RF-19, RF-20
  - Hecho cuando: `PlanAdminTest`, `SampleCleanupPolicyTest`, `SampleDataAdminTest`, `MoneyTest` y `PlanSpeedTest` pasan, y un precio cambiado en el panel aparece en la consulta siguiente.

- [x] T4: Acabado visual y cierre: estilos del panel, `npm run build`, repaso básico en navegador sobre MySQL a 360 y 1280 px, y actualización de `MEMORY.md`.
  - RF: RF-18
  - Hecho cuando: `php artisan test` está en verde con las pruebas de las specs 001 y 002, el repaso recorre entrada, subida de KML, precio y borrado de ejemplo sin desplazamiento horizontal, y `MEMORY.md` indica cómo crear la cuenta, que en producción solo se cargan `TechnologySeeder` y `PlanCardSeeder`, y que la spec 002 ya tiene commit.
