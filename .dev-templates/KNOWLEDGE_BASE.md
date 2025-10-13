# 🧠 BASE DE CONOCIMIENTO ACUMULATIVA
## Sistema de Aprendizaje Cross-Project para Cristian Farfan

**Versión**: 1.0.0
**Última Actualización**: 8 de Octubre, 2025
**Proyectos Completados**: 1 (WP Cupón WhatsApp)

---

## 📋 ÍNDICE

1. [Cómo Usar Este Documento](#cómo-usar-este-documento)
2. [Errores Aprendidos](#errores-aprendidos)
3. [Soluciones Exitosas](#soluciones-exitosas)
4. [Patrones de Código Probados](#patrones-de-código-probados)
5. [Integraciones Dominadas](#integraciones-dominadas)
6. [Checklist Pre-Deploy](#checklist-pre-deploy)
7. [Snippets Reutilizables](#snippets-reutilizables)
8. [Historial de Proyectos](#historial-de-proyectos)

---

## 🎯 CÓMO USAR ESTE DOCUMENTO

### Al INICIAR un nuevo plugin:

1. **LEER** este documento completo
2. **COPIAR** la carpeta `.dev-templates/` al nuevo proyecto
3. **ACTUALIZAR** la sección [Historial de Proyectos](#historial-de-proyectos)
4. **APLICAR** lecciones aprendidas desde el día 1

### Al TERMINAR un plugin:

1. **DOCUMENTAR** nuevos errores en [Errores Aprendidos](#errores-aprendidos)
2. **AGREGAR** soluciones exitosas en [Soluciones Exitosas](#soluciones-exitosas)
3. **REGISTRAR** nuevos snippets en [Snippets Reutilizables](#snippets-reutilizables)
4. **ACTUALIZAR** contador de "Proyectos Completados" arriba
5. **COPIAR** este archivo actualizado a `.dev-templates/` para el próximo proyecto

---

## ❌ ERRORES APRENDIDOS

### Formato de Registro:
```
### E[número] - [Título del Error]
**Proyecto**: [Nombre del proyecto donde ocurrió]
**Fecha**: [Fecha]
**Problema**: [Descripción del problema]
**Causa Raíz**: [Por qué ocurrió]
**Solución**: [Cómo se resolvió]
**Prevención**: [Cómo evitarlo en futuros proyectos]
**Código de Ejemplo**: [Si aplica]
```

---

### E001 - Funciones Duplicadas en Archivos Múltiples
**Proyecto**: WP Cupón WhatsApp
**Fecha**: 7 de Octubre, 2025
**Problema**: `wpcw_render_dashboard()` declarada en `wp-cupon-whatsapp.php` y `admin/dashboard-pages.php`
**Causa Raíz**: Copiar/pegar código sin verificar si la función ya existe
**Solución**: Eliminar duplicados del archivo principal, mantener en archivos dedicados
**Prevención**:
- Usar `function_exists()` antes de declarar funciones
- Linter con regla de funciones duplicadas
- Pre-commit hook que detecta duplicados

**Código de Prevención**:
```php
if ( ! function_exists( 'my_function' ) ) {
    function my_function() {
        // código
    }
}
```

---

### E002 - Funciones sin Prefijo del Plugin
**Proyecto**: WP Cupón WhatsApp
**Fecha**: 7 de Octubre, 2025
**Problema**: Funciones globales sin prefijo `wpcw_`
**Causa Raíz**: No seguir naming conventions de WordPress
**Solución**: Renombrar todas las funciones con prefijo consistente
**Prevención**:
- Template de función con prefijo
- PHPStan rule personalizada
- Code review checklist

**Regla**:
```
SIEMPRE usar prefijo: {plugin_prefix}_nombre_funcion()
Nunca: nombre_funcion()
```

---

### E003 - Falta de Sanitización en Formularios
**Proyecto**: WP Cupón WhatsApp
**Fecha**: 7 de Octubre, 2025
**Problema**: Input de usuario sin sanitizar antes de guardar
**Causa Raíz**: Olvidar sanitizar en algunos campos
**Solución**: Wrapper function que sanitiza automáticamente
**Prevención**:
- Security linter obligatorio
- Template de formulario con sanitización incluida
- Code review enfocado en seguridad

**Código de Prevención**:
```php
// NUNCA hacer:
update_option( 'my_option', $_POST['value'] );

// SIEMPRE hacer:
$value = sanitize_text_field( wp_unslash( $_POST['value'] ?? '' ) );
update_option( 'my_option', $value );
```

---

### E004 - Columnas de BD con Tipos Incorrectos
**Proyecto**: WP Cupón WhatsApp
**Fecha**: 7 de Octubre, 2025
**Problema**: Columna `INT` usada para guardar JSON
**Causa Raíz**: No planificar schema antes de crear tabla
**Solución**: Migración para cambiar tipo de columna
**Prevención**:
- Documentar schema ANTES de crear tabla
- Usar migrations con rollback
- Validar tipos de datos en código

**Template de Schema**:
```php
// SIEMPRE documentar:
/**
 * Tabla: wp_prefix_table_name
 *
 * Columnas:
 * - id (BIGINT UNSIGNED AUTO_INCREMENT)
 * - user_id (BIGINT UNSIGNED) - FK a wp_users
 * - data (LONGTEXT) - JSON serializado
 * - status (VARCHAR(20)) - ENUM simulado
 * - created_at (DATETIME)
 */
```

---

### E005 - JavaScript sin Minificar en Producción
**Proyecto**: WP Cupón WhatsApp
**Fecha**: 7 de Octubre, 2025
**Problema**: Archivos JS sin minificar aumentan tiempo de carga
**Causa Raíz**: Build process incompleto
**Solución**: Webpack configurado con minificación
**Prevención**:
- npm run build antes de deploy
- CI/CD que valida archivos minificados
- .distignore excluye archivos source

**Script npm**:
```json
"scripts": {
  "build": "webpack --mode production",
  "prebuild": "rm -rf dist/",
  "postbuild": "ls -lh dist/"
}
```

---

### E006 - Arquitectura Monolítica (Archivo >1000 líneas)
**Proyecto**: WP Cupón WhatsApp
**Fecha**: 7 de Octubre, 2025
**Problema**: `wp-cupon-whatsapp.php` con 1013 líneas
**Causa Raíz**: No aplicar Single Responsibility Principle
**Solución**: Extraer funcionalidad a archivos dedicados
**Prevención**:
- Límite máximo: 500 líneas por archivo
- Complejidad ciclomática máxima: 10
- Refactorizar cuando archivo crece >300 líneas

**Regla**:
```
1 archivo = 1 responsabilidad
Máximo 500 líneas por archivo PHP
```

---

### E007 - Archivos sin Headers PHP Documentados
**Proyecto**: WP Cupón WhatsApp
**Fecha**: 7 de Octubre, 2025
**Problema**: Archivos PHP sin docblock de cabecera
**Causa Raíz**: No usar template
**Solución**: Template de header en `.dev-templates/`
**Prevención**:
- Pre-commit hook valida headers
- Snippet de VS Code
- Template obligatorio

---

### E008 - Schema de BD Desactualizado vs Código
**Proyecto**: WP Cupón WhatsApp
**Fecha**: 8 de Octubre, 2025
**Problema**: Código espera 17 columnas, BD tiene 5
**Causa Raíz**: Migración no ejecutada en producción
**Solución**: Sistema de migración automática al activar plugin
**Prevención**:
- Migrations automáticas en activación
- Verificar schema en cada request (modo debug)
- CI/CD valida schema vs código

---

### E009 - plugins_url() con dirname(__FILE__) en PHP 8.2
**Proyecto**: WP Cupón WhatsApp
**Fecha**: 8 de Octubre, 2025
**Problema**: Deprecated warning en PHP 8.2
**Causa Raíz**: Uso incorrecto de `dirname(__FILE__)` como segundo parámetro
**Solución**: Usar constante `PLUGIN_URL` en su lugar
**Prevención**:
- Linter detecta patrón incorrecto
- Template usa constantes
- Code review

**Código Correcto**:
```php
// ❌ NUNCA:
plugins_url( 'assets/css/style.css', dirname( __FILE__ ) )

// ✅ SIEMPRE:
PLUGIN_PREFIX_URL . 'assets/css/style.css'
```

---

### E010 - Permisos Incorrectos en Páginas Admin
**Proyecto**: WP Cupón WhatsApp
**Fecha**: 8 de Octubre, 2025
**Problema**: "No tienes permisos" en páginas con capability incorrecto
**Causa Raíz**: No documentar matriz de capabilities
**Solución**: Matriz de permisos documentada
**Prevención**:
- Documentar capabilities requeridas
- Usar constantes para capabilities
- Testing de permisos

**Template**:
```php
// Documentar:
/**
 * Required Capability: manage_options
 * Roles allowed: Administrator
 */
add_menu_page(
    'Page Title',
    'Menu Title',
    'manage_options', // ← Documentado arriba
    'menu-slug',
    'callback'
);
```

---

### E011 - No Verificar Impacto de Cambios
**Proyecto**: WP Cupón WhatsApp
**Fecha**: 8 de Octubre, 2025
**Problema**: Cambio en función core rompe múltiples features
**Causa Raíz**: No tener dependency graph
**Solución**: Documentar dependencias entre componentes
**Prevención**:
- Dependency graph automatizado
- Tests de integración
- Code impact analysis

---

### E012 - UI sin Pruebas de Usabilidad
**Proyecto**: WP Cupón WhatsApp
**Fecha**: 8 de Octubre, 2025
**Problema**: Interfaz confusa para usuarios finales
**Causa Raíz**: No validar con usuarios reales
**Solución**: A/B testing y feedback de usuarios
**Prevención**:
- Prototipos antes de código
- Testing con usuarios beta
- Analytics de uso

---

## ✅ SOLUCIONES EXITOSAS

### S001 - Sistema de Migración Automática de BD
**Proyecto**: WP Cupón WhatsApp
**Descripción**: Sistema que detecta schema desactualizado y migra automáticamente
**Resultado**: 0 errores de schema en producción
**Código**: Ver `includes/class-wpcw-installer-fixed.php`
**Reutilizable**: ✅ Sí

**Implementación**:
```php
1. verify_table_structure() - detecta columnas faltantes
2. migrate_table_schema() - ejecuta ALTER TABLE
3. create_table_indexes() - índices en background
4. Registro en option 'plugin_table_migration_completed'
```

---

### S002 - PHP 8.x Compatibility Layer
**Proyecto**: WP Cupón WhatsApp
**Descripción**: Error handler que suprime warnings deprecated de WordPress core
**Resultado**: 0 warnings en PHP 8.2
**Código**: Ver `includes/php8-compat.php`
**Reutilizable**: ✅ Sí

---

### S003 - Arquitectura de Carpetas Modular
**Proyecto**: WP Cupón WhatsApp
**Descripción**: Estructura que separa admin/public/core/api/integrations
**Resultado**: Código fácil de mantener y escalar
**Documentación**: Ver `.dev-templates/FOLDER_STRUCTURE.md`
**Reutilizable**: ✅ Sí (copiar toda la estructura)

---

### S004 - Sistema de Agentes con Aprendizaje
**Proyecto**: WP Cupón WhatsApp
**Descripción**: 10 agentes especializados que aprenden de errores
**Resultado**: Errores no se repiten entre proyectos
**Documentación**: Ver `docs/agents/PROJECT_STAFF.md`
**Reutilizable**: ✅ Sí (este sistema!)

---

## 🔧 PATRONES DE CÓDIGO PROBADOS

### Patrón: Singleton para Clase Principal
**Uso**: Clase principal del plugin
**Ventaja**: Una sola instancia, acceso global
**Código**:
```php
class Plugin_Name {
    private static $instance = null;

    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        // Inicialización
    }
}

// Uso:
$plugin = Plugin_Name::get_instance();
```

---

### Patrón: Hooks Loader
**Uso**: Registrar todos los hooks en un solo lugar
**Ventaja**: Fácil debug, código organizado
**Código**:
```php
class Plugin_Loader {
    protected $actions = array();
    protected $filters = array();

    public function add_action( $hook, $component, $callback, $priority = 10, $accepted_args = 1 ) {
        $this->actions = $this->add( $this->actions, $hook, $component, $callback, $priority, $accepted_args );
    }

    public function run() {
        foreach ( $this->actions as $hook ) {
            add_action( $hook['hook'], array( $hook['component'], $hook['callback'] ), $hook['priority'], $hook['accepted_args'] );
        }
    }
}
```

---

### Patrón: Repository Pattern para Datos
**Uso**: Acceso a base de datos
**Ventaja**: Lógica de BD separada de negocio
**Código**:
```php
interface Repository_Interface {
    public function find( $id );
    public function all();
    public function create( $data );
    public function update( $id, $data );
    public function delete( $id );
}

class Coupon_Repository implements Repository_Interface {
    private $table_name;

    public function find( $id ) {
        global $wpdb;
        return $wpdb->get_row(
            $wpdb->prepare( "SELECT * FROM {$this->table_name} WHERE id = %d", $id )
        );
    }

    // ... otros métodos
}
```

---

## 🔌 INTEGRACIONES DOMINADAS

### WhatsApp API
**Proyecto**: WP Cupón WhatsApp
**Aprendizajes**:
- URL format: `https://wa.me/{phone}?text={encoded_message}`
- Validar formato de teléfono: código país + número
- URL encode del mensaje
**Snippet**: Ver [Snippets Reutilizables](#snippets-reutilizables)

---

### WooCommerce HPOS (High-Performance Order Storage)
**Proyecto**: WP Cupón WhatsApp
**Aprendizajes**:
- Declarar compatibilidad en plugin header
- Usar `wc_get_order()` en vez de acceso directo a post meta
- Testing en ambos modos (legacy y HPOS)
**Código**:
```php
add_action( 'before_woocommerce_init', function() {
    if ( class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility(
            'custom_order_tables',
            __FILE__,
            true
        );
    }
});
```

---

### WordPress REST API
**Proyecto**: WP Cupón WhatsApp
**Aprendizajes**:
- Namespace format: `plugin-name/v1`
- Siempre usar `permission_callback`
- Sanitizar input en `args`
**Template**:
```php
register_rest_route( 'plugin/v1', '/endpoint', array(
    'methods'             => 'GET',
    'callback'            => 'callback_function',
    'permission_callback' => function() {
        return current_user_can( 'manage_options' );
    },
    'args'                => array(
        'id' => array(
            'required'          => true,
            'validate_callback' => function( $param ) {
                return is_numeric( $param );
            },
            'sanitize_callback' => 'absint',
        ),
    ),
));
```

---

## ✅ CHECKLIST PRE-DEPLOY

### Seguridad
- [ ] Todos los inputs sanitizados
- [ ] Nonces en todos los formularios
- [ ] Capabilities verificadas en cada página admin
- [ ] No hay SQL injection vulnerabilities
- [ ] XSS protection en outputs
- [ ] CSRF tokens en AJAX

### Performance
- [ ] CSS minificado
- [ ] JS minificado
- [ ] Imágenes optimizadas
- [ ] Queries con índices apropiados
- [ ] Caching implementado donde corresponde
- [ ] No hay N+1 query problems

### Compatibilidad
- [ ] PHP 7.4+ tested
- [ ] WordPress 5.8+ tested
- [ ] WooCommerce 6.0+ tested (si aplica)
- [ ] No deprecated functions
- [ ] HPOS compatible (WooCommerce)

### Código
- [ ] No syntax errors (php -l)
- [ ] PHPCS passed
- [ ] PHPStan level 5+ passed
- [ ] No funciones duplicadas
- [ ] Todas las funciones con prefijo
- [ ] Archivos con headers documentados

### Documentación
- [ ] README.md actualizado
- [ ] readme.txt para WordPress.org
- [ ] CHANGELOG.md actualizado
- [ ] Comentarios en código complejo
- [ ] API documentada

### Testing
- [ ] Tests unitarios passed
- [ ] Tests de integración passed
- [ ] Testing manual en ambiente staging
- [ ] Testing con usuarios beta (si es posible)

### Base de Datos
- [ ] Migrations tested
- [ ] Rollback tested
- [ ] Backup creado antes de migration
- [ ] Índices creados
- [ ] Schema documentado

---

## 📦 SNIPPETS REUTILIZABLES

### Generar URL de WhatsApp
```php
/**
 * Generate WhatsApp URL
 *
 * @param string $phone Phone number with country code (e.g., "5491112345678")
 * @param string $message Message to send
 * @return string WhatsApp URL
 */
function generate_whatsapp_url( $phone, $message ) {
    // Remove spaces, dashes, parentheses
    $phone = preg_replace( '/[^0-9]/', '', $phone );

    // Encode message
    $encoded_message = rawurlencode( $message );

    return sprintf(
        'https://wa.me/%s?text=%s',
        $phone,
        $encoded_message
    );
}
```

---

### Verificar Schema de Tabla
```php
/**
 * Verify table has required columns
 *
 * @param string $table_name Table name (without prefix)
 * @param array  $required_columns Array of column names
 * @return array Missing columns
 */
function verify_table_schema( $table_name, $required_columns ) {
    global $wpdb;

    $full_table_name = $wpdb->prefix . $table_name;

    // Get existing columns
    $columns = $wpdb->get_results( "DESCRIBE {$full_table_name}" );
    $existing_columns = array();

    foreach ( $columns as $column ) {
        $existing_columns[] = $column->Field;
    }

    // Find missing columns
    $missing = array();
    foreach ( $required_columns as $required ) {
        if ( ! in_array( $required, $existing_columns ) ) {
            $missing[] = $required;
        }
    }

    return $missing;
}
```

---

### AJAX Handler Template
```php
/**
 * AJAX handler template
 */
function plugin_ajax_handler() {
    // Security check
    check_ajax_referer( 'plugin_nonce', 'nonce' );

    // Permission check
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( array(
            'message' => __( 'Insufficient permissions', 'text-domain' ),
        ) );
    }

    // Get and validate input
    $input = isset( $_POST['input'] ) ? sanitize_text_field( wp_unslash( $_POST['input'] ) ) : '';

    if ( empty( $input ) ) {
        wp_send_json_error( array(
            'message' => __( 'Input required', 'text-domain' ),
        ) );
    }

    // Process
    $result = do_something( $input );

    // Return
    if ( $result ) {
        wp_send_json_success( array(
            'message' => __( 'Success', 'text-domain' ),
            'data'    => $result,
        ) );
    } else {
        wp_send_json_error( array(
            'message' => __( 'Error processing request', 'text-domain' ),
        ) );
    }
}
add_action( 'wp_ajax_plugin_action', 'plugin_ajax_handler' );
```

---

### Crear Página Automáticamente
```php
/**
 * Create page programmatically
 *
 * @param string $title Page title
 * @param string $slug  Page slug
 * @param string $content Page content
 * @return int|WP_Error Page ID or error
 */
function create_page_if_not_exists( $title, $slug, $content ) {
    // Check if page already exists
    $page = get_page_by_path( $slug );

    if ( $page ) {
        return $page->ID;
    }

    // Create page
    $page_id = wp_insert_post( array(
        'post_title'   => $title,
        'post_name'    => $slug,
        'post_content' => $content,
        'post_status'  => 'publish',
        'post_type'    => 'page',
        'post_author'  => 1,
    ) );

    return $page_id;
}
```

---

## 📊 HISTORIAL DE PROYECTOS

### Proyecto #1: WP Cupón WhatsApp
**Inicio**: Septiembre 2024
**Finalización**: Octubre 2025
**Versión Final**: 1.5.1
**Descripción**: Plugin de fidelización con cupones canjeables por WhatsApp para WooCommerce
**Stack**: WordPress 6.4, WooCommerce 8.5, PHP 8.2, MySQL 8.0
**Logros**:
- Sistema de cupones con QR
- Canje vía WhatsApp
- Dashboard con estadísticas
- Integración con WooCommerce HPOS
- Sistema de roles personalizado
- Migración automática de BD

**Errores Aprendidos**: 12 (E001 - E012)
**Soluciones Creadas**: 4 (S001 - S004)
**Líneas de Código**: ~8,500
**Archivos PHP**: 47
**Tiempo Total**: ~160 horas

**Documentación Generada**:
- LESSONS_LEARNED.md
- HANDOFF_GUIDE.md
- DEVELOPER_PROFILE.md
- PROJECT_STAFF.md (10 agentes)
- FOLDER_STRUCTURE.md

**Lecciones Clave**:
1. Migración automática de BD es esencial
2. PHP 8.2 compatibility layer necesario
3. Arquitectura modular facilita mantenimiento
4. Documentar TODO desde día 1

---

### Proyecto #2: [Próximo Plugin]
**Inicio**: [Fecha]
**Estado**: Pendiente
**Descripción**: [Descripción]

_Al completar: copiar este template y llenar con información del proyecto_

---

## 🎯 MÉTRICAS DE MEJORA

### Objetivo: Reducir Errores en Cada Proyecto

| Métrica | Proyecto #1 | Proyecto #2 | Proyecto #3 | Meta |
|---------|-------------|-------------|-------------|------|
| Errores Críticos | 12 | - | - | 0 |
| Tiempo de Debug | 40h | - | - | <10h |
| Funciones Duplicadas | 4 | - | - | 0 |
| Warnings PHP 8.2 | 8 | - | - | 0 |
| Cobertura Tests | 0% | - | - | 80% |

---

## 🔄 PROCESO DE ACTUALIZACIÓN

### Cuando termines un proyecto:

1. **Agregar errores nuevos** a [Errores Aprendidos](#errores-aprendidos)
2. **Documentar soluciones** en [Soluciones Exitosas](#soluciones-exitosas)
3. **Crear snippets** de código reutilizable
4. **Actualizar historial** con el proyecto completado
5. **Incrementar contador** de "Proyectos Completados" arriba
6. **Copiar archivo actualizado** a `.dev-templates/KNOWLEDGE_BASE.md`
7. **Commit y push** para tener backup

### Al empezar nuevo proyecto:

1. **Copiar** `.dev-templates/` completa
2. **Leer** este documento completo
3. **Revisar** errores aprendidos antes de empezar
4. **Aplicar** soluciones exitosas desde día 1

---

## 💡 PRÓXIMAS MEJORAS A IMPLEMENTAR

- [ ] Testing automatizado (PHPUnit + Cypress)
- [ ] CI/CD con GitHub Actions
- [ ] Cobertura de tests >80%
- [ ] Pre-commit hooks automáticos
- [ ] Dependency graph automatizado
- [ ] Performance monitoring
- [ ] Error tracking (Sentry o similar)

---

**📅 Creado**: 8 de Octubre, 2025
**✍️ Autor**: Cristian Farfan con Sistema de Agentes Élite
**🔄 Última Actualización**: 8 de Octubre, 2025
**📊 Versión**: 1.0.0
**📈 Proyectos en Base**: 1

---

**🎯 Lema**: *"Cada error es una lección. Cada proyecto nos hace mejores."*
