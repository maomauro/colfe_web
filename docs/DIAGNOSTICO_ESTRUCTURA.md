# Diagnóstico de estructura y estructura objetivo

Complementa el diagnóstico del 29 sep 2026. Basado en lectura del código del repositorio
(commit `432c0ae`). Objetivo: definir la estructura final **una sola vez**, para no rehacer
la seguridad ni el despliegue.

## 1. Estructura actual y sus problemas

```
colfe_web/            ← hoy toda la carpeta es la raíz web
├── index.php         front controller (no carga config.php)
├── config.php        constantes; se carga solo desde modelos/conexion.php
├── .htaccess         reescritura solo Apache
├── ajax/  (10)       sin sesión; incluye logs.log versionado
├── api/   (6)        token sin validar real
├── controladores/ · modelos/
├── vistas/           plantilla.php + modulos/*.php + css, js, dist, plugins,
│                     bower_components (18 MB), libs (fpdf + externos), img
├── db/               dumps y scripts SQL (11 MB)
└── test_simple.php
```

| # | Hallazgo | Evidencia | Gravedad |
|---|---|---|---|
| E1 | **Los módulos de `vistas/modulos/*.php` no verifican sesión y son accesibles por URL directa.** El control de sesión existe solo en `plantilla.php`. Cada módulo ejecuta él mismo crear/editar/borrar (`socios.php:164-268`). Quien llame `vistas/modulos/socios.php` por URL salta el login. | 0 apariciones de `SESSION` en `vistas/modulos/` | **P0 (nuevo)** |
| E2 | `recibo.php` y `reporte_recoleccion.php` son scripts independientes dentro de `vistas/modulos/`, sin sesión: cualquiera descarga comprobantes de pago por URL. | `liquidacion.js:314`, `recoleccion.js:300` | **P0 (nuevo)** |
| E3 | Toda la carpeta es raíz web: `db/`, `config.php`, `controladores/`, `modelos/`, `logs` quedan descargables salvo que nginx lo impida. | estructura | P0 |
| E4 | 20 `require_once $_SERVER["DOCUMENT_ROOT"]."/colfe_web/..."` en controladores y vistas: solo funcionan si la app vive en `/colfe_web` bajo la raíz web. | grep | P1 |
| E5 | `index.php` no carga `config.php`. Zona horaria, `display_errors` y cookies seguras solo se aplican cuando algún modelo abre la conexión, no antes. `ENVIRONMENT` por defecto es `development`. | `index.php`, `conexion.php` | P0 |
| E6 | `session_start()` está en `plantilla.php`; `ajax/` y `api/` nunca lo llaman, así que no pueden consultar la sesión hoy. | grep | P0 (guard) |
| E7 | Cada `ajax/*.php` repite sus propios `require`, sin bootstrap común. No hay lugar único donde poner autenticación, CSRF o manejo de errores. | `ajax/` | P1 |
| E8 | `vistas/libs/fpdf` (librería de servidor) vive en la carpeta pública. | `recibo.php:3` | P1 |
| E9 | Predicción deshabilitada pero presente: `prediccion.ajax.php` (ejecuta `shell_exec`), `prediccion.php`, `prediccion.js` (llama a `localhost:8000`). | archivos | P0 / P1 |
| E10 | `bower_components` (18 MB) y `plugins` (2,4 MB) versionados, sin gestor de dependencias. | `du` | P2 |
| E11 | `ajax/logs.log` versionado. | `git ls-files` | P1 |
| E12 | `README` promete CSRF y auditoría que no existen. | README | P1 |

Lo que se puede conservar sin cambios: la lista blanca de rutas en `plantilla.php`, el patrón
MVC con nombres en español, y las URLs relativas `vistas/...` y `ajax/...` usadas por las
vistas y por el JavaScript (no hay que reescribirlas si la estructura nueva las respeta).

## 2. Estructura objetivo

```
colfe_web/
├── public/                         ← ÚNICA raíz web (nginx apunta aquí)
│   ├── index.php                   front controller; carga src/bootstrap.php
│   ├── ajax/                       10 endpoints (menos prediccion), con guard de sesión
│   ├── api/                        6 endpoints, con guard de token
│   ├── reportes/                   recibo.php y reporte_recoleccion.php (movidos),
│   │                               con guard de sesión
│   └── vistas/                     SOLO estáticos: css, js, dist, img, plugins,
│                                   bower_components, libs/external
├── src/                            fuera de la raíz web
│   ├── bootstrap.php               carga config, zona horaria, errores, cookies, sesión
│   ├── auth/guard.php              guardSesion() y guardToken()
│   ├── controladores/
│   ├── modelos/
│   ├── vistas/                     plantilla.php y modulos/*.php (plantillas, no accesibles)
│   └── libs/fpdf/
├── config/config.php               todo desde variables de entorno
├── storage/logs/                   logs de la app, fuera de la raíz web
├── db/                             esquema, seed demo, herramientas, reset
│   ├── schema/ · seed/ · tools/ · reset_produccion.sql
├── docker/                         nginx.conf, php.ini
├── tests/                          PHPUnit
├── docs/
├── Dockerfile · docker-compose.yml · docker-compose.prod.yml
├── .env.example · .gitignore · .gitattributes
└── README.md
```

Reglas de la estructura:
1. **Lo único accesible por HTTP es `public/`.** `db/`, `src/`, `config/`, `storage/`, `tests/`, `docker/` quedan fuera sin depender de reglas de nginx.
2. **Las URLs públicas no cambian** (`vistas/...`, `ajax/...`, `api/...`, rutas amigables), así que el JavaScript y las vistas no se reescriben.
3. **Un solo bootstrap** (`src/bootstrap.php`) y un solo guard (`src/auth/guard.php`): la autenticación se escribe una vez y se llama desde cada endpoint.
4. **Rutas siempre con `__DIR__`** o la constante `BASE_PATH`; ninguna con `DOCUMENT_ROOT` ni `/colfe_web`.
5. **Plantillas (`src/vistas/modulos`) solo se incluyen desde el router**, nunca se ejecutan por URL (resuelve E1).

## 3. Mapa de movimientos

| Origen | Destino | Cambio necesario |
|---|---|---|
| `index.php` | `public/index.php` | `require` del bootstrap |
| `config.php` | `config/config.php` | todo por `getenv()`, sin credenciales por defecto, `ENVIRONMENT=production` |
| `controladores/` · `modelos/` | `src/controladores/` · `src/modelos/` | los 20 `require` pasan a `__DIR__` |
| `vistas/plantilla.php`, `vistas/modulos/` | `src/vistas/` | el router los incluye por `BASE_PATH` |
| `vistas/{css,js,dist,img,plugins,bower_components,libs/external}` | `public/vistas/...` | ninguno (mismas URLs) |
| `vistas/libs/fpdf` | `src/libs/fpdf/` | `require` en el recibo |
| `vistas/modulos/recibo.php`, `reporte_recoleccion.php` | `public/reportes/` | 4 enlaces en `liquidacion.js`, `recoleccion.js` y 2 vistas; añadir guard |
| `ajax/` · `api/` | `public/ajax/` · `public/api/` | `require` a `src/` con `__DIR__`; añadir guard |
| `ajax/prediccion.ajax.php`, `vistas/modulos/prediccion.php`, `vistas/js/prediccion.js` | **se eliminan** | quitar `<script>` de la plantilla si existe |
| `ajax/logs.log` | se elimina del repo | `storage/logs/` |
| `db/*.sql` | `db/schema/`, `db/seed/` | ver plan |
| `.htaccess` | `docker/nginx.conf` (`try_files`) | se conserva un `.htaccess` mínimo para Laragon/Apache |
| `test_simple.php` | se elimina | pruebas reales en `tests/` |

## 4. Orden de ejecución recomendado

La reestructura va **antes** de la seguridad: el guard, el token y el bootstrap se escriben
directamente en su ubicación final, sin moverlos después.

1. Fase 0: reestructura con `git mv` (conserva historial) y rutas con `__DIR__`. Verificación: `php -l` de todos los archivos, arranque con `php -S` apuntando a `public/` y comprobar que carga el login y sirve los estáticos.
2. Fase 1: seguridad sobre la estructura final.
3. Fase 2: Docker y despliegue (nginx con `root /var/www/html/public`).

Riesgo de la Fase 0: el proyecto no tiene pruebas automáticas. Se mitiga con cambios
mecánicos (mover y reemplazar rutas, sin tocar lógica), una verificación de sintaxis y un
recorrido manual de cada módulo en Laragon antes de fusionar.
