-- =====================================================
-- CORREGIR ESTADO ANTICIPO ID 3
-- =====================================================

-- Verificar el estado actual
SELECT 
    id_anticipo,
    estado,
    fecha_registro
FROM tbl_anticipos 
WHERE id_anticipo = 3;

-- Actualizar el estado a 'aprobado' si no lo está
UPDATE tbl_anticipos 
SET estado = 'aprobado'
WHERE id_anticipo = 3 
AND estado != 'aprobado';

-- Verificar el cambio
SELECT 
    id_anticipo,
    estado,
    fecha_registro
FROM tbl_anticipos 
WHERE id_anticipo = 3;

-- Verificar que la consulta de la vista funcione correctamente
SELECT 
    a.id_anticipo,
    a.estado,
    s.nombre,
    s.apellido
FROM tbl_anticipos a
INNER JOIN tbl_socios s ON a.id_socio = s.id_socio
WHERE a.id_anticipo = 3;
