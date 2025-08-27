# WP Cupón WhatsApp v1.4.0

**Contributors:** (Cristian Farfan/Pragmatic Solutions)
**Tags:** cupones, whatsapp, woocommerce, elementor, lealtad, canje, produccion-masiva, despliegue-automatico
**Requires at least:** 5.0
**Tested up to:** 6.4
**Stable tag:** 1.4.0
**License:** GPLv2 or later
**License URI:** https://www.gnu.org/licenses/gpl-2.0.html

Un plugin para WordPress que integra un sistema de gestión y canje de cupones a través de WhatsApp, con funcionalidades para programas de lealtad y compatibilidad con WooCommerce y Elementor. **Optimizado para producción masiva** en múltiples sitios.

## 🚀 Descripción

WP Cupón WhatsApp te permite crear y gestionar diferentes tipos de cupones que tus clientes pueden visualizar y solicitar canjear a través de mensajes de WhatsApp. Este plugin está diseñado para facilitar la implementación de programas de fidelización y promociones directas.

### Características Principales:

*   Gestión de cupones públicos y de lealtad
*   Formulario de solicitud de adhesión para comercios e instituciones
*   Integración con MongoDB para sincronización y respaldo de datos
*   **🌍 Configuración Automática Regional**: Detección automática de país, zona horaria y configuraciones locales
*   **🚀 Despliegue Masivo**: Scripts automatizados para instalación en cientos de sitios
*   **⚡ Optimización de Rendimiento**: Sistema avanzado de caché y optimización
*   **📊 Logs Centralizados**: Monitoreo y debugging para múltiples sitios
*   **🔧 Comandos WP-CLI**: Gestión avanzada desde línea de comandos
*   **🌐 API REST**: Integración y automatización via API

## 📋 Requisitos del Sistema

### Mínimos
- WordPress 5.0+
- PHP 7.4+
- MySQL 5.6+
- 128MB RAM

### Recomendados para Producción Masiva
- WordPress 6.0+
- PHP 8.1+
- MySQL 8.0+
- 512MB RAM
- WP-CLI instalado
- Acceso SSH

## 📚 Documentación

La documentación detallada del plugin está disponible en los siguientes archivos:

### Documentación Básica
* [Integración con MongoDB](docs/MONGODB_INTEGRATION.md) - Manual completo de configuración y uso de MongoDB
* [Documentación Técnica](docs/TECHNICAL.md) - Detalles técnicos y arquitectura
* [Guía de Integración](docs/INTEGRATION.md) - Guía para desarrolladores

### 🚀 Documentación para Producción Masiva
* **[README-MASS-DEPLOYMENT.md](README-MASS-DEPLOYMENT.md)** - Guía completa de despliegue masivo
* **[DOCUMENTACION_ADMINISTRADORES.md](DOCUMENTACION_ADMINISTRADORES.md)** - Documentación técnica para administradores
* **[MEJORAS_VALIDACION.md](MEJORAS_VALIDACION.md)** - Mejoras de validación implementadas

### Funcionalidades Adicionales
*   Integración con WooCommerce para el tipo de post `shop_coupon`
*   Páginas de "Mis Cupones" y "Cupones Públicos" mediante shortcodes
*   Integración con Google reCAPTCHA v2 para formularios
*   Widgets de Elementor para mostrar listas de cupones y el formulario de adhesión
*   Panel de administración para ajustes, estadísticas y exportación de datos

## Instalación

1.  Sube la carpeta `wp-cupon-whatsapp` al directorio `/wp-content/plugins/`.
2.  Activa el plugin a través del menú 'Plugins' en WordPress.
3.  Ve a "Cupones WPCW" > "Ajustes" para configurar el plugin (ej. reCAPTCHA, campos obligatorios).
4.  Utiliza los shortcodes o los widgets de Elementor para mostrar los cupones y formularios en tu sitio.

## Shortcodes

*   `[wpcw_solicitud_adhesion_form]`: Muestra el formulario para que comercios/instituciones soliciten adherirse.
*   `[wpcw_mis_cupones]`: Muestra los cupones de lealtad disponibles para el usuario actualmente logueado.
*   `[wpcw_cupones_publicos]`: Muestra una lista de todos los cupones públicos disponibles.

## Integración con Elementor

Este plugin incluye widgets para Elementor que te permiten integrar fácilmente la funcionalidad de cupones en páginas construidas con el editor de Elementor.

### Widgets Disponibles

Para encontrar los widgets, busca la categoría **"WP Cupón WhatsApp"** en el panel de widgets de Elementor.

1.  **Lista de Cupones WPCW**
    *   **Descripción:** Muestra una lista personalizable de cupones. Puedes configurar el tipo de cupones a mostrar (públicos o de lealtad para el usuario logueado), la cantidad, el orden, y qué elementos de la tarjeta de cupón son visibles (imagen, código, descripción).
    *   **Personalización:** Ofrece amplias opciones de estilo para el grid de cupones, las tarjetas individuales, imágenes, textos y el botón de canje, todo configurable desde la pestaña "Estilo" de Elementor.

2.  **Formulario Solicitud Adhesión WPCW**
    *   **Descripción:** Incrusta el formulario de solicitud de adhesión para que nuevos comercios o instituciones puedan registrarse en tu programa de cupones.
    *   **Personalización:** Permite modificar los textos y etiquetas del formulario. Ofrece controles de estilo para las etiquetas, campos de entrada, el botón de envío y los mensajes de error/éxito a través de la pestaña "Estilo" de Elementor. La integración con reCAPTCHA (si está configurada en los ajustes del plugin) también es compatible.

### Uso
Simplemente arrastra y suelta los widgets en tus páginas de Elementor y configúralos usando los controles disponibles en el panel lateral del editor.

## Changelog

### 1.1.0 (Fecha Actual)
*   NEW: Añadida integración con Elementor.
*   NEW: Widget "Lista de Cupones WPCW" para Elementor.
*   NEW: Widget "Formulario Solicitud Adhesión WPCW" para Elementor.
*   NEW: Categoría "WP Cupón WhatsApp" en Elementor.

### 1.0.0
*   Lanzamiento inicial del plugin.

## Frequently Asked Questions

*Próximamente*
