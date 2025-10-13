# 📁 ESTRUCTURA DE CARPETAS ESTÁNDAR - PLUGINS WORDPRESS

## Arquitectura Reutilizable para Proyectos de Cristian Farfan

Esta estructura está diseñada para ser copiada a cualquier nuevo proyecto de plugin WordPress/WooCommerce.

---

## 🏗️ Estructura Completa

```
plugin-name/
│
├── .dev-templates/              # Templates reutilizables (NO incluir en distribución)
│   ├── plugin-header.php
│   ├── class-header.php
│   ├── composer.json
│   ├── package.json
│   ├── README-wordpress.txt
│   └── FOLDER_STRUCTURE.md
│
├── .github/                     # Configuración de GitHub
│   ├── workflows/
│   │   ├── deploy.yml          # Deploy automático a WordPress.org
│   │   ├── phpcs.yml           # Code standards check
│   │   └── phpunit.yml         # Tests automatizados
│   ├── ISSUE_TEMPLATE/
│   │   ├── bug_report.md
│   │   └── feature_request.md
│   └── PULL_REQUEST_TEMPLATE.md
│
├── assets/                      # Assets del plugin
│   ├── css/
│   │   ├── admin/
│   │   │   ├── admin.scss
│   │   │   └── admin.min.css
│   │   └── public/
│   │       ├── public.scss
│   │       └── public.min.css
│   ├── js/
│   │   ├── admin/
│   │   │   ├── admin.js
│   │   │   └── admin.min.js
│   │   └── public/
│   │       ├── public.js
│   │       └── public.min.js
│   ├── images/
│   │   ├── icon-128x128.png
│   │   ├── icon-256x256.png
│   │   └── banner-772x250.png   # Para WordPress.org
│   └── screenshots/              # Screenshots para WordPress.org
│       ├── screenshot-1.png
│       └── screenshot-2.png
│
├── docs/                        # Documentación del proyecto
│   ├── developer/
│   │   ├── DEVELOPER_PROFILE.md
│   │   ├── GETTING_STARTED.md
│   │   └── CONTRIBUTING.md
│   ├── agents/
│   │   ├── PROJECT_STAFF.md
│   │   ├── LESSONS_LEARNED.md
│   │   └── ERROR_REGISTRY.md
│   ├── architecture/
│   │   ├── ARCHITECTURE.md
│   │   ├── DATABASE_SCHEMA.md
│   │   └── HOOKS_REFERENCE.md
│   ├── api/
│   │   ├── REST_API.md
│   │   └── WEBHOOKS.md
│   └── user-guide/
│       ├── INSTALLATION.md
│       ├── CONFIGURATION.md
│       └── FAQ.md
│
├── includes/                    # Código principal del plugin
│   ├── admin/                   # Admin-specific functionality
│   │   ├── class-admin.php
│   │   ├── class-admin-menu.php
│   │   ├── class-admin-settings.php
│   │   ├── partials/            # HTML partials para admin
│   │   │   ├── admin-dashboard.php
│   │   │   └── admin-settings.php
│   │   └── assets/              # Assets específicos de admin
│   │
│   ├── public/                  # Public-facing functionality
│   │   ├── class-public.php
│   │   ├── class-shortcodes.php
│   │   ├── partials/            # HTML partials para public
│   │   │   └── public-display.php
│   │   └── assets/              # Assets específicos de public
│   │
│   ├── core/                    # Core functionality
│   │   ├── class-plugin-name.php          # Main plugin class
│   │   ├── class-loader.php               # Hooks loader
│   │   ├── class-i18n.php                 # Internationalization
│   │   ├── class-activator.php            # Activation hooks
│   │   ├── class-deactivator.php          # Deactivation hooks
│   │   └── class-uninstaller.php          # Uninstall cleanup
│   │
│   ├── api/                     # REST API endpoints
│   │   ├── class-rest-controller.php
│   │   └── endpoints/
│   │       ├── class-endpoint-1.php
│   │       └── class-endpoint-2.php
│   │
│   ├── integrations/            # Integraciones externas
│   │   ├── woocommerce/
│   │   │   ├── class-wc-integration.php
│   │   │   └── class-wc-coupon-extension.php
│   │   ├── whatsapp/
│   │   │   └── class-whatsapp-api.php
│   │   └── elementor/
│   │       └── class-elementor-widgets.php
│   │
│   ├── database/                # Database operations
│   │   ├── class-database.php
│   │   ├── migrations/
│   │   │   ├── migration-001-initial.php
│   │   │   └── migration-002-add-columns.php
│   │   └── seeders/
│   │       └── seeder-demo-data.php
│   │
│   ├── models/                  # Data models
│   │   ├── class-model-base.php
│   │   ├── class-model-coupon.php
│   │   └── class-model-redemption.php
│   │
│   ├── repositories/            # Data access layer
│   │   ├── class-repository-interface.php
│   │   ├── class-coupon-repository.php
│   │   └── class-redemption-repository.php
│   │
│   ├── services/                # Business logic
│   │   ├── class-coupon-service.php
│   │   ├── class-redemption-service.php
│   │   └── class-notification-service.php
│   │
│   ├── helpers/                 # Helper functions
│   │   ├── functions-core.php
│   │   ├── functions-sanitize.php
│   │   └── functions-utilities.php
│   │
│   └── dependencies/            # Third-party libraries (composer)
│
├── languages/                   # Translation files
│   ├── plugin-name.pot         # Template file
│   ├── plugin-name-es_ES.po
│   ├── plugin-name-es_ES.mo
│   ├── plugin-name-en_US.po
│   └── plugin-name-en_US.mo
│
├── templates/                   # Template files
│   ├── admin/
│   │   ├── dashboard.php
│   │   ├── settings.php
│   │   └── reports.php
│   ├── public/
│   │   ├── shortcode-display.php
│   │   └── widget-display.php
│   └── emails/
│       ├── email-header.php
│       ├── email-footer.php
│       └── email-notification.php
│
├── tests/                       # Tests automatizados
│   ├── bootstrap.php
│   ├── unit/                    # Unit tests (PHPUnit)
│   │   ├── test-class-plugin.php
│   │   └── test-class-coupon.php
│   ├── integration/             # Integration tests
│   │   ├── test-api-endpoints.php
│   │   └── test-wc-integration.php
│   └── e2e/                     # End-to-end tests (Cypress)
│       ├── cypress.json
│       └── integration/
│           └── test-user-flow.js
│
├── vendor/                      # Composer dependencies (git ignored)
│
├── node_modules/                # NPM dependencies (git ignored)
│
├── build/                       # Build output (git ignored)
│
├── dist/                        # Distribution files (git ignored)
│
├── .editorconfig               # Editor configuration
├── .eslintrc.js                # ESLint configuration
├── .gitattributes              # Git attributes
├── .gitignore                  # Git ignore rules
├── .distignore                 # WordPress.org distribution ignore
├── .phpcs.xml                  # PHP CodeSniffer rules
├── .phpstan.neon               # PHPStan configuration
│
├── composer.json               # Composer dependencies
├── composer.lock               # Composer lock file
├── package.json                # NPM dependencies
├── package-lock.json           # NPM lock file
├── webpack.config.js           # Webpack build configuration
├── phpunit.xml                 # PHPUnit configuration
│
├── CHANGELOG.md                # Changelog completo
├── LICENSE.txt                 # GPL-2.0+ license
├── README.md                   # README para GitHub
├── readme.txt                  # README para WordPress.org
│
└── plugin-name.php             # Main plugin file (bootstrap)
```

---

## 📦 Archivos Esenciales

### 1. plugin-name.php (Main File)
```php
<?php
/**
 * Plugin Name: [Plugin Name]
 * Author: Cristian Farfan
 * Author URI: https://cristianfarfan.com.ar
 * ...
 */
```

### 2. includes/core/class-plugin-name.php
Clase principal que coordina todo el plugin.

### 3. includes/core/class-loader.php
Registra todos los hooks y filters.

### 4. includes/admin/class-admin.php
Funcionalidad del área de administración.

### 5. includes/public/class-public.php
Funcionalidad del área pública.

---

## 🔧 Configuración de Archivos

### .gitignore
```
# WordPress
/wp-config.php
/wp-content/uploads/
/wp-content/cache/
/wp-content/backup-db/

# Plugin
/vendor/
/node_modules/
/build/
/dist/
*.zip

# IDE
/.idea/
/.vscode/
*.sublime-project
*.sublime-workspace

# OS
.DS_Store
Thumbs.db

# Logs
*.log
error_log
debug.log

# Temporary
*.tmp
*.temp
*.swp
```

### .distignore (para WordPress.org)
```
/.git
/.github
/.dev-templates
/docs
/tests
/node_modules
/vendor
/build
/src

.editorconfig
.eslintrc.js
.gitattributes
.gitignore
.distignore
.phpcs.xml
.phpstan.neon

composer.json
composer.lock
package.json
package-lock.json
webpack.config.js
phpunit.xml

README.md
CHANGELOG.md
*.zip
```

---

## 🚀 Workflow de Desarrollo

### 1. Nuevo Plugin
```bash
# Clonar estructura base
cp -r .dev-templates/ ../nuevo-plugin/

# Reemplazar placeholders
# [PLUGIN_NAME] → Nombre del Plugin
# [plugin-slug] → nombre-del-plugin
# [Plugin_Package] → Nombre_Del_Plugin
# [PLUGIN_PREFIX] → NDP
```

### 2. Desarrollo
```bash
# Instalar dependencias
composer install
npm install

# Desarrollo con watch
npm run dev

# Linting
npm run lint:js
composer phpcs

# Tests
npm run test
composer test
```

### 3. Build para Producción
```bash
# Build optimizado
npm run build

# Crear ZIP para distribución
npm run zip
```

### 4. Deploy a WordPress.org
```bash
# Automático via GitHub Actions
git tag 1.0.0
git push origin 1.0.0
```

---

## 📚 Convenciones de Código

### Naming Conventions

**Archivos:**
- Clases: `class-nombre-de-clase.php`
- Functions: `functions-nombre.php`
- Templates: `template-nombre.php`

**PHP:**
- Clases: `PascalCase` (ej: `Class_Name`)
- Funciones: `snake_case` con prefijo (ej: `pluginprefix_function_name`)
- Variables: `snake_case` (ej: `$variable_name`)
- Constantes: `SCREAMING_SNAKE_CASE` (ej: `PLUGIN_VERSION`)

**JavaScript:**
- Variables/Functions: `camelCase`
- Clases: `PascalCase`
- Constantes: `SCREAMING_SNAKE_CASE`

**CSS/SCSS:**
- BEM Methodology: `block__element--modifier`
- Variables: `$variable-name`

---

## 🎯 Ventajas de Esta Arquitectura

1. **✅ Reutilizable**: Copia toda la estructura a nuevos proyectos
2. **✅ Escalable**: Fácil agregar nuevos módulos
3. **✅ Mantenible**: Código organizado y documentado
4. **✅ Testeada**: Tests automatizados integrados
5. **✅ CI/CD Ready**: GitHub Actions configurado
6. **✅ WordPress.org Ready**: Cumple todos los estándares
7. **✅ Aprendizaje**: Sistema de errores y lecciones aprendidas
8. **✅ Documentada**: Documentación completa incluida

---

## 🤝 Sistema de Agentes Integrado

Cada plugin incluye el sistema de agentes élite que:
- Aprende de errores históricos
- Mantiene registro de lecciones aprendidas
- Aplica mejores prácticas automáticamente
- Documenta decisiones arquitectónicas

---

## 📞 Soporte

**Desarrollador**: Cristian Farfan
**Email**: farfancris@gmail.com
**Web**: https://cristianfarfan.com.ar
**GitHub**: @cdfarfan1

---

**📅 Documento Creado**: 8 de Octubre, 2025
**✍️ Autor**: Sistema de Agentes Élite
**🔄 Última Actualización**: 8 de Octubre, 2025
**📊 Versión**: 1.0.0
