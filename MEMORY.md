# MEMORY.md — Memoria de Econofibra JLINK
Memoria del proyecto entre sesiones. Máximo ~50 líneas: resume o elimina lo que ya no
aporte. Detalle de reglas: leer `AGENTS.md` y `docs/constitution.md`. `README.md` es el de Laravel.

## Estado
- Specs 001 (consulta de cobertura) y 002 (página principal) terminadas y validadas en navegador.
  La spec 001 está en el commit 51f9b4c; la spec 002 sigue sin commit.
- Hechas `/` y `/cobertura`. Servicios, Soporte y Contacto no existen: van sin enlace en la navegación.
- Antes de publicar: número de WhatsApp real, direcciones de Facebook y YouTube (`SITE_FACEBOOK_URL`,
  `SITE_YOUTUBE_URL`), zonas, planes y precios reales, e imágenes reales de `/cobertura`.
- Tarjetas de la portada: tabla `plan_cards`, independiente de los planes por zona.

## Arrancar en local
- PHP 8.4 por WinGet; si `php` no está en el PATH, añadir
  `%LOCALAPPDATA%\Microsoft\WinGet\Packages\PHP.PHP.8.4_Microsoft.Winget.Source_8wekyb3d8bbwe`.
- MySQL 8.4 portable en `%LOCALAPPDATA%\Programs\mysql\mysql-8.4.9-winx64`, en 127.0.0.1:3306:
  `bin\mysqld --defaults-file=<esa ruta>\my.ini`; apagar con
  `mysqladmin -h 127.0.0.1 -u root shutdown`.
- Primera vez: `CREATE DATABASE econofibra CHARACTER SET utf8mb4;` y
  `php artisan migrate:fresh --seed`. Con datos ya cargados: `php artisan migrate` y
  `php artisan db:seed --class="Database\Seeders\PlanCardSeeder"`.
- `npm run build` y `php artisan serve --host=127.0.0.1 --port=8000 --no-reload`.
  Abrir `http://127.0.0.1:8000/`.
- El usuario puede tener su propio servidor en 127.0.0.1:8080: no detenerlo.

## Probar
- `php artisan test` (SQLite en memoria, sin MySQL) y `npm test` (Vitest, sin DOM).
- `page.js` y el CSS solo se verifican de verdad en navegador, con el MCP de Chrome DevTools.
- Puntos de cada escenario y cómo simular la ubicación: `specs/001-consulta-cobertura/plan.md`.
- Probar WhatsApp con un número falso sin tocar `.env`: exportar `COVERAGE_WHATSAPP_NUMBER`
  antes de `php artisan serve --no-reload`. Sustituir `window.open` para no abrir nada real.

## Trampas
- `scroll-behavior: smooth`: el clic del MCP cae fuera si el elemento debe desplazarse.
  Llevarlo antes a la vista con `scrollIntoView({ behavior: 'instant' })`.
- La emulación de ubicación del MCP no concede el permiso: queda en `prompt` y la página
  agota los 20 s. Para consultas, sustituir `getCurrentPosition` como indica el plan.
- `emulate` reemplaza toda la emulación anterior: pasar siempre también `viewport`.
- Una captura de página completa pinta el aviso fijo de WhatsApp a media página: no es un fallo.
- El cliente `mysql` de la consola muestra mal los acentos sin `--default-character-set=utf8mb4`.
- El aviso de RF-44 es fijo; `.final-call__inner` reserva espacio para no tapar el botón final.
- `emulate` recarga la página: ponerla antes de preparar stubs. Con Slow 3G la respuesta llega antes
  de `pagehide`; para una respuesta tardía tras Atrás, retener `fetch` con un temporizador.
- `innerText` de un elemento oculto devuelve su texto: comprobar `hidden`.
- Las capturas de página completa a 1280 px recortan 15 px por la barra de desplazamiento.

## Sin probar
- Posición real de un dispositivo con permiso concedido y apertura real de WhatsApp.
