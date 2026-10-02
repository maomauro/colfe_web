-- =====================================================
-- SCRIPT SIMPLIFICADO PARA CREAR ANTICIPOS DE EJEMPLO
-- Período: 1 de enero de 2025 hasta 26 de agosto de 2025
-- Base de datos: colfe_db
-- =====================================================

-- Configurar timeouts extendidos
SET SESSION innodb_lock_wait_timeout = 300;
SET SESSION lock_wait_timeout = 300;

-- Desactivar verificación de claves foráneas para inserción masiva
SET FOREIGN_KEY_CHECKS = 0;

-- =====================================================
-- CREAR ANTICIPOS DE EJEMPLO
-- =====================================================

-- Iniciar transacción para inserción masiva
START TRANSACTION;

-- Insertar anticipos para socios activos (versión simplificada)
INSERT INTO tbl_anticipos (id_socio, fecha_anticipo, monto, estado, observaciones)
SELECT 
    s.id_socio,
    DATE_ADD('2025-01-01', INTERVAL FLOOR(RAND() * 238) DAY) as fecha_anticipo,
    CASE 
        WHEN s.vinculacion = 'asociado' THEN FLOOR(50000 + RAND() * 100000)
        ELSE FLOOR(30000 + RAND() * 70000)
    END as monto,
    CASE 
        WHEN RAND() <= 0.70 THEN 'aprobado'
        WHEN RAND() <= 0.85 THEN 'pendiente'
        ELSE 'rechazado'
    END as estado,
    CASE 
        WHEN RAND() <= 0.30 THEN 'Anticipo para gastos de producción'
        WHEN RAND() <= 0.50 THEN 'Anticipo para mantenimiento de equipos'
        WHEN RAND() <= 0.70 THEN 'Anticipo para compra de insumos'
        WHEN RAND() <= 0.85 THEN 'Anticipo para gastos familiares'
        ELSE 'Anticipo solicitado por el socio'
    END as observaciones
FROM tbl_socios s
WHERE s.estado = 'activo'
AND RAND() <= 0.25  -- 25% de probabilidad
AND (
    SELECT COUNT(*) 
    FROM tbl_anticipos a2 
    WHERE a2.id_socio = s.id_socio 
    AND a2.fecha_anticipo BETWEEN '2025-01-01' AND '2025-08-26'
) < 3;  -- Máximo 3 anticipos por socio

-- Confirmar transacción
COMMIT;

-- =====================================================
-- VERIFICACIÓN Y ESTADÍSTICAS
-- =====================================================

-- Mostrar estadísticas generales
SELECT 
    'ESTADÍSTICAS GENERALES DE ANTICIPOS' as titulo,
    COUNT(*) as total_anticipos,
    SUM(CASE WHEN estado = 'aprobado' THEN 1 ELSE 0 END) as aprobados,
    SUM(CASE WHEN estado = 'pendiente' THEN 1 ELSE 0 END) as pendientes,
    SUM(CASE WHEN estado = 'rechazado' THEN 1 ELSE 0 END) as rechazados,
    SUM(monto) as total_monto,
    AVG(monto) as promedio_monto
FROM tbl_anticipos 
WHERE fecha_anticipo BETWEEN '2025-01-01' AND '2025-08-26';

-- Mostrar anticipos por socio (top 10)
SELECT 
    s.nombre,
    s.apellido,
    s.vinculacion,
    COUNT(a.id_anticipo) as total_anticipos,
    SUM(a.monto) as total_monto,
    AVG(a.monto) as promedio_monto,
    SUM(CASE WHEN a.estado = 'aprobado' THEN 1 ELSE 0 END) as aprobados,
    SUM(CASE WHEN a.estado = 'pendiente' THEN 1 ELSE 0 END) as pendientes,
    SUM(CASE WHEN a.estado = 'rechazado' THEN 1 ELSE 0 END) as rechazados
FROM tbl_socios s
LEFT JOIN tbl_anticipos a ON s.id_socio = a.id_socio 
    AND a.fecha_anticipo BETWEEN '2025-01-01' AND '2025-08-26'
WHERE s.estado = 'activo'
GROUP BY s.id_socio, s.nombre, s.apellido, s.vinculacion
HAVING total_anticipos > 0
ORDER BY total_monto DESC
LIMIT 10;

-- Mostrar distribución por meses
SELECT 
    DATE_FORMAT(fecha_anticipo, '%Y-%m') as mes,
    COUNT(*) as cantidad_anticipos,
    SUM(monto) as total_monto,
    AVG(monto) as promedio_monto,
    SUM(CASE WHEN estado = 'aprobado' THEN 1 ELSE 0 END) as aprobados,
    SUM(CASE WHEN estado = 'pendiente' THEN 1 ELSE 0 END) as pendientes,
    SUM(CASE WHEN estado = 'rechazado' THEN 1 ELSE 0 END) as rechazados
FROM tbl_anticipos 
WHERE fecha_anticipo BETWEEN '2025-01-01' AND '2025-08-26'
GROUP BY DATE_FORMAT(fecha_anticipo, '%Y-%m')
ORDER BY mes;

-- Mostrar algunos ejemplos de anticipos creados
SELECT 
    a.id_anticipo,
    CONCAT(s.nombre, ' ', s.apellido) as socio,
    s.vinculacion,
    a.fecha_anticipo,
    a.monto,
    a.estado,
    a.observaciones
FROM tbl_anticipos a
INNER JOIN tbl_socios s ON a.id_socio = s.id_socio
WHERE a.fecha_anticipo BETWEEN '2025-01-01' AND '2025-08-26'
ORDER BY a.fecha_anticipo DESC, a.id_anticipo DESC
LIMIT 20;

-- Reactivar verificación de claves foráneas
SET FOREIGN_KEY_CHECKS = 1;

-- =====================================================
-- MENSAJE DE CONFIRMACIÓN
-- =====================================================

SELECT 'Anticipos creados exitosamente para el período 2025-01-01 a 2025-08-26' as mensaje;
