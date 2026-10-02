#!/usr/bin/env python3
"""
Extrae el esquema (sin datos) de un dump de MySQL/MariaDB.

Uso:
    python3 db/tools/extraer_esquema.py db/seed/colfe_demo_2026.sql db/schema/colfe_schema.sql

Quita los INSERT y los DELETE FROM de datos; conserva tablas, vistas,
funciones, procedimientos y triggers.
"""
import re
import sys

INSERT = re.compile(r"^INSERT INTO `\w+`")
DELETE = re.compile(r"^DELETE FROM `\w+`;")


def main(entrada, salida):
    out, i, quitadas = [], 0, 0
    with open(entrada, encoding="utf-8") as f:
        lineas = f.read().split("\n")
    while i < len(lineas):
        l = lineas[i]
        if INSERT.match(l):
            i += 1
            while i < len(lineas) and lineas[i].startswith("\t("):
                quitadas += 1
                i += 1
            continue
        if DELETE.match(l):
            i += 1
            continue
        out.append(l)
        i += 1
    cab = "-- Esquema sin datos, generado con db/tools/extraer_esquema.py desde %s\n" % entrada.split("/")[-1]
    with open(salida, "w", encoding="utf-8", newline="\n") as f:
        f.write(cab + "\n".join(out))
    print("filas de datos omitidas:", quitadas)


if __name__ == "__main__":
    main(sys.argv[1], sys.argv[2])
