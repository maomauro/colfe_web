<?php
/**
 * Script de validación de seguridad para COLFE_WEB
 * Ejecutar este script para verificar la configuración de seguridad
 */

require_once 'config.php';

class SecurityChecker {
    
    private $issues = [];
    private $warnings = [];
    private $success = [];
    
    public function runChecks() {
        echo "🔒 INICIANDO VALIDACIÓN DE SEGURIDAD\n";
        echo "=====================================\n\n";
        
        $this->checkDatabaseSecurity();
        $this->checkFilePermissions();
        $this->checkSessionSecurity();
        $this->checkErrorReporting();
        $this->checkDirectoryListing();
        $this->checkSensitiveFiles();
        $this->checkDependencies();
        
        $this->generateReport();
    }
    
    private function checkDatabaseSecurity() {
        echo "📊 Verificando seguridad de base de datos...\n";
        
        // Verificar si las credenciales están hardcodeadas
        if (DB_USER === 'desarrollo' && DB_PASS === 'desarrollo') {
            $this->warnings[] = "Credenciales de BD por defecto detectadas. Considerar usar variables de entorno.";
        }
        
        // Verificar conexión
        try {
            $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
            $this->success[] = "Conexión a base de datos exitosa";
        } catch (Exception $e) {
            $this->issues[] = "Error de conexión a BD: " . $e->getMessage();
        }
    }
    
    private function checkFilePermissions() {
        echo "📁 Verificando permisos de archivos...\n";
        
        $sensitiveDirs = ['logs', 'uploads', 'ajax'];
        
        foreach ($sensitiveDirs as $dir) {
            if (is_dir($dir)) {
                $perms = fileperms($dir);
                if (($perms & 0x0177) !== 0) {
                    $this->warnings[] = "Directorio $dir tiene permisos muy abiertos";
                } else {
                    $this->success[] = "Permisos de $dir correctos";
                }
            }
        }
    }
    
    private function checkSessionSecurity() {
        echo "🔐 Verificando configuración de sesiones...\n";
        
        if (ini_get('session.cookie_httponly')) {
            $this->success[] = "HttpOnly cookies habilitado";
        } else {
            $this->issues[] = "HttpOnly cookies no habilitado";
        }
        
        if (ini_get('session.use_only_cookies')) {
            $this->success[] = "Solo cookies habilitado";
        } else {
            $this->issues[] = "Solo cookies no habilitado";
        }
    }
    
    private function checkErrorReporting() {
        echo "🐛 Verificando configuración de errores...\n";
        
        if (isProduction()) {
            if (error_reporting() === 0) {
                $this->success[] = "Error reporting deshabilitado en producción";
            } else {
                $this->issues[] = "Error reporting habilitado en producción";
            }
        } else {
            $this->success[] = "Error reporting configurado para desarrollo";
        }
    }
    
    private function checkDirectoryListing() {
        echo "📂 Verificando listado de directorios...\n";
        
        $htaccess = file_get_contents('.htaccess');
        if (strpos($htaccess, 'Options All -Indexes') !== false) {
            $this->success[] = "Listado de directorios deshabilitado";
        } else {
            $this->warnings[] = "Listado de directorios podría estar habilitado";
        }
    }
    
    private function checkSensitiveFiles() {
        echo "🔍 Verificando archivos sensibles...\n";
        
        $sensitiveFiles = ['.env', 'config.php', 'db/colfe_db.sql'];
        
        foreach ($sensitiveFiles as $file) {
            if (file_exists($file)) {
                if (is_readable($file)) {
                    $this->warnings[] = "Archivo $file es legible públicamente";
                } else {
                    $this->success[] = "Archivo $file protegido";
                }
            }
        }
    }
    
    private function checkDependencies() {
        echo "📦 Verificando dependencias...\n";
        
        // Verificar versión de PHP
        if (version_compare(PHP_VERSION, '7.4.0', '>=')) {
            $this->success[] = "PHP " . PHP_VERSION . " compatible";
        } else {
            $this->issues[] = "PHP " . PHP_VERSION . " no es compatible (requiere 7.4+)";
        }
        
        // Verificar extensiones necesarias
        $requiredExtensions = ['pdo', 'pdo_mysql', 'json', 'mbstring'];
        
        foreach ($requiredExtensions as $ext) {
            if (extension_loaded($ext)) {
                $this->success[] = "Extensión $ext disponible";
            } else {
                $this->issues[] = "Extensión $ext no disponible";
            }
        }
    }
    
    private function generateReport() {
        echo "\n📋 REPORTE DE SEGURIDAD\n";
        echo "=======================\n\n";
        
        if (!empty($this->success)) {
            echo "✅ ÉXITOS:\n";
            foreach ($this->success as $success) {
                echo "  - $success\n";
            }
            echo "\n";
        }
        
        if (!empty($this->warnings)) {
            echo "⚠️  ADVERTENCIAS:\n";
            foreach ($this->warnings as $warning) {
                echo "  - $warning\n";
            }
            echo "\n";
        }
        
        if (!empty($this->issues)) {
            echo "❌ PROBLEMAS CRÍTICOS:\n";
            foreach ($this->issues as $issue) {
                echo "  - $issue\n";
            }
            echo "\n";
        }
        
        $totalChecks = count($this->success) + count($this->warnings) + count($this->issues);
        $successRate = ($totalChecks > 0) ? round((count($this->success) / $totalChecks) * 100, 1) : 0;
        
        echo "📊 RESUMEN:\n";
        echo "  - Total de verificaciones: $totalChecks\n";
        echo "  - Éxitos: " . count($this->success) . "\n";
        echo "  - Advertencias: " . count($this->warnings) . "\n";
        echo "  - Problemas críticos: " . count($this->issues) . "\n";
        echo "  - Tasa de éxito: $successRate%\n\n";
        
        if (count($this->issues) > 0) {
            echo "🚨 RECOMENDACIONES:\n";
            echo "  - Resolver los problemas críticos antes de desplegar en producción\n";
            echo "  - Revisar las advertencias para mejorar la seguridad\n";
            echo "  - Considerar implementar HTTPS en producción\n";
            echo "  - Configurar un firewall de aplicación\n";
        } else {
            echo "🎉 ¡Excelente! El sistema parece estar configurado correctamente.\n";
        }
    }
}

// Ejecutar validación
if (php_sapi_name() === 'cli') {
    $checker = new SecurityChecker();
    $checker->runChecks();
} else {
    echo "Este script debe ejecutarse desde la línea de comandos.";
}
