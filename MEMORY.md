# MEMORY.md — Memoria de Econofibra JLINK
Memoria del proyecto entre sesiones. Máximo ~50 líneas: resume o elimina lo que ya no
aporte. Detalle de reglas: leer `AGENTS.md` y `docs/constitution.md`. `README.md` es el de Laravel.

## Estado
- Specs 001 (cobertura), 002 (portada) y 003 (panel en `/panel`) terminadas y validadas en navegador.
  Commits: spec 001 en 51f9b4c, spec 002 en 51d23f4 y spec 003 en 95efbbb.
- Hechas `/`, `/cobertura`, `/servicios`, `/soporte` y `/contacto`; las tres últimas, sin spec por
  decisión del usuario y sin commit todavía. Sus textos redactados siguen pendientes de revisión.
- Antes de publicar: lista "Pendiente antes de publicar" de la spec 003,
  `SITE_FACEBOOK_URL`, `SITE_YOUTUBE_URL` e imágenes reales de `/cobertura`.
- `.env` ya tiene el WhatsApp real (`COVERAGE_WHATSAPP_NUMBER`) y `SITE_CONTACT_PHONES` para llamar.
  Al probar, sustituir `window.open`: no abrir ni escribir a ese número.
- Tarjetas de la portada: tabla `plan_cards`, independiente de los planes por zona.

## Panel
- Cuenta (crear o restablecer): `php artisan admin:account correo@dominio` en una consola real;
  la contraseña se pide oculta, con 12 caracteres o más. La base `econofibra` aún no tiene cuenta.
- En producción solo se cargan `TechnologySeeder` y `PlanCardSeeder`; nunca `CoverageSampleSeeder`.
- Repaso sin tocar `econofibra`: base temporal con `DB_DATABASE` exportado antes de `migrate`,
  `db:seed` y `serve`, más `SESSION_DRIVER=database` y `CACHE_STORE=database`. Borrarla al terminar.

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
- El cliente `mysql` de la consola muestra mal los acentos sin `--default-character-set=utf8mb4`.
- `emulate` recarga la página: ponerla antes de preparar stubs.
- `resize_page` no baja de 500 px: para 360 px usar `emulate` con `viewport`.

## Sin probar
- Posición real de un dispositivo con permiso concedido y apertura real de WhatsApp.
- `admin:account` en consola real sobre MySQL, y HTTPS del panel en un servidor real.
