# MEMORY.md — Memoria de Econofibra JLINK
Memoria del proyecto entre sesiones. Máximo ~50 líneas: resume o elimina lo que ya no
aporte. Detalle de reglas: leer `AGENTS.md` y `docs/constitution.md`. `README.md` es el de Laravel.

## Estado
- Spec 001 (consulta de cobertura) terminada: T1 a T10. Sin commits todavía.
- Hecha solo la página `/cobertura`; `/` redirige ahí. La página principal y las demás no existen.
- Antes de publicar: número de WhatsApp real, zonas, planes y precios reales sin datos de
  ejemplo, e imágenes reales (ver "Pendiente antes de publicar" en la spec).

## Arrancar en local
- PHP 8.4 por WinGet; si `php` no está en el PATH, añadir
  `%LOCALAPPDATA%\Microsoft\WinGet\Packages\PHP.PHP.8.4_Microsoft.Winget.Source_8wekyb3d8bbwe`.
- MySQL 8.4 portable en `%LOCALAPPDATA%\Programs\mysql\mysql-8.4.9-winx64`, en 127.0.0.1:3306:
  `bin\mysqld --defaults-file=<esa ruta>\my.ini`; apagar con
  `mysqladmin -h 127.0.0.1 -u root shutdown`.
- Primera vez: `CREATE DATABASE econofibra CHARACTER SET utf8mb4;` y
  `php artisan migrate:fresh --seed`.
- `npm run build` y `php artisan serve --host=127.0.0.1 --port=8000 --no-reload`.
  Abrir `http://127.0.0.1:8000/cobertura`.
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

## Sin probar en la spec 001
- Posición real de un dispositivo con permiso concedido y apertura real de WhatsApp.
