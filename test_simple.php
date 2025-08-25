<?php
/**
 * TEST SIMPLE - SISTEMA COLFE
 * 
 * Test básico para validar funcionalidades principales del sistema
 * sin depender de múltiples includes que pueden causar errores.
 * 
 * Autor: Sistema de Testing Simple
 * Fecha: <?php echo date('Y-m-d H:i:s'); ?>
 */

// Configuración inicial
error_reporting(E_ALL);
ini_set('display_errors', 1);
date_default_timezone_set('America/Bogota');

// Clase para manejar las pruebas simples
class TestSimple {
    
    private $resultados = [];
    private $exitos = 0;
    private $fallos = 0;
    
    public function __construct() {
        echo "<h1>🧪 TEST SIMPLE - SISTEMA COLFE</h1>";
        echo "<p><strong>Fecha de ejecución:</strong> " . date('Y-m-d H:i:s') . "</p>";
        echo "<hr>";
    }
    
    // Método para registrar resultados
    private function registrarResultado($modulo, $prueba, $resultado, $mensaje = "") {
        $this->resultados[] = [
            'modulo' => $modulo,
            'prueba' => $prueba,
            'resultado' => $resultado,
            'mensaje' => $mensaje,
            'timestamp' => date('H:i:s')
        ];
        
        if ($resultado) {
            $this->exitos++;
            echo "<div style='color: green; margin: 5px 0;'>✅ <strong>$modulo</strong> - $prueba: EXITOSO</div>";
        } else {
            $this->fallos++;
            echo "<div style='color: red; margin: 5px 0;'>❌ <strong>$modulo</strong> - $prueba: FALLÓ - $mensaje</div>";
        }
    }
    
    // Test de conexión a base de datos
    public function testConexionBD() {
        echo "<h2>🔗 PRUEBAS DE CONEXIÓN A BASE DE DATOS</h2>";
        
        try {
            $conexion = new PDO("mysql:host=localhost;dbname=colfe", "root", "");
            $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->registrarResultado("Base de Datos", "Conexión PDO", true, "Conexión exitosa");
            
            // Verificar tablas principales
            $tablas = ['socios', 'recoleccion', 'liquidacion', 'produccion', 'deducibles', 'precios', 'anticipos'];
            foreach ($tablas as $tabla) {
                $stmt = $conexion->query("SHOW TABLES LIKE '$tabla'");
                if ($stmt->rowCount() > 0) {
                    $this->registrarResultado("Base de Datos", "Tabla $tabla existe", true);
                } else {
                    $this->registrarResultado("Base de Datos", "Tabla $tabla existe", false, "Tabla no encontrada");
                }
            }
            
        } catch (PDOException $e) {
            $this->registrarResultado("Base de Datos", "Conexión PDO", false, $e->getMessage());
        }
    }
    
    // Test de archivos del sistema
    public function testArchivosSistema() {
        echo "<h2>📁 PRUEBAS DE ARCHIVOS DEL SISTEMA</h2>";
        
        $archivos_requeridos = [
            'controladores/plantilla.controlador.php',
            'controladores/socios.controlador.php',
            'controladores/recoleccion.controlador.php',
            'controladores/liquidacion.controlador.php',
            'controladores/produccion.controlador.php',
            'modelos/socios.modelo.php',
            'modelos/recoleccion.modelo.php',
            'modelos/liquidacion.modelo.php',
            'modelos/produccion.modelo.php',
            'vistas/plantilla.php',
            'vistas/modulos/menu.php',
            'vistas/modulos/socios.php',
            'vistas/modulos/recoleccion.php',
            'vistas/modulos/liquidacion.php',
            'vistas/modulos/produccion.php',
            'index.php'
        ];
        
        foreach ($archivos_requeridos as $archivo) {
            if (file_exists($archivo)) {
                $this->registrarResultado("Archivos", "Archivo $archivo", true);
            } else {
                $this->registrarResultado("Archivos", "Archivo $archivo", false, "Archivo no encontrado");
            }
        }
    }
    
    // Test de archivos AJAX
    public function testArchivosAjax() {
        echo "<h2>⚡ PRUEBAS DE ARCHIVOS AJAX</h2>";
        
        $archivos_ajax = [
            'ajax/socios.ajax.php',
            'ajax/recoleccion.ajax.php',
            'ajax/liquidacion.ajax.php',
            'ajax/produccion.ajax.php',
            'ajax/deducibles.ajax.php',
            'ajax/precios.ajax.php',
            'ajax/anticipos.ajax.php'
        ];
        
        foreach ($archivos_ajax as $archivo) {
            if (file_exists($archivo)) {
                $this->registrarResultado("AJAX", "Archivo $archivo", true);
            } else {
                $this->registrarResultado("AJAX", "Archivo $archivo", false, "Archivo no encontrado");
            }
        }
    }
    
    // Test de archivos CSS y JS
    public function testArchivosEstaticos() {
        echo "<h2>🎨 PRUEBAS DE ARCHIVOS ESTÁTICOS</h2>";
        
        $archivos_css = [
            'vistas/css/liquidacion.css',
            'vistas/css/recoleccion.css',
            'vistas/css/produccion.css'
        ];
        
        $archivos_js = [
            'vistas/js/socios.js',
            'vistas/js/recoleccion.js',
            'vistas/js/liquidacion.js',
            'vistas/js/produccion.js',
            'vistas/js/calendario.js',
            'vistas/js/plantilla.js'
        ];
        
        foreach ($archivos_css as $archivo) {
            if (file_exists($archivo)) {
                $this->registrarResultado("CSS", "Archivo $archivo", true);
            } else {
                $this->registrarResultado("CSS", "Archivo $archivo", false, "Archivo no encontrado");
            }
        }
        
        foreach ($archivos_js as $archivo) {
            if (file_exists($archivo)) {
                $this->registrarResultado("JavaScript", "Archivo $archivo", true);
            } else {
                $this->registrarResultado("JavaScript", "Archivo $archivo", false, "Archivo no encontrado");
            }
        }
    }
    
    // Test de seguridad
    public function testSeguridad() {
        echo "<h2>🔒 PRUEBAS DE SEGURIDAD</h2>";
        
        // Verificar que no hay archivos de debug
        $archivos_debug = [
            'debug_anticipos.php',
            'debug_functions.php',
            'test_ajax_anticipos.php',
            'verificar_anticipo_id3.php'
        ];
        
        foreach ($archivos_debug as $archivo) {
            if (!file_exists($archivo)) {
                $this->registrarResultado("Seguridad", "Archivo debug $archivo eliminado", true);
            } else {
                $this->registrarResultado("Seguridad", "Archivo debug $archivo eliminado", false, "Archivo debug aún existe");
            }
        }
        
        // Verificar que no hay console.log en archivos JS
        $archivos_js = [
            'vistas/js/socios.js',
            'vistas/js/recoleccion.js',
            'vistas/js/liquidacion.js',
            'vistas/js/produccion.js',
            'vistas/js/calendario.js'
        ];
        
        foreach ($archivos_js as $archivo) {
            if (file_exists($archivo)) {
                $contenido = file_get_contents($archivo);
                if (strpos($contenido, 'console.log') === false) {
                    $this->registrarResultado("Seguridad", "Sin console.log en $archivo", true);
                } else {
                    $this->registrarResultado("Seguridad", "Sin console.log en $archivo", false, "Contiene console.log");
                }
            }
        }
    }
    
    // Test de consistencia visual
    public function testConsistenciaVisual() {
        echo "<h2>🎨 PRUEBAS DE CONSISTENCIA VISUAL</h2>";
        
        // Verificar que no hay referencias a predicción
        $archivos_plantilla = [
            'vistas/plantilla.php',
            'vistas/modulos/menu.php'
        ];
        
        foreach ($archivos_plantilla as $archivo) {
            if (file_exists($archivo)) {
                $contenido = file_get_contents($archivo);
                if (strpos($contenido, 'prediccion') === false) {
                    $this->registrarResultado("Consistencia Visual", "Sin referencias a predicción en $archivo", true);
                } else {
                    $this->registrarResultado("Consistencia Visual", "Sin referencias a predicción en $archivo", false, "Contiene referencias a predicción");
                }
            }
        }
        
        // Verificar archivos CSS de consistencia
        if (file_exists('vistas/css/consistencia.css')) {
            $this->registrarResultado("Consistencia Visual", "Archivo consistencia.css", true);
        } else {
            $this->registrarResultado("Consistencia Visual", "Archivo consistencia.css", false, "Archivo no encontrado");
        }
    }
    
    // Test de sintaxis PHP
    public function testSintaxisPHP() {
        echo "<h2>🐘 PRUEBAS DE SINTAXIS PHP</h2>";
        
        $archivos_php = [
            'index.php',
            'controladores/socios.controlador.php',
            'controladores/recoleccion.controlador.php',
            'controladores/liquidacion.controlador.php',
            'controladores/produccion.controlador.php',
            'modelos/socios.modelo.php',
            'modelos/recoleccion.modelo.php',
            'modelos/liquidacion.modelo.php',
            'modelos/produccion.modelo.php'
        ];
        
        foreach ($archivos_php as $archivo) {
            if (file_exists($archivo)) {
                $output = [];
                $return_var = 0;
                exec("php -l $archivo 2>&1", $output, $return_var);
                
                if ($return_var === 0) {
                    $this->registrarResultado("Sintaxis PHP", "Archivo $archivo", true);
                } else {
                    $error = implode("\n", $output);
                    $this->registrarResultado("Sintaxis PHP", "Archivo $archivo", false, "Error de sintaxis: $error");
                }
            }
        }
    }
    
    // Ejecutar todas las pruebas
    public function ejecutarTodasLasPruebas() {
        $this->testConexionBD();
        $this->testArchivosSistema();
        $this->testArchivosAjax();
        $this->testArchivosEstaticos();
        $this->testSeguridad();
        $this->testConsistenciaVisual();
        $this->testSintaxisPHP();
        
        $this->mostrarResumen();
    }
    
    // Mostrar resumen final
    private function mostrarResumen() {
        echo "<hr>";
        echo "<h2>📊 RESUMEN DE PRUEBAS</h2>";
        echo "<div style='background: #f0f0f0; padding: 20px; border-radius: 10px;'>";
        echo "<h3>Estadísticas:</h3>";
        echo "<p><strong>Total de pruebas:</strong> " . count($this->resultados) . "</p>";
        echo "<p><strong>✅ Exitosas:</strong> <span style='color: green; font-weight: bold;'>$this->exitos</span></p>";
        echo "<p><strong>❌ Fallidas:</strong> <span style='color: red; font-weight: bold;'>$this->fallos</span></p>";
        
        $porcentaje = count($this->resultados) > 0 ? round(($this->exitos / count($this->resultados)) * 100, 2) : 0;
        echo "<p><strong>📈 Porcentaje de éxito:</strong> <span style='font-weight: bold;'>$porcentaje%</span></p>";
        
        if ($this->fallos == 0) {
            echo "<div style='background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin-top: 15px;'>";
            echo "<h3>🎉 ¡TODAS LAS PRUEBAS EXITOSAS!</h3>";
            echo "<p>El sistema está funcionando correctamente en todas las áreas validadas.</p>";
            echo "</div>";
        } else {
            echo "<div style='background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin-top: 15px;'>";
            echo "<h3>⚠️ PRUEBAS CON FALLOS</h3>";
            echo "<p>Se encontraron $this->fallos problemas que requieren atención.</p>";
            echo "</div>";
        }
        
        echo "</div>";
        
        // Mostrar detalles de fallos
        if ($this->fallos > 0) {
            echo "<h3>🔍 DETALLES DE FALLOS:</h3>";
            foreach ($this->resultados as $resultado) {
                if (!$resultado['resultado']) {
                    echo "<div style='background: #fff3cd; padding: 10px; margin: 5px 0; border-left: 4px solid #ffc107;'>";
                    echo "<strong>{$resultado['modulo']}</strong> - {$resultado['prueba']}<br>";
                    echo "<small>Error: {$resultado['mensaje']} (Hora: {$resultado['timestamp']})</small>";
                    echo "</div>";
                }
            }
        }
    }
}

// Ejecutar las pruebas
echo "<!DOCTYPE html>";
echo "<html><head>";
echo "<title>Test Simple - Sistema COLFE</title>";
echo "<style>";
echo "body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }";
echo "h1 { color: #2c3e50; text-align: center; }";
echo "h2 { color: #34495e; border-bottom: 2px solid #3498db; padding-bottom: 10px; }";
echo "hr { border: none; border-top: 2px solid #ecf0f1; margin: 20px 0; }";
echo "</style>";
echo "</head><body>";

$test = new TestSimple();
$test->ejecutarTodasLasPruebas();

echo "</body></html>";
?>

