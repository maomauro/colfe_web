#!/bin/bash
# Entrada de la llave SSH de despliegue de GitHub Actions. En authorized_keys del VPS esa llave va como:
#   restrict,command="/usr/local/sbin/colfe-entrada.sh" ssh-ed25519 AAAA... colfe-deploy
# asi que con ELLA no se abre una shell ni se ejecuta nada que no sea esto: `desplegar <tag>`. Robar la llave o el
# secreto solo permite lanzar un despliegue de Colfe, no controlar el servidor (que comparte Docker con PortalCV y GDB).
#
# Lo instala root, fuera de la carpeta del proyecto (deploy/instalar.sh), para que un despliegue no pueda reemplazarlo.
# Limite conocido: desplegar.sh y docker-compose.prod.yml salen del repositorio; quien pueda hacer push a main puede
# cambiarlos. Eso se cubre con la proteccion de ramas y, en una segunda etapa, con una politica sobre el compose
# (ver GrupoDigitalBank-CRUD, deploy/politica.py).
#
# Para las pruebas: COLFE_DIR.
set -euo pipefail

DIR=${COLFE_DIR:-/srv/sitiosapps/colfe}
orden=${SSH_ORIGINAL_COMMAND:-}

if [[ ! "$orden" =~ ^desplegar\ ([A-Za-z0-9][A-Za-z0-9._-]{0,127})$ ]]; then
    echo "Orden no permitida. Solo: desplegar <tag>" >&2
    exit 1
fi
tag=${BASH_REMATCH[1]}

cd "$DIR"
# Entorno limpio: nada de lo que mande el cliente llega al script de despliegue
exec env -i PATH=/usr/local/bin:/usr/bin:/bin HOME="${HOME:-/root}" TAG="$tag" ./deploy/desplegar.sh
