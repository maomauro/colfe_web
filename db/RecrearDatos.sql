

-------------------------------------------------------------------------
-------------------------------------------------------------------------
-- 1. Desactivar el autocommit para una inserción más rápida
SET autocommit = 0;
START TRANSACTION;

-- 2. Función para generar nombres aleatorios
DROP FUNCTION IF EXISTS random_name;
DELIMITER $$
CREATE FUNCTION random_name(type INT) RETURNS VARCHAR(50)
BEGIN
    DECLARE first_names VARCHAR(1000);
    DECLARE last_names VARCHAR(1000);
    DECLARE random_first VARCHAR(50);
    DECLARE random_last VARCHAR(50);
    
    SET first_names = 'Juan,Maria,Carlos,Ana,Luis,Laura,Pedro,Sofia,Andres,Patricia,Jorge,Daniela';
    SET last_names = 'Gomez,Rodriguez,Lopez,Martinez,Garcia,Perez,Gonzalez,Sanchez,Ramirez,Torres,Diaz,Flores';
    
    SET random_first = SUBSTRING_INDEX(SUBSTRING_INDEX(first_names, ',', FLOOR(1 + RAND() * 12)), ',', -1);
    SET random_last = SUBSTRING_INDEX(SUBSTRING_INDEX(last_names, ',', FLOOR(1 + RAND() * 12)), ',', -1);
    
    RETURN IF(type = 1, random_first, random_last);
END$$
DELIMITER ;

-- 3. Función para generar números de identificación aleatorios
DROP FUNCTION IF EXISTS random_identification;
DELIMITER $$
CREATE FUNCTION random_identification() RETURNS VARCHAR(20)
BEGIN
    RETURN CONCAT(FLOOR(RAND() * 90) + 10, 
                  FLOOR(RAND() * 900) + 100, 
                  FLOOR(RAND() * 900) + 100);
END$$
DELIMITER ;

-- 4. Función para generar teléfonos aleatorios
DROP FUNCTION IF EXISTS random_phone;
DELIMITER $$
CREATE FUNCTION random_phone() RETURNS VARCHAR(20)
BEGIN
    RETURN CONCAT('3', FLOOR(RAND() * 9), 
                  FLOOR(RAND() * 900) + 100, 
                  FLOOR(RAND() * 9000) + 1000);
END$$
DELIMITER ;

-- 5. Insertar 27 socios asociados
INSERT INTO tbl_socios (nombre, apellido, identificacion, telefono, direccion, vinculacion, fecha_ingreso, estado)
SELECT 
    random_name(1) AS nombre,
    random_name(2) AS apellido,
    random_identification() AS identificacion,
    random_phone() AS telefono,
    CONCAT('Calle ', FLOOR(RAND() * 100) + 1, ' #', FLOOR(RAND() * 100) + 1, '-', FLOOR(RAND() * 50) + 1) AS direccion,
    'asociado' AS vinculacion,
    DATE_SUB(CURDATE(), INTERVAL FLOOR(RAND() * 365*3) DAY) AS fecha_ingreso,
    IF(RAND() > 0.2, 'activo', 'inactivo') AS estado
FROM 
    (SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION
     SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9 UNION SELECT 10 UNION
     SELECT 11 UNION SELECT 12 UNION SELECT 13 UNION SELECT 14 UNION SELECT 15 UNION
     SELECT 16 UNION SELECT 17 UNION SELECT 18 UNION SELECT 19 UNION SELECT 20 UNION
     SELECT 21 UNION SELECT 22 UNION SELECT 23 UNION SELECT 24 UNION SELECT 25 UNION
     SELECT 26 UNION SELECT 27) AS temp
LIMIT 27;

-- 6. Insertar 80 socios proveedores
INSERT INTO tbl_socios (nombre, apellido, identificacion, telefono, direccion, vinculacion, fecha_ingreso, estado)
SELECT 
    random_name(1) AS nombre,
    random_name(2) AS apellido,
    random_identification() AS identificacion,
    random_phone() AS telefono,
    CONCAT('Carrera ', FLOOR(RAND() * 100) + 1, ' #', FLOOR(RAND() * 100) + 1, '-', FLOOR(RAND() * 50) + 1) AS direccion,
    'proveedor' AS vinculacion,
    DATE_SUB(CURDATE(), INTERVAL FLOOR(RAND() * 365*5) DAY) AS fecha_ingreso,
    IF(RAND() > 0.15, 'activo', 'inactivo') AS estado
FROM 
    (SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION
     SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9 UNION SELECT 10 UNION
     SELECT 11 UNION SELECT 12 UNION SELECT 13 UNION SELECT 14 UNION SELECT 15 UNION
     SELECT 16 UNION SELECT 17 UNION SELECT 18 UNION SELECT 19 UNION SELECT 20 UNION
     SELECT 21 UNION SELECT 22 UNION SELECT 23 UNION SELECT 24 UNION SELECT 25 UNION
     SELECT 26 UNION SELECT 27 UNION SELECT 28 UNION SELECT 29 UNION SELECT 30 UNION
     SELECT 31 UNION SELECT 32 UNION SELECT 33 UNION SELECT 34 UNION SELECT 35 UNION
     SELECT 36 UNION SELECT 37 UNION SELECT 38 UNION SELECT 39 UNION SELECT 40 UNION
     SELECT 41 UNION SELECT 42 UNION SELECT 43 UNION SELECT 44 UNION SELECT 45 UNION
     SELECT 46 UNION SELECT 47 UNION SELECT 48 UNION SELECT 49 UNION SELECT 50 UNION
     SELECT 51 UNION SELECT 52 UNION SELECT 53 UNION SELECT 54 UNION SELECT 55 UNION
     SELECT 56 UNION SELECT 57 UNION SELECT 58 UNION SELECT 59 UNION SELECT 60 UNION
     SELECT 61 UNION SELECT 62 UNION SELECT 63 UNION SELECT 64 UNION SELECT 65 UNION
     SELECT 66 UNION SELECT 67 UNION SELECT 68 UNION SELECT 69 UNION SELECT 70 UNION
     SELECT 71 UNION SELECT 72 UNION SELECT 73 UNION SELECT 74 UNION SELECT 75 UNION
     SELECT 76 UNION SELECT 77 UNION SELECT 78 UNION SELECT 79 UNION SELECT 80) AS temp
LIMIT 80;

-- 7. Confirmar la transacción
COMMIT;
SET autocommit = 1;

-- 8. Mostrar resultados
SELECT vinculacion, COUNT(*) as cantidad, 
       SUM(CASE WHEN estado = 'activo' THEN 1 ELSE 0 END) as activos
FROM tbl_socios
GROUP BY vinculacion;



-------------------------------------------------------------------------
-------------------------------------------------------------------------

-- Paso 1: Deshabilitar verificación de claves foráneas
SET FOREIGN_KEY_CHECKS = 0;

-- Paso 2: Truncar las tablas en el orden correcto
TRUNCATE TABLE tbl_liquidacion;
TRUNCATE TABLE tbl_produccion;
TRUNCATE TABLE tbl_recoleccion;

-- Paso 3: Volver a habilitar verificación de claves foráneas
SET FOREIGN_KEY_CHECKS = 1;

-- Paso 4: Llena la tabla recoleccion
CALL spInsertIntoRecoleccion();

-- Paso 5: Confirmo estado de liquidación masivo
UPDATE  tbl_liquidacion SET estado = 'liquidacion'