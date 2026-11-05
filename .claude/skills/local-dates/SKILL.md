---
name: local-dates
description: Reglas de fechas y horas de Econofibra JLINK. Úsala al escribir o revisar código que maneje fechas, horas, vigencias de precios, solicitudes de servicio o cualquier cálculo con "hoy".
---

# Fechas y horas

1. **"Hoy" es un parámetro.** El dominio nunca llama a `now()`, `today()`, `Carbon::now()`, `date()` ni `time()`. Recibe la fecha como parámetro o mediante una interfaz `Clock` definida en el dominio e implementada en Infrastructure.
2. **El dominio no depende de Carbon ni de Laravel.** Usa `DateTimeImmutable`; nunca `DateTime` mutable.
3. **Zona horaria explícita.** La zona del negocio se define en una sola configuración (`APP_TIMEZONE`). No se escribe a mano en el código ni se asume la del servidor o del navegador.
4. **Instantes en UTC.** Los momentos exactos, como la creación de una solicitud, se guardan en UTC y se convierten a la zona del negocio solo al mostrarlos.
5. **Fechas de calendario sin hora.** Las fechas sin hora, como una vigencia, se guardan como `DATE` (`Y-m-d`) y no pasan por conversiones de zona horaria, para evitar que cambien de día.
6. **El servidor decide.** Vigencias y plazos se calculan en el backend; nunca se confía en fechas enviadas por el navegador.
7. **Pruebas con fecha fija.** Nunca dependen del reloj real. Incluyen los casos 23:59 y 00:00, el último día del mes y el 29 de febrero cuando apliquen.
