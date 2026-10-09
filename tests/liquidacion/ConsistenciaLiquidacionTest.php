<?php
/**
 * Invariantes de TODAS las liquidaciones guardadas: la aritmética cuadra, los litros coinciden con la
 * recolección real y no queda producción sin liquidar. Es la validación del cálculo contra los datos del demo.
 */
final class ConsistenciaLiquidacionTest extends BaseDeDatosTestCase
{
    public function testHayLiquidaciones(): void
    {
        $this->assertGreaterThan(1000, (int)self::valor("SELECT COUNT(*) FROM tbl_liquidacion"));
    }

    public function testNetoEsIngresosMenosDeducibles(): void
    {
        $n = self::valor("SELECT COUNT(*) FROM tbl_liquidacion
                           WHERE ABS(neto_a_pagar - (total_ingresos - total_deducibles)) > 0.011");
        $this->assertSame(0, (int)$n);
    }

    public function testTotalDeduciblesEsLaSumaDelDetalle(): void
    {
        $n = self::valor("SELECT COUNT(*) FROM tbl_liquidacion l
                           WHERE ABS(l.total_deducibles - (SELECT COALESCE(SUM(ld.monto), 0)
                                                             FROM tbl_liquidacion_deducible ld
                                                            WHERE ld.id_liquidacion = l.id_liquidacion)) > 0.001");
        $this->assertSame(0, (int)$n);
    }

    public function testIngresosSonLitrosPorPrecio(): void
    {
        $n = self::valor("SELECT COUNT(*) FROM tbl_liquidacion
                           WHERE ABS(total_ingresos - ROUND(total_litros * precio_litro, 2)) > 0.011");
        $this->assertSame(0, (int)$n);
    }

    public function testCadaDeduciblePorcentualEsSuPorcentajeDeLosIngresos(): void
    {
        $n = self::valor("SELECT COUNT(*) FROM tbl_liquidacion_deducible ld JOIN tbl_liquidacion l ON l.id_liquidacion = ld.id_liquidacion
                           WHERE ld.tipo_valor = 'porcentaje'
                             AND ABS(ld.monto - ROUND(l.total_ingresos * ld.valor / 100, 2)) > 0.011");
        $this->assertSame(0, (int)$n);
    }

    public function testCadaDeducibleFijoEsSuValor(): void
    {
        $n = self::valor("SELECT COUNT(*) FROM tbl_liquidacion_deducible WHERE tipo_valor = 'fijo' AND ABS(monto - valor) > 0.001");
        $this->assertSame(0, (int)$n);
    }

    public function testElPrecioDeCadaLiquidacionRegiaEnSuFechaDeCierre(): void
    {
        $n = self::valor("SELECT COUNT(*) FROM tbl_liquidacion l JOIN tbl_precios p ON p.id_precio = l.id_precio
                           WHERE NOT (p.fecha_inicio <= l.fecha_liquidacion
                                      AND (p.fecha_fin IS NULL OR p.fecha_fin >= l.fecha_liquidacion))");
        $this->assertSame(0, (int)$n);
    }

    public function testLosRangosDePreciosDeUnaVinculacionNoSeSolapan(): void
    {
        $n = self::valor("SELECT COUNT(*) FROM tbl_precios a JOIN tbl_precios b
                             ON a.vinculacion = b.vinculacion AND a.id_precio < b.id_precio
                            AND a.fecha_inicio <= COALESCE(b.fecha_fin, '9999-12-31')
                            AND COALESCE(a.fecha_fin, '9999-12-31') >= b.fecha_inicio");
        $this->assertSame(0, (int)$n);
    }

    public function testElPrecioGuardadoEsElDeSuTarifa(): void
    {
        $n = self::valor("SELECT COUNT(*) FROM tbl_liquidacion l JOIN tbl_precios p ON p.id_precio = l.id_precio
                           WHERE l.precio_litro <> p.precio OR l.vinculacion <> p.vinculacion");
        $this->assertSame(0, (int)$n);
    }

    public function testLaQuincenaCoincideConLaFecha(): void
    {
        $n = self::valor("SELECT COUNT(*) FROM tbl_liquidacion
                           WHERE (DAY(fecha_liquidacion) = 15 AND quincena <> '1ra')
                              OR (DAY(fecha_liquidacion) <> 15 AND quincena <> '2da')
                              OR (DAY(fecha_liquidacion) <> 15 AND fecha_liquidacion <> LAST_DAY(fecha_liquidacion))");
        $this->assertSame(0, (int)$n);
    }

    public function testLosLitrosLiquidadosSonLosRecolectadosYConfirmados(): void
    {
        $filas = self::filas("SELECT id_liquidacion, id_socio, quincena, fecha_liquidacion, total_litros FROM tbl_liquidacion");
        $suma = [];
        foreach (self::filas("SELECT id_socio, fecha, litros_leche FROM tbl_recoleccion WHERE estado = 'confirmado'") as $r) {
            $suma[$r['id_socio']][] = [$r['fecha'], (float)$r['litros_leche']];
        }
        $diferencias = 0;
        foreach ($filas as $l) {
            [$ini, $fin] = self::rangoQuincena($l['fecha_liquidacion']);
            $total = 0.0;
            foreach ($suma[$l['id_socio']] ?? [] as [$f, $lt]) {
                if ($f >= $ini && $f <= $fin) {
                    $total += $lt;
                }
            }
            if (abs($total - (float)$l['total_litros']) > 0.011) {
                $diferencias++;
            }
        }
        $this->assertSame(0, $diferencias, "liquidaciones cuyos litros no coinciden con la recolección");
    }

    public function testNoQuedaProduccionSinLiquidar(): void
    {
        $n = self::valor("SELECT COUNT(*) FROM tbl_produccion p
                           JOIN tbl_socios s ON s.id_socio = p.id_socio AND s.estado = 'activo'
                           LEFT JOIN tbl_liquidacion l ON l.id_produccion = p.id_produccion
                          WHERE l.id_liquidacion IS NULL");
        $this->assertSame(0, (int)$n, "producciones de socios activos sin liquidación (¿faltó un precio vigente en esa fecha?)");
    }

    public function testCadaVinculacionActivaTienePrecioVigenteHoy(): void
    {
        $this->assertSame([], ModeloCalendario::mdlVinculacionesSinTarifa());
    }
}
