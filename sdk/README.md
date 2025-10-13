# 🚀 WP CUPÓN WHATSAPP SDK

SDKs oficiales para integración con WP Cupón WhatsApp.

**Versión:** 1.0.0
**Desarrollado por:** Pragmatic Solutions - Innovación Aplicada

**Equipo:**
- @API - Carlos Mendoza ($820M) - Lead API Integration
- @FRONTEND - Sophie Laurent ($790M) - JavaScript SDK
- @SECURITY - James O'Connor ($850M) - Security & Auth
- @CTO - Dr. Viktor Petrov ($1.680B) - Architecture

---

## 📋 CONTENIDO

- [PHP SDK](#php-sdk)
- [JavaScript SDK](#javascript-sdk)
- [Documentación API](#documentación-api)
- [Ejemplos](#ejemplos)
- [Autenticación](#autenticación)
- [Rate Limiting](#rate-limiting)
- [Soporte](#soporte)

---

## 🐘 PHP SDK

### Instalación

```bash
# Opción 1: Copiar el archivo directamente
cp sdk/php/WPCuponWhatsappSDK.php tu-proyecto/

# Opción 2: Vía Composer (próximamente)
composer require wp-cupon-whatsapp/sdk
```

### Uso Básico

```php
<?php
require_once 'WPCuponWhatsappSDK.php';

use WPCuponWhatsapp\SDK\WPCuponWhatsappSDK;

// Inicializar SDK
$sdk = new WPCuponWhatsappSDK(
    'https://tusitio.com',
    'tu_api_key',
    'tu_api_secret',
    ['debug' => true] // Opcional
);

// Crear beneficiario
$beneficiario = $sdk->create_beneficiario([
    'institucion_id' => 1,
    'nombre_completo' => 'Juan Pérez',
    'telefono_whatsapp' => '+54 9 11 1234-5678',
    'email' => 'juan@example.com'
]);

// Obtener cupones disponibles
$cupones = $sdk->get_cupones_by_beneficiario($beneficiario['data']['id']);

// Validar cupón
$validacion = $sdk->validate_cupon('DESCUENTO20', $beneficiario['data']['id']);

if ($validacion['valid']) {
    // Registrar canje
    $canje = $sdk->create_canje([
        'beneficiario_id' => $beneficiario['data']['id'],
        'cupon_id' => $cupones['data'][0]['id'],
        'tipo_canje' => 'online',
        'descuento_aplicado' => 300.00,
        'monto_original' => 1500.00,
        'monto_final' => 1200.00
    ]);
}
?>
```

### Métodos Disponibles

#### Beneficiarios

```php
// Crear
$sdk->create_beneficiario($data);

// Obtener por ID
$sdk->get_beneficiario($id);

// Obtener por teléfono
$sdk->get_beneficiario_by_phone($telefono);

// Listar
$sdk->list_beneficiarios($filters);

// Actualizar
$sdk->update_beneficiario($id, $data);

// Desactivar
$sdk->deactivate_beneficiario($id);
```

#### Cupones

```php
// Crear
$sdk->create_cupon($data);

// Obtener por código
$sdk->get_cupon($codigo);

// Listar
$sdk->list_cupones($filters);

// Obtener por beneficiario
$sdk->get_cupones_by_beneficiario($beneficiario_id);

// Validar
$sdk->validate_cupon($codigo, $beneficiario_id);
```

#### Canjes

```php
// Crear
$sdk->create_canje($data);

// Obtener por ID
$sdk->get_canje($id);

// Listar
$sdk->list_canjes($filters);

// Historial de beneficiario
$sdk->get_historial_beneficiario($beneficiario_id, $filters);
```

#### Instituciones

```php
// Obtener
$sdk->get_institucion($id);

// Listar
$sdk->list_instituciones();

// Estadísticas
$sdk->get_stats_institucion($id, $filters);
```

#### WhatsApp

```php
// Enviar mensaje
$sdk->send_whatsapp($telefono, $mensaje);

// Enviar template
$sdk->send_whatsapp_template($telefono, $template_name, $params);
```

#### Webhooks

```php
// Registrar
$sdk->register_webhook($event, $url);

// Listar
$sdk->list_webhooks();

// Eliminar
$sdk->delete_webhook($id);

// Verificar firma
$sdk->verify_webhook_signature($payload, $signature);
```

---

## ⚡ JavaScript SDK

### Instalación

```html
<!-- Opción 1: Directamente en HTML -->
<script src="path/to/wpcw-sdk.js"></script>

<!-- Opción 2: ES6 Module (próximamente) -->
<script type="module">
  import WPCuponWhatsappSDK from './wpcw-sdk.js';
</script>

<!-- Opción 3: NPM (próximamente) -->
<!-- npm install wp-cupon-whatsapp-sdk -->
```

### Uso Básico

```javascript
// Inicializar SDK
const sdk = new WPCuponWhatsappSDK({
    apiUrl: 'https://tusitio.com',
    apiKey: 'tu_api_key',
    apiSecret: 'tu_api_secret',
    debug: true // Opcional
});

// Crear beneficiario
const beneficiario = await sdk.createBeneficiario({
    institucion_id: 1,
    nombre_completo: 'Juan Pérez',
    telefono_whatsapp: '+54 9 11 1234-5678',
    email: 'juan@example.com'
});

// Obtener cupones disponibles
const cupones = await sdk.getCuponesByBeneficiario(beneficiario.data.id);

// Validar cupón
const validacion = await sdk.validateCupon('DESCUENTO20', beneficiario.data.id);

if (validacion.valid) {
    // Registrar canje
    const canje = await sdk.createCanje({
        beneficiario_id: beneficiario.data.id,
        cupon_id: cupones.data[0].id,
        tipo_canje: 'online',
        descuento_aplicado: 300.00,
        monto_original: 1500.00,
        monto_final: 1200.00
    });
}
```

### Métodos Disponibles

Los métodos son equivalentes al PHP SDK, usando camelCase:

```javascript
// Beneficiarios
sdk.createBeneficiario(data)
sdk.getBeneficiario(id)
sdk.getBeneficiarioByPhone(telefono)
sdk.listBeneficiarios(filters)
sdk.updateBeneficiario(id, data)
sdk.deactivateBeneficiario(id)

// Cupones
sdk.createCupon(data)
sdk.getCupon(codigo)
sdk.listCupones(filters)
sdk.getCuponesByBeneficiario(beneficiarioId)
sdk.validateCupon(codigo, beneficiarioId)

// Canjes
sdk.createCanje(data)
sdk.getCanje(id)
sdk.listCanjes(filters)
sdk.getHistorialBeneficiario(beneficiarioId, filters)

// Instituciones
sdk.getInstitucion(id)
sdk.listInstituciones()
sdk.getStatsInstitucion(id, filters)

// WhatsApp
sdk.sendWhatsapp(telefono, mensaje)
sdk.sendWhatsappTemplate(telefono, templateName, params)

// Webhooks
sdk.registerWebhook(event, url)
sdk.listWebhooks()
sdk.deleteWebhook(id)
```

---

## 📚 DOCUMENTACIÓN API

Ver [API_DOCUMENTATION.md](API_DOCUMENTATION.md) para documentación completa de la API REST.

### Endpoints Principales

- **GET** `/beneficiarios` - Listar beneficiarios
- **POST** `/beneficiarios` - Crear beneficiario
- **GET** `/beneficiarios/{id}` - Obtener beneficiario
- **PUT** `/beneficiarios/{id}` - Actualizar beneficiario
- **GET** `/cupones` - Listar cupones
- **POST** `/cupones/validate` - Validar cupón
- **POST** `/canjes` - Registrar canje
- **GET** `/instituciones/{id}/stats` - Estadísticas

---

## 📋 EJEMPLOS

### PHP

```bash
# Ejecutar ejemplo completo
php examples/example-php-complete.php
```

Ver [examples/example-php-complete.php](examples/example-php-complete.php) para código completo.

### JavaScript

Abrir `examples/example-javascript-complete.html` en tu navegador.

Ver [examples/example-javascript-complete.html](examples/example-javascript-complete.html) para código completo.

---

## 🔐 AUTENTICACIÓN

### Obtener Credenciales

1. Ingresá al panel de WordPress
2. Andá a **Dashboard → WP Cupón WA → Configuración → API**
3. Hacé clic en "Generar nuevas credenciales"
4. Copiá tu **API Key** y **API Secret**

### Generar Token JWT

El SDK genera automáticamente tokens JWT. Si necesitás generar uno manualmente:

```javascript
// JavaScript
const header = btoa(JSON.stringify({ typ: 'JWT', alg: 'HS256' }));
const payload = btoa(JSON.stringify({
    iss: apiKey,
    iat: Math.floor(Date.now() / 1000),
    exp: Math.floor(Date.now() / 1000) + 3600
}));

const signature = await crypto.subtle.sign(
    'HMAC',
    cryptoKey,
    new TextEncoder().encode(`${header}.${payload}`)
);

const token = `${header}.${payload}.${btoa(signature)}`;
```

### Headers Requeridos

```
Authorization: Bearer {JWT_TOKEN}
X-API-Key: {API_KEY}
Content-Type: application/json
```

---

## ⏱️ RATE LIMITING

### Límites por Plan

| Plan | Requests/Minuto | Requests/Hora |
|------|-----------------|---------------|
| Free | 60 | 1,000 |
| Pro | 300 | 10,000 |
| Enterprise | 1,000 | 50,000 |

### Manejo de Rate Limit

El SDK maneja automáticamente los errores de rate limit:

```php
try {
    $result = $sdk->create_beneficiario($data);
} catch (\Exception $e) {
    if ($e->getCode() === 429) {
        // Rate limit excedido
        echo "Esperá 60 segundos e intentá nuevamente";
    }
}
```

---

## 🔔 WEBHOOKS

### Eventos Disponibles

- `beneficiario.created` - Nuevo beneficiario creado
- `beneficiario.updated` - Beneficiario actualizado
- `beneficiario.deactivated` - Beneficiario desactivado
- `cupon.created` - Nuevo cupón creado
- `cupon.updated` - Cupón actualizado
- `canje.created` - Nuevo canje registrado
- `canje.validated` - Canje validado presencialmente

### Registrar Webhook

```php
$webhook = $sdk->register_webhook(
    'canje.created',
    'https://mi-app.com/webhook/canje'
);

// Guardá el secret
$secret = $webhook['data']['secret'];
```

### Verificar Firma

```php
// En tu endpoint
$payload = file_get_contents('php://input');
$signature = $_SERVER['HTTP_X_WEBHOOK_SIGNATURE'];

if ($sdk->verify_webhook_signature($payload, $signature)) {
    $event = json_decode($payload, true);
    // Procesar evento
}
```

### Payload de Webhook

```json
{
  "event": "canje.created",
  "timestamp": "2025-10-10T15:20:00Z",
  "data": {
    "id": 1234,
    "beneficiario_id": 1,
    "cupon_id": 10,
    "tipo_canje": "online",
    "descuento_aplicado": 300.00
  }
}
```

---

## 🐛 DEBUG

### Activar Modo Debug

```php
// PHP
$sdk = new WPCuponWhatsappSDK($url, $key, $secret, ['debug' => true]);

// Ver log
$log = $sdk->get_log();
print_r($log);
```

```javascript
// JavaScript
const sdk = new WPCuponWhatsappSDK({
    apiUrl: '...',
    apiKey: '...',
    apiSecret: '...',
    debug: true
});

// Ver log
console.log(sdk.getLog());
```

---

## ❓ PREGUNTAS FRECUENTES

### ¿Cómo obtengo las credenciales?

Desde el panel de WordPress: **Dashboard → WP Cupón WA → Configuración → API**

### ¿Los SDKs funcionan con JavaScript vanilla?

Sí, el SDK JavaScript no tiene dependencias externas.

### ¿Puedo usar el SDK en Node.js?

Sí, el SDK JavaScript es compatible con Node.js y navegadores.

### ¿Hay límite de requests?

Sí, ver sección [Rate Limiting](#rate-limiting).

### ¿Cómo manejo errores?

Todos los métodos del SDK lanzan excepciones en caso de error. Usá try/catch.

### ¿El SDK es thread-safe?

El PHP SDK es stateless y thread-safe. Podés usarlo en aplicaciones multithreaded.

---

## 🔄 ACTUALIZACIONES

### v1.0.0 (Octubre 2025)

- ✅ Release inicial
- ✅ PHP SDK completo
- ✅ JavaScript SDK completo
- ✅ Documentación API REST
- ✅ Ejemplos de integración
- ✅ Sistema de webhooks
- ✅ Autenticación JWT

### Roadmap

**v1.1.0** (Q4 2025):
- Soporte TypeScript
- SDK Python
- SDK Ruby
- Paginación automática
- Cache de tokens

**v1.2.0** (Q1 2026):
- SDK para React Native
- SDK para Flutter
- Batch operations
- GraphQL API (opcional)

---

## 📞 SOPORTE

### Documentación

- **API Docs**: [API_DOCUMENTATION.md](API_DOCUMENTATION.md)
- **Ejemplos**: [examples/](examples/)
- **Changelog**: [CHANGELOG.md](CHANGELOG.md)

### Contacto

- **Email**: soporte@pragmatic-solutions.com
- **GitHub**: https://github.com/pragmatic-solutions/wp-cupon-whatsapp
- **Docs Online**: https://docs.pragmatic-solutions.com.ar

### Reportar Bugs

https://github.com/pragmatic-solutions/wp-cupon-whatsapp/issues

---

## 📄 LICENCIA

MIT License

Copyright © 2025 Pragmatic Solutions - Innovación Aplicada

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.

---

**WP Cupón WhatsApp SDK v1.0.0**
**Desarrollado por Pragmatic Solutions - Innovación Aplicada**
**Equipo: $15.050B patrimonio combinado | 450+ años experiencia | 80+ exits**

---
