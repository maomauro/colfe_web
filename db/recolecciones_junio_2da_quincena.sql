-- =====================================================
-- SCRIPT PARA CREAR RECOLECCIONES - 2DA QUINCENA JUNIO
-- =====================================================

-- Fechas de la segunda quincena de junio (16-30 de junio)
-- Año 2024 (puedes cambiar el año según necesites)

-- Limpiar recolecciones existentes para estas fechas (opcional)
DELETE FROM tbl_recoleccion 
WHERE fecha BETWEEN '2024-06-16' AND '2024-06-30';

-- Insertar recolecciones para cada día de la segunda quincena
-- Día 16 de junio
INSERT INTO tbl_recoleccion (id_socio, fecha, litros_leche, estado)
SELECT 
    s.id_socio,
    '2024-06-16' as fecha,
    CASE 
        WHEN s.vinculacion = 'asociado' THEN FLOOR(RAND() * 50) + 20  -- 20-70 litros
        ELSE FLOOR(RAND() * 30) + 10  -- 10-40 litros para proveedores
    END as litros_leche,
    'sin confirmar' as estado
FROM tbl_socios s 
WHERE s.estado = 'activo';

-- Día 17 de junio
INSERT INTO tbl_recoleccion (id_socio, fecha, litros_leche, estado)
SELECT 
    s.id_socio,
    '2024-06-17' as fecha,
    CASE 
        WHEN s.vinculacion = 'asociado' THEN FLOOR(RAND() * 50) + 20
        ELSE FLOOR(RAND() * 30) + 10
    END as litros_leche,
    'sin confirmar' as estado
FROM tbl_socios s 
WHERE s.estado = 'activo';

-- Día 18 de junio
INSERT INTO tbl_recoleccion (id_socio, fecha, litros_leche, estado)
SELECT 
    s.id_socio,
    '2024-06-18' as fecha,
    CASE 
        WHEN s.vinculacion = 'asociado' THEN FLOOR(RAND() * 50) + 20
        ELSE FLOOR(RAND() * 30) + 10
    END as litros_leche,
    'sin confirmar' as estado
FROM tbl_socios s 
WHERE s.estado = 'activo';

-- Día 19 de junio
INSERT INTO tbl_recoleccion (id_socio, fecha, litros_leche, estado)
SELECT 
    s.id_socio,
    '2024-06-19' as fecha,
    CASE 
        WHEN s.vinculacion = 'asociado' THEN FLOOR(RAND() * 50) + 20
        ELSE FLOOR(RAND() * 30) + 10
    END as litros_leche,
    'sin confirmar' as estado
FROM tbl_socios s 
WHERE s.estado = 'activo';

-- Día 20 de junio
INSERT INTO tbl_recoleccion (id_socio, fecha, litros_leche, estado)
SELECT 
    s.id_socio,
    '2024-06-20' as fecha,
    CASE 
        WHEN s.vinculacion = 'asociado' THEN FLOOR(RAND() * 50) + 20
        ELSE FLOOR(RAND() * 30) + 10
    END as litros_leche,
    'sin confirmar' as estado
FROM tbl_socios s 
WHERE s.estado = 'activo';

-- Día 21 de junio
INSERT INTO tbl_recoleccion (id_socio, fecha, litros_leche, estado)
SELECT 
    s.id_socio,
    '2024-06-21' as fecha,
    CASE 
        WHEN s.vinculacion = 'asociado' THEN FLOOR(RAND() * 50) + 20
        ELSE FLOOR(RAND() * 30) + 10
    END as litros_leche,
    'sin confirmar' as estado
FROM tbl_socios s 
WHERE s.estado = 'activo';

-- Día 22 de junio
INSERT INTO tbl_recoleccion (id_socio, fecha, litros_leche, estado)
SELECT 
    s.id_socio,
    '2024-06-22' as fecha,
    CASE 
        WHEN s.vinculacion = 'asociado' THEN FLOOR(RAND() * 50) + 20
        ELSE FLOOR(RAND() * 30) + 10
    END as litros_leche,
    'sin confirmar' as estado
FROM tbl_socios s 
WHERE s.estado = 'activo';

-- Día 23 de junio
INSERT INTO tbl_recoleccion (id_socio, fecha, litros_leche, estado)
SELECT 
    s.id_socio,
    '2024-06-23' as fecha,
    CASE 
        WHEN s.vinculacion = 'asociado' THEN FLOOR(RAND() * 50) + 20
        ELSE FLOOR(RAND() * 30) + 10
    END as litros_leche,
    'sin confirmar' as estado
FROM tbl_socios s 
WHERE s.estado = 'activo';

-- Día 24 de junio
INSERT INTO tbl_recoleccion (id_socio, fecha, litros_leche, estado)
SELECT 
    s.id_socio,
    '2024-06-24' as fecha,
    CASE 
        WHEN s.vinculacion = 'asociado' THEN FLOOR(RAND() * 50) + 20
        ELSE FLOOR(RAND() * 30) + 10
    END as litros_leche,
    'sin confirmar' as estado
FROM tbl_socios s 
WHERE s.estado = 'activo';

-- Día 25 de junio
INSERT INTO tbl_recoleccion (id_socio, fecha, litros_leche, estado)
SELECT 
    s.id_socio,
    '2024-06-25' as fecha,
    CASE 
        WHEN s.vinculacion = 'asociado' THEN FLOOR(RAND() * 50) + 20
        ELSE FLOOR(RAND() * 30) + 10
    END as litros_leche,
    'sin confirmar' as estado
FROM tbl_socios s 
WHERE s.estado = 'activo';

-- Día 26 de junio
INSERT INTO tbl_recoleccion (id_socio, fecha, litros_leche, estado)
SELECT 
    s.id_socio,
    '2024-06-26' as fecha,
    CASE 
        WHEN s.vinculacion = 'asociado' THEN FLOOR(RAND() * 50) + 20
        ELSE FLOOR(RAND() * 30) + 10
    END as litros_leche,
    'sin confirmar' as estado
FROM tbl_socios s 
WHERE s.estado = 'activo';

-- Día 27 de junio
INSERT INTO tbl_recoleccion (id_socio, fecha, litros_leche, estado)
SELECT 
    s.id_socio,
    '2024-06-27' as fecha,
    CASE 
        WHEN s.vinculacion = 'asociado' THEN FLOOR(RAND() * 50) + 20
        ELSE FLOOR(RAND() * 30) + 10
    END as litros_leche,
    'sin confirmar' as estado
FROM tbl_socios s 
WHERE s.estado = 'activo';

-- Día 28 de junio
INSERT INTO tbl_recoleccion (id_socio, fecha, litros_leche, estado)
SELECT 
    s.id_socio,
    '2024-06-28' as fecha,
    CASE 
        WHEN s.vinculacion = 'asociado' THEN FLOOR(RAND() * 50) + 20
        ELSE FLOOR(RAND() * 30) + 10
    END as litros_leche,
    'sin confirmar' as estado
FROM tbl_socios s 
WHERE s.estado = 'activo';

-- Día 29 de junio
INSERT INTO tbl_recoleccion (id_socio, fecha, litros_leche, estado)
SELECT 
    s.id_socio,
    '2024-06-29' as fecha,
    CASE 
        WHEN s.vinculacion = 'asociado' THEN FLOOR(RAND() * 50) + 20
        ELSE FLOOR(RAND() * 30) + 10
    END as litros_leche,
    'sin confirmar' as estado
FROM tbl_socios s 
WHERE s.estado = 'activo';

-- Día 30 de junio
INSERT INTO tbl_recoleccion (id_socio, fecha, litros_leche, estado)
SELECT 
    s.id_socio,
    '2024-06-30' as fecha,
    CASE 
        WHEN s.vinculacion = 'asociado' THEN FLOOR(RAND() * 50) + 20
        ELSE FLOOR(RAND() * 30) + 10
    END as litros_leche,
    'sin confirmar' as estado
FROM tbl_socios s 
WHERE s.estado = 'activo';

-- =====================================================
-- VERIFICACIÓN DE DATOS INSERTADOS
-- =====================================================

-- Mostrar resumen de recolecciones creadas
SELECT 
    fecha,
    COUNT(*) as total_registros,
    SUM(litros_leche) as total_litros,
    COUNT(CASE WHEN estado = 'confirmado' THEN 1 END) as confirmados,
    COUNT(CASE WHEN estado = 'sin confirmar' THEN 1 END) as sin_confirmar
FROM tbl_recoleccion 
WHERE fecha BETWEEN '2024-06-16' AND '2024-06-30'
GROUP BY fecha
ORDER BY fecha;

-- Mostrar total general
SELECT 
    'TOTAL 2DA QUINCENA JUNIO' as periodo,
    COUNT(*) as total_registros,
    SUM(litros_leche) as total_litros,
    COUNT(CASE WHEN estado = 'confirmado' THEN 1 END) as confirmados,
    COUNT(CASE WHEN estado = 'sin confirmar' THEN 1 END) as sin_confirmar
FROM tbl_recoleccion 
WHERE fecha BETWEEN '2024-06-16' AND '2024-06-30';

-- Mostrar por tipo de vinculación
SELECT 
    s.vinculacion,
    COUNT(*) as total_socios,
    SUM(r.litros_leche) as total_litros
FROM tbl_recoleccion r
INNER JOIN tbl_socios s ON r.id_socio = s.id_socio
WHERE r.fecha BETWEEN '2024-06-16' AND '2024-06-30'
GROUP BY s.vinculacion;
