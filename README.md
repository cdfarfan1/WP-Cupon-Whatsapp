# 🎫 WP Cupón WhatsApp

**Plugin de WordPress para programas de fidelización y canje de cupones por WhatsApp**

[![Version](https://img.shields.io/badge/version-1.5.0-blue.svg)](https://github.com/cdfarfan1/WP-Cupon-Whatsapp)
[![WordPress](https://img.shields.io/badge/wordpress-5.0+-blue.svg)](https://wordpress.org/)
[![WooCommerce](https://img.shields.io/badge/woocommerce-6.0+-purple.svg)](https://woocommerce.com/)
[![PHP](https://img.shields.io/badge/php-7.4+-red.svg)](https://php.net/)
[![License](https://img.shields.io/badge/license-GPL--2.0+-green.svg)](https://www.gnu.org/licenses/gpl-2.0.html)

## 📋 Descripción

WP Cupón WhatsApp es un plugin completo para WordPress que permite crear y gestionar programas de fidelización con canje de cupones a través de WhatsApp. Está completamente integrado con WooCommerce y es compatible con Elementor.

### ✨ Características Principales

- 🎫 **Sistema de Cupones Avanzado**: Creación masiva, importación CSV, gestión por lotes
- 📱 **Integración WhatsApp**: Canje directo con confirmación automática
- 🏪 **Gestión de Comercios**: Sistema completo para múltiples comercios
- 👥 **Roles y Permisos**: Control granular de acceso
- 🔧 **Panel de Administración**: Dashboard completo con estadísticas
- 🔗 **APIs REST**: Integración con sistemas externos
- 🎨 **Shortcodes**: Fácil integración en páginas
- 🧩 **Elementor Widgets**: Interfaz visual drag-and-drop
- 📊 **Reportes Avanzados**: Estadísticas detalladas y exportación
- 🔒 **Seguridad**: Validación completa y protección CSRF
- 🌍 **Internacionalización**: Soporte multi-idioma

## 🚀 Instalación

### Requisitos del Sistema

- **WordPress**: 5.0 o superior
- **WooCommerce**: 6.0 o superior
- **PHP**: 7.4 o superior
- **MySQL**: 5.6 o superior
- **Elementor**: 3.0+ (opcional pero recomendado)

### Instalación Automática

1. Ve al panel de administración de WordPress
2. Navega a **Plugins > Añadir nuevo**
3. Busca "WP Cupón WhatsApp"
4. Haz clic en **Instalar ahora**
5. Activa el plugin

### Instalación Manual

1. **Descarga el plugin**:
   ```bash
   git clone https://github.com/cdfarfan1/WP-Cupon-Whatsapp.git
   ```

2. **Sube los archivos**:
   - Sube la carpeta `wp-cupon-whatsapp` a `/wp-content/plugins/`
   - O usa el instalador de WordPress para subir el archivo ZIP

3. **Activa el plugin**:
   - Ve a **Plugins** en el panel de administración
   - Busca "WP Cupón WhatsApp" y haz clic en **Activar**

4. **Configuración inicial**:
   - El plugin se configurará automáticamente
   - Se crearán las tablas de base de datos necesarias
   - Se registrarán los roles de usuario

## ⚙️ Configuración

### Configuración Básica

1. Ve a **WP Cupón WhatsApp > Configuración**
2. Configura los siguientes parámetros:
   - **API de WhatsApp Business**: Token de autenticación
   - **Número de WhatsApp**: Número para confirmaciones
   - **Mensaje de Cupón**: Plantilla de mensaje personalizado

### Configuración Avanzada

#### Roles de Usuario

El plugin crea automáticamente los siguientes roles:

- **Administrador del Sistema**: Control total
- **Dueño de Comercio**: Gestión de su comercio
- **Personal de Comercio**: Gestión de canjes
- **Gestor de Institución**: Administración institucional
- **Cliente**: Acceso a cupones

#### Permisos y Capacidades

Cada rol tiene capacidades específicas asignadas automáticamente.

## 📖 Uso

### Para Administradores

#### Crear un Comercio

1. Ve a **WP Cupón WhatsApp > Comercios**
2. Haz clic en **Añadir Nuevo**
3. Completa la información del comercio:
   - Nombre y descripción
   - Información de contacto
   - Dirección y logo
4. Asigna un usuario como dueño del comercio

#### Crear Cupones

1. Ve a **WooCommerce > Cupones**
2. Haz clic en **Añadir Cupón**
3. Configura los detalles del cupón
4. En la pestaña "WP Cupón WhatsApp":
   - Marca "Habilitado para WhatsApp"
   - Selecciona el tipo de cupón
   - Configura el comercio asociado

#### Gestionar Canjes

1. Ve a **WP Cupón WhatsApp > Canjes**
2. Revisa las solicitudes pendientes
3. Confirma o rechaza canjes
4. Usa acciones masivas para procesar múltiples canjes

### Para Comercios

#### Gestionar Cupones

1. Ve a **Mis Cupones** (desde el perfil de usuario)
2. Crea nuevos cupones para tu comercio
3. Gestiona cupones existentes
4. Revisa estadísticas de uso

#### Procesar Canjes

1. Recibe notificaciones de canje por WhatsApp
2. Confirma o rechaza solicitudes
3. Genera códigos de cupón automáticamente

### Para Clientes

#### Canjear Cupones

1. Navega a la página de cupones
2. Selecciona un cupón disponible
3. Haz clic en "Canjear por WhatsApp"
4. Envía el mensaje de confirmación
5. Recibe el código de cupón final

## 🎨 Shortcodes

### Formulario de Adhesión
```php
[wpcw_solicitud_adhesion_form]
```

### Lista de Cupones
```php
[wpcw_mis_cupones]
[wpcw_cupones_publicos]
```

### Canje de Cupones
```php
[wpcw_canje_cupon]
```

### Dashboard de Usuario
```php
[wpcw_dashboard_usuario]
```

## 🧩 Elementor Integration

### Widgets Disponibles

1. **Formulario de Adhesión WPCW**
   - Formulario completo de registro de comercios
   - Validación en tiempo real
   - Mensajes de éxito/error

2. **Lista de Cupones WPCW**
   - Muestra cupones disponibles
   - Filtros por tipo y categoría
   - Paginación automática

3. **Dashboard de Usuario WPCW**
   - Panel completo del usuario
   - Información personal
   - Historial de canjes

### Cómo Usar los Widgets

1. Edita una página con Elementor
2. Busca "WP Cupón WhatsApp" en la biblioteca de widgets
3. Arrastra el widget deseado al lienzo
4. Configura las opciones en el panel lateral

## 🔗 APIs REST

### Endpoints Disponibles

#### Confirmación de Canjes
```
GET /wp-json/wpcw/v1/confirm-redemption
```
Parámetros:
- `token`: Token de confirmación único
- `canje_id`: ID del registro de canje

#### Estadísticas
```
GET /wp-json/wpcw/v1/stats
```
Parámetros:
- `period`: Período (day, week, month, year)
- `type`: Tipo de estadística

#### Cupones
```
GET /wp-json/wpcw/v1/coupons
```
Parámetros:
- `user_id`: ID del usuario
- `type`: Tipo de cupón (loyalty, public)
- `limit`: Número máximo de resultados

### Autenticación

Las APIs requieren autenticación mediante:
- Nonces de WordPress
- Tokens JWT (opcional)
- Claves de API (para integraciones externas)

## 🔧 Desarrollo

### Estructura del Plugin

```
wp-cupon-whatsapp/
├── wp-cupon-whatsapp.php          # Archivo principal
├── includes/                      # Clases principales
│   ├── class-wpcw-business-manager.php
│   ├── class-wpcw-coupon-manager.php
│   ├── class-wpcw-redemption-manager.php
│   ├── class-wpcw-rest-api.php
│   ├── class-wpcw-shortcodes.php
│   ├── class-wpcw-elementor.php
│   └── widgets/                   # Widgets de Elementor
├── admin/                         # Panel de administración
│   ├── admin-menu.php
│   ├── business-management.php
│   ├── coupon-meta-boxes.php
│   └── css/js/                    # Assets admin
├── public/                        # Frontend
│   ├── shortcodes.php
│   └── css/js/                    # Assets frontend
├── templates/                     # Plantillas
├── languages/                     # Traducciones
├── tests/                         # Tests
└── docs/                          # Documentación
```

### Hooks y Filtros

#### Actions
- `wpcw_before_coupon_redemption`
- `wpcw_after_coupon_redemption`
- `wpcw_business_registered`
- `wpcw_coupon_created`

#### Filters
- `wpcw_coupon_redemption_message`
- `wpcw_business_registration_fields`
- `wpcw_dashboard_stats`

### Desarrollo de Extensiones

Para crear extensiones del plugin:

```php
// Ejemplo de extensión
class Mi_Extension_WPCW {
    public function __construct() {
        add_action( 'wpcw_init', array( $this, 'init' ) );
        add_filter( 'wpcw_coupon_types', array( $this, 'add_coupon_type' ) );
    }

    public function init() {
        // Código de inicialización
    }

    public function add_coupon_type( $types ) {
        $types['mi_tipo'] = __( 'Mi Tipo de Cupón', 'mi-extension' );
        return $types;
    }
}

new Mi_Extension_WPCW();
```

## 🧪 Testing

### Ejecutar Tests

```bash
# Tests unitarios
phpunit --testsuite unit

# Tests de integración
phpunit --testsuite integration

# Tests de rendimiento
phpunit --testsuite performance

# Todos los tests
phpunit
```

### Cobertura de Código

```bash
# Generar reporte de cobertura
phpunit --coverage-html coverage-report
```

## 🔒 Seguridad

### Medidas Implementadas

- ✅ **Validación de Datos**: Sanitización completa de todas las entradas
- ✅ **Protección CSRF**: Nonces en todos los formularios
- ✅ **Control de Acceso**: Verificación de capacidades de usuario
- ✅ **SQL Injection**: Prepared statements en todas las consultas
- ✅ **XSS Protection**: Escape de datos en output
- ✅ **Rate Limiting**: Límite de solicitudes por IP
- ✅ **Logging**: Registro completo de actividades

### Mejores Prácticas de Seguridad

1. **Mantén el plugin actualizado**
2. **Usa contraseñas fuertes**
3. **Configura permisos de archivo correctamente**
4. **Realiza backups regulares**
5. **Monitorea los logs de actividad**

## 🌍 Internacionalización

### Idiomas Soportados

- Español (es_ES) - Completo
- Inglés (en_US) - Base
- Portugués (pt_BR) - Próximamente

### Añadir Nuevo Idioma

1. Crea archivo `.po` en `/languages/`
2. Usa herramientas como Poedit
3. Carga las traducciones
4. Actualiza el archivo `.mo`

## 📊 Reportes y Estadísticas

### Reportes Disponibles

#### Por Comercio
- Canjes realizados
- Cupones más utilizados
- Ingresos generados
- Usuarios activos

#### Por Institución
- Participación de usuarios
- Efectividad de cupones
- Canjes por período
- Métricas de fidelización

#### Globales
- Total de canjes
- Usuarios registrados
- Comercios activos
- Tendencias de uso

### Exportación de Datos

- CSV para análisis en Excel
- PDF para reportes formales
- JSON para integraciones
- XML para sistemas legacy

## 🆘 Soporte

### 📚 Documentación Completa

#### 📋 **Estado del Proyecto**
- 📊 **[Estado Actual del Proyecto](docs/PROJECT_STATUS.md)**: Resumen ejecutivo, métricas de completitud, problemas conocidos
- ✅ **[Funcionalidades Implementadas](docs/IMPLEMENTED_FEATURES.md)**: Lista completa de features con estado de implementación
- 🚀 **[Guía para Continuación](docs/CONTINUATION_GUIDE.md)**: Instrucciones paso a paso para desarrollo futuro

#### 🏗️ **Arquitectura y Diseño**
- 🏛️ **[Arquitectura y Estructura](docs/ARCHITECTURE_OVERVIEW.md)**: Visión general de componentes y patrones
- 🏗️ **[Arquitectura Completa](docs/ARCHITECTURE.md)**: Diseño técnico detallado por etapas
- 🗄️ **[Esquema de Base de Datos](docs/DATABASE_SCHEMA.md)**: Estructura completa de tablas y relaciones

#### 🔧 **Referencias Técnicas**
- 🔗 **[API Reference](docs/API_REFERENCE.md)**: Documentación completa de endpoints REST
- 🔧 **[Referencias Técnicas](docs/TECHNICAL_REFERENCE.md)**: Base de datos, hooks, configuración, constantes
- 🔒 **[Seguridad](docs/SECURITY.md)**: Guías de seguridad y mejores prácticas

#### 📈 **Desarrollo y Roadmap**
- 🗺️ **[Implementation Roadmap](docs/IMPLEMENTATION_ROADMAP.md)**: Plan completo de desarrollo por fases
- 📊 **[Phase 2 Completion](docs/PHASE2_COMPLETION.md)**: Reporte de finalización del sistema de cupones
- 📈 **[Phase 3 Progress](docs/PHASE3_WEEKS17-18_COMPLETION.md)**: Estado actual del panel de administración

#### 🎨 **Integraciones y Componentes**
- 🧩 **[Elementor Integration](docs/ELEMENTOR.md)**: Documentación de widgets y compatibilidad
- 🔄 **[Formularios Interactivos](docs/FORMULARIOS_INTERACTIVOS.md)**: Sistema de formularios avanzados
- 🌐 **[Integrations](docs/INTEGRATION.md)**: Conexiones con sistemas externos

#### 📖 **Guías de Usuario**
- 📖 **[Manual de Usuario](docs/MANUAL_DE_USUARIO.md)**: Guía completa para usuarios finales
- 🔧 **[Guía de Instalación](docs/GUIA_INSTALACION.md)**: Instalación y configuración paso a paso
- 📋 **[Data Dictionary](docs/DATA_DICTIONARY.md)**: Diccionario de datos del sistema

#### 🔍 **Documentación Adicional**
- 🧪 **[Testing](docs/TESTING.md)**: Estrategias y procedimientos de testing
- 🔧 **[Dependencies](docs/DEPENDENCIES.md)**: Librerías y dependencias del proyecto
- 💾 **[MongoDB Integration](docs/MONGODB_INTEGRATION.md)**: Integración con MongoDB (experimental)
- ⚡ **[Optimizations](docs/OPTIMIZATIONS.md)**: Guías de optimización de performance
- 👥 **[User Onboarding](docs/USER_ONBOARDING_ROADMAP.md)**: Flujo de incorporación de usuarios

### Canales de Soporte

- 📧 **Email**: support@pragmaticsolutions.com.ar
- 💬 **Foro**: [WordPress.org Support](https://wordpress.org/support/plugin/wp-cupon-whatsapp/)
- 📱 **WhatsApp**: +54 9 11 1234-5678
- 🐛 **Issues**: [GitHub Issues](https://github.com/cdfarfan1/WP-Cupon-Whatsapp/issues)

### Canales de Soporte

- 📧 **Email**: support@pragmaticsolutions.com.ar
- 💬 **Foro**: [WordPress.org Support](https://wordpress.org/support/plugin/wp-cupon-whatsapp/)
- 📱 **WhatsApp**: +54 9 11 1234-5678
- 🐛 **Issues**: [GitHub Issues](https://github.com/cdfarfan1/WP-Cupon-Whatsapp/issues)

### Reportar Bugs

Para reportar un bug:

1. Ve a [GitHub Issues](https://github.com/cdfarfan1/WP-Cupon-Whatsapp/issues)
2. Usa la plantilla de bug report
3. Incluye:
   - Versión del plugin
   - Versión de WordPress/WooCommerce
   - Pasos para reproducir
   - Logs de error (si aplica)

## 📝 Registro de Cambios

### Versión 1.5.0
- ✅ Sistema completo de gestión de cupones
- ✅ Integración WhatsApp funcional
- ✅ Panel de administración avanzado
- ✅ APIs REST completas
- ✅ Widgets de Elementor
- ✅ Sistema de roles y permisos
- ✅ Reportes y estadísticas
- ✅ Seguridad de nivel empresarial

### Próximas Versiones

- 🔄 Sincronización con MongoDB
- 🔄 Aplicación móvil nativa
- 🔄 Integración con Zapier
- 🔄 Sistema de notificaciones push
- 🔄 Análisis predictivo

## 👥 Contribuir

¡Las contribuciones son bienvenidas!

### Cómo Contribuir

1. Fork el repositorio
2. Crea una rama para tu feature (`git checkout -b feature/AmazingFeature`)
3. Commit tus cambios (`git commit -m 'Add some AmazingFeature'`)
4. Push a la rama (`git push origin feature/AmazingFeature`)
5. Abre un Pull Request

### Guías de Contribución

- Sigue las [WordPress Coding Standards](https://developer.wordpress.org/coding-standards/wordpress-coding-standards/)
- Escribe tests para nuevas funcionalidades
- Actualiza la documentación
- Usa commits descriptivos

## 📄 Licencia

Este plugin está licenciado bajo la **GPL v2 o posterior**.

```
WP Cupón WhatsApp
Copyright (C) 2025, Pragmatic Solutions

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.
```

## 🙏 Créditos

**Desarrollado por**: [Pragmatic Solutions](https://www.pragmaticsolutions.com.ar)

**Colaboradores**:
- Cristian Farfan (Lead Developer)
- Equipo de QA y Testing
- Comunidad WordPress

## 📞 Contacto

**Pragmatic Solutions**
- 🌐 Website: [www.pragmaticsolutions.com.ar](https://www.pragmaticsolutions.com.ar)
- 📧 Email: info@pragmaticsolutions.com.ar
- 📱 WhatsApp: +54 9 11 1234-5678
- 🐦 Twitter: [@pragmaticsolutions](https://twitter.com/pragmaticsolutions)
- 💼 LinkedIn: [Pragmatic Solutions](https://linkedin.com/company/pragmaticsolutions)

---

**🎉 ¡Gracias por usar WP Cupón WhatsApp!**

Si te gusta este plugin, por favor considera:
- ⭐ Darle una estrella en GitHub
- 📝 Dejar una reseña en WordPress.org
- 🔗 Compartirlo con la comunidad
- 💝 Hacer una donación para apoyar el desarrollo

¡Tu apoyo nos ayuda a seguir mejorando! 🚀
