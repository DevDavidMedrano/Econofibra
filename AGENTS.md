# AGENTS.md — ECONOFIBRA JLINK

## 1. DESCRIPCIÓN DEL PROYECTO

Desarrollar una plataforma web profesional, moderna y responsiva para **Econofibra JLINK**, una empresa distribuidora de servicios de internet mediante diferentes tecnologías de conexión.

La plataforma permitirá:

- Conocer los servicios de internet disponibles.
- Consultar cobertura por domicilio.
- Identificar las tecnologías disponibles.
- Consultar planes, velocidades y precios.
- Recibir recomendaciones entre fibra óptica e internet por antena.
- Solicitar contratación mediante WhatsApp.
- Contactar al equipo de soporte técnico.

El objetivo es desarrollar una plataforma comercial escalable, no solamente una landing page.

El sistema deberá estar preparado para incorporar posteriormente un panel administrativo que permita gestionar zonas de cobertura, tecnologías, planes, precios y solicitudes de clientes.

---

## 2. TECNOLOGÍAS DEL PROYECTO

El proyecto será desarrollado utilizando PHP.

### Stack tecnológico

- Backend: PHP 8.2 o superior.
- Framework: Laravel.
- Gestor de dependencias: Composer.
- Base de datos: MySQL.
- Frontend: HTML5, CSS3 y JavaScript.
- Plantillas: Laravel Blade.
- Estilos: Tailwind CSS o CSS modular.
- Arquitectura: Clean Architecture.
- API: REST cuando sea necesario.
- Control de versiones: Git.

Si el repositorio ya tiene un framework o estructura implementada, respetar las tecnologías existentes y no reemplazarlas sin justificación.

### Reglas técnicas

- Utilizar programación orientada a objetos.
- Aplicar tipado estricto mediante `declare(strict_types=1);`.
- Utilizar namespaces.
- Aplicar PSR-4 para autoloading.
- Mantener responsabilidades independientes.
- Evitar archivos PHP que mezclen lógica, consultas SQL y presentación.
- Utilizar Eloquent o PDO mediante repositorios.
- No utilizar consultas SQL concatenando entradas del usuario.
- Mantener las credenciales fuera del código fuente.

---

## 3. REFERENCIAS VISUALES

Utilizar las dos imágenes de `docs/referencias/` (`pagina-principal.jpeg` y `pagina-cobertura.jpeg`) como referencia principal del diseño. Si una imagen contradice estas reglas, prevalecen las reglas: por ejemplo, los campos de domicilio de la página de cobertura (código postal, colonia, calle y número) no se implementan.

### Identidad visual

Mantener:

- Azul marino como color principal.
- Amarillo como color de acento.
- Blanco y azul claro para fondos.
- Tipografía moderna y legible.
- Tarjetas con bordes redondeados.
- Sombras sutiles.
- Botones destacados.
- Iconografía relacionada con telecomunicaciones.
- Animaciones discretas.

### Página principal

Debe incluir:

1. Navbar con logotipo y navegación.
2. Hero principal con mensaje comercial.
3. Imagen representativa de conectividad.
4. Botón principal para contratar.
5. Indicadores de beneficios:
   - Alta velocidad.
   - Conexión estable.
   - Soporte técnico.
   - Cobertura en tu zona.
6. Sección de planes.
7. Sección de ventajas competitivas.
8. Llamada a la acción para WhatsApp.
9. Footer con redes sociales y contacto.

### Página de cobertura

Debe incluir:

1. Navbar.
2. Hero con el mensaje "Encuentra el internet ideal para tu zona".
3. Formulario de consulta de cobertura.
4. Botón para compartir la ubicación.
5. Tarjetas de tecnologías:
   - Internet por fibra óptica.
   - Internet por antena.
6. Explicación del proceso de validación.
7. Resultados de cobertura.
8. Botón de contacto por WhatsApp.

Reproducir fielmente el diseño de las imágenes (estructura, colores, textos, tarjetas y estilo), aplicando solo los cambios que exigen estas reglas. El resultado debe ser una página real, funcional y adaptable, no una imagen.

---

## 4. REGLA DE NEGOCIO PRINCIPAL: COBERTURA GEOGRÁFICA

Esta es la funcionalidad más importante del sistema.

**La cobertura de Econofibra JLINK no es uniforme.**

Dependiendo de la ubicación del cliente pueden variar:

- La tecnología disponible.
- La velocidad máxima.
- Los planes disponibles.
- Los precios.
- Los costos de instalación.
- La factibilidad técnica.
- La recomendación de contratación.

Un domicilio puede contar con fibra óptica mientras que otro domicilio de la misma colonia puede necesitar internet por antena.

Incluso dentro de una misma colonia pueden existir diferencias de disponibilidad.

Por lo tanto:

**NUNCA asumir que todos los clientes tienen acceso a los mismos planes, tecnologías o precios.**

### 4.1 Flujo de consulta de cobertura

El usuario deberá proporcionar:

- Ubicación compartida desde su dispositivo (latitud y longitud).

Flujo:

1. El cliente confirma que está en el domicilio donde quiere el servicio y comparte su ubicación.
2. El frontend comprueba que se obtuvo la ubicación.
3. Se envían los datos al backend PHP.
4. El backend identifica la zona correspondiente a partir de la ubicación.
5. Se consulta la disponibilidad técnica.
6. Se identifican las tecnologías disponibles.
7. Se obtienen los planes y precios aplicables.
8. Se genera una recomendación.
9. Se devuelve el resultado.
10. El frontend presenta la información.

Si el usuario niega el permiso o no se obtiene la ubicación, mostrar cómo activarla y ofrecer WhatsApp para validación manual, sin planes ni precios. Si no está en el domicilio, pedirle que consulte desde ahí o escriba por WhatsApp. Si la precisión de la ubicación es baja, pasar a validación técnica (Escenario E).

La disponibilidad debe poder validarse por ubicación y factibilidad técnica.

### 4.2 Escenarios de cobertura

#### Escenario A: Fibra óptica disponible

Mostrar:

- Tecnología: Fibra óptica.
- Planes disponibles.
- Velocidades.
- Precios correspondientes a la zona.
- Costos de instalación.
- Botón para contratar.

#### Escenario B: Solamente antena disponible

Mostrar:

- Tecnología: Internet por antena.
- Planes disponibles.
- Velocidades.
- Precios correspondientes a la zona.
- Información de instalación.
- Botón para solicitar contratación.

#### Escenario C: Ambas tecnologías disponibles

Mostrar ambas alternativas y permitir compararlas.

La recomendación se basa en la disponibilidad confirmada: si hay fibra óptica, se recomienda.

#### Escenario D: Sin cobertura confirmada

Mostrar un mensaje indicando que no se ha podido confirmar disponibilidad.

Ofrecer:

- Validación manual.
- Contacto por WhatsApp.
- Registro para futuras expansiones.

#### Escenario E: Validación técnica pendiente

Mostrar que el domicilio requiere revisión por parte del equipo técnico.

No presentar precios como definitivos ni garantizar disponibilidad hasta recibir confirmación.

---

## 5. REGLAS DE PRECIOS Y PLANES

Los precios deben estar relacionados con:

- Zona.
- Tecnología.
- Plan.
- Condiciones comerciales, cuando estén definidas.
- Costos de instalación.

No colocar precios fijos directamente en los componentes visuales.

La información debe provenir de la base de datos mediante el backend PHP.

Ejemplo conceptual:

Zona Norte:
- Fibra 50 Mbps: precio A.
- Fibra 100 Mbps: precio B.

Zona Sur:
- Antena 20 Mbps: precio C.
- Antena 40 Mbps: precio D.

Los valores son ilustrativos.

El sistema deberá permitir modificar precios sin modificar el código fuente.

Un plan no necesariamente estará disponible en todas las zonas.

Los precios deberán consultarse en el servidor y nunca confiar en valores enviados por el navegador.

---

## 6. RECOMENDACIÓN DE TECNOLOGÍA

La recomendación debe estar basada en disponibilidad real y reglas de negocio.

Criterios:

1. Si existe fibra óptica confirmada, recomendarla.
2. Si no existe fibra, evaluar la disponibilidad de internet por antena.
3. Si existen ambas tecnologías, permitir comparar ventajas y precios.
4. Si ninguna está confirmada, solicitar validación técnica.

Nunca recomendar una tecnología que no esté disponible en la ubicación.

La lógica de recomendación debe pertenecer al dominio, no a los componentes visuales.

---

## 7. CLEAN ARCHITECTURE

Implementar una arquitectura limpia con separación de responsabilidades.

Las capas principales serán:

- Domain.
- Application.
- Infrastructure.
- Presentation.

La regla fundamental es que las dependencias deben apuntar hacia el dominio.

El dominio no debe depender de Laravel, Eloquent, controladores, plantillas ni infraestructura.

### 7.1 Domain

Contiene las entidades, reglas de negocio y contratos.

Responsabilidades:

- Entidades.
- Value Objects.
- Interfaces de repositorios.
- Políticas de cobertura.
- Políticas de precios.
- Reglas de recomendación.
- Validaciones del dominio.

Entidades sugeridas:

- Customer.
- Address.
- CoverageZone.
- CoverageResult.
- Technology.
- InternetPlan.
- ZonePrice.
- InstallationCost.
- Recommendation.
- ServiceRequest.

Reglas:

- Un plan pertenece a una tecnología.
- Un precio corresponde a una zona y un plan.
- Una zona puede tener varias tecnologías disponibles.
- Una recomendación requiere disponibilidad confirmada.
- Un plan no puede ofrecerse cuando no está disponible para la zona.

El dominio debe ser independiente del framework.

### 7.2 Application

Contiene los casos de uso.

Casos de uso sugeridos:

- CheckCoverageUseCase.
- GetAvailablePlansUseCase.
- GetZonePricesUseCase.
- RecommendTechnologyUseCase.
- CreateServiceRequestUseCase.
- RequestTechnicalValidationUseCase.

Esta capa coordina las operaciones del dominio.

Debe trabajar mediante interfaces y contratos.

No debe ejecutar consultas SQL directamente.

No debe depender de controladores ni de vistas.

### 7.3 Infrastructure

Contiene las implementaciones técnicas.

Responsabilidades:

- Implementaciones de repositorios.
- Acceso a base de datos.
- Persistencia.
- Integraciones externas.
- Servicios de geolocalización.
- Adaptadores.
- Comunicación con APIs.
- Servicios de WhatsApp cuando corresponda.

Si se utiliza Laravel, Eloquent debe permanecer en infraestructura.

No utilizar modelos Eloquent como entidades del dominio.

Los repositorios concretos deben implementar las interfaces definidas por el dominio.

### 7.4 Presentation

Contiene la interfaz y los puntos de entrada de la aplicación.

Responsabilidades:

- Controladores.
- Requests.
- Resources.
- Vistas.
- Componentes.
- Formularios.
- Navegación.
- Estados visuales.
- Presentación de resultados.

Los controladores deben ser delgados.

Su responsabilidad es recibir solicitudes, validar entradas, ejecutar casos de uso y devolver respuestas.

No deben contener reglas de negocio.

---

## 8. ESTRUCTURA DE CARPETAS

Utilizar una estructura organizada por módulos y capas.

Estructura sugerida para Laravel:

```text
app/
│
├── Domain/
│   ├── Coverage/
│   │   ├── Entities/
│   │   ├── ValueObjects/
│   │   ├── Repositories/
│   │   └── Services/
│   │
│   ├── Plans/
│   │   ├── Entities/
│   │   ├── ValueObjects/
│   │   └── Repositories/
│   │
│   └── Customers/
│       ├── Entities/
│       └── Repositories/
│
├── Application/
│   ├── Coverage/
│   │   ├── UseCases/
│   │   └── DTOs/
│   │
│   ├── Plans/
│   │   ├── UseCases/
│   │   └── DTOs/
│   │
│   └── Customers/
│       └── UseCases/
│
├── Infrastructure/
│   ├── Persistence/
│   │   ├── Eloquent/
│   │   └── Repositories/
│   │
│   ├── ExternalServices/
│   └── Adapters/
│
└── Http/
    ├── Controllers/
    ├── Requests/
    └── Resources/

resources/
│
├── views/
│   ├── layouts/
│   ├── components/
│   ├── pages/
│   │   ├── home/
│   │   ├── coverage/
│   │   ├── services/
│   │   ├── plans/
│   │   ├── support/
│   │   └── contact/
│   │
│   └── partials/
│
├── css/
└── js/

routes/
├── web.php
└── api.php

database/
├── migrations/
├── seeders/
└── factories/

tests/
├── Unit/
├── Feature/
└── Integration/