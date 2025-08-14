<?php
/**
 * Script para descargar Google Fonts Source Sans Pro
 * Descarga las fuentes directamente desde las URLs de Google Fonts
 */

class GoogleFontsDownloader {
    
    private $basePath = 'vistas/libs/external/fonts/';
    private $fontFiles = [
        'SourceSansPro-Light.ttf' => 'https://fonts.gstatic.com/s/sourcesanspro/v22/6xKydSBYKcSV-LCoeQqfX1RYOo3ik4zwlxdr.ttf',
        'SourceSansPro-Regular.ttf' => 'https://fonts.gstatic.com/s/sourcesanspro/v22/6xK3dSBYKcSV-LCoeQqfX1RYOo3qOK7g.ttf',
        'SourceSansPro-Semibold.ttf' => 'https://fonts.gstatic.com/s/sourcesanspro/v22/6xKydSBYKcSV-LCoeQqfX1RYOo3i54rwlxdr.ttf',
        'SourceSansPro-Bold.ttf' => 'https://fonts.gstatic.com/s/sourcesanspro/v22/6xKydSBYKcSV-LCoeQqfX1RYOo3ig4vwlxdr.ttf',
        'SourceSansPro-LightItalic.ttf' => 'https://fonts.gstatic.com/s/sourcesanspro/v22/6xKwdSBYKcSV-LCoeQqfX1RYOo3qPZZMkids18E.ttf',
        'SourceSansPro-Italic.ttf' => 'https://fonts.gstatic.com/s/sourcesanspro/v22/6xK1dSBYKcSV-LCoeQqfX1RYOo3qPZ7nsDc.ttf',
        'SourceSansPro-SemiboldItalic.ttf' => 'https://fonts.gstatic.com/s/sourcesanspro/v22/6xKwdSBYKcSV-LCoeQqfX1RYOo3qPZY4lCds18E.ttf'
    ];
    
    public function downloadFonts() {
        echo "🔤 DESCARGANDO FUENTES GOOGLE FONTS\n";
        echo "===================================\n\n";
        
        if (!is_dir($this->basePath)) {
            mkdir($this->basePath, 0755, true);
            echo "📁 Directorio de fuentes creado: $this->basePath\n";
        }
        
        $successCount = 0;
        $totalCount = count($this->fontFiles);
        
        foreach ($this->fontFiles as $filename => $url) {
            $filepath = $this->basePath . $filename;
            
            echo "📥 Descargando: $filename... ";
            
            if ($this->downloadFile($url, $filepath)) {
                $size = filesize($filepath);
                echo "✅ ($size bytes)\n";
                $successCount++;
            } else {
                echo "❌ Error\n";
            }
        }
        
        echo "\n📊 RESUMEN:\n";
        echo "  - Total de fuentes: $totalCount\n";
        echo "  - Descargadas exitosamente: $successCount\n";
        echo "  - Fallidas: " . ($totalCount - $successCount) . "\n\n";
        
        if ($successCount === $totalCount) {
            echo "🎉 ¡Todas las fuentes se descargaron correctamente!\n";
            echo "✅ La localización de Google Fonts está completa.\n";
        } else {
            echo "⚠️  Algunas fuentes no se pudieron descargar.\n";
            echo "🔧 Verifica la conectividad a internet y vuelve a intentar.\n";
        }
        
        // Crear archivo CSS actualizado con las fuentes TTF
        $this->createUpdatedCSS();
    }
    
    private function downloadFile($url, $filepath) {
        $context = stream_context_create([
            'http' => [
                'timeout' => 30,
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36'
            ]
        ]);
        
        $content = @file_get_contents($url, false, $context);
        
        if ($content !== false) {
            return file_put_contents($filepath, $content) !== false;
        }
        
        return false;
    }
    
    private function createUpdatedCSS() {
        echo "\n📄 Actualizando archivo CSS...\n";
        
        $cssFile = 'vistas/libs/external/css/google-fonts-local.css';
        $cssContent = '
/* Google Fonts - Source Sans Pro (Local) */
@font-face {
    font-family: "Source Sans Pro";
    font-style: normal;
    font-weight: 300;
    src: local("Source Sans Pro Light"), 
         local("SourceSansPro-Light"), 
         url("../fonts/SourceSansPro-Light.ttf") format("truetype");
}

@font-face {
    font-family: "Source Sans Pro";
    font-style: normal;
    font-weight: 400;
    src: local("Source Sans Pro"), 
         local("SourceSansPro-Regular"), 
         url("../fonts/SourceSansPro-Regular.ttf") format("truetype");
}

@font-face {
    font-family: "Source Sans Pro";
    font-style: normal;
    font-weight: 600;
    src: local("Source Sans Pro Semibold"), 
         local("SourceSansPro-Semibold"), 
         url("../fonts/SourceSansPro-Semibold.ttf") format("truetype");
}

@font-face {
    font-family: "Source Sans Pro";
    font-style: normal;
    font-weight: 700;
    src: local("Source Sans Pro Bold"), 
         local("SourceSansPro-Bold"), 
         url("../fonts/SourceSansPro-Bold.ttf") format("truetype");
}

@font-face {
    font-family: "Source Sans Pro";
    font-style: italic;
    font-weight: 300;
    src: local("Source Sans Pro Light Italic"), 
         local("SourceSansPro-LightItalic"), 
         url("../fonts/SourceSansPro-LightItalic.ttf") format("truetype");
}

@font-face {
    font-family: "Source Sans Pro";
    font-style: italic;
    font-weight: 400;
    src: local("Source Sans Pro Italic"), 
         local("SourceSansPro-Italic"), 
         url("../fonts/SourceSansPro-Italic.ttf") format("truetype");
}

@font-face {
    font-family: "Source Sans Pro";
    font-style: italic;
    font-weight: 600;
    src: local("Source Sans Pro Semibold Italic"), 
         local("SourceSansPro-SemiboldItalic"), 
         url("../fonts/SourceSansPro-SemiboldItalic.ttf") format("truetype");
}

/* Fallbacks de fuentes del sistema para mejor compatibilidad */
body, html {
    font-family: "Source Sans Pro", -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
}

/* Clases de utilidad para diferentes pesos */
.font-light {
    font-weight: 300;
}

.font-regular {
    font-weight: 400;
}

.font-semibold {
    font-weight: 600;
}

.font-bold {
    font-weight: 700;
}

.font-italic {
    font-style: italic;
}
';
        
        if (file_put_contents($cssFile, $cssContent)) {
            echo "✅ Archivo CSS actualizado: $cssFile\n";
            echo "✅ Fuentes TTF configuradas localmente\n";
            echo "✅ Fallbacks de fuentes del sistema incluidos\n";
        } else {
            echo "❌ Error actualizando CSS\n";
        }
    }
}

// Ejecutar descarga
if (php_sapi_name() === 'cli') {
    $downloader = new GoogleFontsDownloader();
    $downloader->downloadFonts();
} else {
    echo "Este script debe ejecutarse desde la línea de comandos.";
}
