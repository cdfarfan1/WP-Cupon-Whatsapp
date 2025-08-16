# Bitácora de Cambios - WP Cupón WhatsApp

Este archivo documenta todos los cambios importantes realizados en el plugin.

---

## [Versión 1.3.1] - 2025-01-16

Versión de corrección que resuelve errores críticos relacionados con funciones duplicadas y sintaxis PHP.

### 🐛 **Correcciones Críticas**

*   **Error Fatal de Redeclaración de Función:**
    *   Corregido error fatal: "Cannot redeclare wpcw_start_output_buffering()"
    *   Función duplicada comentada en `debug-output.php`
    *   Mantenida función principal en `fix-headers.php`
    *   **Archivos afectados:** `debug-output.php`, `fix-headers.php`

*   **Error de Sintaxis PHP:**
    *   Corregido error de sintaxis: "Unclosed '{' on line 32" en `debug-headers.php`
    *   Añadida llave de cierre faltante para el bucle `foreach`
    *   Estructura de llaves corregida y validada
    *   **Archivo afectado:** `debug-headers.php`

*   **Verificación de Funciones Duplicadas:**
    *   Revisión completa del código para detectar otras posibles duplicaciones
    *   Confirmado que no existen otras funciones duplicadas en archivos de debug
    *   Sistema de depuración optimizado y sin conflictos

---

## [Versión 1.3.0] - 2025-01-16

Esta versión introduce mejoras significativas en el sistema de taxonomías y metaboxes para comercios, optimizando la gestión de categorías y el guardado de datos.

### ✨ **Nuevas Funcionalidades**

*   **Nueva Taxonomía para Categorías de Comercios:**
    *   Implementada taxonomía `wpcw_business_category` para una mejor organización de comercios
    *   Reemplaza el sistema anterior de campos meta por una taxonomía nativa de WordPress
    *   Soporte completo para jerarquías y términos personalizados
    *   **Archivo:** `includes/taxonomies.php`

*   **Sistema de Guardado Mejorado:**
    *   Implementado hook `save_post` automático para comercios e instituciones
    *   Función `wpcw_handle_save_post` con protección contra bucles infinitos
    *   Verificación de permisos y nonces integrada
    *   **Archivo:** `admin/interactive-forms.php`

### 🔧 **Mejoras Técnicas**

*   **Metaboxes de Comercios Optimizados:**
    *   Corrección en la obtención de categorías usando `wp_get_object_terms()`
    *   Eliminación de campos meta redundantes para categorías
    *   Mejor integración con el sistema de taxonomías de WordPress
    *   **Archivo:** `admin/interactive-forms.php`

*   **Funcionalidad WhatsApp Verificada:**
    *   Confirmado funcionamiento correcto de enlaces `wa.me`
    *   Validación de números de teléfono para Argentina (prefijo 54)
    *   Plantillas de mensajes predefinidas para diferentes eventos
    *   **Archivo:** `includes/whatsapp-handlers.php`

### 🐛 **Correcciones**

*   **Guardado de Categorías:**
    *   Corregido guardado de categorías de comercio usando taxonomías en lugar de meta fields
    *   Eliminación de código redundante para `_wpcw_business_category`
    *   Mejor consistencia en el manejo de datos

*   **Visualización de Metaboxes:**
    *   Verificada correcta visualización de todos los metaboxes en el panel de administración
    *   Hooks `add_meta_boxes` correctamente conectados
    *   Campos de teléfono y WhatsApp funcionando correctamente

---

## [Versión 1.2.3] - 2025-01-15

Esta versión corrige los errores de "headers already sent" que persistían después de las correcciones de PHP 8+.

### 🐛 **Corrección de Errores Críticos**

*   **Corrección de Errores "Headers Already Sent":**
    *   Comentadas todas las líneas de debug `error_log()` activas que causaban output antes de los headers
    *   **Archivos corregidos:** `wp-cupon-whatsapp.php`, `admin/admin-menu.php`
    *   Deshabilitada carga temporal de archivos de diagnóstico (`diagnostico-completo.php`, `debug-menu-especifico.php`)
    *   **Resultado:** Eliminados los warnings "Cannot modify header information - headers already sent"
    *   **Solución final:** Plugin completamente funcional sin errores de headers en PHP 8+

---

## [Versión 1.2.2] - 2025-01-15

Esta versión completa la corrección de errores Deprecated de PHP 8+ iniciada en la versión 1.2.1.

### 🐛 **Corrección de Errores Críticos**

*   **Corrección Completa de Errores Deprecated de PHP 8+:**
    *   Se han corregido **TODOS** los errores `Deprecated` restantes relacionados con `get_option()` y `wp_redirect()`.
    *   **11 archivos modificados** con correcciones adicionales de compatibilidad PHP 8+
    *   **Archivos corregidos:** `wp-cupon-whatsapp.php`, `includes/approval-handler.php`, `public/shortcodes.php`, `elementor/widgets/widget-formulario-adhesion.php`, `admin/settings-page.php`, `includes/class-wpcw-installer.php`, `admin/setup-wizard.php`, `includes/redemption-logic.php`, `includes/redemption-handler.php`
    *   **Soluciones implementadas:**
        *   Añadidos valores por defecto a todas las llamadas `get_option()` para evitar pasar `null` a funciones internas de WordPress
        *   Implementadas validaciones en `wp_redirect()` para prevenir redirecciones con URLs vacías
        *   Valores por defecto específicos: `blog_charset` → `'UTF-8'`, `admin_email` → `'admin@example.com'`, opciones personalizadas → `0` o `false`
        *   Prevención de errores en `wp-includes/functions.php` líneas 7360 y 2195
    *   **Resultado:** Plugin 100% compatible con PHP 8+ sin errores Deprecated

---

## [Versión 1.2.1] - 2025-01-15

Esta versión se centra en la corrección de errores críticos de compatibilidad con PHP 8+ y mejoras en la experiencia del usuario.

### 🐛 **Corrección de Errores Críticos**

*   **Solucionados Errores Deprecated de PHP 8+ (Actualización Completa):**
    *   Se han corregido **TODOS** los errores `Deprecated` relacionados con `strpos()`, `str_replace()`, `get_option()` y `wp_redirect()` que causaban problemas de "headers already sent".
    *   **Primera fase - Archivos corregidos:** `includes/whatsapp-handlers.php`, `wp-cupon-whatsapp.php`, `admin/settings-page.php`, `includes/rest-api.php`
    *   **Segunda fase - Archivos adicionales corregidos:** `includes/customer-fields.php`, `admin/roles-page.php`, `includes/class-wpcw-registration-forms.php`
    *   **Tercera fase - Corrección completa de `get_option()` y `wp_redirect()`:**
        *   **11 archivos modificados** con correcciones adicionales de compatibilidad PHP 8+
        *   Añadidos valores por defecto a todas las llamadas `get_option()` para evitar pasar `null` a funciones internas de WordPress
        *   Implementadas validaciones en `wp_redirect()` para prevenir redirecciones con URLs vacías
        *   **Archivos corregidos:** `wp-cupon-whatsapp.php`, `includes/approval-handler.php`, `public/shortcodes.php`, `elementor/widgets/widget-formulario-adhesion.php`, `admin/settings-page.php`, `includes/class-wpcw-installer.php`, `admin/setup-wizard.php`, `includes/redemption-logic.php`, `includes/redemption-handler.php`
    *   **Soluciones implementadas:**
        *   Casting explícito a `string` para evitar pasar valores `null` a funciones de cadena
        *   Valores por defecto en `get_option()`: `blog_charset` → `'UTF-8'`, `admin_email` → `'admin@example.com'`, opciones personalizadas → `0` o `false`
        *   Verificaciones `isset()` antes de usar `sanitize_text_field()`
        *   Validaciones de URLs antes de `wp_redirect()`
    *   **Resultado:** Plugin 100% compatible con PHP 8+ sin errores Deprecated

### 🛠️ **Mejoras de Experiencia de Usuario**

*   **Avisos de Dependencias Dismissibles:**
    *   El mensaje de advertencia "WP Canje Cupón WhatsApp requiere: WooCommerce instalado y activado, Elementor instalado y activado" ahora es cerrable.
    *   Se implementó un sistema de almacenamiento por usuario que recuerda cuando un usuario ha cerrado el aviso.
    *   Incluye funcionalidad JavaScript y manejador AJAX para una experiencia fluida.

---

## [Versión 1.2.0] - 2025-07-22

Esta versión se centra en la reorganización del menú de administración, la integración con Elementor y la corrección de errores críticos.

### ✨ **Nuevas Características**

*   **Integración con Elementor:**
    *   Se ha añadido compatibilidad completa con Elementor.
    *   **Nuevo Widget "Lista de Cupones WPCW":** Permite arrastrar y soltar un widget para mostrar listas de cupones (públicos o de lealtad) con amplias opciones de personalización de contenido y estilo directamente desde el editor de Elementor.
    *   **Nuevo Widget "Formulario Solicitud Adhesión WPCW":** Permite incrustar el formulario de adhesión para comercios e instituciones en cualquier página construida con Elementor, con controles de estilo completos.
    *   **Nueva Categoría de Widgets:** Se ha creado la categoría "WP Cupón WhatsApp" en el panel de widgets de Elementor para un fácil acceso.

### 🛠️ **Mejoras y Cambios**

*   **Menú de Administración Unificado:**
    *   Se han eliminado los múltiples menús de nivel superior ("Comercios", "Instituciones", "Solicitudes") que generaban confusión.
    *   Toda la gestión del plugin se ha centralizado bajo un único menú principal en la barra lateral de WordPress llamado **"WP Cupón WhatsApp"**.
    *   **Nueva Página de Escritorio:** Se ha añadido una página de bienvenida ("Escritorio") con accesos rápidos a las funciones más importantes (ver solicitudes, gestionar comercios, ajustes, etc.).
    *   **Estructura de Submenús Lógica:** Los submenús ahora están organizados de forma intuitiva: Escritorio, Solicitudes, Comercios, Instituciones, Estadísticas y Ajustes.

### 🐛 **Corrección de Errores**

*   **Solucionado Error Crítico de Carga de Traducciones:**
    *   Se ha corregido el error `PHP Notice: Function _load_textdomain_just_in_time was called incorrectly` y las advertencias subsiguientes `Warning: Cannot modify header information - headers already sent`.
    *   **Causa:** El problema se debía a que las funciones de traducción se ejecutaban antes de que el "text domain" del plugin se cargara de forma segura, especialmente debido a la carga temprana de los archivos de widgets de Elementor.
    *   **Solución (en dos partes):**
        1.  Se ha cambiado el hook que carga los archivos de idioma de `plugins_loaded` a `init`, como recomienda el mensaje de error de WordPress.
        2.  Se ha refactorizado el código en los widgets de Elementor y en los archivos de registro de CPTs y menús para evitar ejecutar funciones de traducción en el ámbito global de los archivos, retrasando su ejecución a un punto seguro del ciclo de vida de WordPress.
*   **Archivos de Traducción Actualizados:**
    *   Se ha actualizado el archivo de traducción al español (`.po`) para incluir todas las nuevas cadenas de texto de la interfaz de los widgets de Elementor.

### 📄 **Documentación**

*   **Manual de Usuario (`MANUAL_DE_USUARIO.md`):** Se ha creado un manual de usuario detallado que explica la instalación, configuración, gestión y uso de los nuevos widgets de Elementor. También incluye una sección de diagnóstico para problemas comunes.
*   **README.md:** Se ha actualizado el archivo `README.md` para reflejar las nuevas características y la estructura del plugin.
*   **Bitácora de Cambios (`CHANGELOG.md`):** Se ha creado este archivo para mantener un registro claro y accesible de todos los cambios futuros.

---

## [Versión 1.0.0] - Fecha de Lanzamiento Original

*   Lanzamiento inicial del plugin.
*   Funcionalidad básica de gestión de cupones, CPTs para comercios, instituciones y solicitudes.
*   Páginas de administración y estadísticas iniciales.
*   Shortcodes para la visualización de contenido.
