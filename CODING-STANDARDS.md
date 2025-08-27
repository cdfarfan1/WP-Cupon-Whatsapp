# Estándares de Codificación - WP Cupón WhatsApp

## 📋 Índice

- [Introducción](#introducción)
- [Herramientas de Linting](#herramientas-de-linting)
- [Estándares PHP](#estándares-php)
- [Estándares JavaScript](#estándares-javascript)
- [Estándares CSS](#estándares-css)
- [Configuración del Entorno](#configuración-del-entorno)
- [Verificación Automática](#verificación-automática)
- [Mejores Prácticas](#mejores-prácticas)

## 🎯 Introducción

Este documento establece los estándares de codificación para el plugin **WP Cupón WhatsApp**. El objetivo es mantener un código consistente, legible y mantenible que siga las mejores prácticas de WordPress y la industria.

### Puntuación de Calidad Actual

**📊 Puntuación General: 7.8/10** - APROBADO con recomendaciones de mejora

## 🛠️ Herramientas de Linting

### PHP_CodeSniffer (PHPCS)
- **Archivo de configuración**: `.phpcs.xml`
- **Estándares**: WordPress, WordPress-Core, WordPress-Extra
- **Instalación**: `composer global require squizlabs/php_codesniffer`

### ESLint
- **Archivo de configuración**: `.eslintrc.js`
- **Estándares**: ESLint Recommended + reglas personalizadas
- **Instalación**: `npm install -g eslint`

### Stylelint
- **Archivo de configuración**: `.stylelintrc.json`
- **Estándares**: Stylelint Standard + orden de propiedades
- **Instalación**: `npm install -g stylelint stylelint-config-standard stylelint-order`

## 🐘 Estándares PHP

### Estilo de Codificación

```php
<?php
/**
 * Ejemplo de código PHP siguiendo los estándares
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

/**
 * Función de ejemplo con documentación adecuada.
 *
 * @param string $param1 Descripción del parámetro.
 * @param int    $param2 Otro parámetro.
 * @return bool Resultado de la operación.
 */
function wpcw_example_function($param1, $param2) {
    // Usar espacios, no tabs
    if (empty($param1)) {
        return false;
    }
    
    // Comillas simples para strings simples
    $message = 'Mensaje de ejemplo';
    
    // Comillas dobles cuando hay variables
    $formatted = "El parámetro es: {$param1}";
    
    return true;
}
```

### Reglas Principales

1. **Indentación**: 4 espacios (no tabs)
2. **Comillas**: Simples para strings, dobles con variables
3. **Nomenclatura**: `snake_case` para funciones y variables
4. **Prefijos**: Todas las funciones deben usar el prefijo `wpcw_`
5. **Documentación**: PHPDoc obligatorio para funciones públicas
6. **Seguridad**: Siempre escapar output y validar input

### Seguridad

```php
// ✅ Correcto - Escapar output
echo esc_html($user_input);
echo esc_url($url);
echo esc_attr($attribute);

// ✅ Correcto - Validar input
$email = sanitize_email($_POST['email']);
$text = sanitize_text_field($_POST['text']);

// ✅ Correcto - Verificar nonces
if (!wp_verify_nonce($_POST['nonce'], 'wpcw_action')) {
    wp_die('Verificación de seguridad falló');
}

// ✅ Correcto - Verificar permisos
if (!current_user_can('manage_options')) {
    wp_die('Sin permisos suficientes');
}
```

## 🟨 Estándares JavaScript

### Estilo de Codificación

```javascript
/**
 * Ejemplo de código JavaScript siguiendo los estándares
 */

(function($) {
    'use strict';
    
    /**
     * Función de ejemplo
     * @param {string} selector - Selector CSS
     * @param {Object} options - Opciones de configuración
     */
    function initializeComponent(selector, options) {
        const defaults = {
            animation: true,
            duration: 300
        };
        
        const settings = $.extend({}, defaults, options);
        
        $(selector).each(function() {
            const $element = $(this);
            
            $element.on('click', function(e) {
                e.preventDefault();
                // Lógica del componente
            });
        });
    }
    
    // Inicializar cuando el DOM esté listo
    $(document).ready(function() {
        initializeComponent('.wpcw-component');
    });
    
})(jQuery);
```

### Reglas Principales

1. **Indentación**: 4 espacios
2. **Comillas**: Simples preferidas
3. **Punto y coma**: Obligatorio
4. **Variables**: `camelCase`
5. **Constantes**: `UPPER_SNAKE_CASE`
6. **Funciones**: Documentar con JSDoc
7. **jQuery**: Usar `$` dentro de closures

### Manejo de Errores

```javascript
// ✅ Correcto - Manejo de errores en AJAX
$.ajax({
    url: ajaxurl,
    type: 'POST',
    data: {
        action: 'wpcw_action',
        nonce: wpcw_vars.nonce
    }
})
.done(function(response) {
    if (response.success) {
        // Manejar éxito
    } else {
        console.error('Error:', response.data);
    }
})
.fail(function(xhr, status, error) {
    console.error('AJAX Error:', error);
});
```

## 🎨 Estándares CSS

### Estilo de Codificación

```css
/**
 * Ejemplo de CSS siguiendo los estándares
 */

.wpcw-component {
    position: relative;
    display: flex;
    width: 100%;
    max-width: 1200px;
    padding: 1rem;
    margin: 0 auto;
    background-color: #fff;
    border: 1px solid #ddd;
    border-radius: 4px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.wpcw-component__header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding-bottom: 1rem;
    margin-bottom: 1rem;
    border-bottom: 1px solid #eee;
}

.wpcw-component__title {
    font-size: 1.5rem;
    font-weight: 600;
    color: #333;
    margin: 0;
}

/* Estados y modificadores */
.wpcw-component--loading {
    opacity: 0.6;
    pointer-events: none;
}

.wpcw-component--error {
    border-color: #dc3545;
    background-color: #f8d7da;
}

/* Media queries */
@media (max-width: 768px) {
    .wpcw-component {
        padding: 0.5rem;
    }
    
    .wpcw-component__header {
        flex-direction: column;
        align-items: flex-start;
    }
}
```

### Reglas Principales

1. **Indentación**: 4 espacios
2. **Comillas**: Simples
3. **Colores**: Hexadecimales en minúsculas y forma corta
4. **Nomenclatura**: BEM (Block Element Modifier)
5. **Prefijos**: Todas las clases con `wpcw-`
6. **Orden**: Propiedades ordenadas lógicamente
7. **Unidades**: `rem` para tamaños, `px` para bordes

### Nomenclatura BEM

```css
/* Bloque */
.wpcw-card { }

/* Elemento */
.wpcw-card__header { }
.wpcw-card__body { }
.wpcw-card__footer { }

/* Modificador */
.wpcw-card--primary { }
.wpcw-card--large { }
.wpcw-card__header--centered { }
```

## ⚙️ Configuración del Entorno

### Instalación de Herramientas

```bash
# PHP_CodeSniffer
composer global require squizlabs/php_codesniffer

# ESLint
npm install -g eslint

# Stylelint
npm install -g stylelint stylelint-config-standard stylelint-order
```

### Configuración del Editor

#### Visual Studio Code

```json
{
    "editor.tabSize": 4,
    "editor.insertSpaces": true,
    "editor.detectIndentation": false,
    "files.trimTrailingWhitespace": true,
    "files.insertFinalNewline": true,
    "php.validate.enable": true,
    "eslint.enable": true,
    "stylelint.enable": true
}
```

#### Extensiones Recomendadas

- PHP Intelephense
- ESLint
- Stylelint
- WordPress Snippets
- Prettier

## 🔍 Verificación Automática

### Script de Linting

Ejecuta el script `lint-check.ps1` para verificar todo el código:

```powershell
# Verificación básica
.\lint-check.ps1

# Con corrección automática
.\lint-check.ps1 -Fix

# Con información detallada
.\lint-check.ps1 -Verbose
```

### Comandos Individuales

```bash
# PHP
phpcs --standard=.phpcs.xml .
phpcbf --standard=.phpcs.xml .

# JavaScript
eslint **/*.js
eslint **/*.js --fix

# CSS
stylelint **/*.css
stylelint **/*.css --fix
```

## 💡 Mejores Prácticas

### Generales

1. **Consistencia**: Mantén el mismo estilo en todo el proyecto
2. **Legibilidad**: El código debe ser autodocumentado
3. **Simplicidad**: Prefiere soluciones simples y claras
4. **Reutilización**: Evita duplicación de código
5. **Rendimiento**: Considera el impacto en performance

### WordPress Específicas

1. **Hooks**: Usa acciones y filtros apropiados
2. **Internacionalización**: Todos los strings deben ser traducibles
3. **Sanitización**: Siempre sanitiza input del usuario
4. **Escape**: Siempre escapa output al navegador
5. **Nonces**: Usa nonces para formularios y AJAX
6. **Capabilities**: Verifica permisos de usuario

### Seguridad

```php
// ✅ Correcto - Formulario seguro
<form method="post">
    <?php wp_nonce_field('wpcw_save_settings', 'wpcw_nonce'); ?>
    <input type="text" name="setting_value" value="<?php echo esc_attr($current_value); ?>" />
    <input type="submit" value="<?php esc_attr_e('Guardar', 'wp-cupon-whatsapp'); ?>" />
</form>

// ✅ Correcto - Procesamiento seguro
if (isset($_POST['wpcw_nonce']) && wp_verify_nonce($_POST['wpcw_nonce'], 'wpcw_save_settings')) {
    $value = sanitize_text_field($_POST['setting_value']);
    update_option('wpcw_setting', $value);
}
```

### Internacionalización

```php
// ✅ Correcto - Strings traducibles
__('Texto a traducir', 'wp-cupon-whatsapp');
esc_html__('Texto con escape', 'wp-cupon-whatsapp');
esc_attr__('Atributo traducible', 'wp-cupon-whatsapp');

// ✅ Correcto - Plurales
_n('1 elemento', '%s elementos', $count, 'wp-cupon-whatsapp');

// ✅ Correcto - Con variables
sprintf(__('Hola %s', 'wp-cupon-whatsapp'), $name);
```

## 📊 Métricas de Calidad

### Objetivos de Calidad

- **Cobertura de linting**: 100%
- **Errores críticos**: 0
- **Advertencias**: < 5 por archivo
- **Complejidad ciclomática**: < 10 por función
- **Líneas por función**: < 50

### Revisión de Código

Antes de cada commit:

1. ✅ Ejecutar `lint-check.ps1`
2. ✅ Verificar que no hay errores
3. ✅ Revisar advertencias
4. ✅ Probar funcionalidad
5. ✅ Verificar seguridad

## 🔄 Proceso de Mejora Continua

1. **Revisión mensual** de estándares
2. **Actualización** de herramientas de linting
3. **Capacitación** del equipo
4. **Métricas** de calidad de código
5. **Feedback** y mejoras

---

**📝 Nota**: Este documento es un estándar vivo que debe actualizarse según las necesidades del proyecto y las mejores prácticas de la industria.

**🔗 Enlaces útiles**:
- [WordPress Coding Standards](https://developer.wordpress.org/coding-standards/)
- [PHP_CodeSniffer](https://github.com/squizlabs/PHP_CodeSniffer)
- [ESLint](https://eslint.org/)
- [Stylelint](https://stylelint.io/)