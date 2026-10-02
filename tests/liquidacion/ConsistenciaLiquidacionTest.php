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

    public function testNetoEsIngresosMenosDeduciblesMenosAnticipos(): void
    {
        $n = self::valor("SELECT COUNT(*) FROM tbl_liquidacion
                           WHERE ABS(neto_a_pagar - (total_ingresos - total_deducibles - total_anticipos)) > 0.011");
        $this->assertSame(0, (int)$n);
    }

    public function testDeduciblesSumanFedeganAdministracionYAhorro(): void
    {
        $n = self::valor("SELECT COUNT(*) FROM tbl_liquidacion
                           WHERE ABS(total_deducibles - (fedegan + administracion + ahorro)) > 0.011");
        $this->assertSame(0, (int)$n);
    }

    public function testIngresosSonLitrosPorPrecio(): void
    {
        $n = self::valor("SELECT COUNT(*) FROM tbl_liquidacion
                           WHERE ABS(total_ingresos - ROUND(total_litros * precio_litro, 2)) > 0.011");
        $this->assertSame(0, (int)$n);
    }

    public function testFedeganEsElPorcentajeDelDeducibleSobreLosIngresos(): void
    {
        $n = self::valor("SELECT COUNT(*) FROM tbl_liquidacion l JOIN tbl_deducibles d ON d.id_deducible = l.id_deducible
                           WHERE ABS(l.fedegan - ROUND(l.total_litros * l.precio_litro * d.fedegan / 100, 2)) > 0.011");
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
        $this->assertSame(0, (int)$n, "producciones de socios activos sin liquidación (¿precio o deducible inactivo?)");
    }

    public function testLosAnticiposDescontadosSonLosAprobadosDeLaQuincena(): void
    {
        $filas = self::filas("SELECT id_liquidacion, id_socio, fecha_liquidacion, total_anticipos FROM tbl_liquidacion");
        $aprobados = [];
        foreach (self::filas("SELECT id_socio, fecha_anticipo, monto FROM tbl_anticipos WHERE estado = 'aprobado'") as $a) {
            $aprobados[$a['id_socio']][] = [$a['fecha_anticipo'], (float)$a['monto']];
        }
        $diferencias = 0;
        foreach ($filas as $l) {
            [$ini, $fin] = self::rangoQuincena($l['fecha_liquidacion']);
            $total = 0.0;
            foreach ($aprobados[$l['id_socio']] ?? [] as [$f, $m]) {
                if ($f >= $ini && $f <= $fin) {
                    $total += $m;
                }
            }
            if (abs($total - (float)$l['total_anticipos']) > 0.011) {
                $diferencias++;
            }
        }
        $this->assertSame(0, $diferencias, "liquidaciones con anticipos distintos a los aprobados de su quincena");
    }

    public function testCadaVinculacionActivaTienePrecioYDeducibleActivos(): void
    {
        $this->assertSame([], ModeloCalendario::mdlVinculacionesSinTarifa());
    }
}
