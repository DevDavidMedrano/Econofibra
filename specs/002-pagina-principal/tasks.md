# Tareas 002 — Página principal

- [x] T1: Catálogo de tarjetas de plan: dominio, caso de uso, tabla, repositorio y seeder, con pruebas primero.
  - RF: RF-8, RF-9, RF-10, RF-11, RF-15, RF-16, RF-31
  - Hecho cuando: `php artisan test` está en verde con `PlanCardCatalogPolicyTest`, `PlanCardIconTest`, `EloquentPlanCardRepositoryTest` y `PlanCardSeederTest`; `migrate:fresh --seed` crea las cuatro tarjetas y repetir el seeder no cambia las editadas.

- [x] T2: Página principal en `/` y navegación compartida: ruta, controlador, vistas, imagen pública, footer, redes configurables, estilos y degradado. Se actualizan las dos pruebas de `CoveragePageTest`.
  - RF: RF-1, RF-2, RF-3, RF-4, RF-5, RF-6, RF-7, RF-8, RF-12, RF-13, RF-14, RF-15, RF-17, RF-18, RF-19, RF-20, RF-21, RF-22, RF-23, RF-24, RF-28
  - Hecho cuando: `php artisan test` está en verde con `HomePageTest`, `SocialLinksTest` y `CoveragePageTest`; `/` responde 200 sin redirección, sin precios y con los enlaces de RF-3 y RF-17; `docs/referencias/hero-principal.jpeg` sigue en su sitio.

- [x] T3: Comportamiento en el navegador: WhatsApp en la página principal, animaciones de tarjetas y salida de `/cobertura`, con pruebas primero.
  - RF: RF-25, RF-26, RF-27, RF-29, RF-30, RF-32, RF-33
  - Hecho cuando: `npm test` y `php artisan test` están en verde, con pruebas de `leave`, de una respuesta tardía tras `leave`, del mensaje de contacto y del texto de RF-26 fuera del botón del footer.

- [x] T4: Validación RF por RF en Chrome DevTools sobre MySQL, a 360 y 1280 px, y actualización de `MEMORY.md`.
  - RF: RF-1 a RF-33
  - Hecho cuando: cada RF y criterio de finalización de la spec tiene su resultado anotado, incluidos la comparación con el mockup, el contraste de RF-28, la reducción de movimiento, Atrás con un resultado y con una consulta en curso, y abrir WhatsApp sin perder el resultado; `php artisan test`, `npm test` y `npm run build` están en verde, y `MEMORY.md` refleja el estado de la spec 002.

## Validación de T4
Chrome DevTools sobre MySQL, a 360 y 1280 px salvo donde se indica. `php artisan test` 442 de 442, `npm test` 193 de 193 y `npm run build` en verde.

| RF | Resultado |
|---|---|
| RF-1, RF-2 | `/` responde 200 sin redirección, con secciones y textos en orden. |
| RF-3, RF-4 | Logo, "Inicio", "Planes" y "Cobertura" llevan a su destino desde ambas páginas; "Servicios", "Soporte" y "Contacto" sin enlace; la opción activa es la correcta. |
| RF-5 | Foto real cargada, con `alt`. |
| RF-6, RF-7 | Comparada sección por sección a 1280 px: solo quedan las desviaciones de RF-7. Corregidos: el botón de la tarjeta destacada llevaba una flecha que el mockup no tiene, y los indicadores tenían tres separadores en vez de dos (sin línea entre "Alta Velocidad" y "Conexión Estable"). |
| RF-8, RF-9, RF-10, RF-13 | Con cambios temporales en MySQL: nuevo valor en la siguiente carga, orden con y sin posición, icono por defecto, tarjeta incompleta oculta, marca movida y texto largo sin salirse. Datos restaurados. |
| RF-11, RF-31 | Cuatro tarjetas sembradas en MySQL sin `migrate:fresh`; se conservan las 7 zonas y los 10 precios. RF-31 por `PlanCardSeederTest`. |
| RF-12, RF-14 | Aviso visible; sin precios, instalación ni "Datos de ejemplo". |
| RF-15 | Con la tabla renombrada temporalmente: 200, título, aviso y "Consultar en mi zona", sin subtítulo, y el resto de la página. |
| RF-16 | Con las tarjetas alteradas, los escenarios A a E dan el mismo resultado. |
| RF-17 | "¡Contrata Ahora!" y "Contratar" dejan la barra a la vista en `/cobertura`, sin abrir WhatsApp. |
| RF-18, RF-19, RF-20 | Footer con logo sobre pestaña blanca, lema y botón de WhatsApp con su nombre accesible. |
| RF-21 | Con dos redes: ambos iconos en pestaña nueva. Con una `https` y otra `http`: solo la primera. Sin redes: solo el botón de WhatsApp. |
| RF-22, RF-23 | 360 px sin desplazamiento horizontal, en una columna y con el rostro completo. Franja de ventajas a 1800 px con las cuatro tarjetas, sin desbordes. |
| RF-24 | Cero llamadas a `getCurrentPosition` y `watchPosition`. |
| RF-25, RF-27 | A 360 y 1280 px, los tres botones abren `wa.me` con el mensaje de contacto (`window.open` sustituido); el aviso fijo no tapa el footer. |
| RF-26 | Sin número: tres botones inactivos con su texto, y "Contratar" sigue funcionando. Corregido: el icono del footer se encogía a 42 x 44 px a 360 px; ahora mide 44 x 44. |
| RF-28 | Desde 1200 px, contra el píxel más claro detrás de cada texto, con el texto oculto y la foto pintada. Mínimos de título, subtítulo e indicadores (se exige 3:1, 4.5:1 y 4.5:1): 1200 px 7.16, 16.12 y 6.69 (sin barra de desplazamiento, 7.16, 16.12 y 6.46); 1240 px 7.09, 16.16 y 5.93; 1280 px 7.17, 16.16 y 7.84; 1440 px 7.14, 16.28 y 6.04; 1800 px 7.07, 16.00 y 11.55. Corregido: a 1200 px "Cobertura en tu Zona" daba 2.40:1 sobre la laptop; el degradado ahora sigue a los textos y se apaga antes del rostro. |
| RF-29, RF-32 | Aparición y cursor de 300 ms como máximo, sin mover otros elementos. Con movimiento reducido: ninguna animación. |
| RF-30, RF-33 | Copia en memoria confirmada (`persisted: true`). Volver con resultado (Atrás y Adelante) y con consulta en curso da el estado inicial. Una respuesta entregada después de volver se descarta, y una consulta nueva funciona. Abrir WhatsApp conserva el resultado. |

Spec 001: escenarios A, B, C, D (con y sin zona) y E (revisión, ambigua y precisión baja) correctos a 360 y 1280 px, sin errores de consola.

Sin probar: apertura real de WhatsApp (`window.open` sustituido; la conservación del resultado se comprobó con eventos simulados, sin abrir otra pestaña) y direcciones reales de redes. El cursor y la reducción de movimiento se midieron solo a 1280 px.
