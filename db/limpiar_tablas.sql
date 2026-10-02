-- =====================================================
-- SCRIPT PARA LIMPIAR TABLAS
-- Trunca las tablas de datos operativos
-- =====================================================

-- Configurar timeouts extendidos
SET SESSION innodb_lock_wait_timeout = 300;
SET SESSION lock_wait_timeout = 300;

-- Desactivar verificación de claves foráneas
SET FOREIGN_KEY_CHECKS = 0;

-- =====================================================
-- TRUNCAR TABLAS
-- =====================================================

-- Limpiar tabla de recolecciones
TRUNCATE TABLE tbl_recoleccion;

-- Limpiar tabla de producción  
TRUNCATE TABLE tbl_produccion;

-- Limpiar tabla de liquidaciones
TRUNCATE TABLE tbl_liquidacion;

-- Limpiar tabla de anticipos
TRUNCATE TABLE tbl_anticipos;

-- Reactivar verificación de claves foráneas
SET FOREIGN_KEY_CHECKS = 1;

-- =====================================================
-- CONFIRMACIÓN
-- =====================================================

SELECT 'TABLAS LIMPIADAS EXITOSAMENTE' as mensaje;

-- Verificar que las tablas estén vacías
SELECT 
    'tbl_recoleccion' as tabla,
    COUNT(*) as registros
FROM tbl_recoleccion
UNION ALL
SELECT 
    'tbl_produccion' as tabla,
    COUNT(*) as registros
FROM tbl_produccion
UNION ALL
SELECT 
    'tbl_liquidacion' as tabla,
    COUNT(*) as registros
FROM tbl_liquidacion
UNION ALL
SELECT 
    'tbl_anticipos' as tabla,
    COUNT(*) as registros
FROM tbl_anticipos;
