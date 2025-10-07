# Solución de Error Fatal - WP Cupón WhatsApp

## Problema Identificado

El plugin original requiere **WooCommerce** y **Elementor** como dependencias obligatorias, lo que causaba un error fatal al intentar activarlo en un entorno local sin estas dependencias.

## Solución Implementada

### 1. Versión Standalone Creada

Se ha creado una versión simplificada del plugin (`wp-cupon-whatsapp-standalone.php`) que:

- ✅ **Elimina dependencias obligatorias** de WooCommerce y Elementor
- ✅ **Mantiene funcionalidad core** del plugin
- ✅ **Permite pruebas locales** sin instalaciones adicionales
- ✅ **Incluye verificación básica** de PHP y WordPress

### 2. Características de la Versión Standalone

```php
// Dependencias eliminadas:
- WooCommerce (ya no es obligatorio)
- Elementor (ya no es obligatorio)

// Dependencias mantenidas:
- WordPress 5.0+
- PHP 7.4+
```

### 3. Funcionalidades Disponibles

- ✅ **Gestión de cupones** (post type personalizado)
- ✅ **Integración con WhatsApp** (wa.me)
- ✅ **Formularios de canje**
- ✅ **Validación de números de teléfono**
- ✅ **Panel de administración**
- ✅ **Shortcodes públicos**
- ✅ **Sistema de roles y permisos**

## Instalación de la Versión Standalone

### Método Automático

```bash
# Ejecutar desde la carpeta del plugin
.\instalar-plugin-local.bat
```

### Método Manual

1. **Copiar archivo principal:**
   ```
   wp-cupon-whatsapp-standalone.php → wp-cupon-whatsapp.php
   ```

2. **Copiar carpetas:**
   - `admin/`
   - `includes/`
   - `public/`
   - `templates/`
   - `languages/`

3. **Ubicación final:**
   ```
   C:\xampp\htdocs\webstore\wp-content\plugins\wp-cupon-whatsapp\
   ```

## Activación del Plugin

### Pasos para Activar

1. **Acceder al panel de WordPress:**
   ```
   https://localhost/webstore/wp-admin
   ```

2. **Ir a Plugins:**
   - Plugins > Plugins Instalados

3. **Buscar y activar:**
   - Buscar "WP Canje Cupon Whatsapp (Standalone)"
   - Hacer clic en "Activar"

4. **Verificar activación:**
   - Debe aparecer un aviso informativo sobre la versión standalone
   - El menú "WP Cupón" debe aparecer en el panel lateral

## Configuración Inicial

### 1. Configuración Básica

```
WP Cupón > Configuración
- Configurar mensajes de WhatsApp
- Establecer números de teléfono
- Configurar validaciones
```

### 2. Crear Primer Cupón

```
WP Cupón > Cupones > Añadir nuevo
- Título del cupón
- Descripción
- Configuración de canje
- Estado de publicación
```

### 3. Usar Shortcodes

```php
// Formulario de canje
[wpcw_canje_form]

// Lista de cupones
[wpcw_cupones_list]

// Formulario específico
[wpcw_canje_form cupon_id="123"]
```

## Diferencias con la Versión Completa

| Característica | Versión Completa | Versión Standalone |
|---|---|---|
| **WooCommerce** | ✅ Requerido | ❌ Opcional |
| **Elementor** | ✅ Requerido | ❌ Opcional |
| **Cupones básicos** | ✅ | ✅ |
| **WhatsApp** | ✅ | ✅ |
| **Validaciones** | ✅ | ✅ |
| **Widgets Elementor** | ✅ | ❌ No disponible |
| **Integración WooCommerce** | ✅ | ❌ No disponible |

## Migración a Versión Completa

Para usar la versión completa:

1. **Instalar dependencias:**
   ```
   - WooCommerce 6.0+
   - Elementor 3.0+
   ```

2. **Reemplazar archivo principal:**
   ```
   wp-cupon-whatsapp-standalone.php → wp-cupon-whatsapp.php (original)
   ```

3. **Reactivar plugin:**
   - Desactivar versión standalone
   - Activar versión completa

## Solución de Problemas Adicionales

### Error de Sintaxis
```bash
# Verificar sintaxis
php -l wp-cupon-whatsapp.php
```

### Problemas de Permisos
```bash
# Verificar permisos de archivos
# Archivos: 644
# Carpetas: 755
```

### Debug de WordPress
```php
// Agregar a wp-config.php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);
```

### Logs de Error
```
# Ubicaciones comunes de logs:
C:\xampp\htdocs\webstore\wp-content\debug.log
C:\xampp\logs\php_error_log
```

## Contacto y Soporte

- **Autor:** Cristian Farfan, Pragmatic Solutions
- **Email:** [Contacto del desarrollador]
- **Versión:** 1.2.3-standalone

---

**Nota:** Esta versión standalone es ideal para desarrollo y pruebas locales. Para producción, se recomienda usar la versión completa con todas las dependencias instaladas.