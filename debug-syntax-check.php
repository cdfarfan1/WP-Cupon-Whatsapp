<?php
/**
 * Script para verificar sintaxis específica de debug-headers.php
 * Identifica problemas de llaves no cerradas
 */

// Leer el archivo debug-headers.php
$file_path = __DIR__ . '/debug-headers.php';

if (!file_exists($file_path)) {
    echo "ERROR: No se encuentra debug-headers.php\n";
    exit(1);
}

$content = file_get_contents($file_path);
if ($content === false) {
    echo "ERROR: No se puede leer debug-headers.php\n";
    exit(1);
}

// Verificar balance de llaves
function check_brace_balance($content) {
    $lines = explode("\n", $content);
    $brace_count = 0;
    $issues = array();
    
    foreach ($lines as $line_num => $line) {
        $line_number = $line_num + 1;
        
        // Contar llaves de apertura
        $open_braces = substr_count($line, '{');
        $close_braces = substr_count($line, '}');
        
        $brace_count += $open_braces - $close_braces;
        
        // Verificar líneas específicas mencionadas en el error
        if ($line_number == 32 || $line_number == 46) {
            $issues[] = "Línea $line_number: Balance de llaves = $brace_count, Contenido: " . trim($line);
        }
        
        // Si el balance es negativo, hay un problema
        if ($brace_count < 0) {
            $issues[] = "ERROR: Llave de cierre sin apertura en línea $line_number";
        }
    }
    
    if ($brace_count != 0) {
        $issues[] = "ERROR: Balance final de llaves = $brace_count (debería ser 0)";
    }
    
    return $issues;
}

// Verificar caracteres invisibles problemáticos
function check_invisible_chars($content) {
    $issues = array();
    
    // Verificar BOM
    if (substr($content, 0, 3) === "\xEF\xBB\xBF") {
        $issues[] = "BOM detectado al inicio del archivo";
    }
    
    // Verificar caracteres de control
    if (preg_match('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', $content)) {
        $issues[] = "Caracteres de control detectados";
    }
    
    return $issues;
}

echo "=== VERIFICACIÓN DE SINTAXIS debug-headers.php ===\n\n";

// Verificar balance de llaves
echo "1. Verificando balance de llaves...\n";
$brace_issues = check_brace_balance($content);
if (empty($brace_issues)) {
    echo "   ✓ Balance de llaves correcto\n";
} else {
    echo "   ✗ Problemas encontrados:\n";
    foreach ($brace_issues as $issue) {
        echo "     - $issue\n";
    }
}

echo "\n2. Verificando caracteres invisibles...\n";
$char_issues = check_invisible_chars($content);
if (empty($char_issues)) {
    echo "   ✓ No se encontraron caracteres problemáticos\n";
} else {
    echo "   ✗ Problemas encontrados:\n";
    foreach ($char_issues as $issue) {
        echo "     - $issue\n";
    }
}

echo "\n3. Información del archivo:\n";
echo "   - Tamaño: " . strlen($content) . " bytes\n";
echo "   - Líneas: " . substr_count($content, "\n") . "\n";
echo "   - Codificación: " . mb_detect_encoding($content) . "\n";

// Mostrar líneas específicas mencionadas en el error
echo "\n4. Contenido de líneas problemáticas:\n";
$lines = explode("\n", $content);
foreach ([32, 46] as $line_num) {
    if (isset($lines[$line_num - 1])) {
        echo "   Línea $line_num: " . trim($lines[$line_num - 1]) . "\n";
        // Mostrar bytes hexadecimales para detectar caracteres invisibles
        $hex = bin2hex($lines[$line_num - 1]);
        echo "   Hex: $hex\n";
    }
}

echo "\n=== FIN DE VERIFICACIÓN ===\n";
?>