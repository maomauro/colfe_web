<?php
require_once __DIR__ . '/../config.php';

class Conexion{
    static public function conectar(){
        try {
            // Usar las constantes definidas en config.php
            $host = DB_HOST;
            $dbname = DB_NAME;
            $username = DB_USER;
            $password = DB_PASS;
            
            $link = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8",
                            $username,
                            $password,
                            array(
                                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                                PDO::ATTR_EMULATE_PREPARES => false
                            ));
            
            return $link;
        } catch (PDOException $e) {
            // Log del error (en producción, no mostrar detalles)
            error_log("Error de conexión: " . $e->getMessage());
            throw new Exception("Error de conexión a la base de datos");
        }
    }
}