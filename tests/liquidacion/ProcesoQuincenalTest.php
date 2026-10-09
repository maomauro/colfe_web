<?php
/**
 * Ejecuta spProcesarLiquidacionQuincenal sobre una quincena pendiente del demo (2da de febrero de 2025)
 * y compara cada fila con un cálculo independiente hecho en PHP.
 */
final class ProcesoQuincenalTest extends BaseDeDatosTestCase
{
    private const FECHA = '2025-02-28';
    private static int $socioProveedor;
    private static int $socioAsociado;

    public static function setUpBeforeClass(): void
    {
        self::limpiar();
        self::$socioProveedor = (int)self::valor("SELECT MIN(id_socio) FROM tbl_socios WHERE estado='activo' AND vinculacion='proveedor'");
        self::$socioAsociado  = (int)self::valor("SELECT MIN(id_socio) FROM tbl_socios WHERE estado='activo' AND vinculacion='asociado'");
    }

    public static function tearDownAfterClass(): void
    {
        self::limpiar();
    }

    private static function limpiar(): void
    {
        self::ejecutar("DELETE FROM tbl_liquidacion WHERE fecha_liquidacion = ?", [self::FECHA]);
        self::ejecutar("DELETE FROM tbl_produccion  WHERE fecha = ?", [self::FECHA]);
        // La auditoría (migración 004) también registró lo que hizo la prueba: se limpia
        self::ejecutar("DELETE FROM tbl_auditoria WHERE tabla = 'tbl_liquidacion'
                          AND JSON_UNQUOTE(JSON_EXTRACT(COALESCE(datos_despues, datos_antes), '$.fecha_liquidacion')) = ?", [self::FECHA]);
    }

    private static function llamar(string $fecha): void
    {
        $st = self::pdo()->prepare("CALL spProcesarLiquidacionQuincenal('liquidacion', :f)");
        $st->execute([':f' => $fecha]);
        $st->closeCursor();
    }

    public function testRechazaUnaFechaQueNoEsQuincena(): void
    {
        $this->expectExceptionMessage('día 15 o el último día del mes');
        self::llamar('2025-02-10');
    }

    public function testRechazaUnaQuincenaSinRecoleccion(): void
    {
        $this->expectExceptionMessage('Faltan registros confirmados');
        self::llamar('2026-09-15');
    }

    public function testRechazaSiHayRecoleccionSinConfirmar(): void
    {
        self::pdo()->beginTransaction();
        try {
            self::ejecutar("UPDATE tbl_recoleccion SET estado='sin confirmar' WHERE fecha='2025-02-20' AND id_socio = ?", [self::$socioProveedor]);
            try {
                self::llamar(self::FECHA);
                $this->fail('Debía rechazar registros sin confirmar');
            } catch (PDOException $e) {
                $this->assertStringContainsString('no confirmados', $e->getMessage());
            }
        } finally {
            self::pdo()->rollBack();
        }
        $this->assertSame(0, (int)self::valor("SELECT COUNT(*) FROM tbl_liquidacion WHERE fecha_liquidacion = ?", [self::FECHA]));
    }

    public function testLiquidaLaQuincenaConLosValoresEsperados(): void
    {
        self::llamar(self::FECHA);

        $esperado = self::esperadoQuincena(self::FECHA);
        $this->assertNotEmpty($esperado);

        $reales = [];
        foreach (self::filas("SELECT * FROM tbl_liquidacion WHERE fecha_liquidacion = ?", [self::FECHA]) as $l) {
            $reales[(int)$l['id_socio']] = $l;
        }

        $this->assertSame(array_keys($esperado), array_keys($reales), 'debe haber una liquidación por cada socio activo con recolección');
        $this->assertCount(107, $reales, 'los 107 socios activos del demo (80 proveedores + 27 asociados)');

        foreach ($esperado as $idSocio => $e) {
            $r = $reales[$idSocio];
            $this->assertEqualsWithDelta($e['litros'], (float)$r['total_litros'], 0.011, "litros socio $idSocio");
            $this->assertEqualsWithDelta($e['precio'], (float)$r['precio_litro'], 0.001, "precio socio $idSocio");
            $this->assertEqualsWithDelta($e['ingresos'], (float)$r['total_ingresos'], 0.02, "ingresos socio $idSocio");
            $this->assertEqualsWithDelta($e['deducibles'], (float)$r['total_deducibles'], 0.02, "deducibles socio $idSocio");
            $this->assertEqualsWithDelta($e['neto'], (float)$r['neto_a_pagar'], 0.03, "neto socio $idSocio");
            $this->assertSame('2da', $r['quincena']);
            $this->assertSame('pre-liquidacion', $r['estado']);
        }
    }

    public function testNoDuplicaAlLiquidarDosVeces(): void
    {
        self::llamar(self::FECHA);
        $antes = (int)self::valor("SELECT COUNT(*) FROM tbl_liquidacion WHERE fecha_liquidacion = ?", [self::FECHA]);
        self::llamar(self::FECHA);
        $despues = (int)self::valor("SELECT COUNT(*) FROM tbl_liquidacion WHERE fecha_liquidacion = ?", [self::FECHA]);
        $this->assertSame($antes, $despues);
    }

    public function testUnDeducibleNuevoSeAplicaSinCambiarElEsquema(): void
    {
        // Un deducible creado de la nada (porcentaje) debe entrar al cálculo y al detalle de cada liquidación
        self::limpiar();
        self::ejecutar("INSERT INTO tbl_deducibles (vinculacion, nombre, tipo_valor, valor, fecha, estado)
                        VALUES ('asociado', 'PRUEBA-DED', 'porcentaje', 2.00, '2026-10-09', 'activo')");
        $idDed = (int)self::valor("SELECT id_deducible FROM tbl_deducibles WHERE nombre = 'PRUEBA-DED'");
        try {
            self::llamar(self::FECHA);
            $filas = self::filas("SELECT l.id_liquidacion, l.total_ingresos, l.total_deducibles, ld.monto, ld.valor
                                    FROM tbl_liquidacion l
                                    JOIN tbl_liquidacion_deducible ld ON ld.id_liquidacion = l.id_liquidacion AND ld.id_deducible = ?
                                   WHERE l.fecha_liquidacion = ?", [$idDed, self::FECHA]);
            $this->assertCount(27, $filas, 'el deducible nuevo se aplica a los 27 asociados y a nadie más');
            foreach ($filas as $f) {
                $this->assertEqualsWithDelta(round((float)$f['total_ingresos'] * 0.02, 2), (float)$f['monto'], 0.011);
                $this->assertGreaterThanOrEqual((float)$f['monto'], (float)$f['total_deducibles']);
            }
            $this->assertSame(0, (int)self::valor("SELECT COUNT(*) FROM tbl_liquidacion_deducible ld
                                                    JOIN tbl_liquidacion l ON l.id_liquidacion = ld.id_liquidacion
                                                   WHERE l.fecha_liquidacion = ? AND l.vinculacion = 'proveedor' AND ld.id_deducible = ?",
                                                  [self::FECHA, $idDed]));
        } finally {
            self::limpiar();
            self::ejecutar("DELETE FROM tbl_deducibles WHERE id_deducible = ?", [$idDed]);
            self::ejecutar("DELETE FROM tbl_auditoria WHERE tabla = 'tbl_deducibles' AND id_registro = ?", [(string)$idDed]);
        }
    }

    public function testNoSePuedeRepetirUnDeducibleActivoConElMismoNombre(): void
    {
        $this->expectExceptionMessage('Ya existe un deducible activo con ese nombre');
        self::ejecutar("INSERT INTO tbl_deducibles (vinculacion, nombre, tipo_valor, valor, fecha, estado)
                        SELECT vinculacion, nombre, tipo_valor, valor, fecha, 'activo' FROM tbl_deducibles
                         WHERE estado = 'activo' LIMIT 1");
    }
}
