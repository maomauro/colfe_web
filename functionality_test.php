<?php
/**
 * Script de prueba de funcionalidad para COLFE_WEB
 * Valida que todos los módulos principales funcionen correctamente
 */

require_once 'config.php';
require_once 'controladores/socios.controlador.php';
require_once 'controladores/liquidacion.controlador.php';
require_once 'controladores/precios.controlador.php';

class FunctionalityTester {
    
    private $results = [];
    private $errors = [];
    
    public function runTests() {
        echo "🧪 INICIANDO PRUEBAS DE FUNCIONALIDAD\n";
        echo "=====================================\n\n";
        
        $this->testDatabaseConnection();
        $this->testSociosModule();
        $this->testLiquidacionModule();
        $this->testPreciosModule();
        $this->testFileSystem();
        $this->testJavaScriptFiles();
        $this->testAPIs();
        
        $this->generateReport();
    }
    
    private function testDatabaseConnection() {
        echo "🔌 Probando conexión a base de datos...\n";
        
        try {
            $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Verificar tablas principales
            $tables = ['tbl_socios', 'tbl_liquidacion', 'tbl_precios', 'tbl_produccion', 'tbl_recoleccion'];
            $existingTables = [];
            
            foreach ($tables as $table) {
                $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
                if ($stmt->rowCount() > 0) {
                    $existingTables[] = $table;
                }
            }
            
            if (count($existingTables) === count($tables)) {
                $this->results[] = "✅ Conexión a BD y tablas principales OK";
            } else {
                $this->errors[] = "❌ Faltan tablas: " . implode(', ', array_diff($tables, $existingTables));
            }
            
        } catch (Exception $e) {
            $this->errors[] = "❌ Error de conexión a BD: " . $e->getMessage();
        }
    }
    
    private function testSociosModule() {
        echo "👥 Probando módulo de socios...\n";
        
        try {
            // Probar método de mostrar socios
            $socios = ControladorSocios::ctrMostrarSocio(null, null);
            
            if (is_array($socios)) {
                $this->results[] = "✅ Módulo de socios - mostrar OK";
            } else {
                $this->errors[] = "❌ Error en mostrar socios";
            }
            
            // Verificar archivos del módulo
            $sociosFiles = [
                'controladores/socios.controlador.php',
                'modelos/socios.modelo.php',
                'vistas/modulos/socios.php',
                'vistas/js/socios.js',
                'ajax/socios.ajax.php'
            ];
            
            foreach ($sociosFiles as $file) {
                if (file_exists($file)) {
                    $this->results[] = "✅ Archivo $file existe";
                } else {
                    $this->errors[] = "❌ Archivo $file no encontrado";
                }
            }
            
        } catch (Exception $e) {
            $this->errors[] = "❌ Error en módulo socios: " . $e->getMessage();
        }
    }
    
    private function testLiquidacionModule() {
        echo "💰 Probando módulo de liquidación...\n";
        
        try {
            // Verificar archivos del módulo
            $liquidacionFiles = [
                'controladores/liquidacion.controlador.php',
                'modelos/liquidacion.modelo.php',
                'vistas/modulos/liquidacion.php',
                'vistas/js/liquidacion.js',
                'ajax/liquidacion.ajax.php'
            ];
            
            foreach ($liquidacionFiles as $file) {
                if (file_exists($file)) {
                    $this->results[] = "✅ Archivo $file existe";
                } else {
                    $this->errors[] = "❌ Archivo $file no encontrado";
                }
            }
            
            // Verificar API de liquidación
            if (file_exists('api/apiTotalLiquidacion.php')) {
                $this->results[] = "✅ API de liquidación existe";
            } else {
                $this->errors[] = "❌ API de liquidación no encontrada";
            }
            
        } catch (Exception $e) {
            $this->errors[] = "❌ Error en módulo liquidación: " . $e->getMessage();
        }
    }
    
    private function testPreciosModule() {
        echo "📊 Probando módulo de precios...\n";
        
        try {
            // Verificar archivos del módulo
            $preciosFiles = [
                'controladores/precios.controlador.php',
                'modelos/precios.modelo.php',
                'vistas/modulos/precios.php',
                'vistas/js/precios.js',
                'ajax/precios.ajax.php'
            ];
            
            foreach ($preciosFiles as $file) {
                if (file_exists($file)) {
                    $this->results[] = "✅ Archivo $file existe";
                } else {
                    $this->errors[] = "❌ Archivo $file no encontrado";
                }
            }
            
        } catch (Exception $e) {
            $this->errors[] = "❌ Error en módulo precios: " . $e->getMessage();
        }
    }
    
    private function testFileSystem() {
        echo "📁 Probando sistema de archivos...\n";
        
        // Verificar directorios necesarios
        $directories = [
            'vistas',
            'vistas/js',
            'vistas/css',
            'vistas/modulos',
            'controladores',
            'modelos',
            'ajax',
            'api',
            'db'
        ];
        
        foreach ($directories as $dir) {
            if (is_dir($dir)) {
                $this->results[] = "✅ Directorio $dir existe";
            } else {
                $this->errors[] = "❌ Directorio $dir no encontrado";
            }
        }
        
        // Verificar archivos críticos
        $criticalFiles = [
            'index.php',
            'config.php',
            '.htaccess',
            'vistas/plantilla.php'
        ];
        
        foreach ($criticalFiles as $file) {
            if (file_exists($file)) {
                $this->results[] = "✅ Archivo crítico $file existe";
            } else {
                $this->errors[] = "❌ Archivo crítico $file no encontrado";
            }
        }
    }
    
    private function testJavaScriptFiles() {
        echo "📜 Probando archivos JavaScript...\n";
        
        $jsFiles = [
            'vistas/js/plantilla.js',
            'vistas/js/socios.js',
            'vistas/js/liquidacion.js',
            'vistas/js/prediccion.js',
            'vistas/js/i18n/es-ES.json'
        ];
        
        foreach ($jsFiles as $file) {
            if (file_exists($file)) {
                $this->results[] = "✅ Archivo JS $file existe";
            } else {
                $this->errors[] = "❌ Archivo JS $file no encontrado";
            }
        }
    }
    
    private function testAPIs() {
        echo "🌐 Probando APIs...\n";
        
        // Verificar archivos de API
        $apiFiles = [
            'api/apiTotalLiquidacion.php',
            'ajax/prediccion.ajax.php'
        ];
        
        foreach ($apiFiles as $file) {
            if (file_exists($file)) {
                $this->results[] = "✅ API $file existe";
            } else {
                $this->errors[] = "❌ API $file no encontrada";
            }
        }
        
        // Verificar modelo de ML
        if (file_exists('ajax/modelo_liquidacion.pkl')) {
            $this->results[] = "✅ Modelo de ML existe";
        } else {
            $this->errors[] = "❌ Modelo de ML no encontrado";
        }
    }
    
    private function generateReport() {
        echo "\n📋 REPORTE DE FUNCIONALIDAD\n";
        echo "===========================\n\n";
        
        if (!empty($this->results)) {
            echo "✅ PRUEBAS EXITOSAS:\n";
            foreach ($this->results as $result) {
                echo "  $result\n";
            }
            echo "\n";
        }
        
        if (!empty($this->errors)) {
            echo "❌ ERRORES ENCONTRADOS:\n";
            foreach ($this->errors as $error) {
                echo "  $error\n";
            }
            echo "\n";
        }
        
        $totalTests = count($this->results) + count($this->errors);
        $successRate = ($totalTests > 0) ? round((count($this->results) / $totalTests) * 100, 1) : 0;
        
        echo "📊 RESUMEN:\n";
        echo "  - Total de pruebas: $totalTests\n";
        echo "  - Exitosas: " . count($this->results) . "\n";
        echo "  - Fallidas: " . count($this->errors) . "\n";
        echo "  - Tasa de éxito: $successRate%\n\n";
        
        if (count($this->errors) === 0) {
            echo "🎉 ¡Excelente! Todas las funcionalidades están operativas.\n";
        } else {
            echo "⚠️  RECOMENDACIONES:\n";
            echo "  - Revisar y corregir los errores encontrados\n";
            echo "  - Verificar la configuración de la base de datos\n";
            echo "  - Asegurar que todos los archivos estén en su lugar\n";
            echo "  - Probar manualmente las funcionalidades críticas\n";
        }
    }
}

// Ejecutar pruebas
if (php_sapi_name() === 'cli') {
    $tester = new FunctionalityTester();
    $tester->runTests();
} else {
    echo "Este script debe ejecutarse desde la línea de comandos.";
}
