<?php
/**
 * Ejemplo completo de integración con PHP SDK
 *
 * Este ejemplo muestra un flujo completo de:
 * 1. Crear beneficiario
 * 2. Listar cupones disponibles
 * 3. Validar cupón
 * 4. Registrar canje
 * 5. Obtener historial
 *
 * @package     WPCuponWhatsappSDK
 * @version     1.0.0
 * @author      Pragmatic Solutions
 */

require_once __DIR__ . '/../php/WPCuponWhatsappSDK.php';

use WPCuponWhatsapp\SDK\WPCuponWhatsappSDK;

// ========================================
// CONFIGURACIÓN
// ========================================

$config = [
    'api_url' => 'https://tusitio.com',
    'api_key' => 'tu_api_key_aqui',
    'api_secret' => 'tu_api_secret_aqui',
    'debug' => true // Activar modo debug
];

try {
    // Inicializar SDK
    $sdk = new WPCuponWhatsappSDK(
        $config['api_url'],
        $config['api_key'],
        $config['api_secret'],
        ['debug' => $config['debug']]
    );

    echo "✅ SDK inicializado correctamente\n";
    echo "📡 Versión: " . $sdk->get_version() . "\n\n";

    // ========================================
    // 1. CREAR BENEFICIARIO
    // ========================================

    echo "=== 1. CREAR BENEFICIARIO ===\n";

    $nuevo_beneficiario = $sdk->create_beneficiario([
        'institucion_id' => 1,
        'nombre_completo' => 'María González',
        'tipo_documento' => 'DNI',
        'numero_documento' => '98765432',
        'telefono_whatsapp' => '+54 9 11 9876-5432',
        'email' => 'maria@example.com'
    ]);

    $beneficiario_id = $nuevo_beneficiario['data']['id'];
    $codigo_beneficiario = $nuevo_beneficiario['data']['codigo_beneficiario'];

    echo "✅ Beneficiario creado\n";
    echo "   ID: {$beneficiario_id}\n";
    echo "   Código: {$codigo_beneficiario}\n";
    echo "   WhatsApp enviado: " . ($nuevo_beneficiario['data']['whatsapp_sent'] ? 'Sí' : 'No') . "\n\n";

    // ========================================
    // 2. LISTAR CUPONES DISPONIBLES
    // ========================================

    echo "=== 2. LISTAR CUPONES DISPONIBLES ===\n";

    $cupones = $sdk->get_cupones_by_beneficiario($beneficiario_id);

    echo "✅ Cupones encontrados: " . count($cupones['data']) . "\n";

    foreach ($cupones['data'] as $index => $cupon) {
        echo "\n   Cupón " . ($index + 1) . ":\n";
        echo "   - Código: {$cupon['codigo']}\n";
        echo "   - Nombre: {$cupon['nombre']}\n";
        echo "   - Descuento: {$cupon['monto']}" . ($cupon['tipo_descuento'] === 'percent' ? '%' : '$') . "\n";
        echo "   - Comercio: {$cupon['comercio']['nombre']}\n";
        echo "   - Vence: {$cupon['fecha_expiracion']}\n";
        echo "   - Ya usado: " . ($cupon['ya_usado'] ? 'Sí' : 'No') . "\n";
    }

    echo "\n";

    // ========================================
    // 3. VALIDAR CUPÓN
    // ========================================

    echo "=== 3. VALIDAR CUPÓN ===\n";

    $cupon_codigo = $cupones['data'][0]['codigo'];
    $cupon_id = $cupones['data'][0]['id'];

    $validacion = $sdk->validate_cupon($cupon_codigo, $beneficiario_id);

    if ($validacion['valid']) {
        echo "✅ Cupón válido\n";
        echo "   Código: {$validacion['data']['codigo']}\n";
        echo "   Descuento: {$validacion['data']['monto']}" . ($validacion['data']['tipo_descuento'] === 'percent' ? '%' : '$') . "\n";
        echo "   Mensaje: {$validacion['data']['message']}\n\n";

        // ========================================
        // 4. REGISTRAR CANJE
        // ========================================

        echo "=== 4. REGISTRAR CANJE ===\n";

        $monto_original = 1500.00;
        $descuento = $validacion['data']['tipo_descuento'] === 'percent'
            ? $monto_original * ($validacion['data']['monto'] / 100)
            : $validacion['data']['monto'];
        $monto_final = $monto_original - $descuento;

        $canje = $sdk->create_canje([
            'beneficiario_id' => $beneficiario_id,
            'cupon_id' => $cupon_id,
            'tipo_canje' => 'online',
            'metodo_validacion' => 'codigo',
            'comercio_id' => $cupones['data'][0]['comercio']['id'],
            'descuento_aplicado' => $descuento,
            'monto_original' => $monto_original,
            'monto_final' => $monto_final
        ]);

        echo "✅ Canje registrado\n";
        echo "   ID: {$canje['data']['id']}\n";
        echo "   Descuento: $" . number_format($descuento, 2) . "\n";
        echo "   Monto original: $" . number_format($monto_original, 2) . "\n";
        echo "   Monto final: $" . number_format($monto_final, 2) . "\n";
        echo "   WhatsApp enviado: " . ($canje['data']['whatsapp_sent'] ? 'Sí' : 'No') . "\n\n";

    } else {
        echo "❌ Cupón inválido\n";
        echo "   Razón: {$validacion['reason']}\n";
        echo "   Mensaje: {$validacion['message']}\n\n";
    }

    // ========================================
    // 5. OBTENER HISTORIAL
    // ========================================

    echo "=== 5. OBTENER HISTORIAL ===\n";

    $historial = $sdk->get_historial_beneficiario($beneficiario_id, [
        'limit' => 10
    ]);

    echo "✅ Canjes en historial: " . count($historial['data']) . "\n";
    echo "   Total canjes: {$historial['stats']['total_canjes']}\n";
    echo "   Ahorro total: $" . number_format($historial['stats']['ahorro_total'], 2) . "\n\n";

    foreach ($historial['data'] as $index => $canje) {
        echo "   Canje " . ($index + 1) . ":\n";
        echo "   - Fecha: {$canje['fecha_canje']}\n";
        echo "   - Cupón: {$canje['cupon_codigo']}\n";
        echo "   - Comercio: {$canje['comercio_nombre']}\n";
        echo "   - Tipo: {$canje['tipo_canje']}\n";
        echo "   - Descuento: $" . number_format($canje['descuento_aplicado'], 2) . "\n\n";
    }

    // ========================================
    // 6. OBTENER DETALLES DEL BENEFICIARIO
    // ========================================

    echo "=== 6. DETALLES DEL BENEFICIARIO ===\n";

    $beneficiario = $sdk->get_beneficiario($beneficiario_id);

    echo "✅ Beneficiario obtenido\n";
    echo "   Nombre: {$beneficiario['data']['nombre_completo']}\n";
    echo "   Código: {$beneficiario['data']['codigo_beneficiario']}\n";
    echo "   Estado: {$beneficiario['data']['estado']}\n";
    echo "   Fecha adhesión: {$beneficiario['data']['fecha_adhesion']}\n";
    echo "   Stats:\n";
    echo "     - Total canjes: {$beneficiario['data']['stats']['total_canjes']}\n";
    echo "     - Ahorro total: $" . number_format($beneficiario['data']['stats']['ahorro_total'], 2) . "\n";
    echo "     - Último canje: {$beneficiario['data']['stats']['ultimo_canje']}\n\n";

    // ========================================
    // 7. OBTENER ESTADÍSTICAS DE INSTITUCIÓN
    // ========================================

    echo "=== 7. ESTADÍSTICAS DE INSTITUCIÓN ===\n";

    $stats = $sdk->get_stats_institucion(1, [
        'fecha_desde' => date('Y-m-01'), // Primer día del mes
        'fecha_hasta' => date('Y-m-d')   // Hoy
    ]);

    echo "✅ Estadísticas obtenidas\n";
    echo "   Beneficiarios:\n";
    echo "     - Total: {$stats['data']['beneficiarios']['total']}\n";
    echo "     - Activos: {$stats['data']['beneficiarios']['activos']}\n";
    echo "     - Nuevos este mes: {$stats['data']['beneficiarios']['nuevos_periodo']}\n";
    echo "\n   Canjes:\n";
    echo "     - Total histórico: {$stats['data']['canjes']['total']}\n";
    echo "     - Este mes: {$stats['data']['canjes']['periodo']}\n";
    echo "     - Online: {$stats['data']['canjes']['online']}\n";
    echo "     - Presencial: {$stats['data']['canjes']['presencial']}\n";
    echo "     - WhatsApp: {$stats['data']['canjes']['whatsapp']}\n";
    echo "\n   Ahorro:\n";
    echo "     - Total: $" . number_format($stats['data']['ahorro']['total'], 2) . "\n";
    echo "     - Este mes: $" . number_format($stats['data']['ahorro']['periodo'], 2) . "\n";
    echo "     - Promedio por canje: $" . number_format($stats['data']['ahorro']['promedio_por_canje'], 2) . "\n";
    echo "\n   Top 3 Comercios:\n";

    foreach (array_slice($stats['data']['comercios']['top_5'], 0, 3) as $index => $comercio) {
        echo "     " . ($index + 1) . ". {$comercio['nombre']}\n";
        echo "        - Canjes: {$comercio['total_canjes']}\n";
        echo "        - Ahorro: $" . number_format($comercio['ahorro_generado'], 2) . "\n";
    }

    echo "\n";

    // ========================================
    // 8. ENVIAR WHATSAPP PERSONALIZADO
    // ========================================

    echo "=== 8. ENVIAR WHATSAPP PERSONALIZADO ===\n";

    $whatsapp = $sdk->send_whatsapp(
        '+54 9 11 9876-5432',
        "¡Hola María! Te recordamos que tenés cupones disponibles. ¡Aprovechalos!"
    );

    echo "✅ WhatsApp enviado\n";
    echo "   Message ID: {$whatsapp['data']['whatsapp_message_id']}\n\n";

    // ========================================
    // DEBUG LOG
    // ========================================

    if ($config['debug']) {
        echo "\n=== DEBUG LOG ===\n";
        $log = $sdk->get_log();
        echo "Total de requests: " . count($log) . "\n";
        foreach ($log as $index => $entry) {
            echo "\n   Request " . ($index + 1) . ":\n";
            echo "   - Método: {$entry['method']}\n";
            echo "   - URL: {$entry['url']}\n";
            echo "   - HTTP Code: {$entry['http_code']}\n";
            echo "   - Timestamp: {$entry['timestamp']}\n";
        }
    }

    echo "\n✅ Ejemplo completado exitosamente\n";

} catch (\Exception $e) {
    echo "\n❌ ERROR: " . $e->getMessage() . "\n";
    echo "Código: " . $e->getCode() . "\n";

    if ($config['debug'] && isset($sdk)) {
        echo "\nDebug info:\n";
        print_r($sdk->get_log());
    }
}
