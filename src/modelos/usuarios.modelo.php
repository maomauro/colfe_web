<?php
require_once __DIR__ . "/conexion.php";
class ModeloUsuarios{
    static public function mdlMostrarUsuarios($tabla, $item, $valor){

        $conexion = new Conexion();
        $stmt = $conexion->conectar()->prepare("SELECT * FROM $tabla WHERE $item = :$item");
        $stmt -> bindParam(":".$item, $valor, PDO::PARAM_STR);

        $stmt->execute();
        return $stmt->fetch();
    }

    /*=============================================
    ACTUALIZAR CLAVE (hash) Y REVOCAR TOKENS DE API
    =============================================*/
    static public function mdlActualizarPassword($idUsuario, $hash){
        $pdo = Conexion::conectar();
        $stmt = $pdo->prepare("UPDATE tbl_usuarios SET password = :password WHERE id = :id");
        $stmt->bindValue(":password", $hash, PDO::PARAM_STR);
        $stmt->bindValue(":id", (int)$idUsuario, PDO::PARAM_INT);
        $ok = $stmt->execute();
        $tok = $pdo->prepare("DELETE FROM tbl_api_tokens WHERE id_usuario = :id");
        $tok->bindValue(":id", (int)$idUsuario, PDO::PARAM_INT);
        $tok->execute();
        return $ok;
    }

    /*=============================================
    CREAR USUARIO (la clave llega ya como hash)
    =============================================*/
    static public function mdlCrearUsuario($username, $hash){
        $stmt = Conexion::conectar()->prepare("INSERT INTO tbl_usuarios (username, password) VALUES (:username, :password)");
        $stmt->bindValue(":username", $username, PDO::PARAM_STR);
        $stmt->bindValue(":password", $hash, PDO::PARAM_STR);
        return $stmt->execute();
    }

    /*=============================================
    BLOQUEO POR INTENTOS FALLIDOS
    =============================================*/
    static public function mdlContarFallos($campo, $valor, $minutos){
        if (!in_array($campo, ["username", "ip"], true)) {
            throw new InvalidArgumentException("Campo no permitido");
        }
        $stmt = Conexion::conectar()->prepare(
            "SELECT COUNT(*) FROM tbl_login_intentos
              WHERE $campo = :valor AND creado_en > DATE_SUB(NOW(), INTERVAL :min MINUTE)"
        );
        $stmt->bindValue(":valor", $valor, PDO::PARAM_STR);
        $stmt->bindValue(":min", (int)$minutos, PDO::PARAM_INT);
        $stmt->execute();
        return (int)$stmt->fetchColumn();
    }

    static public function mdlRegistrarFallo($username, $ip){
        $pdo = Conexion::conectar();
        $pdo->exec("DELETE FROM tbl_login_intentos WHERE creado_en < DATE_SUB(NOW(), INTERVAL 1 DAY)");
        $stmt = $pdo->prepare("INSERT INTO tbl_login_intentos (username, ip) VALUES (:username, :ip)");
        $stmt->bindValue(":username", substr($username, 0, 50), PDO::PARAM_STR);
        $stmt->bindValue(":ip", substr($ip, 0, 45), PDO::PARAM_STR);
        return $stmt->execute();
    }

    static public function mdlLimpiarFallos($username){
        $stmt = Conexion::conectar()->prepare("DELETE FROM tbl_login_intentos WHERE username = :username");
        $stmt->bindValue(":username", $username, PDO::PARAM_STR);
        return $stmt->execute();
    }
}
