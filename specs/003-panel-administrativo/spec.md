# Spec 003 — Panel administrativo

Estado: aprobada

## Problema
Las zonas, planes y precios de Econofibra JLINK solo se pueden cambiar directamente en la base de datos, y hoy son datos de ejemplo. El dueño del negocio no puede cargar su oferta real sin ayuda técnica, y eso frena la publicación del sitio. Como el panel cambia lo que ven los clientes, solo debe poder usarlo el administrador.

## Objetivo
Que el administrador entre a un área protegida, cargue sus zonas desde un archivo KML, defina qué tecnologías hay en cada zona, registre planes y precios, y retire los datos de ejemplo, sin tocar el código fuente ni la base de datos.

## Fuera de alcance
- Solicitudes de clientes: no existen en el sistema y guardarlas obligaría a revisar la privacidad de la spec 001 (RF-29 y RF-30). Quedan para una spec propia.
- Dibujo o vista de zonas en un mapa dentro del panel.
- Varias cuentas, roles, registro público y recuperación de contraseña por correo.
- Alta, edición o baja de tecnologías: fibra óptica e internet por antena son un catálogo fijo.
- Tarjetas de la portada, número de WhatsApp y redes sociales.
- Historial de cambios.
- Condiciones comerciales: siguen sin definirse.

## Requisitos funcionales

### Definiciones
- **Administrador:** la única persona con cuenta en el sistema.
- **Panel:** área privada del sitio con las funciones de esta spec.
- **Texto válido:** de 1 a 100 caracteres, sin contar los espacios de los extremos.
- **Velocidad válida:** número entero de 1 a 100 000 Mbps.
- **Importe válido:** cantidad con hasta dos decimales y un máximo de 999 999.99. El precio debe ser mayor que cero y el costo de instalación, igual o mayor que cero.
- **Polígono válido:** de 3 a 1 000 vértices distintos, coordenadas dentro de rango, área mayor que cero, lados que no se cruzan entre sí, sin huecos y sin cruzar el antimeridiano.
- **Mismo nombre de zona:** dos nombres que coinciden tras quitar los espacios de los extremos, sin distinguir mayúsculas y distinguiendo acentos. Esta comparación es la misma en la subida del KML, al renombrar y en la base de datos.
- **Confirmación explícita:** campo de confirmación que el panel incluye en la petición solo después de que el administrador acepta el aviso en pantalla. Lo exige el servidor (RF-20).
- **Datos de ejemplo:** zonas, estados de tecnología, planes y precios marcados como de ejemplo (RF-45 de la spec 001).
- **Dato real dependiente:** precio o estado de tecnología real en una zona de ejemplo, precio real en un plan de ejemplo, o zona de ejemplo cuyo límite se reemplazó con un KML.

### Acceso
- RF-1: El sistema DEBE ofrecer el panel sin registro público y sin enlaces desde la navegación ni el footer del sitio público.
- RF-2: CUANDO se ejecuta desde la consola del servidor la acción de crear la cuenta o restablecer su contraseña, el sistema DEBE crear la cuenta si no existe o sustituir su contraseña si ya existe, sin crear nunca una segunda cuenta. Si el correo indicado es distinto del de la cuenta existente, lo reemplaza e informa del cambio en la consola. Exige al menos 12 caracteres, guarda la contraseña de forma no legible y cierra las sesiones abiertas.
- RF-3: SI una petición al panel llega sin sesión iniciada o con la sesión vencida, ENTONCES el sistema DEBE llevarla a la entrada del panel, sin mostrar datos ni aplicar cambios.
- RF-4: SI el correo o la contraseña son incorrectos, ENTONCES el sistema DEBE mostrar un mismo mensaje en ambos casos, sin indicar cuál falló. Tras 5 intentos fallidos seguidos con un mismo correo desde una misma dirección de red, DEBE rechazar la entrada durante 15 minutos, aunque la contraseña sea correcta, con el mensaje "Demasiados intentos. Vuelve a intentarlo más tarde". El correo se compara sin distinguir mayúsculas, tanto al entrar como al contar los intentos.
- RF-5: CUANDO el administrador cierra sesión o pasan 30 minutos sin actividad, el sistema DEBE terminar la sesión.
- RF-6: SI una petición de cambio no demuestra que proviene de un formulario del propio panel, ENTONCES el sistema DEBE rechazarla sin aplicar ningún cambio.
- RF-7: MIENTRAS el sitio esté en producción, el sistema DEBE servir todo el sitio por HTTPS, redirigir a HTTPS cualquier petición HTTP y enviar la cookie de sesión con los atributos `secure`, `httponly` y `samesite=lax`. El atributo `secure` se define con `SESSION_SECURE_COOKIE` en la configuración.

### Zonas
- RF-8: CUANDO el administrador sube un archivo KML válido, el sistema DEBE crear una zona por cada elemento con nombre que contenga polígonos, reemplazar el límite de la zona que ya tenga ese nombre y dejar intactas las zonas que no aparecen en el archivo. Luego DEBE mostrar la lista de zonas creadas y reemplazadas y cuántos puntos o líneas ignoró. Las zonas solo se crean por este medio.
- RF-9: SI el archivo no es un KML legible, pesa más de 2 MB, contiene DOCTYPE o entidades, supera 10 000 vértices en total, no contiene polígonos, tiene un polígono cuyo nombre no es un texto válido o que no es un polígono válido, o repite un mismo nombre de zona, ENTONCES el sistema DEBE rechazar el archivo completo, sin cambiar ninguna zona, e indicar el motivo y, si lo hay, el nombre del elemento. Los límites de vértices protegen el tiempo de respuesta de la consulta pública y se cambian sin modificar el código fuente.
- RF-22: CUANDO el sistema lee un archivo KML, DEBE usar solo el contenido del propio archivo, sin seguir `NetworkLink`, `href` ni ninguna otra referencia externa.
- RF-21: El sistema DEBE impedir, también en la base de datos, que dos zonas tengan el mismo nombre de zona.
- RF-10: El sistema DEBE permitir al administrador renombrar una zona con un texto válido que no use otra zona, y eliminarla tras una confirmación explícita, lo que borra también sus estados de tecnología y sus precios.
- RF-11: El sistema DEBE permitir al administrador asignar a cada zona, para fibra óptica y para internet por antena, un estado: disponible, pendiente de revisión o no disponible. Una tecnología sin estado registrado se muestra como no disponible.

### Planes y precios
- RF-12: El sistema DEBE permitir al administrador crear y editar planes con tecnología, nombre (texto válido) y velocidad válida, y eliminarlos tras una confirmación explícita, lo que borra también sus precios en todas las zonas. La tecnología de un plan no se puede cambiar mientras tenga precios en alguna zona.
- RF-13: El sistema DEBE permitir al administrador asignar un plan a una zona con su precio y costo de instalación, ambos importes válidos, cambiar esos importes y quitar el plan de la zona.

### Reglas comunes
- RF-14: SI un dato no cumple estas reglas, hace referencia a una zona o un plan inexistentes, o no se puede guardar, ENTONCES el sistema DEBE rechazar el cambio completo en el servidor, sin guardar nada parcialmente, y mostrar el motivo.
- RF-15: CUANDO el panel guarda un cambio, el sistema DEBE aplicarlo en las consultas de cobertura siguientes, sin cambios en el código fuente y sin alterar las tarjetas de la portada.
- RF-20: SI una petición de eliminar una zona, eliminar un plan o borrar los datos de ejemplo llega sin el campo de confirmación, ENTONCES el sistema DEBE rechazarla sin aplicar ningún cambio, aunque la sesión y el origen sean válidos.

### Datos de ejemplo
- RF-16: El sistema DEBE señalar en el panel los registros que son datos de ejemplo y mostrar cuántos quedan de cada tipo. Lo creado en el panel es real; editar un registro de ejemplo no le quita la marca.
- RF-17: CUANDO el administrador pide con confirmación explícita borrar los datos de ejemplo y no hay ningún dato real dependiente, el sistema DEBE eliminar todos esos registros en una sola operación que se aplica completa o no se aplica.
- RF-19: SI al pedir ese borrado hay algún dato real dependiente, comprobado en el servidor en ese momento, ENTONCES el sistema DEBE no borrar nada y mostrar cada dato con lo que debe corregirse antes: quitar el precio real de la zona o eliminar la zona de ejemplo.

### Interfaz
- RF-18: El sistema DEBE presentar el panel con la identidad visual del sitio, sin desplazamiento horizontal de la página y con todas sus funciones disponibles en anchos de 360 px y 1280 px.

## Casos límite
- Se abre una dirección del panel sin sesión → se llega a la entrada y no se ve ningún dato (RF-3).
- La sesión vence con un formulario abierto → el cambio no se guarda y se pide entrar de nuevo (RF-3, RF-5).
- Se intenta entrar con la contraseña correcta durante el bloqueo → se rechaza hasta que pasen los 15 minutos (RF-4).
- Se ejecuta la acción de consola con la cuenta ya creada → cambia la contraseña; no aparece una segunda cuenta (RF-2).
- Se sube un archivo KMZ, el formato que Google My Maps puede ofrecer al exportar → se rechaza con el consejo de exportarlo como KML (RF-9).
- El KML contiene también puntos o líneas → se ignoran y se informa cuántos (RF-8).
- Un solo polígono del archivo es inválido o tiene huecos → no se aplica ninguna zona del archivo (RF-9).
- Un elemento del KML tiene varios polígonos → forman una sola zona (RF-8).
- Una zona recién creada aún no tiene estados ni planes → la consulta da Escenario D hasta que se completen (RF-11; RF-22 de la spec 001).
- Dos zonas cargadas se superponen → se aceptan; un punto en ambas da Escenario E (RF-8; RF-33 de la spec 001).
- Se reemplaza el límite de una zona → conserva sus estados y precios (RF-8).
- Se reemplaza el límite de una zona de ejemplo → sigue siendo de ejemplo y bloquea el borrado; para tenerla como real se elimina y se vuelve a subir el KML (RF-8, RF-16, RF-19).
- El KML trae un `NetworkLink` y polígonos propios → el enlace no se sigue y se cargan los polígonos del archivo (RF-22).
- Se renombra una zona y solo cambian sus mayúsculas → se acepta, porque no choca con otra zona (RF-10, RF-21).
- Se pide cambiar la tecnología de un plan con precios → se rechaza; antes hay que quitarlo de sus zonas (RF-12, RF-14).
- Se cambia el correo con la acción de consola → la entrada pasa a usar el correo nuevo (RF-2).
- Llega una petición HTTP en producción → se redirige a HTTPS (RF-7).
- Se asigna un precio a un plan cuya tecnología no está disponible en la zona → se guarda, pero no se muestra al cliente (RF-13; RF-14 de la spec 001).
- Precio cero o negativo → se rechaza (RF-14).
- Costo de instalación cero → se guarda y el cliente ve "Instalación sin costo" (RF-13; RF-35 de la spec 001).
- Se manipula el formulario para enviar un importe inválido o una zona inexistente → el servidor lo rechaza (RF-14).
- Hay un precio real en una zona o un plan de ejemplo → el borrado no se aplica y el panel indica qué corregir (RF-19).
- El administrador elimina a mano una zona de ejemplo con datos reales → se borran con ella, porque es una eliminación individual confirmada (RF-10, RF-19).
- Se edita un registro de ejemplo → sigue siendo de ejemplo, no bloquea el borrado y se elimina con él (RF-16, RF-17).
- Una petición con sesión y origen válidos no trae el campo de confirmación → se rechaza y nada cambia (RF-20).
- Se borran los datos de ejemplo antes de cargar las zonas reales → sin zonas, toda consulta da Escenario D (RF-17).
- Se borran los datos de ejemplo → el aviso de RF-26 de la spec 001 desaparece; las tecnologías y las tarjetas de la portada permanecen (RF-15, RF-17).
- No quedan datos de ejemplo → la acción de borrarlos queda inactiva (RF-16).

## Criterios de finalización
- [x] Las pruebas automáticas están en verde y el repaso en navegador está hecho.
- [x] Hay pruebas automáticas del acceso, la cookie y HTTPS, la lectura del KML, las validaciones, el campo de confirmación y el bloqueo del borrado de datos de ejemplo.
- [x] Un cambio de precio hecho en el panel se ve en la consulta de cobertura siguiente.
- [x] Las pruebas de las specs 001 y 002 siguen en verde.

## Pendiente antes de publicar
Nada de esto impide aprobar la spec ni implementarla; sí impide publicar el sitio.
- Crear la cuenta del administrador en el servidor (RF-2).
- Tener HTTPS activo en el servidor y `SESSION_SECURE_COOKIE` definido en su configuración (RF-7).
- Configurar los proxies de confianza si el sitio va detrás de uno, para detectar bien HTTPS y la dirección de red (RF-4, RF-7).
- Cargar las zonas, planes y precios reales y después borrar los datos de ejemplo (RF-17).
- No volver a cargar los datos de ejemplo de la spec 001 (RF-46) en producción.

## Dudas abiertas
