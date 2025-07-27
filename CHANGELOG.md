# Bitácora de Cambios - WP Cupón WhatsApp

Este archivo documenta todos los cambios importantes realizados en el plugin.

---

## [Versión 1.2.0] - 2025-07-22 (En Desarrollo)

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
