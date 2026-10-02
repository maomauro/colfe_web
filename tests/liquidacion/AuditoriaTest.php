<?php
/**
 * Auditoría de cambios (db/migraciones/004_auditoria.sql): quién, qué y los valores antes/después.
 * Se prueba con el contexto que la app fija en cada conexión (sesión web o token de API).
 */
final class AuditoriaTest extends BaseDeDatosTestCase
{
    private static int $usuario;
    private static int $socio;
    private static array $idsAnticipo = [];

    public static function setUpBeforeClass(): void
    {
        $u = self::valor("SELECT MIN(id) FROM tbl_usuarios");
        if (!$u) {
            self::ejecutar("INSERT INTO tbl_usuarios (username, password) VALUES ('aud.test', 'no-valida')");
            $u = self::valor("SELECT id FROM tbl_usuarios WHERE username='aud.test'");
        }
        self::$usuario = (int)$u;
        self::$socio = (int)self::valor("SELECT MIN(id_socio) FROM tbl_socios WHERE estado='activo'");
    }

    protected function setUp(): void
    {
        // Limpieza previa (sin contexto) y DESPUÉS una conexión nueva: el contexto lo lee la app al conectar,
        // así que la prueba debe fijarlo antes de la primera consulta.
        unset($GLOBALS['colfe_contexto']);
        $_SESSION = [];
        self::ejecutar("DELETE FROM tbl_auditoria WHERE tabla = 'tbl_anticipos' AND JSON_EXTRACT(COALESCE(datos_despues, datos_antes), '$.observaciones') = 'PRUEBA-AUD'");
        self::$pdo = null;
    }

    public static function tearDownAfterClass(): void
    {
        unset($GLOBALS['colfe_contexto']);
        $_SESSION = [];
        self::$pdo = null;
        self::ejecutar("DELETE FROM tbl_anticipos WHERE observaciones = 'PRUEBA-AUD'");
        self::ejecutar("DELETE FROM tbl_auditoria WHERE JSON_EXTRACT(COALESCE(datos_despues, datos_antes), '$.observaciones') = 'PRUEBA-AUD'");
        self::ejecutar("DELETE FROM tbl_usuarios WHERE username = 'aud.test'");
    }

    private function crearAnticipo(): int
    {
        self::ejecutar("INSERT INTO tbl_anticipos (id_socio, monto, fecha_anticipo, estado, observaciones) VALUES (?, 1000, '2026-01-10', 'pendiente', 'PRUEBA-AUD')", [self::$socio]);
        return (int)self::valor("SELECT MAX(id_anticipo) FROM tbl_anticipos WHERE observaciones = 'PRUEBA-AUD'");
    }

    private function ultimaAuditoria(string $tabla, string $id): ?array
    {
        $f = self::filas("SELECT * FROM tbl_auditoria WHERE tabla = ? AND id_registro = ? ORDER BY id_auditoria DESC LIMIT 1", [$tabla, $id]);
        return $f[0] ?? null;
    }

    public function testRegistraElUsuarioDeLaSesionWeb(): void
    {
        $_SESSION['id_usuario'] = self::$usuario;
        $id = $this->crearAnticipo();
        $a = $this->ultimaAuditoria('tbl_anticipos', (string)$id);
        $this->assertNotNull($a);
        $this->assertSame('INSERT', $a['accion']);
        $this->assertSame(self::$usuario, (int)$a['id_usuario']);
        $this->assertSame('web', $a['origen']);
        $this->assertNull($a['datos_antes']);
        $this->assertSame('pendiente', json_decode($a['datos_despues'], true)['estado']);
    }

    public function testRegistraElUsuarioDelTokenDeApi(): void
    {
        $GLOBALS['colfe_contexto'] = ['usuario' => self::$usuario, 'origen' => 'api'];
        $id = $this->crearAnticipo();
        $a = $this->ultimaAuditoria('tbl_anticipos', (string)$id);
        $this->assertSame('api', $a['origen']);
        $this->assertSame(self::$usuario, (int)$a['id_usuario']);
    }

    public function testSinContextoQuedaComoSistema(): void
    {
        $id = $this->crearAnticipo();
        $a = $this->ultimaAuditoria('tbl_anticipos', (string)$id);
        $this->assertNull($a['id_usuario']);
        $this->assertSame('sistema', $a['origen']);
    }

    public function testActualizacionGuardaAntesYDespues(): void
    {
        $_SESSION['id_usuario'] = self::$usuario;
        $id = $this->crearAnticipo();
        self::ejecutar("UPDATE tbl_anticipos SET estado = 'aprobado', monto = 2500 WHERE id_anticipo = ?", [$id]);
        $a = $this->ultimaAuditoria('tbl_anticipos', (string)$id);
        $this->assertSame('UPDATE', $a['accion']);
        $antes = json_decode($a['datos_antes'], true);
        $despues = json_decode($a['datos_despues'], true);
        $this->assertSame('pendiente', $antes['estado']);
        $this->assertSame('aprobado', $despues['estado']);
        $this->assertEqualsWithDelta(1000, (float)$antes['monto'], 0.001);
        $this->assertEqualsWithDelta(2500, (float)$despues['monto'], 0.001);
    }

    public function testUnaActualizacionSinCambiosNoSeRegistra(): void
    {
        $_SESSION['id_usuario'] = self::$usuario;
        $id = $this->crearAnticipo();
        $antes = (int)self::valor("SELECT COUNT(*) FROM tbl_auditoria WHERE tabla='tbl_anticipos' AND id_registro = ?", [(string)$id]);
        self::ejecutar("UPDATE tbl_anticipos SET estado = 'pendiente' WHERE id_anticipo = ?", [$id]);   // mismo valor
        $despues = (int)self::valor("SELECT COUNT(*) FROM tbl_auditoria WHERE tabla='tbl_anticipos' AND id_registro = ?", [(string)$id]);
        $this->assertSame($antes, $despues);
    }

    public function testBorradoGuardaLoQueSeElimino(): void
    {
        $_SESSION['id_usuario'] = self::$usuario;
        $id = $this->crearAnticipo();
        self::ejecutar("DELETE FROM tbl_anticipos WHERE id_anticipo = ?", [$id]);
        $a = $this->ultimaAuditoria('tbl_anticipos', (string)$id);
        $this->assertSame('DELETE', $a['accion']);
        $this->assertNull($a['datos_despues']);
        $this->assertSame('PRUEBA-AUD', json_decode($a['datos_antes'], true)['observaciones']);
    }

    public function testLosPreciosYDeduciblesSeAuditan(): void
    {
        $_SESSION['id_usuario'] = self::$usuario;
        $idPrecio = (int)self::valor("SELECT id_precio FROM tbl_precios WHERE estado='activo' AND vinculacion='proveedor'");
        $original = (float)self::valor("SELECT precio FROM tbl_precios WHERE id_precio = ?", [$idPrecio]);
        try {
            self::ejecutar("UPDATE tbl_precios SET precio = ? WHERE id_precio = ?", [$original + 10, $idPrecio]);
            $a = $this->ultimaAuditoria('tbl_precios', (string)$idPrecio);
            $this->assertSame(self::$usuario, (int)$a['id_usuario']);
            $this->assertEqualsWithDelta($original + 10, (float)json_decode($a['datos_despues'], true)['precio'], 0.001);
        } finally {
            self::ejecutar("UPDATE tbl_precios SET precio = ? WHERE id_precio = ?", [$original, $idPrecio]);
            self::ejecutar("DELETE FROM tbl_auditoria WHERE tabla = 'tbl_precios' AND id_registro = ?", [(string)$idPrecio]);
        }
    }

    public function testLasEdicionesDeRecoleccionSeAuditanPeroLasAltasNo(): void
    {
        $_SESSION['id_usuario'] = self::$usuario;
        $r = self::filas("SELECT id_recoleccion, litros_leche FROM tbl_recoleccion WHERE id_socio = ? ORDER BY fecha LIMIT 1", [self::$socio])[0];
        try {
            self::ejecutar("UPDATE tbl_recoleccion SET litros_leche = litros_leche + 1 WHERE id_recoleccion = ?", [$r['id_recoleccion']]);
            $a = $this->ultimaAuditoria('tbl_recoleccion', (string)$r['id_recoleccion']);
            $this->assertSame('UPDATE', $a['accion']);
            $this->assertSame(self::$usuario, (int)$a['id_usuario']);
        } finally {
            self::ejecutar("UPDATE tbl_recoleccion SET litros_leche = ? WHERE id_recoleccion = ?", [$r['litros_leche'], $r['id_recoleccion']]);
            self::ejecutar("DELETE FROM tbl_auditoria WHERE tabla = 'tbl_recoleccion' AND id_registro = ?", [(string)$r['id_recoleccion']]);
        }
        $this->assertSame(0, (int)self::valor("SELECT COUNT(*) FROM tbl_auditoria WHERE tabla='tbl_recoleccion' AND accion='INSERT'"),
            'las altas diarias de recolección son masivas y no se auditan');
    }

    public function testLaVistaMuestraElNombreDeUsuario(): void
    {
        $_SESSION['id_usuario'] = self::$usuario;
        $id = $this->crearAnticipo();
        $v = self::filas("SELECT username FROM v_auditoria WHERE tabla='tbl_anticipos' AND id_registro = ?", [(string)$id]);
        $this->assertNotEmpty($v[0]['username']);
    }
}
