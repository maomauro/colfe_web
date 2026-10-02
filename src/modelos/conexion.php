<?php
require_once __DIR__ . '/../../config/config.php';

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
            
            // Quién hace los cambios: lo leen los triggers de auditoría (db/migraciones/004_auditoria.sql)
            $usuario = null;
            $origen = 'sistema';
            if (!empty($GLOBALS['colfe_contexto']['usuario'])) {          // API móvil (guardToken)
                $usuario = (int)$GLOBALS['colfe_contexto']['usuario'];
                $origen = isset($GLOBALS['colfe_contexto']['origen']) ? $GLOBALS['colfe_contexto']['origen'] : 'api';
            } elseif (!empty($_SESSION['id_usuario'])) {                  // interfaz web
                $usuario = (int)$_SESSION['id_usuario'];
                $origen = 'web';
            }
            $link->exec("SET @colfe_usuario = " . ($usuario === null ? "NULL" : $usuario)
                      . ", @colfe_origen = " . $link->quote($origen));

            return $link;
        } catch (PDOException $e) {
            // Log del error (en producción, no mostrar detalles)
            error_log("Error de conexión: " . $e->getMessage());
            throw new Exception("Error de conexión a la base de datos");
        }
    }
}