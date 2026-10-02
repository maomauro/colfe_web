#!/usr/bin/env bash
# Pruebas CSRF: toda petición que modifica datos exige token y origen válidos; los borrados ya no van por GET.
#
# Uso: BASE_URL=http://127.0.0.1:8099 APP_USER=admin APP_PASS='...' tests/seguridad/csrf_test.sh
# Escribe en la base (crea y borra un socio de prueba): usar solo con una base desechable.
set -u
BASE_URL="${BASE_URL:-http://127.0.0.1:8080}"
APP_USER="${APP_USER:?defina APP_USER}"; APP_PASS="${APP_PASS:?defina APP_PASS}"
FALLOS=0; JAR="$(mktemp)"; trap 'rm -f "$JAR"' EXIT
chk() { if [ "$2" = "$3" ]; then printf "  ok    %-66s %s\n" "$1" "$3"; else printf "  FALLA %-66s esperado %s, recibió %s\n" "$1" "$2" "$3"; FALLOS=$((FALLOS+1)); fi; }
code() { curl -s -b "$JAR" -c "$JAR" -o /dev/null -w "%{http_code}" "$@"; }
meta() { curl -s -b "$JAR" "$BASE_URL/inicio" | sed -n 's/.*name="csrf-token" content="\([0-9a-f]*\)".*/\1/p' | head -1; }
campo() { curl -s -b "$JAR" -c "$JAR" "$BASE_URL/${1:-}" | sed -n 's/.*name="csrf_token" value="\([0-9a-f]*\)".*/\1/p' | head -1; }
HOST="${BASE_URL#*://}"

echo "1) Login"
chk "POST de login sin token" 403 "$(code -d "ingUsuario=$APP_USER" --data-urlencode "ingPassword=$APP_PASS" "$BASE_URL/")"
T="$(campo "")"; [ -n "$T" ] && echo "  ok    la página de login incluye su token" || { echo "  FALLA el login no trae token"; FALLOS=$((FALLOS+1)); }
chk "POST de login con token inventado" 403 "$(code -d "csrf_token=$(printf 'a%.0s' $(seq 1 64))" -d "ingUsuario=$APP_USER" --data-urlencode "ingPassword=$APP_PASS" "$BASE_URL/")"
curl -s -b "$JAR" -c "$JAR" -o /dev/null -d "csrf_token=$T" -d "ingUsuario=$APP_USER" --data-urlencode "ingPassword=$APP_PASS" "$BASE_URL/"
TOK="$(meta)"; [ -n "$TOK" ] && echo "  ok    tras ingresar, la página expone el token en <meta csrf-token>" || { echo "  FALLA no hay meta csrf-token"; FALLOS=$((FALLOS+1)); }

echo "2) AJAX (todos son POST)"
chk "sin token" 403 "$(code -X POST -d idSocio=1 "$BASE_URL/ajax/socios.ajax.php")"
chk "token incorrecto" 403 "$(code -H "X-CSRF-Token: $(printf 'b%.0s' $(seq 1 64))" -X POST -d idSocio=1 "$BASE_URL/ajax/socios.ajax.php")"
chk "token correcto" 200 "$(code -H "X-CSRF-Token: $TOK" -X POST -d idSocio=1 "$BASE_URL/ajax/socios.ajax.php")"
chk "token correcto pero Origin de otro sitio" 403 "$(code -H "X-CSRF-Token: $TOK" -H "Origin: https://sitio-malicioso.example" -X POST -d idSocio=1 "$BASE_URL/ajax/socios.ajax.php")"
chk "token correcto y Origin propio" 200 "$(code -H "X-CSRF-Token: $TOK" -H "Origin: http://$HOST" -X POST -d idSocio=1 "$BASE_URL/ajax/socios.ajax.php")"

echo "3) Formularios y borrados"
chk "crear socio sin token" 403 "$(code -X POST "$BASE_URL/socios" -d nuevoNombreSocio=Csrf -d nuevoApellidoSocio=Prueba -d nuevoIdentificacionSocio=999000111 -d nuevoTelefonoSocio=3000000000 -d nuevoDireccionSocio=x -d nuevoVinculacionSocio=asociado)"
curl -s -b "$JAR" -c "$JAR" -o /dev/null -X POST "$BASE_URL/socios" -d "csrf_token=$TOK" -d nuevoNombreSocio=Csrf -d nuevoApellidoSocio=Prueba -d nuevoIdentificacionSocio=999000111 -d nuevoTelefonoSocio=3000000000 -d nuevoDireccionSocio=x -d nuevoVinculacionSocio=asociado
ID="$(curl -s -b "$JAR" -H "X-CSRF-Token: $TOK" -X POST -d validarIdentificacion=999000111 "$BASE_URL/ajax/socios.ajax.php" | sed -n 's/.*"id_socio":\([0-9]*\).*/\1/p')"
if [ -n "$ID" ]; then echo "  ok    socio de prueba creado con token (id $ID)"; else echo "  FALLA no se creó el socio de prueba"; FALLOS=$((FALLOS+1)); fi
existe() { curl -s -b "$JAR" -H "X-CSRF-Token: $TOK" -X POST -d "idSocio=$ID" "$BASE_URL/ajax/socios.ajax.php" | grep -c '"nombre":"Csrf"'; }
if [ -n "$ID" ]; then
  code "$BASE_URL/socios?idSocio=$ID" >/dev/null;  code "$BASE_URL/index.php?ruta=socios&idSocio=$ID" >/dev/null
  chk "borrar por GET (el método antiguo) ya NO borra" 1 "$(existe)"
  chk "borrar por POST sin token" 403 "$(code -X POST "$BASE_URL/socios" -d "idSocio=$ID")"
  chk "tras el POST sin token, el socio sigue existiendo" 1 "$(existe)"
  code -X POST "$BASE_URL/socios" -d "csrf_token=$TOK" -d "idSocio=$ID" >/dev/null
  chk "borrar por POST con token" 0 "$(existe)"
fi

echo; [ "$FALLOS" -eq 0 ] && echo "RESULTADO: todo en orden" || echo "RESULTADO: $FALLOS fallo(s)"; exit "$FALLOS"
