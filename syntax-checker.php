<?php
/**
 * Sistema de Verificación Automática de Sintaxis
 * Plugin: WP-Cupon-Whatsapp
 * 
 * Este script verifica automáticamente la sintaxis PHP de todos los archivos del plugin
 * y genera un reporte detallado de cualquier error encontrado.
 * 
 * Uso: php syntax-checker.php
 * URL: https://tu-sitio.com/wp-content/plugins/WP-Cupon-Whatsapp/syntax-checker.php?check=1
 */

// Seguridad: Solo permitir acceso con parámetro específico
if (!isset($_GET['check']) || $_GET['check'] !== '1') {
    if (php_sapi_name() !== 'cli') {
        http_response_code(403);
        die('Acceso denegado. Use: ?check=1');
    }
}

// Configuración
define('PLUGIN_DIR', __DIR__);
define('PLUGIN_NAME', 'WP-Cupon-Whatsapp');
define('CHECK_TIMESTAMP', date('Y-m-d H:i:s'));

/**
 * Clase principal para verificación de sintaxis
 */
class WPCWSyntaxChecker {
    
    private $errors = [];
    private $warnings = [];
    private $checked_files = 0;
    private $total_files = 0;
    
    /**
     * Archivos y directorios a excluir de la verificación
     */
    private $excluded = [
        '.git',
        '.gitignore',
        'node_modules',
        'vendor',
        '*.md',
        '*.txt',
        '*.json',
        '*.css',
        '*.js',
        '*.mo',
        '*.po',
        '*.pot',
        'LICENSE'
    ];
    
    /**
     * Ejecutar verificación completa
     */
    public function run() {
        $this->printHeader();
        
        // Obtener todos los archivos PHP
        $files = $this->getPhpFiles(PLUGIN_DIR);
        $this->total_files = count($files);
        
        echo "📁 Directorio: " . PLUGIN_DIR . "\n";
        echo "📊 Total de archivos PHP encontrados: {$this->total_files}\n\n";
        
        // Verificar cada archivo
        foreach ($files as $file) {
            $this->checkFile($file);
        }
        
        // Generar reporte final
        $this->generateReport();
        
        return $this->hasErrors();
    }
    
    /**
     * Obtener todos los archivos PHP del directorio
     */
    private function getPhpFiles($dir) {
        $files = [];
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS)
        );
        
        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $relativePath = str_replace(PLUGIN_DIR . DIRECTORY_SEPARATOR, '', $file->getPathname());
                
                // Verificar si el archivo debe ser excluido
                if (!$this->shouldExclude($relativePath)) {
                    $files[] = $file->getPathname();
                }
            }
        }
        
        return $files;
    }
    
    /**
     * Verificar si un archivo debe ser excluido
     */
    private function shouldExclude($filePath) {
        foreach ($this->excluded as $pattern) {
            if (strpos($filePath, $pattern) !== false) {
                return true;
            }
        }
        return false;
    }
    
    /**
     * Verificar sintaxis de un archivo específico
     */
    private function checkFile($filePath) {
        $relativePath = str_replace(PLUGIN_DIR . DIRECTORY_SEPARATOR, '', $filePath);
        
        // Verificar sintaxis PHP
        $output = [];
        $return_code = 0;
        
        exec("php -l \"$filePath\" 2>&1", $output, $return_code);
        
        $this->checked_files++;
        
        if ($return_code !== 0) {
            // Error de sintaxis encontrado
            $error_message = implode("\n", $output);
            $this->errors[] = [
                'file' => $relativePath,
                'full_path' => $filePath,
                'error' => $error_message,
                'line' => $this->extractLineNumber($error_message)
            ];
            
            echo "❌ ERROR: $relativePath\n";
            echo "   └─ $error_message\n\n";
        } else {
            // Verificaciones adicionales
            $this->performAdditionalChecks($filePath, $relativePath);
            echo "✅ OK: $relativePath\n";
        }
    }
    
    /**
     * Realizar verificaciones adicionales
     */
    private function performAdditionalChecks($filePath, $relativePath) {
        $content = file_get_contents($filePath);
        
        // Verificar BOM UTF-8
        if (substr($content, 0, 3) === "\xEF\xBB\xBF") {
            $this->warnings[] = [
                'file' => $relativePath,
                'type' => 'BOM_UTF8',
                'message' => 'Archivo contiene BOM UTF-8 que puede causar problemas'
            ];
        }
        
        // Verificar espacios en blanco antes de <?php
        if (preg_match('/^\s+<\?php/', $content)) {
            $this->warnings[] = [
                'file' => $relativePath,
                'type' => 'WHITESPACE_BEFORE_PHP',
                'message' => 'Espacios en blanco antes de la etiqueta <?php'
            ];
        }
        
        // Verificar llaves desbalanceadas
        $open_braces = substr_count($content, '{');
        $close_braces = substr_count($content, '}');
        
        if ($open_braces !== $close_braces) {
            $this->warnings[] = [
                'file' => $relativePath,
                'type' => 'UNBALANCED_BRACES',
                'message' => "Llaves desbalanceadas: {$open_braces} abiertas, {$close_braces} cerradas"
            ];
        }
    }
    
    /**
     * Extraer número de línea del mensaje de error
     */
    private function extractLineNumber($error_message) {
        if (preg_match('/on line (\d+)/', $error_message, $matches)) {
            return (int)$matches[1];
        }
        return null;
    }
    
    /**
     * Generar reporte final
     */
    private function generateReport() {
        echo "\n" . str_repeat('=', 60) . "\n";
        echo "📋 REPORTE FINAL DE VERIFICACIÓN\n";
        echo str_repeat('=', 60) . "\n\n";
        
        echo "📊 Estadísticas:\n";
        echo "   • Archivos verificados: {$this->checked_files}/{$this->total_files}\n";
        echo "   • Errores encontrados: " . count($this->errors) . "\n";
        echo "   • Advertencias: " . count($this->warnings) . "\n\n";
        
        // Mostrar errores
        if (!empty($this->errors)) {
            echo "🚨 ERRORES CRÍTICOS:\n";
            echo str_repeat('-', 40) . "\n";
            
            foreach ($this->errors as $i => $error) {
                echo ($i + 1) . ". {$error['file']}\n";
                if ($error['line']) {
                    echo "   Línea: {$error['line']}\n";
                }
                echo "   Error: {$error['error']}\n\n";
            }
        }
        
        // Mostrar advertencias
        if (!empty($this->warnings)) {
            echo "⚠️  ADVERTENCIAS:\n";
            echo str_repeat('-', 40) . "\n";
            
            foreach ($this->warnings as $i => $warning) {
                echo ($i + 1) . ". {$warning['file']}\n";
                echo "   Tipo: {$warning['type']}\n";
                echo "   Mensaje: {$warning['message']}\n\n";
            }
        }
        
        // Estado final
        if ($this->hasErrors()) {
            echo "❌ VERIFICACIÓN FALLIDA - Se encontraron errores críticos\n";
        } elseif (!empty($this->warnings)) {
            echo "⚠️  VERIFICACIÓN COMPLETADA CON ADVERTENCIAS\n";
        } else {
            echo "✅ VERIFICACIÓN EXITOSA - Todos los archivos están correctos\n";
        }
        
        echo "\n🕐 Verificación completada: " . CHECK_TIMESTAMP . "\n";
        
        // Generar archivo de log
        $this->generateLogFile();
    }
    
    /**
     * Generar archivo de log
     */
    private function generateLogFile() {
        $logFile = PLUGIN_DIR . '/syntax-check.log';
        $logContent = "VERIFICACIÓN DE SINTAXIS - " . CHECK_TIMESTAMP . "\n";
        $logContent .= str_repeat('=', 60) . "\n\n";
        
        $logContent .= "Archivos verificados: {$this->checked_files}/{$this->total_files}\n";
        $logContent .= "Errores: " . count($this->errors) . "\n";
        $logContent .= "Advertencias: " . count($this->warnings) . "\n\n";
        
        if (!empty($this->errors)) {
            $logContent .= "ERRORES:\n";
            foreach ($this->errors as $error) {
                $logContent .= "- {$error['file']}: {$error['error']}\n";
            }
            $logContent .= "\n";
        }
        
        if (!empty($this->warnings)) {
            $logContent .= "ADVERTENCIAS:\n";
            foreach ($this->warnings as $warning) {
                $logContent .= "- {$warning['file']}: {$warning['message']}\n";
            }
        }
        
        file_put_contents($logFile, $logContent);
        echo "\n📄 Log guardado en: syntax-check.log\n";
    }
    
    /**
     * Verificar si hay errores
     */
    public function hasErrors() {
        return !empty($this->errors);
    }
    
    /**
     * Mostrar encabezado
     */
    private function printHeader() {
        echo "\n";
        echo str_repeat('=', 60) . "\n";
        echo "🔍 VERIFICADOR AUTOMÁTICO DE SINTAXIS PHP\n";
        echo "Plugin: " . PLUGIN_NAME . "\n";
        echo "Fecha: " . CHECK_TIMESTAMP . "\n";
        echo str_repeat('=', 60) . "\n\n";
    }
}

// Ejecutar verificación
if (php_sapi_name() === 'cli' || (isset($_GET['check']) && $_GET['check'] === '1')) {
    $checker = new WPCWSyntaxChecker();
    $hasErrors = $checker->run();
    
    // Código de salida para CLI
    if (php_sapi_name() === 'cli') {
        exit($hasErrors ? 1 : 0);
    }
} else {
    echo "<h1>Verificador de Sintaxis PHP</h1>";
    echo "<p>Para ejecutar la verificación, añade <code>?check=1</code> a la URL.</p>";
    echo "<p><a href='?check=1'>Ejecutar Verificación</a></p>";
}
?>