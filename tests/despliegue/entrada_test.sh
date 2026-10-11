#!/bin/bash
# Prueba deploy/entrada.sh: solo `desplegar <tag>` pasa; todo lo demas se rechaza SIN ejecutar el despliegue, y el
# script de despliegue corre con el entorno limpio. Usa un desplegar.sh de mentira (no toca Docker ni el servidor).
set -uo pipefail

RAIZ=$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)
T=$(mktemp -d)
trap 'rm -rf "$T"' EXIT
fallos=0

mkdir -p "$T/deploy"
cat > "$T/deploy/desplegar.sh" << 'EOS'
#!/bin/bash
echo "EJECUTADO TAG=${TAG:-} ARGS=$# SECRETO=${SECRETO_FILTRADO:-vacio}" >> "$(cd "$(dirname "$0")/.." && pwd)/registro.txt"
EOS
chmod +x "$T/deploy/desplegar.sh"

ok() { printf 'OK     %s\n' "$1"; }
falla() { printf 'FALLA  %s\n' "$1"; fallos=$((fallos + 1)); }

correr() { # correr <orden>: ejecuta la entrada como lo haria sshd con esa orden
    : > "$T/registro.txt"
    env COLFE_DIR="$T" SECRETO_FILTRADO=filtrado SSH_ORIGINAL_COMMAND="$1" \
        bash "$RAIZ/deploy/entrada.sh" > "$T/salida.txt" 2>&1
}
debe_pasar() {
    if correr "$1" && grep -q "EJECUTADO TAG=$2 " "$T/registro.txt"; then ok "pasa: $1"; else falla "debia pasar: $1"; fi
}
debe_rechazar() {
    if correr "$1"; then falla "debia rechazarse: $(printf '%q' "$1")"
    elif [[ -s "$T/registro.txt" ]]; then falla "se rechazo pero SE EJECUTO el despliegue: $(printf '%q' "$1")"
    else ok "rechazada: $(printf '%q' "$1")"; fi
}

debe_pasar "desplegar latest" latest
debe_pasar "desplegar sha-abc1234" sha-abc1234
debe_pasar "desplegar v0.2.0" v0.2.0
correr "desplegar latest"
if grep -q "SECRETO=vacio" "$T/registro.txt"; then
    ok "el despliegue corre con el entorno limpio (no hereda variables del cliente)"
else
    falla "el despliegue heredo variables del entorno"
fi

debe_rechazar ""                              # una shell interactiva
debe_rechazar "ls /"
debe_rechazar "bash"
debe_rechazar "desplegar"
debe_rechazar "desplegar "
debe_rechazar "desplegar latest; id"
debe_rechazar "desplegar latest && id"
debe_rechazar "desplegar latest | cat"
# shellcheck disable=SC2016
debe_rechazar 'desplegar $(id)'
# shellcheck disable=SC2016
debe_rechazar 'desplegar `id`'
debe_rechazar "desplegar -x"
debe_rechazar "desplegar ../x"
debe_rechazar "desplegar a b"
debe_rechazar "desplegar latest extra"
debe_rechazar "DESPLEGAR latest"
debe_rechazar " desplegar latest"
debe_rechazar $'desplegar latest\nid'
debe_rechazar "desplegar $(printf 'a%.0s' $(seq 1 129))"   # tag demasiado largo
debe_rechazar "cd /srv/sitiosapps/colfe && TAG='latest' ./deploy/desplegar.sh"   # la orden VIEJA ya no vale

echo
if [[ "$fallos" -eq 0 ]]; then echo "Entrada de despliegue: todas las pruebas pasaron."; exit 0; fi
echo "Entrada de despliegue: $fallos prueba(s) fallaron."
exit 1
