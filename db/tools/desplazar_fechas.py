#!/usr/bin/env python3
"""
Desplaza +N años todas las fechas del dump de la base demo de COLFE.

Uso:
    python3 db/tools/desplazar_fechas.py db/colfe_db_20260929.sql db/colfe_db_demo_2026.sql [--anios 1]

Qué hace
  - Solo toca filas de INSERT de las tablas con fechas (lee los literales
    'AAAA-MM-DD' y 'AAAA-MM-DD hh:mm:ss' dentro de cada fila).
  - Desplazar años enteros conserva el día 15 y el último día del mes, que es
    lo que exige spProcesarLiquidacionQuincenal.
  - Caso especial: 29-feb de un año bisiesto no existe en el año destino.
      * recoleccion / produccion / liquidacion con 29-feb: se descartan.
        Así la 2da quincena de febrero queda con recolecciones y sin
        liquidar, lista para procesarla desde la aplicación.
      * socios / anticipos / otras tablas: se mueve al 28-feb.
  - Ajusta las fechas fijas del procedimiento spInsertIntoRecoleccion.

No requiere base de datos: transforma texto y valida conteos.
"""
import argparse
import re
import sys
from collections import Counter
from datetime import date

FECHA = re.compile(r"'(\d{4})-(\d{2})-(\d{2})((?: \d{2}:\d{2}:\d{2})?)'")
INSERT = re.compile(r"^INSERT INTO `(\w+)`")
DESCARTAR_29FEB = {"tbl_recoleccion", "tbl_produccion", "tbl_liquidacion"}


def sumar_anios(y, m, d, n):
    try:
        return date(y + n, m, d)
    except ValueError:  # 29-feb -> 28-feb
        return date(y + n, m, 28)


def es_29feb(fila):
    return any(m.group(2) == "02" and m.group(3) == "29" for m in FECHA.finditer(fila))


def desplazar_fila(fila, n, stats):
    def sub(m):
        y, mo, d, hora = int(m.group(1)), int(m.group(2)), int(m.group(3)), m.group(4)
        nueva = sumar_anios(y, mo, d, n)
        stats["min"] = min(stats.get("min", nueva), nueva)
        stats["max"] = max(stats.get("max", nueva), nueva)
        return "'%s%s'" % (nueva.isoformat(), hora)

    return FECHA.sub(sub, fila)


def transformar(entrada, salida, n):
    antes, despues, descartadas = Counter(), Counter(), Counter()
    rangos = {}
    with open(entrada, encoding="utf-8") as f:
        lineas = f.read().split("\n")

    out = []
    i = 0
    en_proc_generador = False
    while i < len(lineas):
        linea = lineas[i]

        # Fechas fijas del generador de datos demo
        if "CREATE PROCEDURE `spInsertIntoRecoleccion`" in linea:
            en_proc_generador = True
        if en_proc_generador:
            linea = linea.replace("'2024-01-01'", "'%d-01-01'" % (2024 + n))
            linea = linea.replace("'2025-08-26'", "'%d-08-26'" % (2025 + n))
            if linea.startswith("DELIMITER ;"):
                en_proc_generador = False

        m = INSERT.match(linea)
        if not m:
            out.append(linea)
            i += 1
            continue

        tabla = m.group(1)
        out.append(linea)  # cabecera del INSERT
        i += 1
        filas = []
        while i < len(lineas) and lineas[i].startswith("\t("):
            filas.append(lineas[i].rstrip(",;"))
            i += 1
        antes[tabla] += len(filas)

        conservadas = []
        for fila in filas:
            if tabla in DESCARTAR_29FEB and es_29feb(fila):
                descartadas[tabla] += 1
                continue
            conservadas.append(desplazar_fila(fila, n, rangos.setdefault(tabla, {})))
        despues[tabla] += len(conservadas)

        for k, fila in enumerate(conservadas):
            out.append(fila + (";" if k == len(conservadas) - 1 else ","))

    cabecera = (
        "-- NOTA: fechas desplazadas +%d anio(s) con db/tools/desplazar_fechas.py.\n"
        "-- Origen: %s. No editar a mano: regenerar con el script.\n" % (n, entrada.split("/")[-1])
    )
    with open(salida, "w", encoding="utf-8", newline="\n") as f:
        f.write(cabecera + "\n".join(out))

    return antes, despues, descartadas, rangos


def main():
    ap = argparse.ArgumentParser()
    ap.add_argument("entrada")
    ap.add_argument("salida")
    ap.add_argument("--anios", type=int, default=1)
    a = ap.parse_args()
    antes, despues, desc, rangos = transformar(a.entrada, a.salida, a.anios)

    print("%-18s %8s %8s %10s  %s" % ("tabla", "antes", "despues", "descartadas", "rango de fechas nuevo"))
    for t in sorted(antes):
        r = rangos.get(t, {})
        rg = "%s .. %s" % (r["min"], r["max"]) if r else "-"
        print("%-18s %8d %8d %10d  %s" % (t, antes[t], despues[t], desc[t], rg))
    return 0


if __name__ == "__main__":
    sys.exit(main())
