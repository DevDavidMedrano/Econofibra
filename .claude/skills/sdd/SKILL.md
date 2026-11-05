---
name: sdd
description: Flujo Spec-Driven Development de Econofibra JLINK. Define las plantillas de spec.md, plan.md y tasks.md, los requisitos en EARS y los criterios de finalización. Úsala al redactar, revisar o implementar cualquier spec en specs/.
---

# SDD — Spec-Driven Development

Cada funcionalidad vive en `specs/NNN-nombre/` (NNN = siguiente número libre, con tres dígitos; nombre en minúsculas con guiones) y tiene tres archivos: `spec.md`, `plan.md` y `tasks.md`.

Orden obligatorio: spec → aprobación del usuario → plan y tareas → aprobación del usuario → implementación → validación RF por RF.

## Requisitos en EARS

Cada requisito funcional (RF) tiene un identificador estable (RF-1, RF-2…), que no se reutiliza aunque el RF se elimine, y usa una de estas formas:

| Tipo | Forma |
|---|---|
| Siempre | El sistema DEBE `<respuesta>`. |
| Por evento | CUANDO `<evento>`, el sistema DEBE `<respuesta>`. |
| Por estado | MIENTRAS `<estado>`, el sistema DEBE `<respuesta>`. |
| No deseado | SI `<condición no deseada>`, ENTONCES el sistema DEBE `<respuesta>`. |
| Opcional | DONDE `<característica>`, el sistema DEBE `<respuesta>`. |

Cada RF debe poder comprobarse con una prueba. Evita palabras sin medida como "rápido", "fácil" o "adecuado".

## Plantilla de spec.md

Solo el QUÉ y el POR QUÉ: nada de stack, arquitectura ni archivos.

```markdown
# Spec NNN — <nombre>

Estado: borrador | aprobada

## Problema
<Qué necesidad se resuelve y para quién.>

## Objetivo
<Qué resultado se espera.>

## Fuera de alcance
- <Lo que esta spec no incluye.>

## Requisitos funcionales
- RF-1: <requisito en EARS>

## Casos límite
- <Situación> → <comportamiento esperado> (RF-n)

## Criterios de finalización
- [ ] Todos los RF tienen una prueba o verificación en verde.
- [ ] <Criterio propio de esta spec.>

## Dudas abiertas
- <Vacío para poder aprobarla.>
```

## Plantilla de plan.md

```markdown
# Plan NNN — <nombre>

Spec: specs/NNN-nombre/spec.md

## Archivos
| Archivo | Capa | Acción | RF |
|---|---|---|---|

## Funciones puras
<Reglas de negocio sin efectos secundarios. Las que dependan de la fecha reciben "hoy" como parámetro.>

## Decisiones
- <Decisión>. Alternativa descartada: <cuál> porque <motivo>.

## Estrategia de tests
<Qué se prueba con `php artisan test`, en qué carpeta y qué RF cubre cada prueba.>

## Cobertura de RF
| RF | Parte del plan | Prueba |
|---|---|---|
```

## Plantilla de tasks.md

Máximo 10 tareas, en orden. Cada una debe poder implementarse y probarse por separado.

```markdown
# Tareas NNN — <nombre>

- [ ] T1: <tarea>
  - RF: RF-1, RF-2
  - Hecho cuando: <resultado comprobable>
```

## Cambios de requisitos

1. Se actualiza `spec.md`: nuevo RF en EARS y sus casos límite.
2. El usuario aprueba el diff.
3. Se actualizan `plan.md` y `tasks.md`.
4. Se implementa.

## Terminada significa

- `php artisan test` en verde.
- Cada RF enlazado a una prueba o verificación con resultado.
- Los RF de interfaz verificados en navegador, incluida la vista móvil.
- Sin conflictos con `docs/constitution.md`.
- `MEMORY.md` actualizado tras la última tarea.
