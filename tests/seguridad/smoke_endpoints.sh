#!/usr/bin/env bash
# Prueba de humo de seguridad: ningún endpoint responde sin sesión o token válidos.
#
# Uso:  BASE_URL=http://127.0.0.1:8099 APP_USER=admin APP_PASS=... tests/seguridad/smoke_endpoints.sh
# Requiere servidor y base de datos en marcha (con db/migraciones aplicadas).
set -u
BASE_URL="${BASE_URL:-http://127.0.0.1:8080}"
APP_USER="${APP_USER:-}"
APP_PASS="${APP_PASS:-}"
FALLOS=0
JAR="$(mktemp)"; trap 'rm -f "$JAR"' EXIT
ROOT="$(cd "$(dirname "$0")/../.." && pwd)"

espera() { # descripcion, codigo_esperado, codigo_real
  if [ "$2" = "$3" ]; then printf "  ok   %-62s %s\n" "$1" "$3"; else printf "  FALLA %-61s esperado %s, recibió %s\n" "$1" "$2" "$3"; FALLOS=$((FALLOS+1)); fi
}
codigo() { curl -s -o /dev/null -w "%{http_code}" "$@"; }

echo "1) Sin sesión ni token: todo debe devolver 401"
for f in "$ROOT"/public/ajax/*.ajax.php; do n="ajax/$(basename "$f")"; espera "POST $n" 401 "$(codigo -X POST -d x=1 "$BASE_URL/$n")"; done
espera "GET  reportes/recibo.php"             401 "$(codigo "$BASE_URL/reportes/recibo.php?fecha=2026-08-15")"
espera "GET  reportes/reporte_recoleccion.php" 401 "$(codigo "$BASE_URL/reportes/reporte_recoleccion.php?fecha=2026-08-15")"
for n in apiSocios apiRecoleccionQuincena apiTotalLiquidacion; do espera "GET  api/$n.php" 401 "$(codigo "$BASE_URL/api/$n.php")"; done
espera "POST api/apiCrearRecoleccionesLote.php" 401 "$(codigo -X POST -d '{}' "$BASE_URL/api/apiCrearRecoleccionesLote.php")"
espera "POST api/apiValidarToken.php (token inventado)" 401 "$(codigo -X POST -H 'Content-Type: application/json' -d '{"token":"'"$(printf 'a%.0s' $(seq 1 64))"'"}' "$BASE_URL/api/apiValidarToken.php")"
espera "GET  api/apiSocios.php con token inventado"     401 "$(codigo -H "Authorization: Bearer $(printf 'b%.0s' $(seq 1 64))" "$BASE_URL/api/apiSocios.php")"

echo "2) No deben ser accesibles por HTTP (404)"
for r in config/config.php src/bootstrap.php src/auth/guard.php db/seed/colfe_demo_2026.sql CLAUDE.md .git/config; do espera "GET  /$r" 404 "$(codigo "$BASE_URL/$r")"; done

echo "3) CORS: la API no debe responder Access-Control-Allow-Origin: *"
ACAO="$(curl -s -D - -o /dev/null -H 'Origin: https://evil.example' "$BASE_URL/api/apiSocios.php" | grep -i '^access-control-allow-origin' || true)"
[ -z "$ACAO" ] && echo "  ok   sin cabecera ACAO para un origen no autorizado" || { echo "  FALLA ACAO presente: $ACAO"; FALLOS=$((FALLOS+1)); }

if [ -n "$APP_USER" ] && [ -n "$APP_PASS" ]; then
  echo "4) Con credenciales válidas"
  LOGIN_TOKEN="$(curl -s -c "$JAR" -b "$JAR" "$BASE_URL/" | sed -n 's/.*name="csrf_token" value="\([0-9a-f]*\)".*/\1/p' | head -1)"
  curl -s -c "$JAR" -b "$JAR" -o /dev/null -d "csrf_token=$LOGIN_TOKEN" -d "ingUsuario=$APP_USER" --data-urlencode "ingPassword=$APP_PASS" "$BASE_URL/"
  CSRF="$(curl -s -b "$JAR" "$BASE_URL/inicio" | sed -n 's/.*name="csrf-token" content="\([0-9a-f]*\)".*/\1/p')"
  [ -n "$CSRF" ] && ok_msg="token CSRF obtenido" || { echo "  FALLA no se obtuvo el token CSRF de la página"; FALLOS=$((FALLOS+1)); }
  espera "sesión web: POST ajax/socios.ajax.php (con token)" 200 "$(codigo -b "$JAR" -H "X-CSRF-Token: $CSRF" -X POST -d idSocio=1 "$BASE_URL/ajax/socios.ajax.php")"
  espera "sesión web: GET reportes/recibo.php" 200 "$(codigo -b "$JAR" "$BASE_URL/reportes/recibo.php?fecha=2026-08-15")"
  RESP="$(curl -s -H 'Content-Type: application/json' -d "{\"username\":\"$APP_USER\",\"password\":\"$APP_PASS\"}" "$BASE_URL/api/apiLogin.php")"
  TOKEN="$(printf '%s' "$RESP" | sed -n 's/.*"token":"\([0-9a-f]\{64\}\)".*/\1/p')"
  if [ -n "$TOKEN" ]; then
    echo "  ok   apiLogin emitió un token"
    espera "token real: GET api/apiSocios.php"          200 "$(codigo -H "Authorization: Bearer $TOKEN" "$BASE_URL/api/apiSocios.php")"
    espera "token real por ?token=: apiSocios.php"      200 "$(codigo "$BASE_URL/api/apiSocios.php?token=$TOKEN")"
    espera "token real: GET api/apiTotalLiquidacion.php" 200 "$(codigo -H "Authorization: Bearer $TOKEN" "$BASE_URL/api/apiTotalLiquidacion.php")"
    espera "token real: POST api/apiValidarToken.php"   200 "$(codigo -X POST -H 'Content-Type: application/json' -d "{\"token\":\"$TOKEN\"}" "$BASE_URL/api/apiValidarToken.php")"
  else
    echo "  FALLA apiLogin no devolvió token: $RESP"; FALLOS=$((FALLOS+1))
  fi
else
  echo "4) (omitido: defina APP_USER y APP_PASS para probar con credenciales)"
fi

echo; [ "$FALLOS" -eq 0 ] && echo "RESULTADO: todo en orden" || echo "RESULTADO: $FALLOS fallo(s)"
exit "$FALLOS"
