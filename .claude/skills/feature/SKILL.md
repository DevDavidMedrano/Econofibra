---
name: feature
description: Flujo corto para un cambio pequeño que no merece una spec completa.
argument-hint: <descripción del cambio>
disable-model-invocation: true
---

# /feature — cambio pequeño

Cambio pedido: $ARGUMENTS

1. Lee `AGENTS.md`, `docs/constitution.md` y `MEMORY.md`.
2. Comprueba que el cambio es pequeño: una sola responsabilidad, pocos archivos, sin tablas nuevas y sin tocar reglas de cobertura, precios o recomendación. Si no lo es, PARA y sugiere usar `claude --agent coordinator`.
3. Si hay ambigüedad, pregunta antes de escribir código.
4. En la lógica: primero la prueba en rojo y después el código.
5. Ejecuta `php artisan test`. No des el cambio por hecho con pruebas en rojo.
6. Si hay cambios visuales, verifícalos en navegador, incluida la vista móvil.
7. Resume los archivos modificados y el resultado de las pruebas.
