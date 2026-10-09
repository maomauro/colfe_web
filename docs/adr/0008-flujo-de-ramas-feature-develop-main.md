# ADR 0008: Flujo de ramas feature → develop → main

- **Estado:** Aceptada (8 oct 2026)
- **Registrada:** 8 oct 2026
- **Decide:** Edgar

## Contexto

Varias sesiones de trabajo (personas y asistentes) cambian el repositorio. `main` es lo que se publica y debe estar siempre estable. Los PR apilados ya dejaron contenido varado fuera de la rama principal.

## Decisión

Ramas permanentes: `main` (producción) y `develop` (integración), ambas protegidas: solo se cambian por PR, con el CI en verde y las conversaciones resueltas. Cada tarea va en una rama `fase-N/tema`, creada desde `develop` y con su PR **contra `develop`**. Cuando `develop` está listo se abre un PR `develop` → `main`; un PR a `main` desde otra rama falla el check «Verificar rama de origen». Los commits van en español. `develop` es la rama por defecto del repositorio. Igual que en el repositorio de PortalCV.

## Consecuencias

- `main` solo recibe lo que ya pasó por `develop`.
- Si una rama se apoya en otra, se fusiona siempre contra `develop`, para no dejar contenido varado.
- Publicar imágenes corre en cada fusión a `main`; el despliegue al VPS sigue siendo manual (`desplegar.yml`).
- Los PR de documentación pasan por el mismo flujo que los de código.

## Alternativas consideradas

Flujo de una sola rama (`main`): más simple, pero sin un lugar para integrar y probar antes de publicar.
