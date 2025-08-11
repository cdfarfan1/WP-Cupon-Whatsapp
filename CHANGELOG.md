# Bitácora de Cambios - WP Cupón WhatsApp

Este archivo documenta todos los cambios importantes realizados en el plugin.

---

## [Versión 1.3.0] - 2025-08-11

### 🛠️ **Mejoras y Cambios**

*   **Refactorización del Menú de Administración:** Se ha refactorizado y corregido la lógica de creación de menús para garantizar que todos los elementos (Comercios, Instituciones, etc.) se agrupen de forma fiable bajo un único panel principal "WP Cupón WhatsApp", evitando la creación de menús de nivel superior duplicados.
*   **Organización del Código:** Se han eliminado archivos de prueba y depuración (`debug-menu.php`, `diagnostico-temp.php`, `menu-test.php`) que causaban errores, y se ha centralizado el código de diagnóstico en un único archivo dentro de la carpeta `admin`.

### 🐛 **Corrección de Errores**

*   **Solucionado Error Crítico de PHP:** Se han corregido los errores `PHP Deprecated` y `Warning: Cannot modify header information` que aparecían en los logs.
    *   **Causa:** El problema se debía a la ejecución duplicada de la función de registro de menús, causada por un archivo de prueba (`menu-test.php`) que se estaba cargando incorrectamente.
    *   **Solución:** Se ha eliminado el archivo de prueba problemático, resolviendo la causa raíz de los errores.

### 📄 **Documentación**

*   **Actualización de Versión:** El número de versión del plugin se ha actualizado a `1.3.0` en todos los archivos relevantes.

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
