#!/usr/bin/env python3
"""
Genera db/migraciones/004_auditoria.sql: tabla tbl_auditoria, vista v_auditoria y triggers AFTER
INSERT/UPDATE/DELETE que registran quién cambió qué y los valores antes/después (JSON).

Uso:   python3 db/tools/generar_migracion_auditoria.py
Lee las columnas de db/schema/colfe_schema.sql. Si cambia el esquema de una tabla auditada,
vuelva a ejecutarlo (los triggers se recrean con DROP TRIGGER IF EXISTS).

Quién: la app fija @colfe_usuario y @colfe_origen en cada conexión (src/modelos/conexion.php).
"""
import re
import sys

ESQUEMA = "db/schema/colfe_schema.sql"
SALIDA = "db/migraciones/004_auditoria.sql"

# tabla -> (llave primaria, acciones auditadas)
AUDITADAS = {
    "tbl_liquidacion": ("id_liquidacion", "IUD"),
    "tbl_anticipos":   ("id_anticipo",    "IUD"),
    "tbl_precios":     ("id_precio",      "IUD"),
    "tbl_deducibles":  ("id_deducible",   "IUD"),
    "tbl_socios":      ("id_socio",       "IUD"),
    "tbl_recoleccion": ("id_recoleccion", "U"),   # solo ediciones: las altas diarias son masivas
}


def columnas(sql, tabla):
    m = re.search(r"CREATE TABLE IF NOT EXISTS `%s` \((.*?)\n\) ENGINE" % tabla, sql, re.S)
    if not m:
        sys.exit("No se encontró la tabla %s en %s" % (tabla, ESQUEMA))
    return re.findall(r"^\s+`(\w+)`\s", m.group(1), re.M)


def objeto(prefijo, cols):
    return "JSON_OBJECT(" + ", ".join("'%s', %s.`%s`" % (c, prefijo, c) for c in cols) + ")"


def main():
    sql = open(ESQUEMA, encoding="utf-8").read()
    out = ["""-- 004: auditoría de cambios (generado por db/tools/generar_migracion_auditoria.py; no editar a mano).
--
-- Registra INSERT/UPDATE/DELETE de liquidaciones, anticipos, precios, deducibles y socios, y las
-- ediciones de recolección, con el usuario (@colfe_usuario), el origen ('web', 'api' o 'sistema')
-- y los valores antes/después en JSON. Una actualización que no cambia nada no se registra.
--
-- En una base con datos demo conviene aplicarla DESPUÉS de los archivos db/seed/00x_demo_*.sql,
-- para que la regularización del demo no llene la auditoría. Es idempotente.
CREATE TABLE IF NOT EXISTS `tbl_auditoria` (
  `id_auditoria` bigint NOT NULL AUTO_INCREMENT,
  `fecha` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `tabla` varchar(40) NOT NULL,
  `accion` enum('INSERT','UPDATE','DELETE') NOT NULL,
  `id_registro` varchar(40) NOT NULL,
  `id_usuario` int DEFAULT NULL,
  `origen` varchar(10) NOT NULL DEFAULT 'sistema',
  `datos_antes` json DEFAULT NULL,
  `datos_despues` json DEFAULT NULL,
  PRIMARY KEY (`id_auditoria`),
  KEY `idx_aud_registro` (`tabla`,`id_registro`),
  KEY `idx_aud_fecha` (`fecha`),
  KEY `idx_aud_usuario` (`id_usuario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE OR REPLACE VIEW `v_auditoria` AS
  SELECT a.`id_auditoria`, a.`fecha`, a.`tabla`, a.`accion`, a.`id_registro`,
         a.`id_usuario`, u.`username`, a.`origen`, a.`datos_antes`, a.`datos_despues`
    FROM `tbl_auditoria` a
    LEFT JOIN `tbl_usuarios` u ON u.`id` = a.`id_usuario`;
"""]
    nombres = {"I": "INSERT", "U": "UPDATE", "D": "DELETE"}
    for tabla, (pk, acciones) in AUDITADAS.items():
        cols = columnas(sql, tabla)
        assert pk in cols, (tabla, pk)
        corto = tabla.replace("tbl_", "")
        for a in acciones:
            trig = "tr_aud_%s_%s" % (corto, a.lower())
            antes = objeto("OLD", cols) if a in "UD" else "NULL"
            despues = objeto("NEW", cols) if a in "IU" else "NULL"
            ref = "OLD" if a == "D" else "NEW"
            insert = ("INSERT INTO `tbl_auditoria` (`tabla`, `accion`, `id_registro`, `id_usuario`, `origen`, `datos_antes`, `datos_despues`)\n"
                      "        VALUES ('%s', '%s', %s.`%s`, @colfe_usuario, COALESCE(@colfe_origen, 'sistema'), %s, %s);"
                      % (tabla, nombres[a], ref, pk, antes, despues))
            if a == "U":
                igual = " AND ".join("OLD.`%s` <=> NEW.`%s`" % (c, c) for c in cols)
                cuerpo = "    IF NOT (%s) THEN\n        %s\n    END IF;" % (igual, insert)
            else:
                cuerpo = "    " + insert
            out.append("DROP TRIGGER IF EXISTS `%s`;\nDELIMITER //\nCREATE TRIGGER `%s` AFTER %s ON `%s` FOR EACH ROW\nBEGIN\n%s\nEND//\nDELIMITER ;\n"
                       % (trig, trig, nombres[a], tabla, cuerpo))
    open(SALIDA, "w", encoding="utf-8", newline="\n").write("\n".join(out))
    print("generado %s (%d triggers)" % (SALIDA, sum(len(v[1]) for v in AUDITADAS.values())))


if __name__ == "__main__":
    main()
