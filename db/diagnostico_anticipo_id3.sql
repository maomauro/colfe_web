-- =====================================================
-- DIAGNÓSTICO ANTICIPO ID 3
-- =====================================================

-- Verificar directamente el registro con ID 3
SELECT 
    id_anticipo,
    id_socio,
    monto,
    fecha_anticipo,
    estado,
    observaciones,
    usuario_registro,
    fecha_registro
FROM tbl_anticipos 
WHERE id_anticipo = 3;

-- Verificar la consulta que usa la vista
SELECT 
    a.id_anticipo,
    a.id_socio,
    a.monto,
    a.fecha_anticipo,
    a.estado,
    a.observaciones,
    a.usuario_registro,
    a.fecha_registro,
    s.nombre,
    s.apellido,
    s.identificacion,
    s.telefono,
    s.vinculacion
FROM tbl_anticipos a
INNER JOIN tbl_socios s ON a.id_socio = s.id_socio
WHERE a.id_anticipo = 3;

-- Verificar todos los anticipos para comparar
SELECT 
    id_anticipo,
    estado,
    fecha_registro
FROM tbl_anticipos 
ORDER BY id_anticipo;

-- Verificar si hay algún problema con la vista
SELECT 
    a.*,
    s.nombre,
    s.apellido,
    s.identificacion,
    s.telefono,
    s.vinculacion
FROM tbl_anticipos a
INNER JOIN tbl_socios s ON a.id_socio = s.id_socio
ORDER BY a.fecha_registro DESC;
