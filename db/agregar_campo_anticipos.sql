-- =====================================================
-- SCRIPT PARA AGREGAR CAMPO TOTAL_ANTICIPOS
-- =====================================================

-- Verificar si el campo existe
SELECT COLUMN_NAME 
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_SCHEMA = 'colfe_db' 
AND TABLE_NAME = 'tbl_liquidacion' 
AND COLUMN_NAME = 'total_anticipos';

-- Agregar el campo si no existe
ALTER TABLE tbl_liquidacion 
ADD COLUMN IF NOT EXISTS total_anticipos DECIMAL(10,2) DEFAULT 0.00 
COMMENT 'Total de anticipos del socio en la quincena';

-- Actualizar registros existentes que tengan NULL
UPDATE tbl_liquidacion 
SET total_anticipos = 0.00 
WHERE total_anticipos IS NULL;

-- Verificar la estructura actualizada
DESCRIBE tbl_liquidacion;

-- Verificar que no hay valores NULL
SELECT COUNT(*) as registros_con_null 
FROM tbl_liquidacion 
WHERE total_anticipos IS NULL;
