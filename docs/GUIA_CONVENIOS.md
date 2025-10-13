# Guía Completa: Cómo Crear y Gestionar Convenios

## Flujo Completo del Sistema

```
1. INSTITUCIONES          2. COMERCIOS              3. CONVENIOS             4. CUPONES
   └─────────────────────────────────────────────────────────────────────────────┘
        Quién son los             Quién ofrece          Acuerdo entre          Beneficios
        beneficiarios             los descuentos        ambas partes           específicos
```

---

## Paso 1: Crear una Institución

Una **Institución** es una organización cuyos miembros recibirán beneficios (ej: sindicato, club, asociación).

### Cómo crear una institución:

1. Ve a **wp-admin** → **Instituciones** → **Añadir Nueva**
2. Completa los datos:
   - **Título**: Nombre de la institución (ej: "Sindicato de Trabajadores")
   - **Contenido**: Descripción de la institución
   - **Email de contacto**: Se usará para notificaciones de convenios

3. **Metadatos importantes** (campos personalizados):
   - `_institution_email`: Email de contacto para propuestas
   - `_institution_contact_name`: Nombre del contacto
   - `_institution_members_count`: Cantidad de miembros

4. **Publica** la institución

### Usuarios vinculados:
- Los **Administradores de Institución** (rol `wpcw_institution_manager`) pueden gestionar convenios

---

## Paso 2: Crear un Comercio

Un **Comercio** es un negocio que ofrece descuentos y beneficios.

### Cómo crear un comercio:

1. Ve a **wp-admin** → **Comercios** → **Añadir Nuevo**
2. Completa los datos:
   - **Título**: Nombre del comercio (ej: "Pizzería Don Antonio")
   - **Contenido**: Descripción del negocio
   - **Imagen destacada**: Logo o foto del comercio

3. **Metadatos importantes**:
   - `_business_address`: Dirección física
   - `_business_phone`: Teléfono de contacto
   - `_business_category`: Categoría (gastronomía, salud, etc.)
   - `_business_owner_id`: ID del usuario dueño

4. **Publica** el comercio

### Usuarios vinculados:
- **Dueño del Comercio** (rol `wpcw_business_owner`): Crea convenios y cupones
- **Empleados** (rol `wpcw_employee`): Validan canjes en el local

---

## Paso 3: Crear un Convenio

Un **Convenio** es el acuerdo entre una Institución y un Comercio para ofrecer beneficios.

### ⚠️ IMPORTANTE: El convenio es el núcleo del sistema
Sin un convenio activo, NO se pueden crear cupones válidos.

### Cómo crear un convenio:

#### Opción A: Desde el panel de administración

1. Ve a **wp-admin** → **Convenios** → **Añadir Nuevo**
2. **Título**: Se genera automáticamente como "Convenio: [Comercio] - [Institución]"
3. Completa los campos obligatorios:

**Detalles del Convenio:**
- **Proveedor (Comercio)**: Selecciona el comercio que ofrece el beneficio
- **Institución Beneficiaria**: Selecciona la institución cuyos miembros recibirán el beneficio
- **Términos del Convenio**: Describe claramente el acuerdo (ej: "15% de descuento en todos los productos los días de semana")

**Opcionales:**
- **Porcentaje de Descuento**: Si aplica (ej: 15%)
- **Usos Máximos por Beneficiario**: Límite de canjes por persona (0 = ilimitado)
- **Fecha de Inicio**: Cuándo comienza a ser válido
- **Fecha de Finalización**: Cuándo expira

4. **Estado del Convenio** (sidebar derecha):
   - **Pendiente**: Esperando aprobación
   - **Activo**: Funcionando, permite crear cupones ✅
   - **Rechazado**: No aprobado
   - **Expirado**: Fuera de vigencia

5. **Publica** el convenio y cambia el estado a **Activo**

#### Opción B: Desde el panel del comercio (propuesta)

1. El dueño del comercio inicia sesión
2. Ve a **Panel Principal** → **Convenios**
3. Click en **Proponer Nuevo Convenio**
4. Completa el formulario:
   - Selecciona la institución
   - Describe los términos del beneficio
5. Envía la propuesta
6. La institución recibe un email con link de aprobación
7. Una vez aprobado, el convenio queda **Activo**

---

## Paso 4: Crear Cupones asociados al Convenio

Los **Cupones** son los beneficios concretos que pueden canjear los beneficiarios.

### ⚠️ REQUISITO: Debes tener un convenio activo

### Cómo crear un cupón:

1. Ve a **Marketing** → **Cupones** → **Añadir cupón** (WooCommerce)
2. Completa los datos del cupón de WooCommerce:
   - **Código del cupón**: Ej: "SINDICATO15"
   - **Tipo de descuento**: Porcentaje, fijo, etc.
   - **Importe del cupón**: Ej: 15
   - **Restricciones**: Productos, categorías, etc.

3. **En el metabox "Configuración de la Plataforma de Beneficios"**:
   - **Asociar a Convenio**: ⭐ Selecciona el convenio activo
   - Si no tienes convenios activos, verás el mensaje:
     > "No tienes convenios activos. Debes proponer y tener un convenio aceptado para poder crear un cupón."

4. **Publica** el cupón

### El cupón ahora está vinculado:
- ✅ Solo válido para miembros de la institución del convenio
- ✅ Respeta los términos del convenio
- ✅ Aparece en la lista de convenios del panel

---

## Paso 5: Canje de Cupones

### ¿Quién puede canjear?
- Los **beneficiarios** (miembros de la institución) registrados en la plataforma

### Proceso de canje:

1. **El beneficiario**:
   - Ve los cupones disponibles en su dashboard
   - Solicita canje por WhatsApp o en persona
   - Muestra su cupón al comercio

2. **El empleado del comercio**:
   - Inicia sesión en wp-admin
   - Ve a **Validar Canje**
   - Escanea QR o ingresa código
   - Verifica identidad del beneficiario
   - **Aprueba** el canje

3. **El sistema**:
   - Registra el canje en la tabla `wp_wpcw_canjes`
   - Vincula: convenio_id + coupon_id + beneficiario + comercio
   - Actualiza estadísticas

---

## Resumen del Flujo Completo

```
📋 1. CREAR INSTITUCIÓN
   "Sindicato de Trabajadores"
   ↓

🏪 2. CREAR COMERCIO
   "Pizzería Don Antonio"
   ↓

🤝 3. CREAR CONVENIO
   Institución: Sindicato
   Comercio: Pizzería
   Términos: 15% descuento
   Estado: ACTIVO ✅
   ↓

🎟️ 4. CREAR CUPONES
   Código: SINDICATO15
   Convenio: [el creado arriba]
   Descuento: 15%
   ↓

👥 5. BENEFICIARIOS CANJEAN
   Sistema valida todo automáticamente
```

---

## Verificación: ¿El sistema está funcionando?

### Checklist de verificación:

- [ ] ¿Existe al menos 1 institución publicada?
- [ ] ¿Existe al menos 1 comercio publicado?
- [ ] ¿Existe al menos 1 convenio con estado **Activo**?
- [ ] ¿El convenio tiene vinculados una institución Y un comercio?
- [ ] ¿Los cupones tienen asignado un convenio en el metabox?
- [ ] ¿Los beneficiarios están registrados y asociados a la institución?

---

## Errores Comunes

### ❌ "No puedo crear cupones"
**Causa**: No tienes convenios activos
**Solución**: Crea un convenio y ponlo en estado "Activo"

### ❌ "No veo el selector de convenios"
**Causa**: El metabox no está cargando
**Solución**: Verifica que el archivo `admin/convenio-meta-boxes.php` esté incluido en `wp-cupon-whatsapp.php`

### ❌ "El convenio no aparece en la lista"
**Causa**: El estado no es "active" o el post_status no es "publish"
**Solución**: Edita el convenio y asegúrate de:
  1. Estado del convenio = "Activo"
  2. Publicar el post (botón azul "Publicar")

### ❌ "Los cupones no se validan"
**Causa**: Falta la tabla de canjes o el cupón no tiene convenio
**Solución**:
  1. Verifica que la tabla `wp_wpcw_canjes` existe
  2. Verifica que el cupón tiene `_wpcw_associated_convenio_id` en postmeta

---

## Estructura de Datos

### Tablas involucradas:

```sql
-- Instituciones
wp_posts (post_type = 'wpcw_institution')
wp_postmeta (_institution_email, _institution_contact_name)

-- Comercios
wp_posts (post_type = 'wpcw_business')
wp_postmeta (_business_address, _business_phone, _business_owner_id)

-- Convenios
wp_posts (post_type = 'wpcw_convenio')
wp_postmeta (
  _convenio_provider_id,      -- ID del comercio
  _convenio_recipient_id,     -- ID de la institución
  _convenio_status,           -- pending|active|rejected|expired
  _convenio_terms,            -- Términos del acuerdo
  _convenio_discount_percentage,
  _convenio_start_date,
  _convenio_end_date
)

-- Cupones
wp_posts (post_type = 'shop_coupon')  -- WooCommerce
wp_postmeta (_wpcw_associated_convenio_id)  -- ID del convenio

-- Canjes
wp_wpcw_canjes (
  canje_id,
  coupon_id,
  user_id,
  business_id,
  convenio_id,          -- ⭐ Nuevo campo
  fecha_canje,
  estado
)
```

---

## Siguiente Paso

### Para el desarrollador:

Si quieres **generar datos de prueba**, usa el seeder:

```php
// Activa el modo debug en wp-config.php:
define('WPCW_DEBUG_MODE', true);

// Luego ve a:
wp-admin → Developer Tools → Generar Datos
```

Esto creará automáticamente:
- 3 Instituciones
- 10 Comercios
- 8 Convenios activos
- 30 Cupones vinculados
- 20 Beneficiarios
- 50 Canjes de ejemplo

### Para el usuario final:

Sigue los pasos 1-4 de esta guía en orden. No te saltes pasos.

---

## Soporte

Si tienes problemas:

1. Verifica que WooCommerce está instalado y activo
2. Revisa los logs en `wp-content/debug.log`
3. Verifica que los roles existen: `wpcw_business_owner`, `wpcw_employee`, `wpcw_institution_manager`
4. Consulta la documentación del seeder en `docs/developer/SEEDER_DOCUMENTATION.md`

---

**Última actualización**: Octubre 2025
**Versión del plugin**: 1.5.1
