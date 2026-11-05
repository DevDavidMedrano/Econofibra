# Tareas 001 — Consulta de cobertura

- [x] T1: Crear Laravel sin perder los archivos existentes, según "Creación de Laravel" del plan, con Tailwind, Vitest, `config/coverage.php`, la suite `Integration` y la prueba de aislamiento del dominio.
  - RF: RF-42
  - Hecho cuando: AGENTS.md, MEMORY.md, .mcp.json y docs/constitution.md conservan su hash; `.gitignore` combinado mantiene `!.env.example` y `.claude/settings.local.json`; `php artisan test` y `npm test` terminan en verde con MySQL apagado; `CoverageConfigTest` comprueba que el número de WhatsApp sale de la configuración y que el umbral es 100, la espera de ubicación 20 y la del servidor 15.

- [x] T2: Implementar en el dominio la geometría y la precisión: `GeoPoint`, `PrecisionMargin`, `ZoneBoundary`, `CoverageZone`, `ZoneMatch`, `PrecisionPolicy` y `ZoneLocator`.
  - RF: RF-11, RF-12, RF-13, RF-33
  - Hecho cuando: `php artisan test` pasa, sin base de datos, con estos casos: margen de 100 y de 100.1 m, margen ausente, coordenadas fuera de rango, punto dentro, fuera y sobre el borde, zonas superpuestas, margen que alcanza otra zona, punto sin zona con margen que alcanza una y punto en zona con margen que solo alcanza terreno sin zona.

- [x] T3: Implementar en el dominio la oferta y el resultado: `Money`, `InternetPlan`, `PlanCompletenessPolicy`, `TechnologyOffer`, `CoverageResult`, `TechnologyRecommender` y `CoverageResolver`.
  - RF: RF-13, RF-14, RF-15, RF-18, RF-19, RF-20, RF-21, RF-22, RF-23, RF-24
  - Hecho cuando: `php artisan test` pasa, sin base de datos, con los Escenarios A a E; las combinaciones confirmada y pendiente de fibra y antena; precio cero, negativo o ausente; instalación en cero y negativa; plan sin nombre o velocidad; y E sin planes ni recomendación.

- [x] T4: Crear la persistencia: migraciones, modelos Eloquent, repositorios, `CoverageServiceProvider` y los seeders con los datos de ejemplo del plan.
  - RF: RF-16, RF-17, RF-26, RF-45, RF-46
  - Hecho cuando: `php artisan test` pasa con SQLite en memoria; los repositorios devuelven entidades del dominio y no modelos Eloquent; una tecnología sin estado se lee como no disponible; cada tabla admite la marca de ejemplo por registro; `SampleDataRepository` detecta un solo registro marcado; y `SampleDataSeederTest` comprueba que los datos de ejemplo cumplen la tabla de resultados del plan.

- [x] T5: Crear `CheckCoverageUseCase`, `CheckCoverageRequest`, `CoverageResultResource`, `CoverageCheckController` y la ruta `POST /api/coverage/check` sin sesión.
  - RF: RF-6, RF-11, RF-12, RF-13, RF-15, RF-16, RF-17, RF-26, RF-30, RF-33, RF-35, RF-50
  - Hecho cuando: `CheckCoverageApiTest` pasa para cada escenario y cada motivo de E, consultas rechazadas con 422 y sin planes, margen nulo o vacío como E, margen cero, negativo o no numérico como rechazo, valores manipulados ignorados, precio modificado visible en la consulta siguiente, nombre de zona y `sample_data`; y `LocationPrivacyTest` comprueba que no hay escrituras en base de datos ni en registros con las coordenadas de la consulta.

- [x] T6: Construir la página `/cobertura` estática, con Blade y Tailwind: navegación, secciones, textos de RF-1, plantillas de resultados y mensajes, imágenes SVG temporales y vista de 360 px.
  - RF: RF-1, RF-2, RF-3, RF-4, RF-5, RF-28, RF-29, RF-31, RF-48, RF-52, RF-53, RF-54
  - Hecho cuando: `CoveragePageTest` comprueba los textos literales, el orden y las opciones de navegación sin enlace; y en navegador no hay desplazamiento horizontal a 360 ni a 1280 px, "Consultar en mi zona" lleva al formulario y la comparación con el mockup solo muestra las diferencias de RF-52.

- [x] T7: Implementar el flujo del navegador con `flow.js`, `geolocation.js`, `api.js` y `page.js`: confirmación, solicitud de ubicación, tiempos de espera, reintentos y estados de botones.
  - RF: RF-6, RF-7, RF-8, RF-9, RF-10, RF-27, RF-32, RF-37, RF-38, RF-39, RF-40, RF-47, RF-49, RF-55
  - Hecho cuando: `npm test` pasa con pruebas de botones inactivos, cambio de respuesta, reintento con posición nueva, posición y respuesta tardías descartadas, 20 s con el aviso de permiso abierto, 15 s del servidor, fallos 422 y 500, y descarte de la ubicación conservada; y en navegador, con ubicación simulada, se ven los mensajes de permiso negado, tiempo agotado y navegador sin ubicación.

- [x] T8: Mostrar los resultados con `presenter.js` y las plantillas: Escenarios A a E, recomendación, aviso de validación, aviso de datos de ejemplo y mensajes de RF-36.
  - RF: RF-18, RF-19, RF-20, RF-21, RF-22, RF-23, RF-24, RF-26, RF-34, RF-35, RF-36
  - Hecho cuando: `npm test` pasa con el modelo de vista de cada escenario y motivo; y en navegador, con los puntos del plan, cada escenario muestra lo que exige su RF, sin precios en D ni E, y con el aviso de RF-26 en todos los resultados.

- [x] T9: Conectar WhatsApp con `whatsapp.js`: mensajes por situación, enlace de mapa, botones inactivos sin número y texto de respaldo.
  - RF: RF-25, RF-41, RF-43, RF-44, RF-51
  - Hecho cuando: `npm test` pasa con cada fila de RF-41, incluidos el motivo y la codificación del enlace; y en navegador, con un número de prueba en `.env`, cada botón abre `wa.me` con su mensaje; sin número, los botones quedan inactivos con su texto y la consulta sigue funcionando.

- [x] T10: Verificar la spec completa con MySQL y Chrome DevTools, RF por RF, y actualizar MEMORY.md.
  - RF: RF-1 a RF-55
  - Hecho cuando: `php artisan migrate:fresh --seed` funciona en MySQL; `php artisan test` y `npm test` están en verde; todos los criterios de finalización de la spec están marcados con su resultado, en 360 y 1280 px; localStorage, sessionStorage e IndexedDB no contienen la ubicación; un fallo con MySQL apagado muestra el mensaje de RF-27; y MEMORY.md recoge cómo arrancar y probar el proyecto.
