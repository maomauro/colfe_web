# Decisiones de arquitectura (ADR)

Cada archivo registra **una decisión**: por qué se tomó, qué implica y qué otras opciones había. Así quien llegue después no tiene que adivinar el motivo. Las decisiones D1 a D4 del plan de trabajo están aquí; **D5** (cómo cubrir del 27 ago al 30 sep 2026) sigue abierta y se registrará cuando se decida.

| N.º | Decisión | Estado |
|---|---|---|
| [0001](0001-datos-demo-hasta-la-demostracion.md) | Datos demo en pruebas y producción hasta la demostración | Aceptada (decisión D1) |
| [0002](0002-proxy-compartido-con-portalcv.md) | COLFE usa el nginx compartido de PortalCV | Aceptada (decisión D2, 3 oct 2026) |
| [0003](0003-imagen-docker-debian.md) | Imagen Docker basada en Debian, no Alpine | Aceptada |
| [0004](0004-tokens-de-api-en-base-de-datos.md) | Tokens de la API móvil guardados como hash en la base de datos | Aceptada |
| [0005](0005-csrf-por-token-y-origen.md) | Protección CSRF con token por sesión y verificación del origen | Aceptada |
| [0006](0006-estructura-public-src-config-storage.md) | Estructura de carpetas con una sola raíz web (`public/`) | Aceptada |
| [0007](0007-respaldo-semanal-solo-local.md) | Respaldo semanal de la base de datos, solo en el VPS por ahora | Aceptada, con revisión obligatoria antes de datos reales |
| [0008](0008-flujo-de-ramas-feature-develop-main.md) | Flujo de ramas feature → develop → main | Aceptada (8 oct 2026) |
| [0009](0009-php-81-y-desarrollo-con-docker.md) | La aplicación soporta PHP 8.1+; las imágenes usan 8.4 y el desarrollo se hace con Docker | Aceptada (decisión D3, 3 oct 2026) |
| [0010](0010-repositorio-publico.md) | El repositorio es público | Aceptada (decisión D4, 3 oct 2026) |
| [0011](0011-precios-con-vigencia.md) | Precios con vigencia, sin solapes | Aceptada, pendiente de construir (8 oct 2026) |
| [0012](0012-retirar-anticipos-y-ahorro-liquidacion-solo-fija.md) | Retirar anticipos y ahorro; liquidación solo fija | Aceptada y construida (9 oct 2026, migraciones 006 y 007) |
| [0013](0013-deducibles-uno-por-fila.md) | Deducibles uno por fila | Aceptada y construida (9 oct 2026, migración 007) |

## Cómo agregar una decisión

1. Copiar el último archivo, numerarlo (`0014-…`) y llenar contexto, decisión, consecuencias y alternativas.
2. Agregar su fila a esta tabla.
3. Si una decisión reemplaza a otra, cambiar el estado de la anterior a «Reemplazada por ADR NNNN»; no se borra.

Los **estados** son: *Propuesta*, *Aceptada*, *Aceptada, pendiente de construir* y *Reemplazada*.
