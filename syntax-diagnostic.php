<?php
/**
 * DIAGNÓSTICO AVANZADO DE SINTAXIS
 * 
 * Script para identificar el problema exacto en debug-headers.php
 * Error: "Unclosed '{' on line 32 in debug-headers.php on line 153"
 */

// Prevenir ejecución directa desde web
if (php_sapi_name() !== 'cli' && !defined('ABSPATH')) {
    die('Acceso denegado');
}

class WPCWSyntaxDiagnostic {
    private $file_path;
    private $errors = array();
    private $warnings = array();
    
    public function __construct($file_path) {
        $this->file_path = $file_path;
    }
    
    /**
     * Analizar sintaxis del archivo
     */
    public function analyze() {
        if (!file_exists($this->file_path)) {
            $this->errors[] = "Archivo no encontrado: {$this->file_path}";
            return false;
        }
        
        $content = file_get_contents($this->file_path);
        $lines = explode("\n", $content);
        
        echo "\n=== DIAGNÓSTICO DE SINTAXIS ===\n";
        echo "Archivo: {$this->file_path}\n";
        echo "Total de líneas: " . count($lines) . "\n\n";
        
        // Verificar sintaxis PHP
        $this->checkPHPSyntax();
        
        // Analizar balance de llaves
        $this->analyzeBraceBalance($lines);
        
        // Verificar línea 32 específicamente
        $this->analyzeLine32($lines);
        
        // Verificar línea 153
        $this->analyzeLine153($lines);
        
        // Mostrar resultados
        $this->displayResults();
        
        return empty($this->errors);
    }
    
    /**
     * Verificar sintaxis PHP usando php -l
     */
    private function checkPHPSyntax() {
        $output = array();
        $return_code = 0;
        
        // Intentar verificar sintaxis
        $command = "php -l \"" . $this->file_path . "\" 2>&1";
        exec($command, $output, $return_code);
        
        echo "--- VERIFICACIÓN DE SINTAXIS PHP ---\n";
        if ($return_code === 0) {
            echo "✅ Sintaxis PHP: VÁLIDA\n";
        } else {
            echo "❌ Sintaxis PHP: ERROR\n";
            foreach ($output as $line) {
                echo "   $line\n";
                $this->errors[] = $line;
            }
        }
        echo "\n";
    }
    
    /**
     * Analizar balance de llaves, corchetes y paréntesis
     */
    private function analyzeBraceBalance($lines) {
        echo "--- ANÁLISIS DE BALANCE DE LLAVES ---\n";
        
        $braces = 0;      // {}
        $brackets = 0;    // []
        $parentheses = 0; // ()
        $in_string = false;
        $in_comment = false;
        
        foreach ($lines as $line_num => $line) {
            $line_number = $line_num + 1;
            $original_line = $line;
            
            // Remover comentarios de línea
            if (strpos($line, '//') !== false && !$in_string) {
                $line = substr($line, 0, strpos($line, '//'));
            }
            
            // Procesar carácter por carácter
            for ($i = 0; $i < strlen($line); $i++) {
                $char = $line[$i];
                $prev_char = $i > 0 ? $line[$i-1] : '';
                
                // Manejar strings
                if (($char === '"' || $char === "'") && $prev_char !== '\\') {
                    $in_string = !$in_string;
                    continue;
                }
                
                if ($in_string) continue;
                
                // Contar llaves, corchetes y paréntesis
                switch ($char) {
                    case '{':
                        $braces++;
                        if ($line_number == 32) {
                            echo "🔍 Línea 32: Llave de apertura '{' encontrada\n";
                        }
                        break;
                    case '}':
                        $braces--;
                        if ($braces < 0) {
                            $this->errors[] = "Línea $line_number: Llave de cierre '}' sin apertura correspondiente";
                        }
                        break;
                    case '[':
                        $brackets++;
                        break;
                    case ']':
                        $brackets--;
                        break;
                    case '(':
                        $parentheses++;
                        break;
                    case ')':
                        $parentheses--;
                        break;
                }
            }
            
            // Reportar problemas en líneas específicas
            if ($line_number == 32 || $line_number == 153) {
                echo "Línea $line_number: Braces=$braces, Brackets=$brackets, Parentheses=$parentheses\n";
                echo "   Contenido: " . trim($original_line) . "\n";
            }
        }
        
        echo "\nBalance final:\n";
        echo "Llaves {}: $braces " . ($braces == 0 ? '✅' : '❌') . "\n";
        echo "Corchetes []: $brackets " . ($brackets == 0 ? '✅' : '❌') . "\n";
        echo "Paréntesis (): $parentheses " . ($parentheses == 0 ? '✅' : '❌') . "\n\n";
        
        if ($braces != 0) {
            $this->errors[] = "Llaves desbalanceadas: $braces llaves sin cerrar";
        }
    }
    
    /**
     * Analizar línea 32 específicamente
     */
    private function analyzeLine32($lines) {
        echo "--- ANÁLISIS DE LÍNEA 32 ---\n";
        
        if (isset($lines[31])) { // Línea 32 (índice 31)
            $line32 = $lines[31];
            echo "Línea 32: " . trim($line32) . "\n";
            
            // Verificar contexto (líneas anteriores y posteriores)
            for ($i = 29; $i <= 34; $i++) {
                if (isset($lines[$i-1])) {
                    $marker = ($i == 32) ? '>>> ' : '    ';
                    echo "{$marker}Línea $i: " . trim($lines[$i-1]) . "\n";
                }
            }
        } else {
            echo "❌ Línea 32 no existe en el archivo\n";
        }
        echo "\n";
    }
    
    /**
     * Analizar línea 153
     */
    private function analyzeLine153($lines) {
        echo "--- ANÁLISIS DE LÍNEA 153 ---\n";
        
        if (isset($lines[152])) { // Línea 153 (índice 152)
            $line153 = $lines[152];
            echo "Línea 153: " . trim($line153) . "\n";
            
            // Verificar contexto
            for ($i = 150; $i <= 155; $i++) {
                if (isset($lines[$i-1])) {
                    $marker = ($i == 153) ? '>>> ' : '    ';
                    echo "{$marker}Línea $i: " . trim($lines[$i-1]) . "\n";
                }
            }
        } else {
            echo "❌ Línea 153 no existe en el archivo\n";
        }
        echo "\n";
    }
    
    /**
     * Mostrar resultados finales
     */
    private function displayResults() {
        echo "=== RESULTADOS DEL DIAGNÓSTICO ===\n";
        
        if (empty($this->errors)) {
            echo "✅ No se encontraron errores de sintaxis\n";
        } else {
            echo "❌ Errores encontrados:\n";
            foreach ($this->errors as $error) {
                echo "   • $error\n";
            }
        }
        
        if (!empty($this->warnings)) {
            echo "\n⚠️ Advertencias:\n";
            foreach ($this->warnings as $warning) {
                echo "   • $warning\n";
            }
        }
        
        echo "\n=== FIN DEL DIAGNÓSTICO ===\n";
    }
}

// Ejecutar diagnóstico
if (php_sapi_name() === 'cli') {
    // Modo línea de comandos
    $file_to_check = isset($argv[1]) ? $argv[1] : 'debug-headers.php';
} else {
    // Modo web (solo si está en WordPress)
    $file_to_check = 'debug-headers.php';
}

$diagnostic = new WPCWSyntaxDiagnostic($file_to_check);
$result = $diagnostic->analyze();

if (php_sapi_name() === 'cli') {
    exit($result ? 0 : 1);
}

?>