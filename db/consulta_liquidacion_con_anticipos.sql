-- =====================================================
-- CONSULTA SQL PARA LIQUIDACIÓN CON ANTICIPOS
-- =====================================================

-- Primero, verificar si existe el campo total_anticipos en tbl_liquidacion
-- Si no existe, agregarlo:
ALTER TABLE tbl_liquidacion 
ADD COLUMN IF NOT EXISTS total_anticipos DECIMAL(10,2) DEFAULT 0.00 
COMMENT 'Total de anticipos del socio en la quincena';

-- =====================================================
-- CONSULTA PRINCIPAL MODIFICADA
-- =====================================================

INSERT INTO tbl_liquidacion (
    id_produccion, id_deducible, id_precio, id_socio, vinculacion, quincena, identificacion,
    total_litros, precio_litro, total_ingresos,
    fedegan, administracion, ahorro, total_deducibles, total_anticipos,
    neto_a_pagar, estado, fecha_liquidacion
)
SELECT
    p.id_produccion,
    d.id_deducible,
    pr.id_precio,
    p.id_socio,
    s.vinculacion,
    p.quincena,
    s.identificacion,
    p.total_litros,
    pr.precio AS precio_litro,
    ROUND(p.total_litros * pr.precio, 2) AS total_ingresos,
    ROUND((p.total_litros * pr.precio) * (d.fedegan / 100), 2) AS fedegan,
    d.administracion,
    d.ahorro,
    ROUND(((p.total_litros * pr.precio) * (d.fedegan / 100)) + d.administracion + d.ahorro, 2) AS total_deducibles,
    -- Cálculo del total de anticipos aprobados del socio en la quincena
    COALESCE(ROUND(SUM(CASE 
        WHEN a.estado = 'aprobado' THEN a.monto 
        ELSE 0 
    END), 2), 0.00) AS total_anticipos,
    -- Cálculo del neto a pagar considerando anticipos
    ROUND(
        (p.total_litros * pr.precio) - 
        (((p.total_litros * pr.precio) * (d.fedegan / 100)) + d.administracion + d.ahorro) -
        COALESCE(SUM(CASE 
            WHEN a.estado = 'aprobado' THEN a.monto 
            ELSE 0 
        END), 0.00)
    , 2) AS neto_a_pagar,
    'pre-liquidacion' AS estado,
    p_fecha_liquidacion AS fecha_liquidacion
FROM
    tbl_produccion p
JOIN
    tbl_socios s ON p.id_socio = s.id_socio
JOIN
    tbl_deducibles d ON s.vinculacion = d.vinculacion AND d.estado = 'activo'
JOIN
    tbl_precios pr ON s.vinculacion = pr.vinculacion AND pr.estado = 'activo'
-- LEFT JOIN para anticipos (puede que no haya anticipos)
LEFT JOIN
    tbl_anticipos a ON p.id_socio = a.id_socio 
    AND a.estado = 'aprobado'
    AND a.fecha_anticipo BETWEEN 
        CASE 
            WHEN p.quincena = 1 THEN DATE_SUB(p_fecha_liquidacion, INTERVAL DAY(p_fecha_liquidacion)-1 DAY)
            ELSE DATE_ADD(DATE_SUB(p_fecha_liquidacion, INTERVAL DAY(p_fecha_liquidacion)-1 DAY), INTERVAL 15 DAY)
        END
        AND 
        CASE 
            WHEN p.quincena = 1 THEN DATE_ADD(DATE_SUB(p_fecha_liquidacion, INTERVAL DAY(p_fecha_liquidacion)-1 DAY), INTERVAL 14 DAY)
            ELSE LAST_DAY(p_fecha_liquidacion)
        END
WHERE
    p.fecha = p_fecha_liquidacion
    AND p.quincena = v_quincena
GROUP BY
    p.id_produccion, d.id_deducible, pr.id_precio, p.id_socio, s.vinculacion, 
    p.quincena, s.identificacion, p.total_litros, pr.precio, d.fedegan, 
    d.administracion, d.ahorro;

-- =====================================================
-- CONSULTA ALTERNATIVA (MÁS SIMPLE)
-- =====================================================

-- Si prefieres una versión más simple, puedes usar esta:

/*
INSERT INTO tbl_liquidacion (
    id_produccion, id_deducible, id_precio, id_socio, vinculacion, quincena, identificacion,
    total_litros, precio_litro, total_ingresos,
    fedegan, administracion, ahorro, total_deducibles, total_anticipos,
    neto_a_pagar, estado, fecha_liquidacion
)
SELECT
    p.id_produccion,
    d.id_deducible,
    pr.id_precio,
    p.id_socio,
    s.vinculacion,
    p.quincena,
    s.identificacion,
    p.total_litros,
    pr.precio AS precio_litro,
    ROUND(p.total_litros * pr.precio, 2) AS total_ingresos,
    ROUND((p.total_litros * pr.precio) * (d.fedegan / 100), 2) AS fedegan,
    d.administracion,
    d.ahorro,
    ROUND(((p.total_litros * pr.precio) * (d.fedegan / 100)) + d.administracion + d.ahorro, 2) AS total_deducibles,
    -- Subconsulta para obtener total de anticipos
    COALESCE((
        SELECT ROUND(SUM(a.monto), 2)
        FROM tbl_anticipos a
        WHERE a.id_socio = p.id_socio 
        AND a.estado = 'aprobado'
        AND a.fecha_anticipo BETWEEN 
            CASE 
                WHEN p.quincena = 1 THEN DATE_SUB(p_fecha_liquidacion, INTERVAL DAY(p_fecha_liquidacion)-1 DAY)
                ELSE DATE_ADD(DATE_SUB(p_fecha_liquidacion, INTERVAL DAY(p_fecha_liquidacion)-1 DAY), INTERVAL 15 DAY)
            END
            AND 
            CASE 
                WHEN p.quincena = 1 THEN DATE_ADD(DATE_SUB(p_fecha_liquidacion, INTERVAL DAY(p_fecha_liquidacion)-1 DAY), INTERVAL 14 DAY)
                ELSE LAST_DAY(p_fecha_liquidacion)
            END
    ), 0.00) AS total_anticipos,
    -- Cálculo del neto a pagar
    ROUND(
        (p.total_litros * pr.precio) - 
        (((p.total_litros * pr.precio) * (d.fedegan / 100)) + d.administracion + d.ahorro) -
        COALESCE((
            SELECT ROUND(SUM(a.monto), 2)
            FROM tbl_anticipos a
            WHERE a.id_socio = p.id_socio 
            AND a.estado = 'aprobado'
            AND a.fecha_anticipo BETWEEN 
                CASE 
                    WHEN p.quincena = 1 THEN DATE_SUB(p_fecha_liquidacion, INTERVAL DAY(p_fecha_liquidacion)-1 DAY)
                    ELSE DATE_ADD(DATE_SUB(p_fecha_liquidacion, INTERVAL DAY(p_fecha_liquidacion)-1 DAY), INTERVAL 15 DAY)
                END
                AND 
                CASE 
                    WHEN p.quincena = 1 THEN DATE_ADD(DATE_SUB(p_fecha_liquidacion, INTERVAL DAY(p_fecha_liquidacion)-1 DAY), INTERVAL 14 DAY)
                    ELSE LAST_DAY(p_fecha_liquidacion)
                END
        ), 0.00)
    , 2) AS neto_a_pagar,
    'pre-liquidacion' AS estado,
    p_fecha_liquidacion AS fecha_liquidacion
FROM
    tbl_produccion p
JOIN
    tbl_socios s ON p.id_socio = s.id_socio
JOIN
    tbl_deducibles d ON s.vinculacion = d.vinculacion AND d.estado = 'activo'
JOIN
    tbl_precios pr ON s.vinculacion = pr.vinculacion AND pr.estado = 'activo'
WHERE
    p.fecha = p_fecha_liquidacion
    AND p.quincena = v_quincena;
*/

-- =====================================================
-- CONSULTA DE VERIFICACIÓN
-- =====================================================

-- Para verificar que los anticipos se están calculando correctamente:
SELECT 
    s.id_socio,
    CONCAT(s.nombre, ' ', s.apellido) as socio,
    s.identificacion,
    p.quincena,
    p.total_litros,
    pr.precio as precio_litro,
    ROUND(p.total_litros * pr.precio, 2) as total_ingresos,
    COALESCE(SUM(CASE WHEN a.estado = 'aprobado' THEN a.monto ELSE 0 END), 0) as total_anticipos,
    ROUND(
        (p.total_litros * pr.precio) - 
        COALESCE(SUM(CASE WHEN a.estado = 'aprobado' THEN a.monto ELSE 0 END), 0)
    , 2) as neto_con_anticipos
FROM tbl_produccion p
JOIN tbl_socios s ON p.id_socio = s.id_socio
JOIN tbl_precios pr ON s.vinculacion = pr.vinculacion AND pr.estado = 'activo'
LEFT JOIN tbl_anticipos a ON p.id_socio = a.id_socio 
    AND a.estado = 'aprobado'
    AND a.fecha_anticipo BETWEEN 
        CASE 
            WHEN p.quincena = 1 THEN DATE_SUB('2025-01-15', INTERVAL DAY('2025-01-15')-1 DAY)
            ELSE DATE_ADD(DATE_SUB('2025-01-15', INTERVAL DAY('2025-01-15')-1 DAY), INTERVAL 15 DAY)
        END
        AND 
        CASE 
            WHEN p.quincena = 1 THEN DATE_ADD(DATE_SUB('2025-01-15', INTERVAL DAY('2025-01-15')-1 DAY), INTERVAL 14 DAY)
            ELSE LAST_DAY('2025-01-15')
        END
WHERE p.fecha = '2025-01-15' AND p.quincena = 1
GROUP BY s.id_socio, s.nombre, s.apellido, s.identificacion, p.quincena, p.total_litros, pr.precio
ORDER BY s.nombre;
