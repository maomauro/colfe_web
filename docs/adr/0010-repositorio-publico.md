# ADR 0010: El repositorio es público

- **Estado:** Aceptada (decisión D4, 3 oct 2026)
- **Registrada:** 8 oct 2026
- **Decide:** Edgar

## Contexto

El repositorio de GitHub es público y tiene GitHub Pages activo desde `main`.

## Decisión

Se mantiene público. Se aceptan sus consecuencias y se imponen reglas para que no exponga datos sensibles.

## Consecuencias

- Los datos del seed y de los dumps antiguos del historial son sintéticos.
- Las credenciales `admin/admin` y `user/12345` estuvieron publicadas: ya no existen, la migración 002 las elimina.
- Nunca se suben `.env`, claves, certificados ni datos reales de socios. Las claves están en un gestor privado, no en el repositorio.
- Los detalles de despliegue de `docs/` y `deploy/` son visibles para cualquiera.
- Pendiente: revisar si GitHub Pages debe seguir activo (plan, Fase 5.3).

## Alternativas consideradas

Repositorio privado: no se registró una comparación formal; el plan solo consigna que el repositorio es público.
