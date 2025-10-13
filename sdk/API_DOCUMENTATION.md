# 📡 WP CUPÓN WHATSAPP - DOCUMENTACIÓN API REST

**Versión API:** v1
**Base URL:** `https://tusitio.com/wp-json/wpcw/v1`
**Autenticación:** JWT Bearer Token
**Formato:** JSON

**Desarrollado por:**
- @API - Carlos Mendoza ($820M) - Lead API Integration
- @SECURITY - James O'Connor ($850M) - Security & Auth
- @CTO - Dr. Viktor Petrov ($1.680B) - Architecture

---

## 📋 TABLA DE CONTENIDOS

1. [Autenticación](#autenticación)
2. [Endpoints de Beneficiarios](#endpoints-de-beneficiarios)
3. [Endpoints de Cupones](#endpoints-de-cupones)
4. [Endpoints de Canjes](#endpoints-de-canjes)
5. [Endpoints de Instituciones](#endpoints-de-instituciones)
6. [Endpoints de WhatsApp](#endpoints-de-whatsapp)
7. [Endpoints de Webhooks](#endpoints-de-webhooks)
8. [Códigos de Error](#códigos-de-error)
9. [Rate Limiting](#rate-limiting)
10. [Ejemplos de Uso](#ejemplos-de-uso)

---

## 🔐 AUTENTICACIÓN

### Obtener credenciales

Las credenciales (API Key y API Secret) se generan desde el panel de administración:

**Dashboard → WP Cupón WA → Configuración → API**

### Generar Token JWT

Todos los requests deben incluir un token JWT en el header `Authorization`.

**Header:**
```
Authorization: Bearer {JWT_TOKEN}
X-API-Key: {API_KEY}
```

**Estructura del JWT:**

```javascript
// Header
{
  "typ": "JWT",
  "alg": "HS256"
}

// Payload
{
  "iss": "{API_KEY}",
  "iat": 1234567890,  // Timestamp actual
  "exp": 1234571490   // Expira en 1 hora
}

// Signature
HMACSHA256(
  base64UrlEncode(header) + "." + base64UrlEncode(payload),
  {API_SECRET}
)
```

**Token completo:**
```
eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJhcGlfa2V5IiwiaWF0IjoxMjM0NTY3ODkwLCJleHAiOjEyMzQ1NzE0OTB9.signature
```

### Ejemplo con PHP:

```php
$sdk = new \WPCuponWhatsapp\SDK\WPCuponWhatsappSDK(
    'https://tusitio.com',
    'tu_api_key',
    'tu_api_secret'
);
```

### Ejemplo con JavaScript:

```javascript
const sdk = new WPCuponWhatsappSDK({
    apiUrl: 'https://tusitio.com',
    apiKey: 'tu_api_key',
    apiSecret: 'tu_api_secret'
});
```

---

## 👤 ENDPOINTS DE BENEFICIARIOS

### GET `/beneficiarios`

Lista todos los beneficiarios.

**Headers:**
```
Authorization: Bearer {JWT_TOKEN}
X-API-Key: {API_KEY}
```

**Query Params:**
| Parámetro | Tipo | Descripción |
|-----------|------|-------------|
| `page` | int | Página (default: 1) |
| `per_page` | int | Resultados por página (default: 20, max: 100) |
| `search` | string | Buscar por nombre, documento o teléfono |
| `institucion_id` | int | Filtrar por institución |
| `estado` | string | Filtrar por estado (activa, inactiva, suspendida) |
| `orderby` | string | Ordenar por (fecha_adhesion, nombre_completo) |
| `order` | string | ASC o DESC |

**Response 200:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "institucion_id": 1,
      "user_id": 123,
      "nombre_completo": "Juan Pérez",
      "numero_documento": "12345678",
      "tipo_documento": "DNI",
      "telefono_whatsapp": "+54 9 11 1234-5678",
      "email": "juan@example.com",
      "codigo_beneficiario": "BEN-ABC123XY",
      "estado": "activa",
      "fecha_adhesion": "2025-10-01 10:30:00",
      "metadata": {}
    }
  ],
  "pagination": {
    "total": 150,
    "total_pages": 8,
    "current_page": 1,
    "per_page": 20
  }
}
```

---

### GET `/beneficiarios/{id}`

Obtiene un beneficiario específico por ID.

**Response 200:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "institucion_id": 1,
    "user_id": 123,
    "nombre_completo": "Juan Pérez",
    "numero_documento": "12345678",
    "tipo_documento": "DNI",
    "telefono_whatsapp": "+54 9 11 1234-5678",
    "email": "juan@example.com",
    "codigo_beneficiario": "BEN-ABC123XY",
    "estado": "activa",
    "fecha_adhesion": "2025-10-01 10:30:00",
    "metadata": {},
    "stats": {
      "total_canjes": 15,
      "ahorro_total": 4500.00,
      "ultimo_canje": "2025-10-10 15:20:00"
    }
  }
}
```

---

### POST `/beneficiarios`

Crea un nuevo beneficiario.

**Body:**
```json
{
  "institucion_id": 1,
  "nombre_completo": "Juan Pérez",
  "tipo_documento": "DNI",
  "numero_documento": "12345678",
  "telefono_whatsapp": "+54 9 11 1234-5678",
  "email": "juan@example.com"
}
```

**Campos requeridos:** `institucion_id`, `nombre_completo`, `telefono_whatsapp`

**Response 201:**
```json
{
  "success": true,
  "message": "Beneficiario creado exitosamente",
  "data": {
    "id": 151,
    "codigo_beneficiario": "BEN-XYZ789AB",
    "whatsapp_sent": true
  }
}
```

---

### PUT `/beneficiarios/{id}`

Actualiza un beneficiario existente.

**Body:**
```json
{
  "nombre_completo": "Juan Carlos Pérez",
  "email": "juancarlos@example.com",
  "estado": "activa"
}
```

**Response 200:**
```json
{
  "success": true,
  "message": "Beneficiario actualizado exitosamente",
  "data": {
    "id": 1,
    "updated_fields": ["nombre_completo", "email"]
  }
}
```

---

### POST `/beneficiarios/{id}/deactivate`

Desactiva un beneficiario.

**Response 200:**
```json
{
  "success": true,
  "message": "Beneficiario desactivado exitosamente"
}
```

---

### GET `/beneficiarios/{id}/cupones`

Obtiene cupones disponibles para un beneficiario.

**Response 200:**
```json
{
  "success": true,
  "data": [
    {
      "id": 10,
      "codigo": "DESCUENTO20",
      "tipo_descuento": "percent",
      "monto": 20,
      "nombre": "20% OFF en Restaurant X",
      "descripcion": "Descuento en todo el menú",
      "comercio": {
        "id": 5,
        "nombre": "Restaurant X"
      },
      "fecha_expiracion": "2025-12-31",
      "ya_usado": false
    }
  ]
}
```

---

### GET `/beneficiarios/{id}/historial`

Obtiene historial de canjes de un beneficiario.

**Query Params:**
| Parámetro | Tipo | Descripción |
|-----------|------|-------------|
| `fecha_desde` | date | Fecha inicial (YYYY-MM-DD) |
| `fecha_hasta` | date | Fecha final (YYYY-MM-DD) |
| `tipo_canje` | string | Filtrar por tipo (online, presencial, whatsapp) |
| `limit` | int | Límite de resultados (default: 50) |

**Response 200:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1234,
      "cupon_codigo": "DESCUENTO20",
      "comercio_nombre": "Restaurant X",
      "fecha_canje": "2025-10-10 15:20:00",
      "tipo_canje": "online",
      "descuento_aplicado": 300.00,
      "monto_original": 1500.00,
      "monto_final": 1200.00
    }
  ],
  "stats": {
    "total_canjes": 15,
    "ahorro_total": 4500.00
  }
}
```

---

## 🎫 ENDPOINTS DE CUPONES

### GET `/cupones`

Lista todos los cupones disponibles.

**Query Params:**
| Parámetro | Tipo | Descripción |
|-----------|------|-------------|
| `institucion_id` | int | Filtrar por institución |
| `comercio_id` | int | Filtrar por comercio |
| `tipo_beneficio` | string | Filtrar por tipo |
| `activos` | bool | Solo cupones activos (default: true) |
| `vigentes` | bool | Solo cupones no vencidos (default: true) |

**Response 200:**
```json
{
  "success": true,
  "data": [
    {
      "id": 10,
      "codigo": "DESCUENTO20",
      "tipo_descuento": "percent",
      "monto": 20,
      "nombre": "20% OFF en Restaurant X",
      "descripcion": "Descuento en todo el menú",
      "comercio": {
        "id": 5,
        "nombre": "Restaurant X",
        "logo": "https://example.com/logo.jpg"
      },
      "institucion_id": 1,
      "tipo_beneficio": "descuento_porcentaje",
      "fecha_expiracion": "2025-12-31",
      "usos_maximos": 1,
      "restricciones": {
        "monto_minimo": 500,
        "categorias_permitidas": ["comida", "bebidas"]
      }
    }
  ]
}
```

---

### GET `/cupones/{codigo}`

Obtiene detalles de un cupón por código.

**Response 200:**
```json
{
  "success": true,
  "data": {
    "id": 10,
    "codigo": "DESCUENTO20",
    "tipo_descuento": "percent",
    "monto": 20,
    "nombre": "20% OFF en Restaurant X",
    "descripcion": "Descuento válido en todo el menú. No acumulable con otras promociones.",
    "comercio": {
      "id": 5,
      "nombre": "Restaurant X",
      "direccion": "Av. Corrientes 1234, CABA",
      "telefono": "+54 11 4567-8901",
      "logo": "https://example.com/logo.jpg"
    },
    "institucion_id": 1,
    "tipo_beneficio": "descuento_porcentaje",
    "fecha_expiracion": "2025-12-31",
    "usos_maximos": 1,
    "usos_actuales": 87,
    "restricciones": {
      "monto_minimo": 500,
      "categorias_permitidas": ["comida", "bebidas"],
      "dias_validos": ["lunes", "martes", "miercoles", "jueves", "viernes"]
    }
  }
}
```

---

### POST `/cupones`

Crea un nuevo cupón.

**Body:**
```json
{
  "codigo": "DESCUENTO30",
  "tipo_descuento": "percent",
  "monto": 30,
  "nombre": "30% OFF Especial",
  "descripcion": "Descuento especial para beneficiarios",
  "institucion_id": 1,
  "comercio_id": 5,
  "tipo_beneficio": "descuento_porcentaje",
  "fecha_expiracion": "2025-12-31",
  "usos_maximos": 1,
  "restricciones": {
    "monto_minimo": 1000
  }
}
```

**Campos requeridos:** `codigo`, `tipo_descuento`, `monto`, `institucion_id`

**Response 201:**
```json
{
  "success": true,
  "message": "Cupón creado exitosamente",
  "data": {
    "id": 25,
    "codigo": "DESCUENTO30"
  }
}
```

---

### POST `/cupones/validate`

Valida si un cupón puede ser usado por un beneficiario.

**Body:**
```json
{
  "codigo": "DESCUENTO20",
  "beneficiario_id": 1
}
```

**Response 200 (Cupón válido):**
```json
{
  "success": true,
  "valid": true,
  "data": {
    "codigo": "DESCUENTO20",
    "monto": 20,
    "tipo_descuento": "percent",
    "message": "Cupón válido. Descuento: 20%"
  }
}
```

**Response 200 (Cupón inválido):**
```json
{
  "success": true,
  "valid": false,
  "reason": "already_used",
  "message": "Ya utilizaste este cupón anteriormente"
}
```

**Razones de invalidez:**
- `already_used` - Cupón ya usado por el beneficiario
- `expired` - Cupón vencido
- `max_usage_reached` - Cupón alcanzó uso máximo
- `invalid_institution` - Institución no coincide
- `inactive_beneficiary` - Beneficiario inactivo
- `not_found` - Cupón no existe

---

## 📊 ENDPOINTS DE CANJES

### GET `/canjes`

Lista todos los canjes.

**Query Params:**
| Parámetro | Tipo | Descripción |
|-----------|------|-------------|
| `beneficiario_id` | int | Filtrar por beneficiario |
| `comercio_id` | int | Filtrar por comercio |
| `tipo_canje` | string | Filtrar por tipo (online, presencial, whatsapp) |
| `fecha_desde` | date | Fecha inicial (YYYY-MM-DD) |
| `fecha_hasta` | date | Fecha final (YYYY-MM-DD) |
| `page` | int | Página (default: 1) |
| `per_page` | int | Resultados por página (default: 50) |

**Response 200:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1234,
      "beneficiario": {
        "id": 1,
        "nombre": "Juan Pérez",
        "codigo": "BEN-ABC123XY"
      },
      "cupon": {
        "id": 10,
        "codigo": "DESCUENTO20"
      },
      "comercio": {
        "id": 5,
        "nombre": "Restaurant X"
      },
      "fecha_canje": "2025-10-10 15:20:00",
      "tipo_canje": "online",
      "metodo_validacion": "codigo",
      "descuento_aplicado": 300.00,
      "monto_original": 1500.00,
      "monto_final": 1200.00,
      "order_id": 5678,
      "validado_por": {
        "id": 2,
        "nombre": "Supervisor Admin"
      }
    }
  ],
  "pagination": {
    "total": 500,
    "current_page": 1,
    "total_pages": 10
  }
}
```

---

### GET `/canjes/{id}`

Obtiene detalles de un canje específico.

**Response 200:**
```json
{
  "success": true,
  "data": {
    "id": 1234,
    "beneficiario": {
      "id": 1,
      "nombre_completo": "Juan Pérez",
      "codigo_beneficiario": "BEN-ABC123XY",
      "telefono_whatsapp": "+54 9 11 1234-5678"
    },
    "cupon": {
      "id": 10,
      "codigo": "DESCUENTO20",
      "nombre": "20% OFF en Restaurant X",
      "tipo_descuento": "percent",
      "monto": 20
    },
    "comercio": {
      "id": 5,
      "nombre": "Restaurant X",
      "direccion": "Av. Corrientes 1234, CABA"
    },
    "fecha_canje": "2025-10-10 15:20:00",
    "tipo_canje": "online",
    "metodo_validacion": "codigo",
    "descuento_aplicado": 300.00,
    "monto_original": 1500.00,
    "monto_final": 1200.00,
    "order_id": 5678,
    "validado_por": {
      "id": 2,
      "nombre": "Supervisor Admin"
    },
    "metadata": {
      "ip_address": "192.168.1.1",
      "user_agent": "Mozilla/5.0..."
    }
  }
}
```

---

### POST `/canjes`

Registra un nuevo canje.

**Body:**
```json
{
  "beneficiario_id": 1,
  "cupon_id": 10,
  "tipo_canje": "presencial",
  "metodo_validacion": "qr",
  "comercio_id": 5,
  "descuento_aplicado": 300.00,
  "monto_original": 1500.00,
  "monto_final": 1200.00
}
```

**Campos requeridos:** `beneficiario_id`, `cupon_id`, `tipo_canje`

**Response 201:**
```json
{
  "success": true,
  "message": "Canje registrado exitosamente",
  "data": {
    "id": 1235,
    "whatsapp_sent": true
  }
}
```

---

## 🏢 ENDPOINTS DE INSTITUCIONES

### GET `/instituciones`

Lista todas las instituciones activas.

**Response 200:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "nombre": "Sindicato Ejemplo",
      "identificador": "sindicato-ejemplo",
      "logo_url": "https://example.com/logo.png",
      "telefono_soporte": "+54 11 1234-5678",
      "email_contacto": "info@sindicato.com",
      "estado": "activa",
      "fecha_registro": "2025-01-01",
      "stats": {
        "total_beneficiarios": 1500,
        "total_canjes": 4200,
        "ahorro_generado": 125000.00
      }
    }
  ]
}
```

---

### GET `/instituciones/{id}`

Obtiene detalles de una institución.

**Response 200:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "nombre": "Sindicato Ejemplo",
    "identificador": "sindicato-ejemplo",
    "logo_url": "https://example.com/logo.png",
    "telefono_soporte": "+54 11 1234-5678",
    "email_contacto": "info@sindicato.com",
    "whatsapp_phone_id": "123456789",
    "estado": "activa",
    "fecha_registro": "2025-01-01",
    "ultima_actualizacion": "2025-10-10",
    "metadata": {}
  }
}
```

---

### GET `/instituciones/{id}/stats`

Obtiene estadísticas de una institución.

**Query Params:**
| Parámetro | Tipo | Descripción |
|-----------|------|-------------|
| `fecha_desde` | date | Fecha inicial (YYYY-MM-DD) |
| `fecha_hasta` | date | Fecha final (YYYY-MM-DD) |

**Response 200:**
```json
{
  "success": true,
  "data": {
    "beneficiarios": {
      "total": 1500,
      "activos": 1450,
      "inactivos": 30,
      "suspendidos": 20,
      "nuevos_periodo": 45
    },
    "cupones": {
      "total": 25,
      "activos": 20,
      "vencidos": 5,
      "mas_usados": [
        {
          "codigo": "DESCUENTO20",
          "nombre": "20% OFF Restaurant X",
          "total_usos": 150
        }
      ]
    },
    "canjes": {
      "total": 4200,
      "online": 2100,
      "presencial": 1500,
      "whatsapp": 600,
      "periodo": 380
    },
    "ahorro": {
      "total": 125000.00,
      "periodo": 12500.00,
      "promedio_por_canje": 29.76
    },
    "comercios": {
      "total": 15,
      "top_5": [
        {
          "nombre": "Restaurant X",
          "total_canjes": 450,
          "ahorro_generado": 18000.00
        }
      ]
    }
  }
}
```

---

## 💬 ENDPOINTS DE WHATSAPP

### POST `/whatsapp/send`

Envía un mensaje de WhatsApp.

**Body:**
```json
{
  "telefono": "+54 9 11 1234-5678",
  "mensaje": "Hola! Tenés un nuevo cupón disponible."
}
```

**Response 200:**
```json
{
  "success": true,
  "message": "Mensaje enviado exitosamente",
  "data": {
    "whatsapp_message_id": "wamid.ABCxyz123...",
    "telefono": "+54 9 11 1234-5678"
  }
}
```

---

### POST `/whatsapp/send-template`

Envía un template de WhatsApp.

**Body:**
```json
{
  "telefono": "+54 9 11 1234-5678",
  "template": "bienvenida_beneficiario",
  "params": {
    "nombre": "Juan",
    "institucion": "Sindicato Ejemplo"
  }
}
```

**Response 200:**
```json
{
  "success": true,
  "message": "Template enviado exitosamente",
  "data": {
    "whatsapp_message_id": "wamid.ABCxyz123...",
    "telefono": "+54 9 11 1234-5678",
    "template": "bienvenida_beneficiario"
  }
}
```

---

## 🔔 ENDPOINTS DE WEBHOOKS

### GET `/webhooks`

Lista webhooks registrados.

**Response 200:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "event": "beneficiario.created",
      "url": "https://mi-app.com/webhook/beneficiario",
      "created_at": "2025-10-01",
      "last_triggered": "2025-10-10 10:30:00",
      "active": true
    }
  ]
}
```

---

### POST `/webhooks`

Registra un nuevo webhook.

**Body:**
```json
{
  "event": "canje.created",
  "url": "https://mi-app.com/webhook/canje"
}
```

**Eventos disponibles:**
- `beneficiario.created`
- `beneficiario.updated`
- `beneficiario.deactivated`
- `cupon.created`
- `cupon.updated`
- `canje.created`
- `canje.validated`

**Response 201:**
```json
{
  "success": true,
  "message": "Webhook registrado exitosamente",
  "data": {
    "id": 2,
    "event": "canje.created",
    "url": "https://mi-app.com/webhook/canje",
    "secret": "whsec_abc123xyz789"
  }
}
```

**Importante:** Guardá el `secret`, se usa para verificar la firma de los webhooks.

---

### DELETE `/webhooks/{id}`

Elimina un webhook.

**Response 200:**
```json
{
  "success": true,
  "message": "Webhook eliminado exitosamente"
}
```

---

## ❌ CÓDIGOS DE ERROR

### Respuestas de Error

Todas las respuestas de error siguen este formato:

```json
{
  "success": false,
  "error": {
    "code": "invalid_request",
    "message": "Campo requerido faltante: nombre_completo",
    "details": {
      "field": "nombre_completo"
    }
  }
}
```

### Códigos HTTP

| Código | Significado |
|--------|-------------|
| 200 | OK - Request exitoso |
| 201 | Created - Recurso creado |
| 400 | Bad Request - Request inválido |
| 401 | Unauthorized - Autenticación requerida |
| 403 | Forbidden - No tienes permisos |
| 404 | Not Found - Recurso no encontrado |
| 422 | Unprocessable Entity - Validación falló |
| 429 | Too Many Requests - Rate limit excedido |
| 500 | Internal Server Error - Error del servidor |

### Códigos de Error Específicos

| Código | Descripción |
|--------|-------------|
| `invalid_auth` | Token JWT inválido o expirado |
| `missing_credentials` | Faltan API Key o Secret |
| `invalid_request` | Request mal formado |
| `validation_failed` | Datos de validación fallaron |
| `resource_not_found` | Recurso no encontrado |
| `already_exists` | Recurso ya existe (duplicado) |
| `permission_denied` | Sin permisos para esta operación |
| `rate_limit_exceeded` | Límite de requests excedido |
| `coupon_already_used` | Cupón ya usado |
| `coupon_expired` | Cupón vencido |
| `inactive_beneficiary` | Beneficiario inactivo |

---

## ⏱️ RATE LIMITING

### Límites

| Tipo de Cliente | Requests por Minuto | Requests por Hora |
|-----------------|---------------------|-------------------|
| **Free** | 60 | 1,000 |
| **Pro** | 300 | 10,000 |
| **Enterprise** | 1,000 | 50,000 |

### Headers de Rate Limit

Cada response incluye headers con información del rate limit:

```
X-RateLimit-Limit: 60
X-RateLimit-Remaining: 45
X-RateLimit-Reset: 1634567890
```

### Response cuando se excede el límite:

```json
{
  "success": false,
  "error": {
    "code": "rate_limit_exceeded",
    "message": "Has excedido el límite de requests. Intenta nuevamente en 30 segundos.",
    "retry_after": 30
  }
}
```

---

## 💡 EJEMPLOS DE USO

### Ejemplo 1: Crear beneficiario y obtener sus cupones

```php
<?php
use WPCuponWhatsapp\SDK\WPCuponWhatsappSDK;

$sdk = new WPCuponWhatsappSDK(
    'https://tusitio.com',
    'tu_api_key',
    'tu_api_secret'
);

// Crear beneficiario
$beneficiario = $sdk->create_beneficiario([
    'institucion_id' => 1,
    'nombre_completo' => 'María González',
    'telefono_whatsapp' => '+54 9 11 9876-5432',
    'email' => 'maria@example.com',
    'tipo_documento' => 'DNI',
    'numero_documento' => '98765432'
]);

echo "Beneficiario creado: " . $beneficiario['data']['codigo_beneficiario'] . "\n";

// Obtener cupones disponibles
$cupones = $sdk->get_cupones_by_beneficiario($beneficiario['data']['id']);

echo "Cupones disponibles: " . count($cupones['data']) . "\n";
?>
```

### Ejemplo 2: Validar y registrar un canje

```javascript
const sdk = new WPCuponWhatsappSDK({
    apiUrl: 'https://tusitio.com',
    apiKey: 'tu_api_key',
    apiSecret: 'tu_api_secret'
});

async function procesarCanje() {
    try {
        // Validar cupón
        const validacion = await sdk.validateCupon('DESCUENTO20', 1);

        if (!validacion.valid) {
            console.error('Cupón inválido:', validacion.message);
            return;
        }

        // Registrar canje
        const canje = await sdk.createCanje({
            beneficiario_id: 1,
            cupon_id: 10,
            tipo_canje: 'online',
            descuento_aplicado: 300.00,
            monto_original: 1500.00,
            monto_final: 1200.00
        });

        console.log('Canje registrado:', canje.data.id);
        console.log('WhatsApp enviado:', canje.data.whatsapp_sent);

    } catch (error) {
        console.error('Error:', error.message);
    }
}

procesarCanje();
```

### Ejemplo 3: Obtener estadísticas de institución

```php
<?php
$sdk = new WPCuponWhatsappSDK(...);

// Estadísticas del último mes
$stats = $sdk->get_stats_institucion(1, [
    'fecha_desde' => '2025-09-10',
    'fecha_hasta' => '2025-10-10'
]);

echo "Canjes del mes: " . $stats['data']['canjes']['periodo'] . "\n";
echo "Ahorro generado: $" . $stats['data']['ahorro']['periodo'] . "\n";
echo "Nuevos beneficiarios: " . $stats['data']['beneficiarios']['nuevos_periodo'] . "\n";
?>
```

### Ejemplo 4: Configurar webhooks

```javascript
// Registrar webhook
const webhook = await sdk.registerWebhook(
    'canje.created',
    'https://mi-app.com/webhook/canje'
);

console.log('Webhook ID:', webhook.data.id);
console.log('Secret:', webhook.data.secret); // Guardar este valor

// En tu servidor, verificar firma del webhook
const crypto = require('crypto');

function verifyWebhookSignature(payload, signature, secret) {
    const hmac = crypto.createHmac('sha256', secret);
    hmac.update(payload);
    const expected = hmac.digest('hex');

    return crypto.timingSafeEqual(
        Buffer.from(signature),
        Buffer.from(expected)
    );
}

// Express.js endpoint
app.post('/webhook/canje', (req, res) => {
    const signature = req.headers['x-webhook-signature'];
    const payload = JSON.stringify(req.body);

    if (!verifyWebhookSignature(payload, signature, webhook.data.secret)) {
        return res.status(401).send('Invalid signature');
    }

    // Procesar evento
    console.log('Nuevo canje:', req.body);
    res.status(200).send('OK');
});
```

---

## 📚 RECURSOS ADICIONALES

### SDKs Oficiales

- **PHP SDK**: `/sdk/php/WPCuponWhatsappSDK.php`
- **JavaScript SDK**: `/sdk/javascript/wpcw-sdk.js`

### Ejemplos Completos

Ver carpeta `/sdk/examples/` para más ejemplos de integración.

### Soporte

- **Email**: soporte@pragmatic-solutions.com
- **Documentación**: https://docs.pragmatic-solutions.com.ar
- **GitHub**: https://github.com/pragmatic-solutions/wp-cupon-whatsapp

---

**Documentación API v1**
**© 2025 Pragmatic Solutions - Innovación Aplicada**
**Desarrollado por @API, @SECURITY, @CTO**

---
