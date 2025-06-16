<?php
class Conexion{
    static public function conectar(){
        $link = new PDO("mysql:host=localhost;dbname=colfe_db",
                        "desarrollo",
                        "desarrollo");
        $link->exec("set names utf8");
        return $link;
    }
}