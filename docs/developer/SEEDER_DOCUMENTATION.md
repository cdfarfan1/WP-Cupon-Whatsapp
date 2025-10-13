# 🌱 Sistema de Generación de Datos de Ejemplo (Seeder)

## 📋 Índice

1. [Introducción](#introducción)
2. [Datos Generados](#datos-generados)
3. [Tipos de Usuarios](#tipos-de-usuarios)
4. [Credenciales de Acceso](#credenciales-de-acceso)
5. [Instalación y Activación](#instalación-y-activación)
6. [Uso del Sistema](#uso-del-sistema)
7. [Datos para Testing](#datos-para-testing)
8. [Estructura de Datos](#estructura-de-datos)
9. [Limpieza de Datos](#limpieza-de-datos)

---

## 🎯 Introducción

El **Sistema Seeder** genera automáticamente un ecosistema completo de datos de ejemplo para desarrollo y testing del plugin WP Cupón WhatsApp. Todos los datos utilizan emails y teléfonos reales para facilitar las pruebas.

**Ubicación del código**: `includes/debug/class-wpcw-seeder.php`
**Página de administración**: `admin/developer-tools-page.php`

---

## 📊 Datos Generados

### Resumen Total: **144 elementos**

| Tipo | Cantidad | Descripción |
|------|----------|-------------|
| 🏛️ **Instituciones** | 3 | Entidades que ofrecen beneficios |
| 🏪 **Comercios** | 10 | Negocios adheridos al sistema |
| 🤝 **Convenios** | 8 | Acuerdos institución-comercio |
| 🎫 **Cupones** | 30 | Códigos de descuento WooCommerce |
| 👥 **Beneficiarios** | 20 | Usuarios que reciben beneficios |
| 👔 **Dueños de Comercio** | 5 | Propietarios de negocios |
| 🛒 **Vendedores/Empleados** | 15 | Staff que valida canjes |
| 🏛️ **Usuarios Institución** | 3 | Administradores del sistema |
| 📋 **Canjes** | 50 | Transacciones de canje registradas |

---

## 👥 Tipos de Usuarios

### 1. Beneficiarios (20 usuarios)

**Rol WordPress**: `customer`
**Capacidades**: Recibir y canjear cupones
**Password**: `Beneficiario123!`

**Nombres generados**:
- Juan González, María Rodríguez, Carlos Fernández, Ana López
- Roberto Martínez, Laura Sánchez, Diego Pérez, Sofía Gómez
- Martín Díaz, Valentina Torres, Lucas Ramírez, Camila Flores
- Franco Castro, Lucía Morales, Mateo Jiménez, Emma Rojas
- Santiago Herrera, Isabella Mendoza, Nicolás Silva, Mía Vargas

**Metadatos guardados**:
```php
'_wpcw_dni' => '20000000-45000000' (aleatorio)
'_wpcw_phone' => '+5493883349901' o '+5493885214566'
'_wpcw_whatsapp' => '+5493883349901' o '+5493885214566'
'_wpcw_institution_id' => ID de institución asignada
'_wpcw_fecha_nacimiento' => Edad entre 20-60 años
'billing_phone' => Teléfono
'billing_email' => Email con alias
'billing_first_name' => Nombre
'billing_last_name' => Apellido
```

**Usernames**:
- `beneficiario_juan_0`, `beneficiario_maria_1`, etc.

**Emails** (con aliases Gmail):
- `farfancris+beneficiario0_1@gmail.com`
- `criis2709+beneficiario1_1@gmail.com`

---

### 2. Dueños de Comercio (5 usuarios)

**Rol WordPress**: `wpcw_business_owner`
**Capacidades**: Administrar su comercio, ver estadísticas
**Password**: `DuenoComercio123!`

**Nombres generados**:
- Roberto Gómez, María Fernández, Carlos López
- Ana Martínez, Jorge Silva

**Metadatos guardados**:
```php
'_wpcw_business_id' => ID del comercio asignado
'_wpcw_phone' => Teléfono real
'_wpcw_user_type' => 'business_owner'
'billing_phone' => Teléfono
'billing_email' => Email
```

**Usernames**:
- `dueno_comercio_roberto_0`
- `dueno_comercio_maria_1`

**Display Name**:
- "Roberto Gómez (Dueño - La Esquina del Sabor)"
- "María Fernández (Dueño - Panadería Don Juan)"

---

### 3. Vendedores/Empleados (15 usuarios) ⚡ VALIDAN CANJES

**Rol WordPress**: `wpcw_employee`
**Capacidades**: `read`, `redeem_coupons` (validar canjes)
**Password**: `Vendedor123!`

**Nombres generados**:
- Juan Pérez, Sofía González, Lucas Díaz, Valentina Ramírez
- Mateo Flores, Emma Castro, Santiago Morales, Isabella Jiménez
- Nicolás Rojas, Mía Herrera, Franco Mendoza, Camila Vargas
- Benjamín Sánchez, Martina Romero, Tomás Acosta

**Cargos asignados**:
- Vendedor
- Cajero
- Encargado de Turno
- Asistente de Ventas
- Supervisor

**Metadatos guardados**:
```php
'_wpcw_business_id' => ID del comercio donde trabaja
'_wpcw_phone' => Teléfono real
'_wpcw_user_type' => 'business_staff'
'_wpcw_position' => 'Vendedor' | 'Cajero' | etc.
'billing_phone' => Teléfono
'billing_email' => Email
```

**Usernames**:
- `vendedor_juan_0`, `vendedor_sofia_1`, etc.

**Display Name**:
- "Juan Pérez (Vendedor)"
- "Sofía González (Cajero)"
- "Lucas Díaz (Encargado de Turno)"

---

### 4. Usuarios de Institución (3 usuarios)

**Rol WordPress**: `wpcw_institution_manager`
**Capacidades**: Administrar sistema de beneficios completo
**Password**: `Institucion123!`

**Usernames**:
- `institucion_admin_0`, `institucion_admin_1`, `institucion_admin_2`

**Display Name**:
- "Admin - Municipalidad de San Fernando del Valle de Catamarca"
- "Admin - Gobierno de la Provincia de Catamarca"

---

## 🔐 Credenciales de Acceso

### Tabla de Credenciales

| Tipo | Username | Password | Email Base |
|------|----------|----------|-----------|
| Beneficiario | `beneficiario_juan_0` | `Beneficiario123!` | `farfancris@gmail.com` |
| Dueño | `dueno_comercio_roberto_0` | `DuenoComercio123!` | `farfancris@gmail.com` |
| Vendedor | `vendedor_juan_0` | `Vendedor123!` | `farfancris@gmail.com` |
| Institución | `institucion_admin_0` | `Institucion123!` | `farfancris@gmail.com` |

### Emails Reales Utilizados

**Base**:
- `farfancris@gmail.com`
- `criis2709@gmail.com`

**Con aliases Gmail** (para evitar duplicados):
- `farfancris+beneficiario0_1@gmail.com`
- `farfancris+vendedor5_1@gmail.com`
- `criis2709+dueno1_1@gmail.com`

### Teléfonos Reales Utilizados

- `+5493883349901`
- `+5493885214566`

---

## 🏛️ Instituciones Generadas

1. **Municipalidad de San Fernando del Valle de Catamarca**
   - Email: `farfancris@gmail.com`
   - Teléfono: `+5493883349901`
   - Dirección: Av. Belgrano XXX, Catamarca
   - CUIT: 30-XXXXXXXX-X

2. **Gobierno de la Provincia de Catamarca**
   - Email: `criis2709@gmail.com`
   - Teléfono: `+5493885214566`

3. **Universidad Nacional de Catamarca**
   - Email: `farfancris@gmail.com`
   - Teléfono: `+5493883349901`

---

## 🏪 Comercios Generados

| # | Nombre | Categoría | Teléfono |
|---|--------|-----------|----------|
| 1 | La Esquina del Sabor | Restaurante | +5493883349901 |
| 2 | Panadería Don Juan | Panadería | +5493885214566 |
| 3 | Farmacia Central | Farmacia | +5493883349901 |
| 4 | Supermercado El Ahorro | Supermercado | +5493885214566 |
| 5 | Librería Catamarca | Librería | +5493883349901 |
| 6 | Ferretería San Martín | Ferretería | +5493885214566 |
| 7 | Boutique Fashion | Ropa | +5493883349901 |
| 8 | Calzados Piero | Calzado | +5493885214566 |
| 9 | Electro Hogar | Electrónica | +5493883349901 |
| 10 | Gym Fitness Center | Gimnasio | +5493885214566 |

**Datos de cada comercio**:
```php
'_business_email' => Email real
'_business_phone' => Teléfono real
'_business_whatsapp' => Teléfono WhatsApp
'_business_address' => 'Calle X N° XXX, Catamarca'
'_business_category' => Categoría
'_business_cuit' => '20-XXXXXXXX-X'
'_business_status' => 'approved'
```

---

## 🎫 Cupones Generados (30 total)

**Formato del código**: `TESTXXXXXX` (6 caracteres alfanuméricos)

**Características**:
- Descuento: 10% - 50% (aleatorio)
- Tipo: Porcentual
- Uso individual: Sí
- Límite de uso: 1 vez
- Límite por usuario: 1 vez
- Fecha de expiración: +90 días
- Asociado a un convenio activo

**Ejemplos**:
- `TESTAB12CD` → 25% de descuento
- `TESTZ9Y8X7` → 15% de descuento
- `TEST123ABC` → 40% de descuento

---

## 📋 Canjes Generados (50 total)

**Estados distribuidos**:
- `pending` - Pendiente de validación
- `approved` - Aprobado por comercio
- `rejected` - Rechazado
- `used` - Usado/canjeado

**Características**:
```php
'user_id' => Beneficiario aleatorio
'coupon_id' => Cupón aleatorio
'comercio_id' => Comercio aleatorio
'estado_canje' => Estado aleatorio
'fecha_solicitud_canje' => Últimos 90 días (aleatorio)
'numero_canje' => 'YYYYMMDD-XXXX'
'token_confirmacion' => Token único
'codigo_validacion' => 'VALXXXXXXXX'
'origen_canje' => 'seeder'
'notas_internas' => '[SEEDED] Datos de ejemplo para testing'
'ip_address' => '127.0.0.1'
'user_agent' => 'Test Seeder'
```

### Distribución Temporal de Canjes

Los canjes están distribuidos en los últimos 90 días para generar estadísticas realistas:

- **Últimos 7 días**: ~8 canjes
- **Últimos 30 días**: ~17 canjes
- **Últimos 60 días**: ~33 canjes
- **Últimos 90 días**: 50 canjes

---

## 🚀 Instalación y Activación

### Paso 1: Activar Modo Debug

Editar `wp-config.php` y agregar:

```php
define('WPCW_DEBUG_MODE', true);
```

### Paso 2: Acceder a Herramientas DEV

1. Ir a: **WordPress Admin**
2. Menú: **WP Cupón WhatsApp**
3. Submenu: **Herramientas DEV** (aparece solo con debug activo)

### Paso 3: Verificar Estado Actual

La página mostrará una tabla con el estado actual:

```
📊 Estado Actual de Datos
┌─────────────────────────────────┬─────────┐
│ 🏛️ Instituciones                │    0    │
│ 🏪 Comercios                     │    0    │
│ 🤝 Convenios                     │    0    │
│ 🎫 Cupones                       │    0    │
│ 👥 Beneficiarios (Customers)    │    0    │
│ 👔 Dueños de Comercio            │    0    │
│ 🛒 Vendedores/Empleados          │    0    │
│ 🏛️ Usuarios Institución         │    0    │
│ 📋 Canjes Registrados            │    0    │
└─────────────────────────────────┴─────────┘
```

---

## 🎮 Uso del Sistema

### Generar Datos

1. Click en botón: **"🌱 Generar Ecosistema Completo"**
2. Esperar ~10-30 segundos (depende del servidor)
3. Ver mensaje de éxito:

```
✅ Ecosistema generado: 144 elementos creados
(3 instituciones, 10 comercios, 8 convenios, 30 cupones,
20 beneficiarios, 5 dueños de comercio, 15 empleados/vendedores,
3 usuarios institución, 50 canjes)
```

4. Refrescar la página para ver estadísticas actualizadas

### Limpiar Datos

1. Scroll hasta **"🗑️ Zona de Peligro"**
2. Click en: **"BORRAR TODOS LOS DATOS DE EJEMPLO"**
3. Confirmar en el diálogo:

```
⚠️ ¿Estás ABSOLUTAMENTE SEGURO?

Esta acción eliminará:
• Todos los posts marcados como datos de ejemplo
• Todos los usuarios de ejemplo
• Todos los canjes de ejemplo

Esta acción NO se puede deshacer.
```

4. Ver mensaje de confirmación:

```
🗑️ Limpieza completada: 144 elementos eliminados
(73 posts, 43 usuarios, 28 canjes)
```

---

## 🧪 Datos para Testing

### Escenario 1: Validar Canje como Vendedor

1. **Logout** de WordPress
2. **Login**:
   - Usuario: `vendedor_juan_0`
   - Password: `Vendedor123!`
3. Ir a: **WP Cupón WhatsApp > Validar Canje**
4. Ingresar código de canje (ver en dashboard de canjes)
5. Validar o rechazar

### Escenario 2: Ver Estadísticas como Dueño

1. **Login**:
   - Usuario: `dueno_comercio_roberto_0`
   - Password: `DuenoComercio123!`
2. Ir a: **WP Cupón WhatsApp > Estadísticas de Comercio**
3. Ver canjes de su comercio
4. Ver gráficas de uso

### Escenario 3: Canjear Cupón como Beneficiario

1. **Login**:
   - Usuario: `beneficiario_juan_0`
   - Password: `Beneficiario123!`
2. Ir a página de canjes (/canjes/)
3. Seleccionar cupón disponible
4. Generar canje
5. Ver código de validación

### Escenario 4: Administrar como Institución

1. **Login**:
   - Usuario: `institucion_admin_0`
   - Password: `Institucion123!`
2. Acceso completo al dashboard
3. Ver todos los canjes de todas las instituciones
4. Generar reportes

---

## 📐 Estructura de Datos

### Post Meta (Instituciones)

```php
_wpcw_is_seeded: true
_institution_email: farfancris@gmail.com
_institution_phone: +5493883349901
_institution_address: Av. Belgrano 123, Catamarca
_institution_cuit: 30-12345678-9
```

### Post Meta (Comercios)

```php
_wpcw_is_seeded: true
_business_email: farfancris@gmail.com
_business_phone: +5493883349901
_business_whatsapp: +5493883349901
_business_address: Calle 1 N° 234, Catamarca
_business_category: Restaurante
_business_cuit: 20-87654321-5
_business_status: approved
```

### Post Meta (Convenios)

```php
_wpcw_is_seeded: true
_convenio_provider_id: 123 (ID del comercio)
_convenio_recipient_id: 456 (ID de la institución)
_convenio_status: active
_convenio_discount_percentage: 25
_convenio_start_date: 2024-09-01
_convenio_end_date: 2025-09-01
```

### Post Meta (Cupones)

```php
_wpcw_is_seeded: true
discount_type: percent
coupon_amount: 25
individual_use: yes
usage_limit: 1
usage_limit_per_user: 1
expiry_date: 2025-03-31
_wpcw_associated_convenio_id: 789
```

### User Meta (Beneficiarios)

```php
_wpcw_is_seeded: true
_wpcw_dni: 35123456
_wpcw_phone: +5493883349901
_wpcw_whatsapp: +5493883349901
_wpcw_institution_id: 123
_wpcw_fecha_nacimiento: 1985-05-15
billing_phone: +5493883349901
billing_email: farfancris+beneficiario0_1@gmail.com
billing_first_name: Juan
billing_last_name: González
```

### User Meta (Vendedores)

```php
_wpcw_is_seeded: true
_wpcw_business_id: 234 (Comercio donde trabaja)
_wpcw_phone: +5493883349901
_wpcw_user_type: business_staff
_wpcw_position: Vendedor
billing_phone: +5493883349901
billing_email: farfancris+vendedor0_1@gmail.com
```

### Tabla wp_wpcw_canjes

```php
user_id: 12 (Beneficiario)
coupon_id: 456 (Post ID del cupón)
comercio_id: 234 (Comercio)
estado_canje: 'approved'
fecha_solicitud_canje: '2024-12-15 14:30:00'
numero_canje: '20241215-0001'
token_confirmacion: 'abc123...xyz'
codigo_validacion: 'VALA1B2C3D4'
origen_canje: 'seeder'
notas_internas: '[SEEDED] Datos de ejemplo para testing'
ip_address: '127.0.0.1'
user_agent: 'Test Seeder'
```

---

## 🗑️ Limpieza de Datos

### Identificación de Datos Seeded

Todos los datos generados están marcados para fácil identificación:

**Posts (Instituciones, Comercios, Convenios, Cupones)**:
- Meta: `_wpcw_is_seeded = true`

**Users (Todos los tipos)**:
- Meta: `_wpcw_is_seeded = true`

**Canjes (Tabla custom)**:
- Campo: `origen_canje = 'seeder'`
- Campo: `notas_internas LIKE '[SEEDED]%'`

### Proceso de Limpieza

El sistema ejecuta:

```sql
-- 1. Eliminar posts
SELECT post_id FROM wp_postmeta WHERE meta_key = '_wpcw_is_seeded'
DELETE post WHERE ID IN (...)

-- 2. Eliminar users
SELECT user_id FROM wp_usermeta WHERE meta_key = '_wpcw_is_seeded'
DELETE user WHERE ID IN (...)

-- 3. Eliminar canjes
DELETE FROM wp_wpcw_canjes
WHERE origen_canje = 'seeder'
   OR notas_internas LIKE '[SEEDED]%'
```

### Seguridad

- **No elimina datos reales**: Solo elimina datos con marca especial
- **Confirmación obligatoria**: Requiere confirmar antes de eliminar
- **Conteo detallado**: Muestra cuántos elementos se eliminaron de cada tipo
- **No reversible**: Una vez eliminado, no se puede recuperar

---

## 📊 Estadísticas y Métricas

### Datos Generados para Testing de Estadísticas

El seeder genera datos con **variación temporal** para testing realista:

**Distribución de Canjes**:
- 16% últimos 7 días
- 34% últimos 30 días
- 66% últimos 60 días
- 100% últimos 90 días

**Estados de Canjes** (distribución aleatoria):
- ~25% pending
- ~25% approved
- ~25% used
- ~25% rejected

**Comercios más activos** (aleatorio):
- Algunos comercios tendrán más canjes que otros
- Permite testing de gráficas de distribución

**Cupones más usados** (aleatorio):
- Permite ver qué cupones son más populares
- Testing de análisis de descuentos

---

## 🔧 Personalización

### Cambiar Cantidades

Editar `includes/debug/class-wpcw-seeder.php` línea 35:

```php
public static function seed_all() {
    $results = [];

    $results['institutions'] = self::seed_institutions( 3 );      // Cambiar aquí
    $results['businesses'] = self::seed_businesses( 10 );         // Cambiar aquí
    $results['convenios'] = self::seed_convenios( 8 );            // Cambiar aquí
    $results['coupons'] = self::seed_coupons( 30 );               // Cambiar aquí
    $results['beneficiaries'] = self::seed_beneficiaries( 20 );   // Cambiar aquí
    $results['business_owners'] = self::seed_business_owners( 5 );// Cambiar aquí
    $results['business_staff'] = self::seed_business_staff( 15 ); // Cambiar aquí
    $results['institution_users'] = self::seed_institution_users( 3 ); // Cambiar aquí
    $results['redemptions'] = self::seed_redemptions( 50 );       // Cambiar aquí

    return ['message' => '...'];
}
```

### Cambiar Emails/Teléfonos

Editar líneas 19-30:

```php
private static $test_emails = [
    'farfancris@gmail.com',
    'criis2709@gmail.com',
    'tuEmail@gmail.com',  // Agregar más
];

private static $test_phones = [
    '+5493883349901',
    '+5493885214566',
    '+5493881234567',     // Agregar más
];
```

---

## 🐛 Troubleshooting

### Problema: No aparece el menú "Herramientas DEV"

**Solución**: Verificar que `WPCW_DEBUG_MODE` esté definido en `wp-config.php`

### Problema: Error al generar datos

**Solución**: Verificar logs en `wp-content/debug.log`

### Problema: Emails duplicados

**Solución**: El sistema usa aliases Gmail automáticamente (+sufijo)

### Problema: Canjes no aparecen en estadísticas

**Solución**: Verificar que la tabla `wp_wpcw_canjes` exista y tenga las columnas correctas

---

## 📚 Referencias

- **Archivo principal**: `includes/debug/class-wpcw-seeder.php` (670 líneas)
- **Página admin**: `admin/developer-tools-page.php` (220 líneas)
- **Roles**: `includes/roles.php`
- **Tabla canjes**: Schema en `database/migration-update-canjes-table.sql`

---

## ✅ Checklist de Testing

- [ ] Activar modo debug
- [ ] Generar datos completos
- [ ] Login como vendedor y validar canje
- [ ] Login como beneficiario y solicitar canje
- [ ] Login como dueño y ver estadísticas
- [ ] Login como institución y ver dashboard completo
- [ ] Verificar emails en Gmail (con aliases)
- [ ] Probar página de validación de canjes
- [ ] Ver estadísticas con datos variados
- [ ] Limpiar todos los datos
- [ ] Verificar que no quedaron datos residuales

---

**Versión**: 1.0
**Última actualización**: 2025-01-13
**Autor**: Sistema WP Cupón WhatsApp
**Contacto**: farfancris@gmail.com, criis2709@gmail.com
