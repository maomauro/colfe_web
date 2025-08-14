<?php
/**
 * Script para descargar y localizar dependencias externas
 * Ejecutar este script para descargar todas las fuentes externas
 */

class ExternalLibsDownloader {
    
    private $basePath = 'vistas/libs/external/';
    private $externalLibs = [
        // Google Fonts - Source Sans Pro
        'css' => [
            'google-fonts-source-sans-pro.css' => 'https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700,300italic,400italic,600italic'
        ],
        
        // DataTables CSS
        'css' => [
            'datatables.min.css' => 'https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css',
            'datatables-buttons.min.css' => 'https://cdn.datatables.net/buttons/1.7.1/css/buttons.dataTables.min.css',
            'jquery-ui.min.css' => 'https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css'
        ],
        
        // JavaScript Libraries
        'js' => [
            'jquery-3.6.0.min.js' => 'https://code.jquery.com/jquery-3.6.0.min.js',
            'datatables.min.js' => 'https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js',
            'datatables-buttons.min.js' => 'https://cdn.datatables.net/buttons/1.7.1/js/dataTables.buttons.min.js',
            'datatables-buttons-html5.min.js' => 'https://cdn.datatables.net/buttons/1.7.1/js/buttons.html5.min.js',
            'datatables-buttons-print.min.js' => 'https://cdn.datatables.net/buttons/1.7.1/js/buttons.print.min.js',
            'jszip.min.js' => 'https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js',
            'pdfmake.min.js' => 'https://cdn.jsdelivr.net/npm/pdfmake@0.1.36/build/pdfmake.min.js',
            'pdfmake-vfs-fonts.js' => 'https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.36/vfs_fonts.js',
            'core.js' => 'https://cdnjs.cloudflare.com/ajax/libs/core-js/2.4.1/core.js',
            'fullcalendar-locale-es.js' => 'https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.10.2/locale/es.js',
            'jquery-ui.min.js' => 'https://code.jquery.com/ui/1.13.2/jquery-ui.min.js'
        ]
    ];
    
    public function downloadAll() {
        echo "📥 INICIANDO DESCARGA DE DEPENDENCIAS EXTERNAS\n";
        echo "==============================================\n\n";
        
        $this->createDirectories();
        $this->downloadCSSFiles();
        $this->downloadJSFiles();
        $this->createGoogleFontsLocal();
        
        echo "\n✅ DESCARGA COMPLETADA\n";
        echo "Ahora puedes actualizar plantilla.php para usar las fuentes locales.\n";
    }
    
    private function createDirectories() {
        $dirs = [
            $this->basePath . 'css',
            $this->basePath . 'js',
            $this->basePath . 'fonts'
        ];
        
        foreach ($dirs as $dir) {
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
                echo "📁 Directorio creado: $dir\n";
            }
        }
    }
    
    private function downloadCSSFiles() {
        echo "\n📄 Descargando archivos CSS...\n";
        
        $cssFiles = [
            'datatables.min.css' => 'https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css',
            'datatables-buttons.min.css' => 'https://cdn.datatables.net/buttons/1.7.1/css/buttons.dataTables.min.css',
            'jquery-ui.min.css' => 'https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css'
        ];
        
        foreach ($cssFiles as $filename => $url) {
            $filepath = $this->basePath . 'css/' . $filename;
            if ($this->downloadFile($url, $filepath)) {
                echo "✅ CSS descargado: $filename\n";
            } else {
                echo "❌ Error descargando: $filename\n";
            }
        }
    }
    
    private function downloadJSFiles() {
        echo "\n📜 Descargando archivos JavaScript...\n";
        
        $jsFiles = [
            'jquery-3.6.0.min.js' => 'https://code.jquery.com/jquery-3.6.0.min.js',
            'datatables.min.js' => 'https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js',
            'datatables-buttons.min.js' => 'https://cdn.datatables.net/buttons/1.7.1/js/dataTables.buttons.min.js',
            'datatables-buttons-html5.min.js' => 'https://cdn.datatables.net/buttons/1.7.1/js/buttons.html5.min.js',
            'datatables-buttons-print.min.js' => 'https://cdn.datatables.net/buttons/1.7.1/js/buttons.print.min.js',
            'jszip.min.js' => 'https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js',
            'pdfmake.min.js' => 'https://cdn.jsdelivr.net/npm/pdfmake@0.1.36/build/pdfmake.min.js',
            'pdfmake-vfs-fonts.js' => 'https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.36/vfs_fonts.js',
            'core.js' => 'https://cdnjs.cloudflare.com/ajax/libs/core-js/2.4.1/core.js',
            'fullcalendar-locale-es.js' => 'https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.10.2/locale/es.js',
            'jquery-ui.min.js' => 'https://code.jquery.com/ui/1.13.2/jquery-ui.min.js'
        ];
        
        foreach ($jsFiles as $filename => $url) {
            $filepath = $this->basePath . 'js/' . $filename;
            if ($this->downloadFile($url, $filepath)) {
                echo "✅ JS descargado: $filename\n";
            } else {
                echo "❌ Error descargando: $filename\n";
            }
        }
    }
    
    private function createGoogleFontsLocal() {
        echo "\n🔤 Creando Google Fonts local...\n";
        
        // Crear archivo CSS local para Google Fonts
        $googleFontsCSS = $this->basePath . 'css/google-fonts-local.css';
        $fontContent = '
/* Google Fonts - Source Sans Pro (Local) */
@font-face {
    font-family: "Source Sans Pro";
    font-style: normal;
    font-weight: 300;
    src: local("Source Sans Pro Light"), local("SourceSansPro-Light"), url("../fonts/SourceSansPro-Light.woff2") format("woff2");
}

@font-face {
    font-family: "Source Sans Pro";
    font-style: normal;
    font-weight: 400;
    src: local("Source Sans Pro"), local("SourceSansPro-Regular"), url("../fonts/SourceSansPro-Regular.woff2") format("woff2");
}

@font-face {
    font-family: "Source Sans Pro";
    font-style: normal;
    font-weight: 600;
    src: local("Source Sans Pro Semibold"), local("SourceSansPro-Semibold"), url("../fonts/SourceSansPro-Semibold.woff2") format("woff2");
}

@font-face {
    font-family: "Source Sans Pro";
    font-style: normal;
    font-weight: 700;
    src: local("Source Sans Pro Bold"), local("SourceSansPro-Bold"), url("../fonts/SourceSansPro-Bold.woff2") format("woff2");
}

@font-face {
    font-family: "Source Sans Pro";
    font-style: italic;
    font-weight: 300;
    src: local("Source Sans Pro Light Italic"), local("SourceSansPro-LightItalic"), url("../fonts/SourceSansPro-LightItalic.woff2") format("woff2");
}

@font-face {
    font-family: "Source Sans Pro";
    font-style: italic;
    font-weight: 400;
    src: local("Source Sans Pro Italic"), local("SourceSansPro-Italic"), url("../fonts/SourceSansPro-Italic.woff2") format("woff2");
}

@font-face {
    font-family: "Source Sans Pro";
    font-style: italic;
    font-weight: 600;
    src: local("Source Sans Pro Semibold Italic"), local("SourceSansPro-SemiboldItalic"), url("../fonts/SourceSansPro-SemiboldItalic.woff2") format("woff2");
}
';
        
        if (file_put_contents($googleFontsCSS, $fontContent)) {
            echo "✅ Google Fonts CSS local creado\n";
        } else {
            echo "❌ Error creando Google Fonts CSS local\n";
        }
        
        // Crear archivo de instrucciones para descargar fuentes
        $instructionsFile = $this->basePath . 'fonts/README.md';
        $instructions = '
# Fuentes Google Fonts - Source Sans Pro

Para completar la localización de Google Fonts, descarga manualmente los archivos de fuentes desde:

https://fonts.google.com/specimen/Source+Sans+Pro

## Archivos necesarios:
- SourceSansPro-Light.woff2
- SourceSansPro-Regular.woff2
- SourceSansPro-Semibold.woff2
- SourceSansPro-Bold.woff2
- SourceSansPro-LightItalic.woff2
- SourceSansPro-Italic.woff2
- SourceSansPro-SemiboldItalic.woff2

## Instrucciones:
1. Ve a https://fonts.google.com/specimen/Source+Sans+Pro
2. Haz clic en "Download family"
3. Extrae los archivos .woff2 a esta carpeta
4. Asegúrate de que los nombres coincidan con los referenciados en google-fonts-local.css
';
        
        if (file_put_contents($instructionsFile, $instructions)) {
            echo "✅ Instrucciones de fuentes creadas\n";
        }
    }
    
    private function downloadFile($url, $filepath) {
        $context = stream_context_create([
            'http' => [
                'timeout' => 30,
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
            ]
        ]);
        
        $content = @file_get_contents($url, false, $context);
        
        if ($content !== false) {
            return file_put_contents($filepath, $content) !== false;
        }
        
        return false;
    }
}

// Ejecutar descarga
if (php_sapi_name() === 'cli') {
    $downloader = new ExternalLibsDownloader();
    $downloader->downloadAll();
} else {
    echo "Este script debe ejecutarse desde la línea de comandos.";
}
