# ADR 0003: Imagen Docker basada en Debian, no Alpine

- **Estado:** Aceptada
- **Registrada:** 8 oct 2026
- **Decide:** Edgar

## Contexto

`public/reportes/recibo.php` genera PDF con `iconv` y la opción `//TRANSLIT`, que la biblioteca musl de Alpine no soporta.

## Decisión

La imagen de la aplicación (`Dockerfile`) usa PHP-FPM sobre Debian (glibc). Las imágenes corren PHP 8.4.

## Consecuencias

- La imagen pesa más que una Alpine.
- Los recibos se generan correctamente con caracteres acentuados.
- Cualquier cambio de imagen base debe probar la generación del recibo.

## Alternativas consideradas

Alpine: más liviana, pero rompe la transliteración del recibo.
