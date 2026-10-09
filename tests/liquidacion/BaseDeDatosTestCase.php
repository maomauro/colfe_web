<?php
use PHPUnit\Framework\TestCase;

/** Utilidades comunes: conexión PDO y cálculo esperado de una quincena, independiente del SQL de la app. */
abstract class BaseDeDatosTestCase extends TestCase
{
    protected static ?PDO $pdo = null;

    public static function pdo(): PDO
    {
        if (self::$pdo === null) {
            self::$pdo = Conexion::conectar();
        }
        return self::$pdo;
    }

    /** @return array<int,array<string,mixed>> */
    protected static function filas(string $sql, array $params = []): array
    {
        $st = self::pdo()->prepare($sql);
        $st->execute($params);
        return $st->fetchAll(PDO::FETCH_ASSOC);
    }

    protected static function valor(string $sql, array $params = [])
    {
        $st = self::pdo()->prepare($sql);
        $st->execute($params);
        return $st->fetchColumn();
    }

    protected static function ejecutar(string $sql, array $params = []): void
    {
        self::pdo()->prepare($sql)->execute($params);
    }

    /** Rango [inicio, fin] de la quincena a partir de la fecha de liquidación (día 15 o fin de mes). */
    protected static function rangoQuincena(string $fechaLiquidacion): array
    {
        $f = new DateTimeImmutable($fechaLiquidacion);
        if ((int)$f->format('j') === 15) {
            return [$f->modify('first day of this month')->format('Y-m-d'), $fechaLiquidacion, '1ra'];
        }
        return [$f->modify('first day of this month')->modify('+15 days')->format('Y-m-d'), $fechaLiquidacion, '2da'];
    }

    /**
     * Calcula, sin usar el procedimiento almacenado, lo que debe resultar de liquidar la quincena:
     * una fila por socio activo con recolección confirmada, con el precio vigente en la fecha y los deducibles activos de su vinculación.
     * @return array<int,array<string,float|string|int>> indexado por id_socio
     */
    protected static function esperadoQuincena(string $fechaLiquidacion): array
    {
        [$ini, $fin] = self::rangoQuincena($fechaLiquidacion);
        // El precio que cuenta es el vigente en la fecha de cierre de la quincena
        $precios = array_column(self::filas(
            "SELECT vinculacion, precio FROM tbl_precios
              WHERE fecha_inicio <= :f AND (fecha_fin IS NULL OR fecha_fin >= :f2)",
            [':f' => $fechaLiquidacion, ':f2' => $fechaLiquidacion]
        ), 'precio', 'vinculacion');
        $deds = [];   // vinculación => [[tipo_valor, valor], ...]
        foreach (self::filas("SELECT vinculacion, tipo_valor, valor FROM tbl_deducibles WHERE estado='activo'") as $d) {
            $deds[$d['vinculacion']][] = [$d['tipo_valor'], (float)$d['valor']];
        }
        $litros = self::filas(
            "SELECT r.id_socio, s.vinculacion, SUM(r.litros_leche) AS litros
               FROM tbl_recoleccion r JOIN tbl_socios s ON s.id_socio = r.id_socio
              WHERE r.fecha BETWEEN :i AND :f AND r.estado = 'confirmado' AND s.estado = 'activo'
              GROUP BY r.id_socio, s.vinculacion",
            [':i' => $ini, ':f' => $fin]
        );
        $esperado = [];
        foreach ($litros as $l) {
            $v = $l['vinculacion'];
            $ingresos = (float)$l['litros'] * (float)$precios[$v];
            $ingresos = round($ingresos, 2);
            $deducibles = 0.0;
            foreach ($deds[$v] ?? [] as [$tipo, $valor]) {
                $deducibles += ($tipo === 'porcentaje') ? round($ingresos * $valor / 100, 2) : $valor;
            }
            $esperado[(int)$l['id_socio']] = [
                'litros' => round((float)$l['litros'], 2),
                'precio' => (float)$precios[$v],
                'ingresos' => round($ingresos, 2),
                'deducibles' => round($deducibles, 2),
                'neto' => round($ingresos - $deducibles, 2),
            ];
        }
        return $esperado;
    }
}
