// Recorrido real en Chromium: login, cada módulo (errores de JS y respuestas 4xx/5xx de AJAX) y borrado con confirmación.
//   BASE_URL=http://127.0.0.1:8099 APP_USER=admin APP_PASS=... node tests/navegador/recorrido.mjs
// Requiere playwright (npm install en tests/navegador) y un Chromium (PLAYWRIGHT_CHROMIUM, el del sistema o el que instala Playwright). Escribe en la base (socio de prueba).
import { chromium } from 'playwright-core';
import { existsSync } from 'node:fs';

const BASE = process.env.BASE_URL || 'http://127.0.0.1:8080';
const USER = process.env.APP_USER, PASS = process.env.APP_PASS;
const EXE = process.env.PLAYWRIGHT_CHROMIUM || '/opt/pw-browsers/chromium-1194/chrome-linux/chrome';
const opcionesLanzamiento = existsSync(EXE) ? { executablePath: EXE, args: ['--no-sandbox'] } : { args: ['--no-sandbox'] };
const SHOTS = process.env.SHOTS_DIR || '';
if (!USER || !PASS) { console.error('Defina APP_USER y APP_PASS'); process.exit(2); }

let fallos = 0;
const ok = (m) => console.log('  ok    ' + m);
const mal = (m) => { console.log('  FALLA ' + m); fallos++; };

const browser = await chromium.launch(opcionesLanzamiento);
const ctx = await browser.newContext({ viewport: { width: 1400, height: 900 } });
const page = await ctx.newPage();

let errores = [];
page.on('pageerror', (e) => errores.push('JS: ' + e.message));
page.on('response', (r) => {
  const u = r.url();
  if (u.startsWith(BASE) && r.status() >= 400 && !u.endsWith('favicon.ico')) errores.push(`HTTP ${r.status()} ${r.request().method()} ${u.replace(BASE, '')}`);
});
const limpiar = () => { const e = errores; errores = []; return e; };

console.log('1) Login');
await page.goto(BASE + '/');
await page.fill('#ingUsuario', USER);
await page.fill('#ingPassword', PASS);
await Promise.all([page.waitForURL('**/inicio', { timeout: 15000 }), page.click('button[type=submit]')]);
(await page.locator('meta[name="csrf-token"]').count()) === 1 ? ok('entró y la página expone el token CSRF') : mal('no hay meta csrf-token');
limpiar();

console.log('2) Módulos (sin errores de JS ni respuestas 4xx/5xx)');
for (const ruta of ['inicio', 'socios', 'calendario', 'recoleccion', 'produccion', 'deducibles', 'precios', 'liquidacion']) {
  await page.goto(`${BASE}/${ruta}`, { waitUntil: 'networkidle' });
  await page.waitForTimeout(500);
  const e = limpiar();
  const filas = await page.locator('table.dataTable tbody tr').count();
  e.length === 0 ? ok(`${ruta.padEnd(12)} sin errores${filas ? ' (' + filas + ' filas en la tabla)' : ''}`) : mal(`${ruta}: ${e.slice(0, 3).join(' | ')}`);
  if (SHOTS) await page.screenshot({ path: `${SHOTS}/${ruta}.png` });
}

console.log('3) Borrado de un socio con confirmación (POST + token)');
const token = await page.locator('meta[name="csrf-token"]').getAttribute('content');
const crear = await ctx.request.post(`${BASE}/socios`, { form: { csrf_token: token, nuevoNombreSocio: 'Navegador', nuevoApellidoSocio: 'Prueba', nuevoIdentificacionSocio: '888000222', nuevoTelefonoSocio: '3000000001', nuevoDireccionSocio: 'x', nuevoVinculacionSocio: 'proveedor' } });
crear.status() === 200 ? ok('socio de prueba creado') : mal('no se pudo crear el socio de prueba: ' + crear.status());
await page.goto(`${BASE}/socios`, { waitUntil: 'networkidle' });
const buscador = page.locator('input[type=search]').first();
if (await buscador.count()) { await buscador.fill('Navegador'); await page.waitForTimeout(400); }
const fila = page.locator('table.dataTable tbody tr', { hasText: 'Navegador' }).first();
(await fila.count()) === 1 ? ok('el socio aparece en la tabla') : mal('el socio de prueba no aparece en la tabla');
limpiar();
// Las vistas ya no muestran el botón de borrar (código muerto), pero el manejador JS sigue activo y es
// delegado: se inyecta el botón para probar el flujo completo (confirmación, POST y token).
const idNuevo = await fila.locator('.btnEditarSocio').getAttribute('idSocio');
await fila.locator('td').last().evaluate((td, id) => td.insertAdjacentHTML('beforeend', '<button class="btnEliminarSocio" idSocio="' + id + '">x</button>'), idNuevo);
await fila.locator('.btnEliminarSocio').click();
await page.waitForSelector('.swal2-confirm', { timeout: 5000 });
if (SHOTS) await page.screenshot({ path: `${SHOTS}/confirmar_borrado.png` });
await Promise.all([page.waitForResponse((r) => r.url().endsWith('/socios') && r.request().method() === 'POST', { timeout: 10000 }), page.click('.swal2-confirm')]);
await page.waitForSelector('.swal2-confirm', { timeout: 5000 });   // aviso de éxito
const titulo = (await page.locator('.swal2-title').innerText()).trim();
/borrado correctamente/.test(titulo) ? ok(`la app confirma: "${titulo}"`) : mal(`mensaje inesperado: "${titulo}"`);
await page.click('.swal2-confirm');
await page.waitForLoadState('networkidle');
const e3 = limpiar();
e3.length === 0 ? ok('sin errores durante el borrado') : mal(e3.join(' | '));
await page.goto(`${BASE}/socios`, { waitUntil: 'networkidle' });
(await page.locator('table.dataTable tbody tr', { hasText: 'Navegador' }).count()) === 0 ? ok('el socio ya no está en la tabla') : mal('el socio sigue en la tabla');

console.log('4) Cierre de sesión');
await page.goto(`${BASE}/salir`); await page.waitForTimeout(800);
await page.goto(`${BASE}/socios`);
(await page.locator('#ingUsuario').count()) === 1 ? ok('tras salir, se pide el ingreso de nuevo') : mal('sigue con sesión tras salir');

await browser.close();
console.log(fallos === 0 ? '\nRESULTADO: todo en orden' : `\nRESULTADO: ${fallos} fallo(s)`);
process.exit(fallos ? 1 : 0);
