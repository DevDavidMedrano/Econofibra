# Constitución — Econofibra JLINK

Principios que ninguna spec, plan o tarea puede incumplir. El detalle está en `AGENTS.md`. Cambiar un principio requiere la aprobación del usuario.

## I. La cobertura no es uniforme

- Nunca se asume que todos los clientes tienen acceso a los mismos planes, tecnologías o precios.
- La cobertura se valida por ubicación y factibilidad técnica.
- Sin confirmación no se garantiza disponibilidad ni se presentan precios como definitivos.
- Los textos comerciales generales de la página principal, como el del hero, no son una oferta para un domicilio concreto: la disponibilidad se confirma en la consulta de cobertura.

## II. Los precios los decide el servidor

- Los precios dependen de zona, tecnología, plan y costos de instalación, y de las condiciones comerciales cuando estén definidas.
- Provienen de la base de datos, nunca de componentes visuales ni de valores enviados por el navegador.
- Se pueden modificar sin cambiar el código fuente.

## III. Solo se recomienda lo disponible

- Nunca se recomienda una tecnología que no esté disponible en la ubicación.
- Si hay fibra óptica confirmada, se recomienda.
- La lógica de recomendación pertenece al dominio.

## IV. Clean Architecture

- Las dependencias apuntan hacia el dominio: Presentation e Infrastructure → Application → Domain.
- El dominio no depende de Laravel, Eloquent, controladores, plantillas ni infraestructura.
- Eloquent permanece en Infrastructure y sus modelos no son entidades del dominio.
- Los controladores son delgados y no contienen reglas de negocio.

## V. Código PHP

- PHP 8.2 o superior, `declare(strict_types=1);`, namespaces y PSR-4.
- Cada archivo tiene una responsabilidad; no se mezclan lógica, SQL y presentación.

## VI. Seguridad

- Las entradas del usuario se validan en el servidor.
- No se concatenan entradas del usuario en consultas SQL.
- Las credenciales quedan fuera del código fuente.

## VII. Pruebas primero

- En la lógica, la prueba se escribe antes que el código.
- Ninguna tarea se da por hecha con `php artisan test` en rojo.
- Las reglas de fechas siguen la skill `local-dates`.

## VIII. Experiencia

- Toda página funciona en móvil y escritorio.
- Se respeta la identidad visual de `AGENTS.md`: azul marino, amarillo de acento, fondos blancos y azul claro.
- El diseño reproduce fielmente las imágenes de referencia, con solo los cambios que exigen estas reglas o que el usuario apruebe.

## IX. Proceso

- Las funcionalidades siguen el flujo SDD (skill `sdd`) con aprobación del usuario de la spec, el plan y las tareas.
- Los cambios pequeños pueden usar `/feature`, salvo que toquen cobertura, precios o recomendación.
