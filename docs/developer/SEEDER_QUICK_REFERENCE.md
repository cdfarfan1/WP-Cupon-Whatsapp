# 🚀 Seeder - Referencia Rápida

## Activación

```php
// En wp-config.php
define('WPCW_DEBUG_MODE', true);
```

## Acceso

**WordPress Admin > WP Cupón WhatsApp > Herramientas DEV**

---

## 📊 Datos Generados

| Tipo | Cantidad | Rol |
|------|----------|-----|
| 🏛️ Instituciones | 3 | - |
| 🏪 Comercios | 10 | - |
| 🤝 Convenios | 8 | - |
| 🎫 Cupones | 30 | - |
| 👥 Beneficiarios | 20 | `customer` |
| 👔 Dueños | 5 | `wpcw_business_owner` |
| 🛒 Vendedores | 15 | `wpcw_employee` |
| 🏛️ Admins | 3 | `wpcw_institution_manager` |
| 📋 Canjes | 50 | - |

**Total: 144 elementos**

---

## 🔐 Credenciales

| Usuario | Password |
|---------|----------|
| `beneficiario_juan_0` | `Beneficiario123!` |
| `dueno_comercio_roberto_0` | `DuenoComercio123!` |
| `vendedor_juan_0` | `Vendedor123!` |
| `institucion_admin_0` | `Institucion123!` |

---

## 📧 Emails

- `farfancris@gmail.com`
- `criis2709@gmail.com`

**Con aliases**: `farfancris+beneficiario0_1@gmail.com`

---

## 📱 Teléfonos

- `+5493883349901`
- `+5493885214566`

---

## 🎯 Testing Rápido

### Validar Canje (Vendedor)

```bash
Usuario: vendedor_juan_0
Password: Vendedor123!
Ir a: WP Cupón WhatsApp > Validar Canje
```

### Ver Estadísticas (Dueño)

```bash
Usuario: dueno_comercio_roberto_0
Password: DuenoComercio123!
Ir a: WP Cupón WhatsApp > Estadísticas
```

### Solicitar Canje (Beneficiario)

```bash
Usuario: beneficiario_juan_0
Password: Beneficiario123!
Ir a: /canjes/
```

---

## 🏪 Comercios Generados

1. La Esquina del Sabor (Restaurante)
2. Panadería Don Juan
3. Farmacia Central
4. Supermercado El Ahorro
5. Librería Catamarca
6. Ferretería San Martín
7. Boutique Fashion
8. Calzados Piero
9. Electro Hogar
10. Gym Fitness Center

---

## 🏛️ Instituciones Generadas

1. Municipalidad de San Fernando del Valle de Catamarca
2. Gobierno de la Provincia de Catamarca
3. Universidad Nacional de Catamarca

---

## 📋 Distribución de Canjes

- **Últimos 7 días**: ~8 canjes (16%)
- **Últimos 30 días**: ~17 canjes (34%)
- **Últimos 60 días**: ~33 canjes (66%)
- **Últimos 90 días**: 50 canjes (100%)

**Estados**: 25% pending, 25% approved, 25% used, 25% rejected

**Horarios**: 8:00 AM - 8:00 PM (horario comercial)

---

## 🗑️ Limpieza

1. Ir a **Herramientas DEV**
2. Scroll a **"Zona de Peligro"**
3. Click en **"BORRAR TODOS LOS DATOS DE EJEMPLO"**
4. Confirmar

**Identifica datos por**:
- Post meta: `_wpcw_is_seeded = true`
- User meta: `_wpcw_is_seeded = true`
- Canjes: `origen_canje = 'seeder'`

---

## 🔧 Archivos

- **Seeder**: `includes/debug/class-wpcw-seeder.php`
- **Admin**: `admin/developer-tools-page.php`
- **Roles**: `includes/roles.php`
- **Docs**: `docs/developer/SEEDER_DOCUMENTATION.md`

---

## ⚡ Roles y Permisos

| Rol | Capacidades Clave |
|-----|-------------------|
| `customer` | Canjear cupones |
| `wpcw_business_owner` | Administrar comercio |
| `wpcw_employee` | **Validar canjes** (`redeem_coupons`) |
| `wpcw_institution_manager` | Administrar sistema completo |

---

## 🎫 Cupones

**Formato**: `TESTXXXXXX` (6 caracteres)
**Descuento**: 10% - 50%
**Vencimiento**: +90 días
**Límite**: 1 uso por usuario

---

## 📊 Para Testing de Estadísticas

El seeder genera:

✅ Distribución temporal realista (últimos 90 días)
✅ Horarios comerciales (8am-8pm)
✅ Estados variados (pending/approved/rejected/used)
✅ Múltiples comercios con actividad variable
✅ Cupones con uso distribuido
✅ Beneficiarios activos e inactivos

---

## 🐛 Troubleshooting

**Menú no aparece**: Verificar `WPCW_DEBUG_MODE` en wp-config.php
**Error al generar**: Ver `wp-content/debug.log`
**Emails duplicados**: Sistema usa aliases Gmail automáticamente

---

**Documentación completa**: `docs/developer/SEEDER_DOCUMENTATION.md`
