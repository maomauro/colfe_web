-- =====================================================
-- SCRIPT PARA CONFIRMAR RECOLECCIONES - 2DA QUINCENA JUNIO
-- =====================================================

-- Confirmar todas las recolecciones de la segunda quincena de junio
UPDATE tbl_recoleccion 
SET estado = 'confirmado'
WHERE fecha BETWEEN '2024-06-16' AND '2024-06-30'
AND estado = 'sin confirmar';

-- Verificar el resultado
SELECT 
    'RECOLECCIONES CONFIRMADAS' as accion,
    COUNT(*) as total_confirmadas
FROM tbl_recoleccion 
WHERE fecha BETWEEN '2024-06-16' AND '2024-06-30'
AND estado = 'confirmado';

-- Mostrar resumen final
SELECT 
    fecha,
    COUNT(*) as total_registros,
    SUM(litros_leche) as total_litros,
    COUNT(CASE WHEN estado = 'confirmado' THEN 1 END) as confirmados
FROM tbl_recoleccion 
WHERE fecha BETWEEN '2024-06-16' AND '2024-06-30'
GROUP BY fecha
ORDER BY fecha;
