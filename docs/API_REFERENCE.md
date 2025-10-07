# 🔗 API Reference - WP Cupón WhatsApp

## 📡 REST API Endpoints

### **Base URL**
```
https://yoursite.com/wp-json/wpcw/v1/
```

### **Autenticación**
- **Tipo**: WordPress Nonces + User Authentication
- **Headers requeridos**:
  ```
  X-WP-Nonce: [nonce_value]
  Authorization: Bearer [token] (opcional)
  Content-Type: application/json
  ```

---

## 🎫 **Cupones (Coupons)**

### **GET /wp-json/wpcw/v1/coupons**
Obtiene lista de cupones disponibles para el usuario.

#### **Parámetros de Query**
```json
{
  "user_id": 123,           // Opcional: ID del usuario (default: current user)
  "type": "loyalty",        // Opcional: "loyalty" | "public" | "all"
  "category": 456,          // Opcional: ID de categoría
  "business_id": 789,       // Opcional: ID del comercio
  "page": 1,                // Opcional: Página (default: 1)
  "per_page": 20,           // Opcional: Items por página (default: 20)
  "search": "descuento"     // Opcional: Búsqueda por título/descripción
}
```

#### **Respuesta Exitosa (200)**
```json
{
  "success": true,
  "data": {
    "coupons": [
      {
        "id": 123,
        "code": "DESCUENTO20",
        "title": "20% de descuento",
        "description": "Descuento especial en productos seleccionados",
        "discount_type": "percent",
        "amount": "20",
        "expiry_date": "2025-12-31",
        "business_name": "Tienda Ejemplo",
        "image_url": "https://example.com/wp-content/uploads/coupon-image.jpg",
        "can_redeem": true,
        "redemption_url": "https://wa.me/5491123456789?text=...",
        "categories": ["Electrónicos", "Tecnología"]
      }
    ],
    "pagination": {
      "total": 45,
      "total_pages": 3,
      "current_page": 1,
      "per_page": 20
    }
  }
}
```

#### **Errores Comunes**
- `401`: Usuario no autenticado
- `403`: Usuario no autorizado para ver cupones
- `404`: No se encontraron cupones

---

### **POST /wp-json/wpcw/v1/coupons/{id}/redeem**
Inicia el proceso de canje de un cupón.

#### **Parámetros de URL**
- `id`: ID del cupón a canjear

#### **Body (JSON)**
```json
{
  "business_id": 456,       // Opcional: ID del comercio específico
  "notes": "Comentarios adicionales"  // Opcional
}
```

#### **Respuesta Exitosa (200)**
```json
{
  "success": true,
  "data": {
    "redemption_id": 789,
    "redemption_number": "20250916-1234",
    "whatsapp_url": "https://wa.me/5491123456789?text=Hola%20quiero%20canjear...",
    "token": "abc123def456",
    "expires_in": 3600,
    "instructions": "Envía este mensaje por WhatsApp para confirmar el canje"
  }
}
```

#### **Errores Comunes**
- `400`: Datos inválidos o cupón no disponible
- `401`: Usuario no autenticado
- `403`: Usuario no puede canjear este cupón
- `404`: Cupón no encontrado
- `429`: Límite de canjes excedido

---

## 🏪 **Comercios (Businesses)**

### **GET /wp-json/wpcw/v1/businesses**
Obtiene lista de comercios registrados.

#### **Parámetros de Query**
```json
{
  "status": "active",       // Opcional: "active" | "inactive" | "all"
  "category": "restaurantes", // Opcional: Categoría del comercio
  "location": "ciudad",     // Opcional: Ubicación
  "search": "pizza",        // Opcional: Búsqueda
  "page": 1,                // Opcional
  "per_page": 20            // Opcional
}
```

#### **Respuesta Exitosa (200)**
```json
{
  "success": true,
  "data": {
    "businesses": [
      {
        "id": 123,
        "name": "Pizzería Donde Mario",
        "description": "Las mejores pizzas de la ciudad",
        "category": "Restaurantes",
        "address": "Av. Principal 123",
        "phone": "+5491123456789",
        "whatsapp": "+5491123456789",
        "email": "info@pizzeria.com",
        "logo_url": "https://example.com/logo.jpg",
        "status": "active",
        "total_coupons": 15,
        "active_coupons": 12
      }
    ],
    "pagination": {
      "total": 150,
      "total_pages": 8,
      "current_page": 1,
      "per_page": 20
    }
  }
}
```

---

### **GET /wp-json/wpcw/v1/businesses/{id}**
Obtiene detalles de un comercio específico.

#### **Respuesta Exitosa (200)**
```json
{
  "success": true,
  "data": {
    "business": {
      "id": 123,
      "name": "Pizzería Donde Mario",
      "description": "Las mejores pizzas de la ciudad desde 1995",
      "category": "Restaurantes",
      "address": "Av. Principal 123, Ciudad",
      "phone": "+5491123456789",
      "whatsapp": "+5491123456789",
      "email": "info@pizzeria.com",
      "website": "https://pizzeria.com",
      "logo_url": "https://example.com/logo.jpg",
      "status": "active",
      "owner": {
        "name": "Mario Rossi",
        "email": "mario@pizzeria.com"
      },
      "stats": {
        "total_coupons": 15,
        "active_coupons": 12,
        "total_redemptions": 234,
        "successful_redemptions": 215
      }
    }
  }
}
```

---

## 📊 **Estadísticas (Statistics)**

### **GET /wp-json/wpcw/v1/stats**
Obtiene estadísticas del sistema.

#### **Parámetros de Query**
```json
{
  "period": "month",        // Opcional: "day" | "week" | "month" | "year"
  "business_id": 123,       // Opcional: Filtrar por comercio
  "institution_id": 456,    // Opcional: Filtrar por institución
  "start_date": "2025-01-01", // Opcional: Fecha inicio
  "end_date": "2025-12-31"    // Opcional: Fecha fin
}
```

#### **Respuesta Exitosa (200)**
```json
{
  "success": true,
  "data": {
    "summary": {
      "total_coupons": 1250,
      "active_coupons": 890,
      "total_redemptions": 5670,
      "successful_redemptions": 5234,
      "total_businesses": 45,
      "active_businesses": 42,
      "total_users": 1234,
      "active_users": 987
    },
    "charts": {
      "redemptions_by_month": [
        {"month": "2025-01", "count": 450},
        {"month": "2025-02", "count": 520}
      ],
      "top_businesses": [
        {"name": "Tienda A", "redemptions": 234},
        {"name": "Tienda B", "redemptions": 198}
      ]
    }
  }
}
```

---

### **GET /wp-json/wpcw/v1/stats/business/{id}**
Estadísticas específicas de un comercio.

#### **Respuesta Exitosa (200)**
```json
{
  "success": true,
  "data": {
    "business_stats": {
      "total_coupons": 25,
      "active_coupons": 18,
      "total_redemptions": 456,
      "successful_redemptions": 423,
      "failed_redemptions": 33,
      "avg_response_time": "2.3",
      "redemption_rate": "92.8",
      "popular_coupons": [
        {"code": "DESC20", "redemptions": 45},
        {"code": "ENVIOGRATIS", "redemptions": 38}
      ]
    }
  }
}
```

---

## 🔐 **Confirmación de Canjes (Redemption Confirmation)**

### **GET /wp-json/wpcw/v1/confirm-redemption**
Confirma o rechaza un canje vía token.

#### **Parámetros de Query**
```json
{
  "token": "abc123def456",  // Requerido: Token de confirmación
  "action": "confirm"       // Requerido: "confirm" | "reject"
}
```

#### **Respuesta Exitosa (200)**
```html
<!DOCTYPE html>
<html>
<head>
    <title>Confirmación de Canje</title>
    <style>
        body { font-family: Arial, sans-serif; text-align: center; padding: 50px; }
        .success { color: green; }
        .error { color: red; }
    </style>
</head>
<body>
    <h1>✅ Canje Confirmado</h1>
    <p>El canje ha sido confirmado exitosamente.</p>
    <p><strong>Número de Canje:</strong> 20250916-1234</p>
    <p><strong>Código del Cupón:</strong> DESCUENTO20</p>
    <a href="https://wa.me/5491123456789?text=Canje%20confirmado">Enviar confirmación por WhatsApp</a>
</body>
</html>
```

#### **Errores Comunes**
- `400`: Token inválido o acción no permitida
- `404`: Canje no encontrado
- `409`: Canje ya procesado

---

## 👤 **Usuarios (Users)**

### **GET /wp-json/wpcw/v1/user/profile**
Obtiene perfil del usuario actual.

#### **Respuesta Exitosa (200)**
```json
{
  "success": true,
  "data": {
    "user": {
      "id": 123,
      "name": "Juan Pérez",
      "email": "juan@example.com",
      "whatsapp": "+5491123456789",
      "institution": {
        "id": 456,
        "name": "Universidad Nacional"
      },
      "stats": {
        "total_redemptions": 15,
        "successful_redemptions": 14,
        "favorite_categories": ["Electrónicos", "Ropa"]
      }
    }
  }
}
```

---

### **POST /wp-json/wpcw/v1/user/profile**
Actualiza perfil del usuario.

#### **Body (JSON)**
```json
{
  "whatsapp": "+5491123456789",
  "favorite_categories": [1, 2, 3]
}
```

#### **Respuesta Exitosa (200)**
```json
{
  "success": true,
  "message": "Perfil actualizado correctamente"
}
```

---

## 📋 **Aplicaciones (Applications)**

### **POST /wp-json/wpcw/v1/applications**
Envía una solicitud de adhesión.

#### **Body (JSON)**
```json
{
  "applicant_type": "comercio",  // "comercio" | "institucion"
  "fantasy_name": "Mi Comercio",
  "legal_name": "Mi Comercio S.A.",
  "cuit": "30123456789",
  "contact_person": "Juan Pérez",
  "email": "contacto@micomercio.com",
  "whatsapp": "+5491123456789",
  "address_main": "Calle Principal 123",
  "description": "Descripción del comercio",
  "recaptcha_token": "recaptcha_response"  // Si reCAPTCHA está habilitado
}
```

#### **Respuesta Exitosa (201)**
```json
{
  "success": true,
  "data": {
    "application_id": 789,
    "message": "Solicitud enviada correctamente. Nos pondremos en contacto pronto.",
    "status": "pendiente_revision"
  }
}
```

---

## 🔧 **Webhooks**

### **Configuración de Webhooks**
Los webhooks se pueden configurar en la página de configuración del plugin.

#### **Eventos Disponibles**
- `redemption.confirmed` - Canje confirmado
- `redemption.rejected` - Canje rechazado
- `coupon.created` - Cupón creado
- `business.approved` - Comercio aprobado
- `application.submitted` - Solicitud enviada

#### **Payload de Ejemplo**
```json
{
  "event": "redemption.confirmed",
  "timestamp": "2025-09-16T14:30:00Z",
  "data": {
    "redemption_id": 123,
    "redemption_number": "20250916-1234",
    "user_id": 456,
    "coupon_id": 789,
    "business_id": 101,
    "coupon_code": "DESCUENTO20",
    "amount": "20.00",
    "currency": "ARS"
  }
}
```

---

## 🚨 **Códigos de Error**

### **Códigos HTTP**
- `200` - OK
- `201` - Created
- `400` - Bad Request
- `401` - Unauthorized
- `403` - Forbidden
- `404` - Not Found
- `409` - Conflict
- `429` - Too Many Requests
- `500` - Internal Server Error

### **Códigos de Error de Aplicación**
```json
{
  "success": false,
  "error": {
    "code": "COUPON_NOT_AVAILABLE",
    "message": "El cupón no está disponible para canje",
    "details": {
      "coupon_id": 123,
      "reason": "expired"
    }
  }
}
```

### **Errores Comunes**
- `COUPON_EXPIRED` - Cupón expirado
- `COUPON_NOT_FOUND` - Cupón no encontrado
- `USER_NOT_VERIFIED` - Usuario no verificado
- `BUSINESS_INACTIVE` - Comercio inactivo
- `RATE_LIMIT_EXCEEDED` - Límite de solicitudes excedido
- `INVALID_TOKEN` - Token inválido

---

## 🔒 **Rate Limiting**

### **Límites por Endpoint**
- **GET /coupons**: 100 requests/minute
- **POST /coupons/{id}/redeem**: 10 requests/minute por usuario
- **GET /stats**: 50 requests/minute
- **POST /applications**: 5 requests/hour por IP

### **Headers de Rate Limit**
```http
X-RateLimit-Limit: 100
X-RateLimit-Remaining: 95
X-RateLimit-Reset: 1631779200
X-RateLimit-Retry-After: 60
```

---

## 📝 **Ejemplos de Uso**

### **JavaScript (Frontend)**
```javascript
// Obtener cupones disponibles
fetch('/wp-json/wpcw/v1/coupons', {
  method: 'GET',
  headers: {
    'X-WP-Nonce': wpApiSettings.nonce
  }
})
.then(response => response.json())
.then(data => {
  if (data.success) {
    console.log('Cupones:', data.data.coupons);
  }
});

// Canjear un cupón
fetch('/wp-json/wpcw/v1/coupons/123/redeem', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
    'X-WP-Nonce': wpApiSettings.nonce
  },
  body: JSON.stringify({
    business_id: 456
  })
})
.then(response => response.json())
.then(data => {
  if (data.success) {
    window.location.href = data.data.whatsapp_url;
  }
});
```

### **PHP (Backend)**
```php
// Usar la API desde código PHP
$api_url = rest_url('wpcw/v1/coupons');
$nonce = wp_create_nonce('wp_rest');

$response = wp_remote_get($api_url, array(
  'headers' => array(
    'X-WP-Nonce' => $nonce
  )
));

if (!is_wp_error($response)) {
  $data = json_decode(wp_remote_retrieve_body($response), true);
  if ($data['success']) {
    // Procesar cupones
  }
}
```

---

*Documento creado el: 16 de septiembre de 2025*
*Última actualización: 16 de septiembre de 2025*