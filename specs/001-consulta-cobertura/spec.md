# Spec 001 — Consulta de cobertura

Estado: aprobada

## Problema
La cobertura de Econofibra JLINK no es uniforme: la tecnología, los planes, los precios y los costos de instalación cambian según la ubicación, incluso entre domicilios de una misma colonia. Hoy una persona interesada no puede saber qué servicio le corresponde en su domicilio, y prometerle planes o precios sin confirmar la ubicación generaría ofertas que la empresa no podría cumplir.

## Objetivo
Que una persona, desde el domicilio donde quiere el servicio, comparta su ubicación y vea qué tecnologías, planes y precios corresponden a su zona, o qué camino seguir si no se pueden confirmar. Si hay servicio confirmado en la zona, debe poder iniciar la contratación por WhatsApp. Los precios de la zona quedan sujetos a la validación técnica del domicilio.

## Fuera de alcance
- Página principal y demás páginas de la plataforma.
- Registro de solicitudes de clientes y almacenamiento de ubicaciones.
- Panel administrativo.
- Campos de domicilio (código postal, colonia, calle, número exterior e interior).
- Preguntas sobre el uso o las necesidades del cliente.
- Condiciones comerciales: aún no están definidas; en esta versión solo se manejan precio y costo de instalación.
- La validación técnica del domicilio, que ocurre fuera del sistema tras el contacto por WhatsApp.
- Carga de las zonas, planes y precios reales, y publicación de la página (ver "Pendiente antes de publicar").

## Requisitos funcionales

### Definiciones
- **Consulta:** comienza cuando el usuario pulsa el botón de compartir ubicación o de reintentar, y termina cuando el sistema muestra un resultado o un mensaje de error. Incluye la espera de la ubicación y del permiso. Cada consulta usa una posición nueva del dispositivo.
- **Resultado:** la pantalla de un Escenario A, B, C, D o E. Los mensajes de RF-8, RF-10, RF-27 y RF-32 no son resultados.
- **Zona:** área de cobertura registrada en la base de datos con un nombre comercial.
- **Margen de precisión:** radio en metros alrededor del punto, informado por el dispositivo.
- **Umbral de precisión:** 100 metros, valor configurable sin cambiar el código fuente. Un margen de 100 m es suficiente.
- **Precisión baja:** margen mayor que el umbral, o ubicación sin dato de precisión. Un margen ausente, nulo o vacío cuenta como sin dato de precisión.
- **Tiempo máximo de espera de la ubicación:** 20 segundos, valor configurable sin cambiar el código fuente. Cuenta desde que se solicita la ubicación e incluye el tiempo con el aviso de permiso sin responder. Al vencer, se trata como ubicación no obtenida (RF-10).
- **Tiempo máximo de espera del servidor:** 15 segundos, valor configurable sin cambiar el código fuente.
- **Estado de una tecnología en una zona:** exactamente uno de estos tres: disponible, pendiente de revisión o no disponible. Una tecnología sin estado registrado en la zona se considera no disponible.
- **Plan completo:** plan asignado a la zona con nombre, velocidad, precio mayor que cero y costo de instalación igual o mayor que cero.
- **Tecnología confirmada:** tecnología en estado disponible en la zona y con al menos un plan completo en esa zona.
- **Validación manual:** contacto por WhatsApp con el equipo de Econofibra JLINK. El sistema no registra nada.
- **Validación técnica del domicilio:** revisión que hace el equipo técnico fuera del sistema, tras el contacto por WhatsApp.

### Página
- RF-1: El sistema DEBE ofrecer una página de cobertura con estas secciones, en este orden y con estos textos:
  1. Navegación (RF-3).
  2. Mensaje principal "Encuentra el internet ideal para tu zona" y subtítulo "La disponibilidad, velocidad y precio dependen de tu ubicación y de la tecnología disponible."
  3. Barra "Consulta tu cobertura" con el texto "Comparte tu ubicación y descubre qué planes están disponibles en tu zona.", la pregunta de confirmación de domicilio, sus opciones, el aviso de RF-29 y el botón de compartir ubicación, con el texto "Compartir ubicación".
  4. Zona de resultados (RF-53).
  5. Tarjeta "Internet por Fibra Óptica" con "Alta velocidad", "Conexión estable", "Ideal para hogar y negocio" y el botón "Consultar en mi zona".
  6. Tarjeta "Internet por Antena" con "Cobertura en zonas alejadas", "Instalación según factibilidad", "Planes según ubicación" y el botón "Consultar en mi zona".
  7. Explicación del proceso "¿Cómo saber qué opción tienes?" con los pasos "Comparte tu ubicación", "Validamos la tecnología disponible" y "Te mostramos planes y precio para tu zona".
  8. Aviso de RF-2.
  9. Llamada final "¿No sabes qué tecnología llega a tu casa?", "Nuestro equipo te ayuda" y el botón "Contáctanos por WhatsApp".
- RF-2: El sistema DEBE mostrar en la página el aviso "La cobertura está sujeta a validación técnica del domicilio."
- RF-3: El sistema DEBE mostrar en la navegación el logotipo, las opciones "Inicio", "Servicios", "Planes", "Cobertura", "Soporte" y "Contacto" en el orden de la imagen de referencia, con "Cobertura" marcada como página activa, y el botón de WhatsApp "Contáctanos". "Inicio" lleva a la página principal y "Planes" a su sección de planes (spec 002); la raíz del sitio muestra la página principal y ya no redirige a esta página. Las demás opciones sin página se muestran sin enlace y se activan cuando esas páginas existan.
- RF-4: El sistema DEBE presentar la página sin desplazamiento horizontal y con todas sus funciones disponibles en anchos de pantalla de 360 px y de 1280 px.
- RF-5: El sistema DEBE reproducir a 1280 px la imagen de referencia docs/referencias/pagina-cobertura.jpeg en estructura, orden de secciones, colores, textos, tarjetas y estilo, salvo por las desviaciones de RF-52. Se comprueba comparando la página con la imagen, sección por sección, en estructura, orden, colores, textos y estilo, sin exigir igualdad píxel a píxel.
- RF-52: El sistema DEBE apartarse de la imagen de referencia solo en estas desviaciones:
  - Sin los campos de código postal, colonia, calle y número.
  - La barra "Consulta tu cobertura" conserva su lugar y aspecto, pero contiene los elementos de RF-1, crece en altura según ese contenido y su botón amarillo dice "Compartir ubicación" en lugar de "Consultar disponibilidad".
  - El texto "Ingresa tu dirección..." se sustituye por el de RF-1.
  - El paso 3 del proceso dice "Te mostramos planes y precio para tu zona" en lugar de "para tu domicilio".
  - "Inicio" y "Planes" enlazan a la página principal y las demás opciones de navegación sin página se muestran sin enlace (RF-3).
  - El logotipo y las ilustraciones pueden ser imágenes temporales parecidas y libres de uso.
  - Los elementos que la imagen no incluye (resultados, avisos, mensajes y estados inactivos) se añaden con los mismos colores, tipografía, bordes redondeados y sombras.
- RF-53: El sistema DEBE mostrar la zona de resultados inmediatamente debajo de la barra "Consulta tu cobertura", visible solo cuando haya un resultado o mensaje que mostrar.
- RF-54: MIENTRAS el ancho de pantalla sea de 360 px, el sistema DEBE conservar los textos y el orden de secciones de RF-1, apilando en una columna los elementos que a 1280 px van en fila.
- RF-28: CUANDO el usuario pulsa "Consultar en mi zona" en una tarjeta de tecnología, el sistema DEBE llevarlo al formulario de consulta.

### Confirmación y ubicación
- RF-6: El sistema DEBE enviar en cada consulta solo la latitud, la longitud, el margen de precisión si el dispositivo lo informa y la confirmación de domicilio, sin pedir campos de domicilio. Un margen nulo o vacío cuenta como no informado.
- RF-48: El sistema DEBE mostrar en el formulario la pregunta "¿Estás en el domicilio donde quieres el servicio?" con las opciones "Sí" y "No", sin ninguna seleccionada al inicio.
- RF-7: MIENTRAS la respuesta vigente a la pregunta de confirmación no sea "Sí" o haya un resultado en pantalla, el sistema DEBE mantener inactivo el botón de compartir ubicación.
- RF-8: SI el usuario responde "No", ENTONCES el sistema DEBE pedirle que consulte desde ahí o que escriba por WhatsApp, quitar de la pantalla cualquier mensaje anterior y borrar la ubicación conservada, sin solicitar su ubicación.
- RF-49: MIENTRAS haya una consulta en curso o un resultado en pantalla, el sistema DEBE mantener inactivas las opciones de la pregunta de confirmación y, con un resultado en pantalla, mostrar junto a ellas el texto "Para cambiar tu respuesta, usa Consultar de nuevo".
- RF-55: CUANDO comienza una consulta o el usuario cambia su respuesta a la pregunta de confirmación, el sistema DEBE retirar de la pantalla el mensaje anterior y descartar la ubicación conservada.
- RF-9: CUANDO el usuario pulsa el botón activo de compartir ubicación o un botón de reintentar, el sistema DEBE solicitar al dispositivo una posición nueva, sin reutilizar una posición guardada por el navegador ni por la página.
- RF-29: El sistema DEBE mostrar, antes de solicitar la ubicación, el aviso "Usamos tu ubicación para consultar la cobertura y no la guardamos. Si abres WhatsApp, prepararemos un mensaje con tu ubicación; tú decides si lo envías".
- RF-30: El servidor DEBE descartar la ubicación al responder la consulta, sin escribirla en la base de datos ni en los registros del sistema.
- RF-47: MIENTRAS la página siga abierta, el navegador DEBE conservar la ubicación de la última consulta solo en esa página, para armar el mensaje de WhatsApp, y borrarla al recargar o cerrar la página o cuando RF-8, RF-37, RF-38, RF-55, o RF-30 y RF-33 de la spec 002 la descarten, sin escribirla en el almacenamiento persistente del navegador.
- RF-31: El sistema DEBE tratar la confirmación de domicilio como una declaración del usuario, no como una prueba, y omitir cualquier texto que afirme que el domicilio fue verificado.
- RF-10: SI no se obtiene la ubicación, ENTONCES el sistema DEBE ofrecer validación manual, sin mostrar planes ni precios, y mostrar según la causa:
  - Permiso negado o bloqueado: los pasos para permitir la ubicación en los ajustes del navegador y un botón de reintentar.
  - Tiempo máximo de espera agotado o posición no disponible: el consejo de activar la ubicación del dispositivo y aceptar el permiso si el navegador lo pide, y un botón de reintentar.
  - Navegador sin función de ubicación: el consejo de usar otro navegador o dispositivo, sin botón de reintentar.
- RF-11: CUANDO el servidor recibe una consulta válida con precisión baja, el sistema DEBE mostrar el Escenario E con el motivo de precisión baja.
- RF-12: SI el servidor recibe una consulta sin confirmación de domicilio, con latitud o longitud ausentes o no numéricas, con latitud fuera de -90 a 90, con longitud fuera de -180 a 180, o con un margen de precisión informado (ni nulo ni vacío) que sea no numérico, cero o negativo, ENTONCES el sistema DEBE rechazarla sin devolver planes ni precios.
- RF-32: SI una consulta es rechazada, ENTONCES el sistema DEBE mostrar "No pudimos procesar tu ubicación", un botón de reintentar y la opción de validación manual.

### Determinación del resultado
- RF-13: CUANDO el servidor recibe una consulta válida, el sistema DEBE evaluar el margen de precisión en el servidor y resolver en este orden, deteniéndose en la primera condición que se cumpla:
  1. Precisión baja: Escenario E (RF-11).
  2. Ubicación ambigua entre zonas: Escenario E (RF-33).
  3. Ubicación sin zona, o zona con todas sus tecnologías no disponibles: Escenario D (RF-22).
  4. Zona sin tecnologías confirmadas: Escenario E (RF-23).
  5. Zona con tecnologías confirmadas: Escenario A, B o C (RF-18, RF-19, RF-20).
- RF-33: SI el punto cae en más de una zona, o su margen de precisión alcanza una zona distinta de la que contiene el punto, ENTONCES el sistema DEBE mostrar el Escenario E con el motivo de ubicación ambigua.
- RF-50: CUANDO el servidor identifica una única zona para la ubicación, DEBE incluir su nombre comercial en la respuesta, aunque la página no lo muestre.
- RF-14: El sistema DEBE tratar como confirmada una tecnología solo si cumple la definición de tecnología confirmada.
- RF-15: El sistema DEBE mostrar únicamente los planes completos de las tecnologías confirmadas en la zona identificada, con el nombre, la velocidad, el precio y el costo de instalación de esa zona.
- RF-16: El sistema DEBE obtener de la base de datos las zonas, tecnologías, planes, velocidades, precios y costos de instalación, e ignorar cualquiera de esos valores enviado por el navegador.
- RF-17: CUANDO se modifica en la base de datos una velocidad, un precio o un costo de instalación, el sistema DEBE mostrar el nuevo valor en las consultas siguientes, sin cambios en el código fuente.

### Escenarios de resultado
- RF-18: MIENTRAS fibra óptica sea la única tecnología confirmada en la zona (Escenario A), el sistema DEBE mostrar la tecnología y, por cada plan, su nombre, velocidad, precio, costo de instalación y un botón "Contratar".
- RF-19: MIENTRAS internet por antena sea la única tecnología confirmada en la zona (Escenario B), el sistema DEBE mostrar la tecnología y, por cada plan, su nombre, velocidad, precio, costo de instalación y un botón "Contratar".
- RF-20: MIENTRAS fibra óptica e internet por antena estén confirmadas en la zona (Escenario C), el sistema DEBE mostrar ambas tecnologías para compararlas y, por cada plan, su nombre, velocidad, precio, costo de instalación y un botón "Contratar".
- RF-21: MIENTRAS el resultado sea A, B o C, el sistema DEBE señalar como recomendada la fibra óptica si está confirmada y, si no lo está, el internet por antena.
- RF-34: MIENTRAS el resultado sea A, B o C, el sistema DEBE mostrar en cada plan, junto al precio y al costo de instalación, incluida "Instalación sin costo", el texto "Precio e instalación de tu zona, sujetos a validación técnica del domicilio".
- RF-35: CUANDO el costo de instalación de un plan es cero, el sistema DEBE mostrar "Instalación sin costo".
- RF-22: SI la ubicación no pertenece a ninguna zona, o todas las tecnologías de la zona están no disponibles (Escenario D), ENTONCES el sistema DEBE informar que no se pudo confirmar la disponibilidad y ofrecer por WhatsApp la validación manual y el registro para futuras expansiones, sin mostrar planes, precios ni recomendación.
- RF-23: SI la zona no tiene tecnologías confirmadas y al menos una está pendiente de revisión, o está disponible sin planes completos (Escenario E), ENTONCES el sistema DEBE informar que el domicilio requiere revisión del equipo técnico y ofrecer validación manual.
- RF-24: MIENTRAS el resultado sea el Escenario E, el sistema DEBE omitir en la zona de resultados planes, precios, recomendación y cualquier texto que garantice disponibilidad.
- RF-36: MIENTRAS el Escenario E tenga motivo de precisión baja o de ubicación ambigua, el sistema DEBE explicar que no se pudo ubicar el domicilio con seguridad, que requiere revisión del equipo técnico, aconsejar reintentar con la ubicación del dispositivo activada, y ofrecer un botón de reintentar y validación manual.

### Nueva consulta
- RF-37: CUANDO el sistema muestra un resultado, DEBE ofrecer un botón "Consultar de nuevo" que borra el resultado de la pantalla y la ubicación conservada, y deja la pregunta de confirmación sin responder. Con un resultado en pantalla, es la única forma de consultar otra vez, salvo el botón de reintentar de RF-36.
- RF-38: CUANDO el usuario pulsa reintentar, el sistema DEBE iniciar una consulta con una posición nueva, conservar su confirmación de domicilio, y quitar de la pantalla el resultado o mensaje anterior y la ubicación conservada.
- RF-39: MIENTRAS haya una consulta en curso, el sistema DEBE mantener inactivos los botones de compartir ubicación y de reintentar, e indicar que está consultando.
- RF-40: SI llega una posición o una respuesta de una consulta que el sistema ya dio por fallida al agotarse el tiempo máximo de espera de la ubicación o del servidor, ENTONCES el sistema DEBE descartarla sin cambiar lo que hay en pantalla.

### WhatsApp
- RF-25: CUANDO el usuario pulsa un botón "Contratar", de validación manual, de registro para futuras expansiones o de contacto, el sistema DEBE abrir una conversación de WhatsApp con el número del negocio.
- RF-41: CUANDO el sistema abre WhatsApp, DEBE preparar, sin enviarlo, porque la decisión de enviarlo es del usuario, un mensaje ya escrito con el motivo del contacto (contratación, validación manual, registro para futuras expansiones o contacto) y los datos que se conozcan, aunque no se muestren en pantalla, según la situación:
  - Botón "Contratar": enlace de la ubicación, nombre de la zona y nombre del plan elegido.
  - Otros botones en un Escenario A, B o C: enlace de la ubicación y nombre de la zona.
  - Escenario D con zona, o Escenario E por RF-23: enlace de la ubicación y nombre de la zona.
  - Escenario D sin zona, Escenario E por precisión baja o por ubicación ambigua, o fallo de RF-27: enlace de la ubicación, sin zona.
  - Consulta rechazada (RF-32), ubicación no obtenida (RF-10), usuario fuera del domicilio (RF-8) o sin consulta realizada: sin enlace ni zona.
- RF-51: El sistema DEBE formar el enlace de la ubicación como un enlace a un mapa web público que contenga la latitud y la longitud.
- RF-42: El sistema DEBE leer el número de WhatsApp de la configuración, sin número escrito en el código fuente.
- RF-43: SI no hay número de WhatsApp configurado, ENTONCES el sistema DEBE mostrar inactivos los botones de WhatsApp con el texto "WhatsApp no disponible por ahora" y mantener operativa la consulta.
- RF-44: CUANDO el usuario pulsa un botón de WhatsApp, el sistema DEBE mostrar en la página el texto "Si WhatsApp no se abrió, escríbenos al" seguido del número configurado.

### Datos de ejemplo y fallos
- RF-26: MIENTRAS exista en la base de datos al menos una zona, estado de tecnología, plan o precio marcado como de ejemplo, el sistema DEBE mostrar al inicio de cada resultado A, B, C, D o E el texto "Datos de ejemplo: estas zonas, planes y precios no son reales".
- RF-45: El sistema DEBE permitir marcar como de ejemplo cada registro de zona, estado de tecnología, plan y precio por separado.
- RF-46: El sistema DEBE incluir datos de ejemplo marcados que produzcan los Escenarios A, B, C, D y E, incluidas una zona con fibra pendiente de revisión, una zona con todas sus tecnologías no disponibles y dos zonas superpuestas.
- RF-27: SI la consulta no se puede completar por un fallo del sistema, una pérdida de conexión o porque se agota el tiempo máximo de espera del servidor, ENTONCES el sistema DEBE informar del error, ofrecer un botón de reintentar y la opción de validación manual, sin mostrar planes ni precios.

## Casos límite
- El usuario intenta compartir la ubicación sin confirmar el domicilio → el botón sigue inactivo y no se solicita la ubicación (RF-7).
- El usuario responde "No" y luego cambia a "Sí" → se retira el mensaje de RF-8, el botón se activa y puede consultar (RF-7, RF-55).
- El usuario pulsa compartir tras un mensaje de error → se retira el mensaje y se descarta la ubicación conservada (RF-55).
- El usuario responde "Sí" y luego cambia a "No" antes de consultar → el botón vuelve a quedar inactivo (RF-7, RF-8).
- El usuario quiere responder "No" con una consulta en curso o un resultado en pantalla → las opciones están inactivas; debe esperar la respuesta y pulsar "Consultar de nuevo" (RF-37, RF-49).
- El usuario responde "No" tras un mensaje de error → el mensaje se reemplaza y se borra la ubicación conservada (RF-8, RF-55).
- El usuario declara estar en el domicilio, pero está en otro sitio → el sistema no puede detectarlo; el precio y la instalación siguen sujetos a validación técnica (RF-31, RF-34).
- El permiso está bloqueado de forma permanente y reintentar no vuelve a preguntar → se muestran otra vez los pasos de los ajustes y la validación manual (RF-10).
- El navegador tiene una posición antigua guardada → no se usa; se pide una posición nueva (RF-9).
- Pasan 20 segundos desde la solicitud sin posición, incluso con el aviso de permiso sin responder → se trata como ubicación no obtenida por tiempo agotado (RF-10).
- La posición llega después de esos 20 segundos → se descarta (RF-40).
- Un reintento no logra obtener la posición → se muestra el mensaje de RF-10 y WhatsApp va sin enlace, porque la ubicación anterior ya se borró (RF-38, RF-41).
- Margen exactamente de 100 m → precisión suficiente; continúa la evaluación (RF-13).
- Margen de 100.1 m → Escenario E por precisión baja (RF-11).
- La precisión sigue baja tras varios reintentos, como en una computadora de escritorio → el usuario sigue en Escenario E, con reintento y validación manual disponibles (RF-36).
- Margen ausente, nulo o vacío → Escenario E por precisión baja, sin precios y con WhatsApp (RF-11, RF-24).
- Margen informado negativo, cero o no numérico → la consulta se rechaza (RF-12, RF-32).
- Coordenadas no numéricas o fuera de rango → la consulta se rechaza (RF-12, RF-32).
- Coordenadas válidas lejos de toda zona → Escenario D (RF-22).
- El punto no cae en ninguna zona, pero su margen alcanza una → Escenario E por ubicación ambigua (RF-33).
- El punto cae en una zona y su margen solo alcanza terreno sin zona → se usa esa zona (RF-13, RF-33).
- El punto cae en dos zonas superpuestas, aunque tengan la misma oferta → Escenario E (RF-33).
- El navegador envía un precio, plan o zona manipulados → se ignoran y se responde con los datos del servidor (RF-16).
- Un plan existe, pero no está disponible en la zona → no se muestra (RF-15).
- Un plan de la zona no tiene nombre, velocidad, precio o costo de instalación, o su precio es cero o negativo → no es un plan completo y no se muestra (RF-15).
- Costo de instalación negativo → no es un plan completo y no se muestra (RF-15).
- Costo de instalación igual a cero → el plan se muestra con "Instalación sin costo" y con el aviso de validación técnica (RF-34, RF-35).
- Una tecnología disponible se queda sin planes completos en la zona → deja de estar confirmada (RF-14).
- Fibra confirmada y antena pendiente de revisión en la misma zona → Escenario A, sin mostrar antena (RF-14, RF-18).
- Antena confirmada y fibra pendiente de revisión en la misma zona → Escenario B, con antena recomendada y sin mostrar fibra (RF-14, RF-19, RF-21).
- La zona tiene tecnologías pendientes o disponibles sin planes completos, y ninguna confirmada → Escenario E (RF-23).
- La zona solo tiene tecnologías no disponibles, o ninguna registrada → Escenario D (RF-22).
- Dos domicilios de una misma colonia caen en zonas distintas → cada uno recibe el resultado de su zona (RF-13, RF-15).
- Doble pulsación del botón de compartir → se ejecuta una sola consulta (RF-39).
- Hay planes en pantalla → "Compartir ubicación" está inactivo; "Consultar de nuevo" retira los planes y deja la pregunta sin responder (RF-7, RF-37).
- El Escenario E por precisión baja o ubicación ambigua ofrece reintentar → la consulta se repite sin volver a pedir la confirmación (RF-36, RF-38).
- Las tarjetas dicen "Cobertura en zonas alejadas" y "Alta velocidad" durante un Escenario D o E → se mantienen como en la imagen de referencia: son descripción general de cada tecnología, no una oferta para el domicilio. RF-24 se limita a la zona de resultados (RF-1, RF-24).
- El servidor no responde en 15 segundos → error con reintento y validación manual (RF-27).
- La respuesta llega después de esos 15 segundos, con o sin un reintento en curso → se descarta (RF-40).
- Se recarga o se cierra la página → se pierden la confirmación, la ubicación y el resultado (RF-7, RF-47).
- Cambia un precio con un resultado abierto → la pantalla no cambia; el nuevo valor aparece en la siguiente consulta (RF-17, RF-34).
- Se pierde la conexión durante la consulta → error con reintento y validación manual (RF-27).
- Sin ubicación, como en el usuario fuera del domicilio o el permiso negado → el mensaje de WhatsApp va sin enlace, zona ni plan (RF-41).
- Escenario E por zona pendiente de revisión → el mensaje lleva el enlace y la zona, aunque la página no muestre la zona (RF-41, RF-50).
- Escenario E por precisión baja o ubicación ambigua → el mensaje lleva el enlace, sin zona ni plan (RF-41).
- Escenario D con ubicación fuera de toda zona → el mensaje lleva el enlace, sin zona ni plan (RF-41).
- Fallo de RF-27 → el mensaje lleva el enlace, sin zona ni plan (RF-41).
- Consulta rechazada → el mensaje va sin enlace, zona ni plan (RF-41).
- No hay número de WhatsApp configurado → los botones quedan inactivos con su texto y la consulta sigue funcionando (RF-43).
- WhatsApp no se abre en el dispositivo → el usuario ve el número en la página (RF-44).
- Queda un solo registro de ejemplo entre datos reales → el aviso sigue visible en todos los resultados (RF-26).

## Criterios de finalización
- [x] Todos los RF tienen una prueba o verificación en verde. Resultado (T10): `php artisan test` 357/357, `npm test` 169/169 y recorrido en navegador sobre MySQL a 360 y 1280 px. Sin probar: posición real con permiso concedido y apertura real de WhatsApp.
- [x] La página a 1280 px está comparada con docs/referencias/pagina-cobertura.jpeg sección por sección en estructura, orden, colores, textos y estilo, sin exigir igualdad píxel a píxel, y las únicas diferencias son las de RF-52. Se admiten imágenes temporales parecidas y libres de uso. Resultado (T10): comparada sección por sección; solo aparecen las diferencias de RF-52.
- [x] Los Escenarios A, B, C, D y E están verificados en navegador, en vista móvil y de escritorio, usando ubicación simulada con un margen mayor que cero que corresponda a cada caso. Resultado (T10): 12 casos, iguales a 360 y 1280 px.
- [x] Los casos de permiso negado, tiempo agotado, navegador sin ubicación, usuario fuera del domicilio, precisión baja y ubicación ambigua están verificados en navegador. Resultado (T10): correctos a 360 y 1280 px; el tiempo agotado (20 s) y el del servidor (15 s) se midieron con su duración real a 360 px.
- [x] El caso de ubicación sin dato de precisión está verificado con una prueba del servidor. Resultado (T10): `CheckCoverageApiTest` y petición sobre MySQL con margen ausente, nulo y vacío: Escenario E.
- [x] Ningún precio, plan, velocidad o costo de instalación está escrito en la interfaz: todos provienen de la base de datos. Resultado (T10): sin valores en `resources/`; un cambio en MySQL se vio en la consulta siguiente.
- [x] Los datos de ejemplo producen los cinco escenarios y el aviso de RF-26 se ve en todos los resultados. Resultado (T10): comprobado con los datos de ejemplo cargados en MySQL.
- [x] Una prueba comprueba que la ubicación no queda en la base de datos, en los registros del sistema ni en el almacenamiento persistente del navegador. Resultado (T10): `LocationPrivacyTest`; coordenadas únicas ausentes del volcado de MySQL, `storage/` y `laravel.log` tras un error 500; localStorage, sessionStorage e IndexedDB vacíos.

## Pendiente antes de publicar
Nada de esto impide aprobar la spec ni implementarla; sí impide publicar la página.
- Configurar el número de WhatsApp real (RF-42, RF-43), que el usuario debe proporcionar.
- Cargar las zonas, planes y precios reales y retirar los datos de ejemplo (RF-26).
- Sustituir las imágenes temporales por el logotipo y las imágenes reales del mensaje principal y de las tarjetas (RF-52).

## Dudas abiertas
