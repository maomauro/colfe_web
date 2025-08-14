<?php
/**
 * Script de verificación de localización de dependencias
 * Verifica que todas las dependencias externas hayan sido localizadas correctamente
 */

class LocalizationVerifier {
    
    private $basePath = 'vistas/libs/external/';
    private $results = [];
    private $errors = [];
    private $warnings = [];
    
    public function verifyAll() {
        echo "🔍 VERIFICANDO LOCALIZACIÓN DE DEPENDENCIAS\n";
        echo "==========================================\n\n";
        
        $this->verifyCSSFiles();
        $this->verifyJSFiles();
        $this->verifyFonts();
        $this->verifyPlantillaReferences();
        
        $this->generateReport();
    }
    
    private function verifyCSSFiles() {
        echo "📄 Verificando archivos CSS...\n";
        
        $cssFiles = [
            'google-fonts-local.css',
            'datatables.min.css',
            'datatables-buttons.min.css',
            'jquery-ui.min.css'
        ];
        
        foreach ($cssFiles as $file) {
            $filepath = $this->basePath . 'css/' . $file;
            if (file_exists($filepath)) {
                $size = filesize($filepath);
                if ($size > 0) {
                    $this->results[] = "✅ CSS: $file ($size bytes)";
                } else {
                    $this->errors[] = "❌ CSS: $file está vacío";
                }
            } else {
                $this->errors[] = "❌ CSS: $file no encontrado";
            }
        }
    }
    
    private function verifyJSFiles() {
        echo "📜 Verificando archivos JavaScript...\n";
        
        $jsFiles = [
            'jquery-3.6.0.min.js',
            'datatables.min.js',
            'datatables-buttons.min.js',
            'datatables-buttons-html5.min.js',
            'datatables-buttons-print.min.js',
            'jszip.min.js',
            'pdfmake.min.js',
            'pdfmake-vfs-fonts.js',
            'core.js',
            'fullcalendar-locale-es.js',
            'jquery-ui.min.js'
        ];
        
        foreach ($jsFiles as $file) {
            $filepath = $this->basePath . 'js/' . $file;
            if (file_exists($filepath)) {
                $size = filesize($filepath);
                if ($size > 0) {
                    $this->results[] = "✅ JS: $file ($size bytes)";
                } else {
                    $this->errors[] = "❌ JS: $file está vacío";
                }
            } else {
                $this->errors[] = "❌ JS: $file no encontrado";
            }
        }
    }
    
    private function verifyFonts() {
        echo "🔤 Verificando fuentes...\n";
        
        $fontFiles = [
            'SourceSansPro-Light.woff2',
            'SourceSansPro-Regular.woff2',
            'SourceSansPro-Semibold.woff2',
            'SourceSansPro-Bold.woff2',
            'SourceSansPro-LightItalic.woff2',
            'SourceSansPro-Italic.woff2',
            'SourceSansPro-SemiboldItalic.woff2'
        ];
        
        $fontsFound = 0;
        foreach ($fontFiles as $file) {
            $filepath = $this->basePath . 'fonts/' . $file;
            if (file_exists($filepath)) {
                $size = filesize($filepath);
                if ($size > 0) {
                    $this->results[] = "✅ Font: $file ($size bytes)";
                    $fontsFound++;
                } else {
                    $this->errors[] = "❌ Font: $file está vacío";
                }
            } else {
                $this->warnings[] = "⚠️ Font: $file no encontrado (descarga manual requerida)";
            }
        }
        
        if ($fontsFound === 0) {
            $this->warnings[] = "⚠️ No se encontraron archivos de fuentes. Revisa vistas/libs/external/fonts/README.md para instrucciones.";
        }
    }
    
    private function verifyPlantillaReferences() {
        echo "🔗 Verificando referencias en plantilla.php...\n";
        
        $plantillaContent = file_get_contents('vistas/plantilla.php');
        
        // Verificar que no hay referencias externas
        $externalPatterns = [
            'https://fonts.googleapis.com',
            'https://cdn.datatables.net',
            'https://cdnjs.cloudflare.com',
            'https://code.jquery.com',
            'https://cdn.jsdelivr.net'
        ];
        
        foreach ($externalPatterns as $pattern) {
            if (strpos($plantillaContent, $pattern) !== false) {
                $this->warnings[] = "⚠️ Referencia externa encontrada: $pattern";
            } else {
                $this->results[] = "✅ Sin referencias externas: $pattern";
            }
        }
        
        // Verificar referencias locales
        $localPatterns = [
            'vistas/libs/external/css/',
            'vistas/libs/external/js/'
        ];
        
        foreach ($localPatterns as $pattern) {
            if (strpos($plantillaContent, $pattern) !== false) {
                $this->results[] = "✅ Referencia local encontrada: $pattern";
            } else {
                $this->errors[] = "❌ Referencia local no encontrada: $pattern";
            }
        }
    }
    
    private function generateReport() {
        echo "\n📋 REPORTE DE LOCALIZACIÓN\n";
        echo "=========================\n\n";
        
        if (!empty($this->results)) {
            echo "✅ VERIFICACIONES EXITOSAS:\n";
            foreach ($this->results as $result) {
                echo "  $result\n";
            }
            echo "\n";
        }
        
        if (!empty($this->warnings)) {
            echo "⚠️  ADVERTENCIAS:\n";
            foreach ($this->warnings as $warning) {
                echo "  $warning\n";
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
        
        $totalChecks = count($this->results) + count($this->warnings) + count($this->errors);
        $successRate = ($totalChecks > 0) ? round((count($this->results) / $totalChecks) * 100, 1) : 0;
        
        echo "📊 RESUMEN:\n";
        echo "  - Total de verificaciones: $totalChecks\n";
        echo "  - Exitosas: " . count($this->results) . "\n";
        echo "  - Advertencias: " . count($this->warnings) . "\n";
        echo "  - Errores: " . count($this->errors) . "\n";
        echo "  - Tasa de éxito: $successRate%\n\n";
        
        if (count($this->errors) === 0) {
            echo "🎉 ¡Excelente! La localización se ha completado correctamente.\n";
            if (count($this->warnings) > 0) {
                echo "⚠️  Nota: Algunas advertencias requieren atención (principalmente fuentes).\n";
            }
        } else {
            echo "🚨 RECOMENDACIONES:\n";
            echo "  - Revisar y corregir los errores encontrados\n";
            echo "  - Verificar que todos los archivos se descargaron correctamente\n";
            echo "  - Comprobar las referencias en plantilla.php\n";
        }
        
        echo "\n📝 PRÓXIMOS PASOS:\n";
        echo "  1. Descargar manualmente las fuentes Google Fonts si no están presentes\n";
        echo "  2. Probar todas las funcionalidades del sistema\n";
        echo "  3. Verificar que DataTables y jQuery UI funcionan correctamente\n";
        echo "  4. Actualizar la documentación si es necesario\n";
    }
}

// Ejecutar verificación
if (php_sapi_name() === 'cli') {
    $verifier = new LocalizationVerifier();
    $verifier->verifyAll();
} else {
    echo "Este script debe ejecutarse desde la línea de comandos.";
}
