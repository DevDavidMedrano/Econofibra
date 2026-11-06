# Spec 002 — Página principal

Estado: aprobada

## Problema
Hoy la raíz del sitio redirige a la consulta de cobertura: quien llega a Econofibra JLINK no ve quién es la empresa, qué planes ofrece ni por qué elegirla. Además, la cobertura no es uniforme, así que la página debe presentar la oferta general sin que parezca una oferta para un domicilio concreto y sin mostrar precios que dependen de la zona.

## Objetivo
Que una persona que llega al sitio conozca a Econofibra JLINK, sus planes y sus ventajas, y pase a la consulta de cobertura para confirmar qué le corresponde en su domicilio, o escriba por WhatsApp. La página reproduce el mockup con los cambios aprobados por el usuario.

## Fuera de alcance
- Páginas de Servicios, Soporte y Contacto.
- Panel administrativo: las tarjetas de planes se editan directamente en la base de datos.
- Precios, costos de instalación y recomendación de tecnología en la página principal.
- Cambios en la consulta de cobertura de la spec 001, salvo la navegación (RF-3), la llegada a la barra de consulta (RF-17) y la salida de la página (RF-30, RF-33).
- Footer en la página de cobertura: su imagen de referencia no lo incluye.
- Versión del logotipo para fondo oscuro.
- Carga de las zonas, planes y precios reales por zona, que siguen siendo datos de ejemplo de la spec 001.

## Requisitos funcionales

### Definiciones
- **Tarjeta de plan:** presentación informativa de un plan comercial con nombre, velocidad, frase, clave de icono, posición y marca "Más Popular" opcional. No implica que el plan exista en cada zona y no sustituye a los planes por zona de la spec 001.
- **Clave de icono:** valor guardado en la tarjeta que elige un icono del sistema de esta lista cerrada: `casa`, `control`, `maletin` y `cohete`. Una clave que no está en la lista usa el icono por defecto del sistema, genérico de conectividad.
- **Tarjeta completa:** tarjeta de plan con nombre, frase y clave de icono no vacíos, y velocidad mayor que cero. Un valor con solo espacios cuenta como vacío.
- **Red social configurada:** red con una dirección `https://` con dominio en la configuración. Una dirección vacía o sin esa forma cuenta como no configurada.

### Página
- RF-1: CUANDO el usuario abre la raíz del sitio, el sistema DEBE mostrar la página principal sin redirigir a la página de cobertura.
- RF-2: El sistema DEBE mostrar en la página principal estas secciones, en este orden y con estos textos:
  1. Navegación (RF-3).
  2. Mensaje principal "Conectando tu mundo con mayor velocidad", subtítulo "Internet de fibra óptica confiable, estable y rápido para tu hogar o negocio.", botón "¡Contrata Ahora!" e indicadores "Alta Velocidad", "Conexión Estable", "Soporte Técnico 24/7" y "Cobertura en tu Zona".
  3. Sección de planes "Nuestros Planes", con el subtítulo "Elige el plan ideal para ti", las tarjetas de plan (RF-8) y el aviso de RF-12. Sin tarjetas que mostrar se aplica RF-15.
  4. Franja "¿Por qué elegir Econofibra JLINK?" con "Internet de calidad", "Atención personalizada", "Instalación profesional" y "Soporte técnico siempre contigo".
  5. Llamada "¡Estamos listos para conectarte!" con el botón "Contáctanos por WhatsApp", junto a la franja anterior como en la imagen de referencia.
  6. Footer (RF-18).
- RF-3: El sistema DEBE mostrar en la página principal y en la de cobertura la misma navegación, salvo la opción activa de cada página: logotipo, opciones "Inicio", "Servicios", "Planes", "Cobertura", "Soporte" y "Contacto" en ese orden, y botón de WhatsApp "Contáctanos". Se aplican estos destinos:
  - El logotipo e "Inicio" llevan a la página principal.
  - "Planes" lleva a la sección de planes de la página principal, también desde la página de cobertura.
  - "Cobertura" lleva a la página de cobertura.
  - "Servicios", "Soporte" y "Contacto" se muestran sin enlace hasta que sus páginas existan. "Servicios" conserva la flecha de la imagen de referencia, sin menú desplegable.
- RF-4: MIENTRAS el usuario esté en la página principal, el sistema DEBE marcar "Inicio" como opción activa. En la página de cobertura la opción activa sigue siendo "Cobertura".
- RF-5: El sistema DEBE usar docs/referencias/hero-principal.jpeg como imagen real del mensaje principal, con una descripción alternativa. Los textos del mensaje principal son texto de la página, no parte de la imagen.
- RF-6: El sistema DEBE reproducir a 1280 px la imagen de referencia docs/referencias/pagina-principal.jpeg en estructura, orden de secciones, colores, textos, tarjetas y estilo, salvo por las desviaciones de RF-7. Se comprueba comparando la página con la imagen, sección por sección, sin exigir igualdad píxel a píxel.
- RF-7: El sistema DEBE apartarse de la imagen de referencia solo en estas desviaciones:
  - La navegación sigue RF-3: "Cobertura" va antes que "Soporte", conserva el icono de ubicación de la página de cobertura y las opciones sin página van sin enlace.
  - La imagen del mensaje principal es la foto real (RF-5), que no es igual al fondo del mockup, y lleva el degradado de RF-28.
  - El logotipo es el real, sobre fondo blanco, también en el footer (RF-19).
  - "Instalación rápida" se sustituye por "Instalación profesional".
  - La sección de planes añade el aviso de RF-12.
  - El footer no incluye el icono del globo.
  - El footer añade el contacto por WhatsApp de RF-20, porque las reglas del proyecto exigen contacto en el footer.
  - Los iconos de redes sociales no configuradas se ocultan (RF-21).
  - Las tarjetas de plan se animan (RF-29).
  - Los elementos que la imagen no incluye (avisos, mensajes y estados inactivos) se añaden con los mismos colores, tipografía, bordes redondeados y sombras.
- RF-22: El sistema DEBE presentar la página principal sin desplazamiento horizontal y con todas sus funciones disponibles en anchos de pantalla de 360 px y de 1280 px.
- RF-23: MIENTRAS el ancho de pantalla sea de 360 px, el sistema DEBE conservar los textos y el orden de secciones de RF-2, apilando en una columna los elementos que a 1280 px van en fila. El mensaje principal, el subtítulo, el botón y los indicadores van sobre fondo azul marino, separados de la foto; la foto va debajo, con el rostro de la persona completo a la vista.
- RF-28: MIENTRAS los textos del mensaje principal vayan sobre la foto, el sistema DEBE aplicar bajo ellos un degradado azul marino, sin cubrir el rostro de la persona, que deje un contraste mínimo de 3:1 en el mensaje principal y de 4.5:1 en el subtítulo y los indicadores, medido contra el punto más claro del fondo detrás de cada texto.
- RF-29: DONDE el dispositivo no pida reducir el movimiento, el sistema DEBE animar las tarjetas de plan al aparecer y al pasar el cursor sobre ellas, con una duración máxima de 300 ms por animación y sin mover los demás elementos de la página.
- RF-32: MIENTRAS el dispositivo pida reducir el movimiento, el sistema DEBE mostrar las tarjetas de plan sin animaciones.

### Tarjetas de plan
- RF-8: El sistema DEBE mostrar cada tarjeta completa con el icono de su clave, nombre, velocidad en Mbps, frase y botón "Contratar", en orden ascendente de su posición guardada. Las tarjetas sin posición van al final. Las que empatan, incluidas las que no tienen posición, se ordenan por nombre y, si también coincide, por orden de creación.
- RF-9: El sistema DEBE obtener de la base de datos el nombre, la velocidad, la frase, la clave de icono, la posición y la marca "Más Popular" de cada tarjeta de plan.
- RF-10: CUANDO se modifica en la base de datos uno de los valores de RF-9, el sistema DEBE mostrar el nuevo valor en la siguiente carga de la página, sin cambios en el código fuente.
- RF-11: El sistema DEBE incluir como datos iniciales reales, sin marcarlas como de ejemplo, estas tarjetas en este orden:
  1. "Hogar Conectado", 50 Mbps, "Navega, estudia y disfruta", con clave de icono `casa`.
  2. "Gaming", 100 Mbps, "Juega sin lag", con clave de icono `control`.
  3. "Negocios", 200 Mbps, "Trabaja sin interrupciones", con clave de icono `maletin`.
  4. "Premium", 300 Mbps, "Máximo rendimiento", con clave de icono `cohete` y marca "Más Popular".
- RF-31: CUANDO se cargan los datos iniciales y ya existe alguna tarjeta de plan, el sistema DEBE dejar las tarjetas existentes como están, sin duplicarlas, sobrescribirlas ni añadir otras.
- RF-12: El sistema DEBE mostrar en la sección de planes el aviso "Disponibilidad y precio sujetos a la cobertura de tu zona".
- RF-13: DONDE una tarjeta tenga la marca "Más Popular", el sistema DEBE mostrarla con la etiqueta "Más Popular", el borde amarillo y el botón amarillo de la imagen de referencia.
- RF-14: El sistema DEBE omitir en la página principal precios (salvo el anuncio general "Planes desde", pedido por el usuario y configurable), costos de instalación, recomendación de tecnología y cualquier texto que afirme que un plan está disponible en el domicilio del usuario.
- RF-15: SI no hay ninguna tarjeta completa o no se pueden leer las tarjetas, ENTONCES el sistema DEBE mostrar el resto de la página y, en la sección de planes, el título, sin el subtítulo, el aviso de RF-12 y un botón "Consultar en mi zona" que lleva al mismo destino que RF-17.
- RF-16: El sistema DEBE mantener las tarjetas de plan independientes de los planes por zona: ni cuentan para el aviso de datos de ejemplo (RF-26 de la spec 001) ni alteran los resultados de la consulta de cobertura.

### Acciones
- RF-17: CUANDO el usuario pulsa "¡Contrata Ahora!" o el "Contratar" de una tarjeta, el sistema DEBE llevarlo a la página de cobertura con la barra "Consulta tu cobertura" a la vista, sin abrir WhatsApp. RF-25 de la spec 001, por el que "Contratar" abre WhatsApp, aplica solo a los botones de la página de cobertura.
- RF-24: El sistema DEBE abstenerse de solicitar la ubicación del usuario en la página principal.
- RF-25: CUANDO el usuario pulsa un botón de WhatsApp de la página principal, el sistema DEBE abrir una conversación con el número configurado y un mensaje preparado, sin enviarlo, con el motivo de contacto, sin enlace de ubicación, zona ni plan.
- RF-26: SI no hay número de WhatsApp configurado, ENTONCES el sistema DEBE mostrar inactivos los botones de WhatsApp de la página principal con el texto "WhatsApp no disponible por ahora" y mantener operativo el resto de la página. En el footer, el texto va junto al botón, fuera de él, y el botón conserva su tamaño y su forma de icono.
- RF-27: CUANDO el usuario pulsa un botón de WhatsApp de la página principal, el sistema DEBE mostrar el texto "Si WhatsApp no se abrió, escríbenos al" seguido del número configurado, en un aviso fijo en la parte inferior de la pantalla, como en la página de cobertura, y reservar espacio al final de la página para que el aviso no tape el footer.

### Salida de la página de cobertura
- RF-30: CUANDO el usuario sale de la página de cobertura por un enlace o con los botones del navegador, el sistema DEBE descartar la ubicación conservada, el resultado y cualquier consulta en curso. Abrir WhatsApp no cuenta como salir de la página: se conservan el resultado y la ubicación.
- RF-33: CUANDO el usuario vuelve a la página de cobertura con Atrás o Adelante, el sistema DEBE mostrarla en su estado inicial, con la pregunta de confirmación sin responder, sin restaurar la ubicación, el resultado ni una consulta en curso.

### Footer
- RF-18: El sistema DEBE mostrar en el footer de la página principal, sobre fondo azul marino, el logotipo, el texto "Tu mejor conexión, nuestro compromiso" y los contactos de RF-20 y RF-21.
- RF-19: El sistema DEBE mostrar el logotipo del footer sobre una pestaña blanca, como en la navegación.
- RF-20: El sistema DEBE mostrar en el footer, en el lugar de los iconos de redes sociales y junto a ellos, un botón de contacto por WhatsApp con el aspecto de esos iconos y el nombre accesible "Contáctanos por WhatsApp".
- RF-21: DONDE Facebook o YouTube sean redes sociales configuradas, el sistema DEBE mostrar su icono con un enlace a esa dirección, que se abre en una pestaña nueva, y ocultarlo mientras no lo estén. Las direcciones se cambian sin modificar el código fuente.

## Casos límite
- Se abre `/` → se ve la página principal; ya no hay redirección a `/cobertura` (RF-1).
- Se abre `/cobertura` directamente → la consulta funciona como en la spec 001, con los enlaces nuevos en la navegación (RF-3).
- Se pulsa "Planes" desde `/cobertura` → se abre la página principal en la sección de planes (RF-3).
- Se pulsa "Planes" en la página principal → la página va a la sección de planes y "Inicio" sigue activa (RF-3, RF-4).
- El usuario pulsa "Contratar" en "Premium" y en su zona no existe ese plan → llega a la consulta de cobertura, que solo le muestra los planes de su zona; el plan de la tarjeta no se conserva (RF-16, RF-17).
- El usuario vio "fibra óptica" en el mensaje principal y en su zona solo hay antena → el texto es comercial general; la consulta muestra lo que corresponde a su ubicación (RF-14, RF-17).
- Una tarjeta no tiene nombre, frase o clave de icono, alguno tiene solo espacios, o su velocidad es cero, negativa o nula → no se muestra; las demás sí (RF-8).
- Ninguna tarjeta es completa, la tabla está vacía o falla la lectura → la página se muestra sin subtítulo de planes y con el botón "Consultar en mi zona" (RF-15).
- Hay más o menos de cuatro tarjetas → se muestran todas las completas, cada una con el icono de su clave, sin desplazamiento horizontal (RF-8, RF-22).
- La clave de icono de una tarjeta no está en la lista cerrada → la tarjeta se muestra con el icono por defecto (RF-8).
- Se cambia la clave de icono de una tarjeta por otra de la lista → el nuevo icono se ve en la siguiente carga (RF-9, RF-10).
- Dos tarjetas tienen la misma posición → se ordenan por nombre y, si coincide, por orden de creación (RF-8).
- Una tarjeta no tiene posición → va después de las que la tienen (RF-8).
- Se vuelven a cargar los datos iniciales con tarjetas ya editadas → las tarjetas quedan como estaban (RF-31).
- Se vuelven a cargar los datos iniciales sin ninguna tarjeta → se crean las cuatro de RF-11 (RF-11, RF-31).
- Ninguna tarjeta tiene la marca "Más Popular" → todas se ven con el estilo normal (RF-13).
- Varias tarjetas tienen la marca → cada una se ve destacada (RF-13).
- La marca pasa de "Premium" a otra tarjeta → el estilo destacado cambia en la siguiente carga (RF-10, RF-13).
- Un nombre o una frase largos → el texto salta de línea sin salirse de la tarjeta (RF-8, RF-22).
- Cambia la velocidad de una tarjeta → no cambia ningún plan de ninguna zona (RF-10, RF-16).
- Se retiran los datos de ejemplo de la spec 001 → las tarjetas de plan permanecen (RF-11, RF-16).
- Existen datos de ejemplo por zona → la página principal no muestra el aviso "Datos de ejemplo", porque no presenta esos datos (RF-14, RF-16).
- Solo una red social está configurada → se muestra solo ese icono (RF-21).
- Ninguna red social está configurada → el footer muestra el logotipo, el texto y el contacto por WhatsApp (RF-18, RF-20, RF-21).
- Una dirección de red social no empieza por `https://` o no tiene dominio → cuenta como no configurada y el icono se oculta (RF-21).
- No hay número de WhatsApp configurado → los botones de WhatsApp quedan inactivos con su texto; "¡Contrata Ahora!" y "Contratar" siguen llevando a la consulta (RF-17, RF-26).
- No hay número de WhatsApp configurado y se mira el footer → el botón de icono queda inactivo con su forma y el texto va a su lado (RF-26).
- WhatsApp no se abre en el dispositivo → el usuario ve el número en la página (RF-27).
- El navegador envía valores de plan manipulados → la página no recibe ni usa datos de plan del navegador (RF-9).
- El dispositivo pide reducir el movimiento → las tarjetas aparecen directamente y no reaccionan con animación al cursor (RF-32).
- El usuario sale de `/cobertura` con un resultado en pantalla → no se le pide confirmación; el resultado y la ubicación se descartan (RF-30).
- El usuario sale de `/cobertura` con una consulta en curso → la consulta se descarta y su respuesta no se usa (RF-30).
- El usuario vuelve a `/cobertura` con Atrás → ve la página en su estado inicial y debe responder la pregunta otra vez; el navegador no restaura una copia guardada (RF-33).

## Criterios de finalización
- [x] Todos los RF tienen una prueba o verificación en verde.
- [x] La página a 1280 px está comparada con docs/referencias/pagina-principal.jpeg sección por sección, y las únicas diferencias son las de RF-7.
- [x] La página está verificada en navegador a 360 y 1280 px, incluidos los enlaces de navegación desde la página principal y desde la de cobertura.
- [x] Ningún precio (salvo el anuncio general "Planes desde") ni costo de instalación aparece en la página principal, y el nombre, la velocidad, la frase, la clave de icono, la posición y la marca de cada tarjeta provienen de la base de datos. Los dibujos de los iconos son del sistema.
- [x] Un cambio de tarjeta en la base de datos se ve en la siguiente carga y no altera los resultados de la consulta de cobertura.
- [x] El contraste de RF-28 está medido en navegador a 1280 px sobre la foto real.
- [x] La salida de `/cobertura` y el regreso con Atrás están verificados en navegador con un resultado en pantalla y con una consulta en curso (RF-30, RF-33).
- [x] La consulta de cobertura de la spec 001 sigue pasando todas sus pruebas, con las actualizaciones que exigen RF-1 y RF-3.

## Pendiente antes de publicar
Nada de esto impide aprobar la spec ni implementarla; sí impide publicar la página.
- Configurar las direcciones de Facebook y YouTube, que el usuario debe proporcionar (RF-21).
- Configurar el número de WhatsApp real (RF-25, RF-26).
- Cargar las zonas, planes y precios reales de la spec 001: "¡Contrata Ahora!" y "Contratar" llevan a esa consulta.

## Dudas abiertas
