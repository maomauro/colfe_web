# ADR 0009: La aplicación soporta PHP 8.1+; las imágenes usan 8.4 y el desarrollo se hace con Docker

- **Estado:** Aceptada (decisión D3, 3 oct 2026)
- **Registrada:** 8 oct 2026
- **Decide:** Edgar

## Contexto

Laragon trae PHP 8.1.10 y MySQL 8.0.30, mientras las imágenes de producción usan una versión más nueva. Hay que evitar diferencias entre desarrollo y producción.

## Decisión

El código debe funcionar en **PHP 8.1 o superior**. Las imágenes Docker usan PHP 8.4 y el desarrollo local recomendado es Docker (`docker compose up -d --build`, en `http://localhost:8080`), no Laragon. La base es MySQL 8.0.

## Consecuencias

- No se pueden usar funciones de PHP posteriores a 8.1 en el código compartido.
- El CI corre en PHP 8.4 y MySQL 8.0, igual que producción.
- Laragon sigue sirviendo para otros proyectos, pero no es el entorno de referencia de COLFE.

## Alternativas consideradas

Fijar la misma versión de PHP en Laragon y en las imágenes: obliga a mantener el entorno local sincronizado a mano.
