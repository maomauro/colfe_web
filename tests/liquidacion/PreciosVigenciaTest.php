<?php
/**
 * Precios con vigencia (db/migraciones/008_precios_con_vigencia.sql): rangos sin solapes, creación que
 * cierra el precio abierto anterior y liquidación con el precio vigente en la fecha de cierre.
 * Cada prueba deja la tabla de precios como la encontró.
 */
final class PreciosVigenciaTest extends BaseDeDatosTestCase
{
    private const FECHA = '2025-02-28';
    private static array $originales = [];

    public static function setUpBeforeClass(): void
    {
        // Estado de los precios antes de empezar, para restaurarlo
        self::$originales = self::filas("SELECT id_precio, fecha_inicio, fecha_fin FROM tbl_precios");
    }

    protected function setUp(): void
    {
        self::restaurar();
    }

    public static function tearDownAfterClass(): void
    {
        self::restaurar();
    }

    /** Borra lo que crearon las pruebas y devuelve los precios originales a sus fechas. */
    private static function restaurar(): void
    {
        self::ejecutar("DELETE FROM tbl_liquidacion WHERE fecha_liquidacion = ?", [self::FECHA]);
        self::ejecutar("DELETE FROM tbl_produccion WHERE fecha = ?", [self::FECHA]);
        $ids = array_column(self::$originales, 'id_precio');
        $marcas = implode(',', array_fill(0, count($ids), '?'));
        self::ejecutar("DELETE FROM tbl_precios WHERE id_precio NOT IN ($marcas)", $ids);
        foreach (self::$originales as $p) {
            self::ejecutar("UPDATE tbl_precios SET fecha_inicio = ?, fecha_fin = ? WHERE id_precio = ?",
                [$p['fecha_inicio'], $p['fecha_fin'], $p['id_precio']]);
        }
        self::ejecutar("DELETE FROM tbl_auditoria WHERE tabla IN ('tbl_precios', 'tbl_liquidacion')
                          AND (tabla = 'tbl_precios' OR JSON_UNQUOTE(JSON_EXTRACT(COALESCE(datos_despues, datos_antes), '$.fecha_liquidacion')) = ?)",
            [self::FECHA]);
    }

    private static function crear(string $vinculacion, float $precio, string $inicio, ?string $fin = null): void
    {
        $st = self::pdo()->prepare("CALL spCrearPrecio(?, ?, ?, ?)");
        $st->execute([$vinculacion, $precio, $inicio, $fin]);
        $st->closeCursor();
    }

    private static function llamarLiquidacion(string $fecha): void
    {
        $st = self::pdo()->prepare("CALL spProcesarLiquidacionQuincenal('liquidacion', :f)");
        $st->execute([':f' => $fecha]);
        $st->closeCursor();
    }

    public function testElDemoTieneUnPrecioAbiertoPorVinculacion(): void
    {
        $this->assertSame(
            ['asociado', 'proveedor'],
            array_column(self::filas("SELECT vinculacion FROM tbl_precios WHERE fecha_fin IS NULL ORDER BY vinculacion"), 'vinculacion')
        );
    }

    public function testNoPermiteUnRangoQueSeSolapa(): void
    {
        $this->expectExceptionMessage('se solapa con otro precio');
        // El precio abierto del demo cubre desde 2023 hasta hoy y más allá
        self::ejecutar("INSERT INTO tbl_precios (vinculacion, precio, fecha_inicio, fecha_fin) VALUES ('asociado', 1800, '2026-01-01', '2026-01-31')");
    }

    public function testPermiteRangosContiguosSinSolape(): void
    {
        $abierto = self::filas("SELECT id_precio FROM tbl_precios WHERE vinculacion = 'proveedor' AND fecha_fin IS NULL")[0]['id_precio'];
        self::ejecutar("UPDATE tbl_precios SET fecha_fin = '2029-12-31' WHERE id_precio = ?", [$abierto]);
        self::ejecutar("INSERT INTO tbl_precios (vinculacion, precio, fecha_inicio, fecha_fin) VALUES ('proveedor', 1700, '2030-01-01', NULL)");
        $this->assertSame('2029-12-31', self::valor("SELECT fecha_fin FROM tbl_precios WHERE id_precio = ?", [$abierto]));
        $this->assertSame(1, (int)self::valor("SELECT COUNT(*) FROM tbl_precios WHERE vinculacion = 'proveedor' AND fecha_inicio = '2030-01-01' AND fecha_fin IS NULL"));
    }

    public function testLaFechaDeFinNoPuedeSerAnteriorALaDeInicio(): void
    {
        $this->expectExceptionMessage('fecha de fin no puede ser anterior');
        self::ejecutar("INSERT INTO tbl_precios (vinculacion, precio, fecha_inicio, fecha_fin) VALUES ('proveedor', 1700, '2031-02-01', '2031-01-01')");
    }

    public function testCrearUnPrecioCierraElAbiertoAnterior(): void
    {
        $abierto = self::filas("SELECT id_precio FROM tbl_precios WHERE vinculacion = 'proveedor' AND fecha_fin IS NULL")[0]['id_precio'];
        self::crear('proveedor', 1750, '2030-06-01');
        $this->assertSame('2030-05-31', self::valor("SELECT fecha_fin FROM tbl_precios WHERE id_precio = ?", [$abierto]), 'el abierto termina el día anterior');
        $nuevo = self::filas("SELECT precio, fecha_inicio, fecha_fin FROM tbl_precios WHERE vinculacion = 'proveedor' AND fecha_inicio = '2030-06-01'")[0];
        $this->assertEqualsWithDelta(1750, (float)$nuevo['precio'], 0.001);
        $this->assertNull($nuevo['fecha_fin'], 'el nuevo queda abierto');
    }

    public function testCrearUnPrecioQueSeSolapaNoCambiaNada(): void
    {
        $abierto = self::filas("SELECT id_precio, fecha_inicio FROM tbl_precios WHERE vinculacion = 'asociado' AND fecha_fin IS NULL")[0];
        try {
            // Empieza antes que el abierto: no se puede cerrar y el rango se solapa
            self::crear('asociado', 1900, '2022-01-01');
            $this->fail('Debía rechazar el rango solapado');
        } catch (PDOException $e) {
            $this->assertStringContainsString('se solapa', $e->getMessage());
        }
        $this->assertNull(self::valor("SELECT fecha_fin FROM tbl_precios WHERE id_precio = ?", [$abierto['id_precio']]), 'el abierto sigue abierto');
        $this->assertSame(0, (int)self::valor("SELECT COUNT(*) FROM tbl_precios WHERE precio = 1900"), 'no se guardó el nuevo');
    }

    public function testLaLiquidacionUsaElPrecioVigenteEnLaFechaDeCierre(): void
    {
        // El asociado pasa a 2.000 desde el 20-feb-2025; el proveedor no cambia
        self::crear('asociado', 2000, '2025-02-20');
        self::llamarLiquidacion(self::FECHA);
        $asociados = self::filas("SELECT DISTINCT precio_litro FROM tbl_liquidacion WHERE fecha_liquidacion = ? AND vinculacion = 'asociado'", [self::FECHA]);
        $proveedores = self::filas("SELECT DISTINCT precio_litro FROM tbl_liquidacion WHERE fecha_liquidacion = ? AND vinculacion = 'proveedor'", [self::FECHA]);
        $this->assertCount(1, $asociados);
        $this->assertEqualsWithDelta(2000, (float)$asociados[0]['precio_litro'], 0.001, 'asociados con el precio nuevo, vigente al 28-feb');
        $this->assertCount(1, $proveedores);
        $this->assertEqualsWithDelta(1650, (float)$proveedores[0]['precio_litro'], 0.001, 'proveedores con su precio de siempre');
    }

    public function testUnPrecioFuturoNoSeUsaAntesDeSuInicio(): void
    {
        // Rige desde el 1-mar-2025: la quincena que cierra el 28-feb sigue con el anterior
        self::crear('asociado', 2500, '2025-03-01');
        self::llamarLiquidacion(self::FECHA);
        $p = self::filas("SELECT DISTINCT precio_litro FROM tbl_liquidacion WHERE fecha_liquidacion = ? AND vinculacion = 'asociado'", [self::FECHA]);
        $this->assertEqualsWithDelta(1700, (float)$p[0]['precio_litro'], 0.001);
    }

    public function testLaAppSeNiegaALiquidarSiNoHayPrecioVigenteEnLaFecha(): void
    {
        // La app abre su propia conexión: el cambio debe estar confirmado para que lo vea (restaurar() lo deshace)
        $idPrecio = (int)self::valor("SELECT id_precio FROM tbl_precios WHERE vinculacion = 'asociado' AND fecha_fin IS NULL");
        self::ejecutar("UPDATE tbl_precios SET fecha_fin = '2025-02-01' WHERE id_precio = ?", [$idPrecio]);
        $mensaje = ModeloCalendario::mdlCrearEvento('liquidacion', '2025-03-15');
        $this->assertStringContainsString('falta un precio vigente el 2025-03-15', $mensaje);
        $this->assertStringContainsString('asociado', $mensaje);
        $this->assertStringNotContainsString('proveedor', $mensaje);
        // El mismo precio sí cubre una fecha anterior a su cierre
        $this->assertSame([], ModeloCalendario::mdlVinculacionesSinTarifa('2025-01-15'));
    }
}
