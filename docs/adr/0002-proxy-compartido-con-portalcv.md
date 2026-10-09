# ADR 0002: COLFE usa el nginx compartido de PortalCV

- **Estado:** Aceptada (decisión D2, 3 oct 2026)
- **Registrada:** 8 oct 2026
- **Decide:** Edgar

## Contexto

En el VPS ya corre PortalCV en Docker: `portalcv-nginx-prod` ocupa los puertos 80 y 443 y PortalCV usa MariaDB 11. COLFE necesita publicarse en su propio subdominio sin quitarle los puertos.

## Decisión

El contenedor `portalcv-nginx-prod` carga `colfe.conf` y alcanza al contenedor `colfe-web` por la red Docker de PortalCV (variable `PROXY_NETWORK`, alias `colfe-web`). COLFE conserva su **propio MySQL 8.0**. El certificado es de origen de Cloudflare, con SSL en modo *Completo (estricto)*. El bloque `colfe.conf` también vive en el repositorio de PortalCV (`docker/colfe.conf`).

## Consecuencias

- Un solo nginx atiende los dos proyectos y un solo certificado de origen.
- COLFE depende del nombre de la carpeta de PortalCV, de donde sale el nombre de la red (`curriculum-vitae-web_portalcv-net-prod`): **no se debe renombrar**.
- Un cambio en el nginx compartido puede afectar a los dos proyectos; no se toca PortalCV sin avisar.
- Las bases de datos están separadas (MySQL para COLFE, MariaDB para PortalCV).

## Alternativas consideradas

Publicar COLFE en un puerto interno propio detrás del nginx existente.
