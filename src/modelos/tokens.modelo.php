<?php
require_once __DIR__ . "/conexion.php";

class ModeloTokens
{
    /*=============================================
    CREAR TOKEN (se guarda solo el hash)
    =============================================*/
    static public function mdlCrearToken($idUsuario, $token, $ttlSegundos)
    {
        $pdo = Conexion::conectar();

        // Limpieza oportunista de tokens vencidos
        $pdo->exec("DELETE FROM tbl_api_tokens WHERE expira_en < NOW()");

        $stmt = $pdo->prepare(
            "INSERT INTO tbl_api_tokens (id_usuario, token_hash, expira_en)
             VALUES (:id_usuario, :hash, DATE_ADD(NOW(), INTERVAL :ttl SECOND))"
        );
        $stmt->bindValue(":id_usuario", (int)$idUsuario, PDO::PARAM_INT);
        $stmt->bindValue(":hash", hash('sha256', $token), PDO::PARAM_STR);
        $stmt->bindValue(":ttl", (int)$ttlSegundos, PDO::PARAM_INT);
        return $stmt->execute();
    }

    /*=============================================
    VALIDAR TOKEN: devuelve id_usuario/username/expira_en o false
    =============================================*/
    static public function mdlValidarToken($token)
    {
        $pdo = Conexion::conectar();
        $stmt = $pdo->prepare(
            "SELECT t.id_token, t.id_usuario, u.username, t.expira_en
               FROM tbl_api_tokens t
               JOIN tbl_usuarios u ON u.id = t.id_usuario
              WHERE t.token_hash = :hash AND t.expira_en > NOW()
              LIMIT 1"
        );
        $stmt->bindValue(":hash", hash('sha256', $token), PDO::PARAM_STR);
        $stmt->execute();
        $fila = $stmt->fetch();

        if ($fila) {
            $upd = $pdo->prepare("UPDATE tbl_api_tokens SET ultimo_uso = NOW() WHERE id_token = :id");
            $upd->bindValue(":id", (int)$fila["id_token"], PDO::PARAM_INT);
            $upd->execute();
        }
        return $fila;
    }

    /*=============================================
    REVOCAR TOKEN
    =============================================*/
    static public function mdlRevocarToken($token)
    {
        $stmt = Conexion::conectar()->prepare("DELETE FROM tbl_api_tokens WHERE token_hash = :hash");
        $stmt->bindValue(":hash", hash('sha256', $token), PDO::PARAM_STR);
        return $stmt->execute();
    }
}
