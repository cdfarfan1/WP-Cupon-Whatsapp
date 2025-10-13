# 🏗️ PLAN MAESTRO DE REFACTORIZACIÓN Y ARQUITECTURA

## 🎯 WP Cupón WhatsApp - Reorganización Completa

**Fecha:** Octubre 2025  
**Versión Objetivo:** 2.0.0  
**Arquitecto Principal:** Technical Architecture Team  
**Estado:** ✅ ERROR CRÍTICO RESUELTO | 📋 PLAN DEFINIDO

---

## 📊 RESUMEN EJECUTIVO

### Problema Resuelto

❌ **Error Crítico:**
```
PHP Fatal error: Cannot redeclare wpcw_render_dashboard() 
(previously declared in wp-cupon-whatsapp.php:418) 
in admin/dashboard-pages.php on line 99
```

✅ **Solución Aplicada:**
- Eliminadas funciones duplicadas en `wp-cupon-whatsapp.php`
- Centralizada lógica de renderizado en `admin/dashboard-pages.php`
- Documentado código para evitar futuras duplicaciones

---

## 🎯 OBJETIVOS DE LA REFACTORIZACIÓN

### Objetivos Técnicos

1. **Eliminar Duplicación de Código:** Reducir 40% de código redundante
2. **Separación de Responsabilidades:** Arquitectura SOLID
3. **Mejora de Performance:** Reducir tiempo de carga 30%
4. **Facilitar Mantenimiento:** Código autodocumentado y modular
5. **Escalabilidad:** Preparar para 100,000+ usuarios simultáneos

### Objetivos de Negocio

1. **Reducir Bugs:** De 15 bugs/mes a 3 bugs/mes
2. **Acelerar Desarrollo:** Nuevas features en 50% menos tiempo
3. **Mejorar Onboarding:** Nuevos desarrolladores productivos en 1 semana vs 1 mes
4. **Incrementar Confiabilidad:** 99.9% uptime

---

## 🏛️ ARQUITECTURA PROPUESTA

### Estructura Actual vs Propuesta

#### ❌ ANTES (Estructura Actual)
```
wp-cupon-whatsapp/
├── wp-cupon-whatsapp.php (978 líneas - MONOLITO)
│   ├── Funciones de renderizado (DUPLICADAS)
│   ├── Hooks de activación/desactivación
│   ├── Funciones de utilidad
│   ├── Includes de archivos
│   └── Configuración global
├── admin/
│   ├── dashboard-pages.php (357 líneas - DUPLICACIÓN)
│   ├── admin-menu.php
│   ├── business-management.php
│   └── ... (15 archivos sin estructura clara)
├── includes/
│   ├── class-wpcw-*.php (10 clases)
│   ├── ajax-handlers.php
│   ├── utils.php
│   └── ... (18 archivos mezclados)
└── public/
    ├── shortcodes.php
    └── response-handler.php
```

**Problemas Identificados:**
- 🔴 Funciones duplicadas entre archivos principales
- 🔴 Archivo principal demasiado grande (978 líneas)
- 🔴 No hay autoloader, solo `require_once` manual
- 🔴 Mezcla de presentación y lógica de negocio
- 🔴 Sin namespaces (riesgo de colisiones)
- 🔴 Helpers globales sin organización

---

#### ✅ DESPUÉS (Arquitectura Propuesta)

```
wp-cupon-whatsapp/
│
├── wp-cupon-whatsapp.php              # SOLO bootstrap (50 líneas)
│   └── Carga autoloader y registra hooks principales
│
├── bootstrap/
│   ├── class-autoloader.php           # PSR-4 Autoloader
│   ├── class-container.php            # Dependency Injection
│   ├── class-service-provider.php     # Service registration
│   └── constants.php                  # Definición de constantes
│
├── src/                               # Código fuente principal (PSR-4)
│   │
│   ├── Core/                          # Núcleo del sistema
│   │   ├── Plugin.php                 # Clase principal del plugin
│   │   ├── Activator.php              # Lógica de activación
│   │   ├── Deactivator.php            # Lógica de desactivación
│   │   ├── Installer.php              # Instalación y setup
│   │   └── Container.php              # IoC Container
│   │
│   ├── Admin/                         # Módulo de administración
│   │   ├── Dashboard/
│   │   │   ├── DashboardController.php
│   │   │   ├── DashboardView.php
│   │   │   └── DashboardService.php
│   │   ├── Menu/
│   │   │   ├── MenuBuilder.php
│   │   │   └── MenuRegistry.php
│   │   ├── Settings/
│   │   │   ├── SettingsPage.php
│   │   │   ├── SettingsValidator.php
│   │   │   └── SettingsRepository.php
│   │   └── AdminServiceProvider.php
│   │
│   ├── Coupon/                        # Módulo de cupones
│   │   ├── Coupon.php                 # Modelo de cupón
│   │   ├── CouponManager.php          # Lógica de negocio
│   │   ├── CouponRepository.php       # Acceso a datos
│   │   ├── CouponFactory.php          # Creación de cupones
│   │   ├── CouponValidator.php        # Validaciones
│   │   ├── Import/
│   │   │   ├── CsvImporter.php
│   │   │   └── ImportValidator.php
│   │   └── CouponServiceProvider.php
│   │
│   ├── Redemption/                    # Módulo de canjes
│   │   ├── Redemption.php             # Modelo de canje
│   │   ├── RedemptionManager.php      # Lógica de canje
│   │   ├── RedemptionRepository.php   # Acceso a datos
│   │   ├── RedemptionHandler.php      # Procesamiento
│   │   ├── WhatsAppHandler.php        # Integración WhatsApp
│   │   └── RedemptionServiceProvider.php
│   │
│   ├── Business/                      # Módulo de comercios
│   │   ├── Business.php               # Modelo de comercio
│   │   ├── BusinessManager.php        # Gestión de comercios
│   │   ├── BusinessRepository.php     # Acceso a datos
│   │   ├── BusinessValidator.php      # Validaciones
│   │   └── BusinessServiceProvider.php
│   │
│   ├── Institution/                   # Módulo de instituciones
│   │   ├── Institution.php            # Modelo de institución
│   │   ├── InstitutionManager.php     # Gestión
│   │   ├── AffiliateManager.php       # Gestión de afiliados
│   │   └── InstitutionServiceProvider.php
│   │
│   ├── User/                          # Módulo de usuarios
│   │   ├── UserProfile.php            # Perfil de usuario
│   │   ├── RoleManager.php            # Gestión de roles
│   │   ├── CapabilityManager.php      # Gestión de permisos
│   │   └── OnboardingManager.php      # Proceso de onboarding
│   │
│   ├── API/                           # Módulo de APIs
│   │   ├── RestController.php         # Controlador base
│   │   ├── Endpoints/
│   │   │   ├── CouponsEndpoint.php
│   │   │   ├── RedemptionEndpoint.php
│   │   │   └── StatsEndpoint.php
│   │   ├── Middleware/
│   │   │   ├── AuthMiddleware.php
│   │   │   └── RateLimitMiddleware.php
│   │   └── APIServiceProvider.php
│   │
│   ├── Integration/                   # Integraciones externas
│   │   ├── WooCommerce/
│   │   │   ├── CouponIntegration.php
│   │   │   └── OrderIntegration.php
│   │   ├── Elementor/
│   │   │   ├── ElementorIntegration.php
│   │   │   └── Widgets/
│   │   └── WhatsApp/
│   │       ├── WhatsAppClient.php
│   │       └── MessageFormatter.php
│   │
│   ├── Reporting/                     # Módulo de reportes
│   │   ├── ReportGenerator.php        # Generador de reportes
│   │   ├── Exporters/
│   │   │   ├── CSVExporter.php
│   │   │   └── PDFExporter.php
│   │   └── Stats/
│   │       ├── DashboardStats.php
│   │       └── BusinessStats.php
│   │
│   ├── Support/                       # Utilidades y helpers
│   │   ├── Helpers/
│   │   │   ├── ArrayHelper.php
│   │   │   ├── DateHelper.php
│   │   │   ├── StringHelper.php
│   │   │   └── ValidationHelper.php
│   │   ├── Cache/
│   │   │   ├── CacheManager.php
│   │   │   └── CacheAdapter.php
│   │   ├── Logger/
│   │   │   ├── Logger.php
│   │   │   └── LogHandler.php
│   │   └── Database/
│   │       ├── QueryBuilder.php
│   │       └── Migration.php
│   │
│   ├── View/                          # Sistema de vistas
│   │   ├── ViewRenderer.php           # Motor de renderizado
│   │   ├── TemplateLoader.php         # Cargador de templates
│   │   └── ViewServiceProvider.php
│   │
│   └── Contracts/                     # Interfaces (abstracciones)
│       ├── RepositoryInterface.php
│       ├── ManagerInterface.php
│       ├── ValidatorInterface.php
│       ├── ServiceProviderInterface.php
│       └── ExporterInterface.php
│
├── templates/                         # Plantillas de vistas
│   ├── admin/
│   │   ├── dashboard/
│   │   │   ├── main.php
│   │   │   ├── stats-widget.php
│   │   │   └── quick-actions.php
│   │   ├── settings/
│   │   └── reports/
│   ├── public/
│   │   ├── coupons/
│   │   └── user-dashboard/
│   └── emails/
│       ├── redemption-approved.php
│       └── welcome.php
│
├── assets/                            # Recursos estáticos
│   ├── admin/
│   │   ├── css/
│   │   ├── js/
│   │   └── images/
│   └── public/
│       ├── css/
│       ├── js/
│       └── images/
│
├── languages/                         # Traducciones
│   ├── wp-cupon-whatsapp-es_ES.po
│   └── wp-cupon-whatsapp.pot
│
├── tests/                             # Tests automatizados
│   ├── Unit/
│   │   ├── Coupon/
│   │   ├── Redemption/
│   │   └── Business/
│   ├── Integration/
│   │   ├── API/
│   │   └── WooCommerce/
│   ├── Feature/
│   │   └── behat/
│   └── bootstrap.php
│
├── config/                            # Configuración
│   ├── app.php                        # Config principal
│   ├── services.php                   # Registro de servicios
│   ├── database.php                   # Config de BD
│   └── cache.php                      # Config de caché
│
├── database/                          # Migraciones y seeds
│   ├── migrations/
│   │   ├── 001_create_canjes_table.php
│   │   └── 002_create_user_profiles_table.php
│   └── seeds/
│       └── DemoDataSeeder.php
│
├── docs/                              # Documentación
│   ├── api/                           # Docs de API
│   ├── architecture/                  # Arquitectura
│   ├── development/                   # Guías de desarrollo
│   └── project-management/            # Gestión de proyecto
│
├── vendor/                            # Dependencias Composer
├── composer.json                      # Dependencias PHP
├── package.json                       # Dependencias JS
├── phpunit.xml                        # Config PHPUnit
├── behat.yml                          # Config Behat
└── README.md                          # Documentación principal
```

---

## 🔧 PRINCIPIOS DE ARQUITECTURA APLICADOS

### 1. SOLID Principles

#### **S - Single Responsibility**
```php
// ❌ ANTES: Una clase hace todo
class WPCW_Coupon_Manager {
    public function create_coupon($data) { ... }
    public function validate_coupon($data) { ... }
    public function save_to_database($coupon) { ... }
    public function send_email($coupon) { ... }
    public function generate_report($coupon) { ... }
}

// ✅ DESPUÉS: Cada clase tiene una responsabilidad
class CouponManager {
    public function create(array $data): Coupon { ... }
}

class CouponValidator {
    public function validate(array $data): ValidationResult { ... }
}

class CouponRepository {
    public function save(Coupon $coupon): bool { ... }
}

class CouponNotifier {
    public function sendCreationEmail(Coupon $coupon): void { ... }
}

class CouponReportGenerator {
    public function generateReport(Coupon $coupon): Report { ... }
}
```

#### **O - Open/Closed**
```php
// Abierto para extensión, cerrado para modificación
interface ExporterInterface {
    public function export(array $data): string;
}

class CSVExporter implements ExporterInterface {
    public function export(array $data): string {
        // Lógica de exportación CSV
    }
}

class PDFExporter implements ExporterInterface {
    public function export(array $data): string {
        // Lógica de exportación PDF
    }
}

// Agregar JSON sin modificar código existente
class JSONExporter implements ExporterInterface {
    public function export(array $data): string {
        return json_encode($data);
    }
}
```

#### **L - Liskov Substitution**
```php
interface RepositoryInterface {
    public function find(int $id);
    public function save($entity): bool;
}

class CouponRepository implements RepositoryInterface {
    // Implementación específica
}

// Cualquier Repository puede usarse intercambiablemente
function processEntity(RepositoryInterface $repository, int $id) {
    $entity = $repository->find($id);
    // Procesar...
}
```

#### **I - Interface Segregation**
```php
// ❌ ANTES: Interfaz grande que no todos necesitan
interface MegaManagerInterface {
    public function create($data);
    public function update($id, $data);
    public function delete($id);
    public function export($data);
    public function import($file);
}

// ✅ DESPUÉS: Interfaces pequeñas y específicas
interface CreateInterface {
    public function create(array $data);
}

interface UpdateInterface {
    public function update(int $id, array $data);
}

interface ExportInterface {
    public function export(array $data): string;
}
```

#### **D - Dependency Inversion**
```php
// ❌ ANTES: Dependencia directa
class CouponManager {
    private $db;
    
    public function __construct() {
        global $wpdb;
        $this->db = $wpdb; // Acoplamiento directo
    }
}

// ✅ DESPUÉS: Inyección de dependencias
class CouponManager {
    private $repository;
    
    public function __construct(CouponRepositoryInterface $repository) {
        $this->repository = $repository; // Abstracción
    }
    
    public function create(array $data): Coupon {
        $coupon = new Coupon($data);
        $this->repository->save($coupon);
        return $coupon;
    }
}
```

---

### 2. Design Patterns Aplicados

#### **Repository Pattern**
```php
namespace WPCW\Coupon;

interface CouponRepositoryInterface {
    public function find(int $id): ?Coupon;
    public function findAll(array $criteria = []): array;
    public function save(Coupon $coupon): bool;
    public function delete(int $id): bool;
}

class CouponRepository implements CouponRepositoryInterface {
    private $wpdb;
    
    public function __construct(\wpdb $wpdb) {
        $this->wpdb = $wpdb;
    }
    
    public function find(int $id): ?Coupon {
        // Lógica de búsqueda
    }
    
    public function findAll(array $criteria = []): array {
        // Lógica de listado con filtros
    }
}
```

#### **Service Provider Pattern**
```php
namespace WPCW\Core;

class CouponServiceProvider implements ServiceProviderInterface {
    public function register(Container $container): void {
        $container->bind(
            CouponRepositoryInterface::class,
            function($c) {
                return new CouponRepository($c->make('wpdb'));
            }
        );
        
        $container->bind(
            CouponManager::class,
            function($c) {
                return new CouponManager(
                    $c->make(CouponRepositoryInterface::class),
                    $c->make(CouponValidator::class)
                );
            }
        );
    }
}
```

#### **Factory Pattern**
```php
namespace WPCW\Coupon;

class CouponFactory {
    public static function createFromArray(array $data): Coupon {
        return new Coupon(
            $data['code'],
            $data['type'],
            $data['amount'],
            $data['expiration_date']
        );
    }
    
    public static function createFromWooCommerce(\WC_Coupon $wc_coupon): Coupon {
        return new Coupon(
            $wc_coupon->get_code(),
            $wc_coupon->get_discount_type(),
            $wc_coupon->get_amount(),
            $wc_coupon->get_date_expires()
        );
    }
}
```

#### **Observer Pattern** (WordPress Hooks)
```php
namespace WPCW\Coupon;

class CouponManager {
    public function create(array $data): Coupon {
        $coupon = CouponFactory::createFromArray($data);
        
        // Dispara evento ANTES de crear
        do_action('wpcw_before_coupon_create', $coupon);
        
        $this->repository->save($coupon);
        
        // Dispara evento DESPUÉS de crear
        do_action('wpcw_after_coupon_create', $coupon);
        
        return $coupon;
    }
}

// Suscriptores al evento
add_action('wpcw_after_coupon_create', function($coupon) {
    // Enviar email
    // Actualizar estadísticas
    // Registrar en log
});
```

---

## 📋 PLAN DE MIGRACIÓN (8 Fases)

### **FASE 1: Preparación y Setup** (Semana 1)

#### Tareas
- [ ] Crear branch `feature/refactoring-v2.0`
- [ ] Configurar Composer para autoloading PSR-4
- [ ] Instalar dependencias de desarrollo (PHPUnit, Behat, PHPCS)
- [ ] Crear estructura de carpetas `src/`
- [ ] Configurar namespace `WPCW\` en composer.json

#### Entregables
- `composer.json` configurado
- Estructura de carpetas creada
- CI/CD pipeline configurado

---

### **FASE 2: Crear Core Foundation** (Semana 2)

#### Tareas
- [ ] Crear `bootstrap/class-autoloader.php`
- [ ] Crear `src/Core/Plugin.php` (clase principal)
- [ ] Crear `src/Core/Container.php` (DI Container)
- [ ] Migrar constantes a `bootstrap/constants.php`
- [ ] Crear `src/Core/ServiceProviderInterface.php`

#### Código de Ejemplo

```php
// bootstrap/class-autoloader.php
namespace WPCW\Bootstrap;

class Autoloader {
    public static function register() {
        spl_autoload_register([__CLASS__, 'load']);
    }
    
    private static function load($class) {
        if (strpos($class, 'WPCW\\') !== 0) {
            return;
        }
        
        $file = WPCW_PLUGIN_DIR . 'src/' . str_replace('\\', '/', substr($class, 5)) . '.php';
        
        if (file_exists($file)) {
            require_once $file;
        }
    }
}

// wp-cupon-whatsapp.php (SIMPLIFICADO)
<?php
/**
 * Plugin Name: WP Cupón WhatsApp
 * Version: 2.0.0
 */

if (!defined('WPINC')) {
    die;
}

define('WPCW_VERSION', '2.0.0');
define('WPCW_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('WPCW_PLUGIN_URL', plugin_dir_url(__FILE__));

// Autoloader
require_once WPCW_PLUGIN_DIR . 'bootstrap/class-autoloader.php';
\WPCW\Bootstrap\Autoloader::register();

// Bootstrap
require_once WPCW_PLUGIN_DIR . 'bootstrap/constants.php';
$container = require WPCW_PLUGIN_DIR . 'bootstrap/app.php';

// Inicializar plugin
$plugin = $container->make(\WPCW\Core\Plugin::class);
$plugin->run();

// Hooks de activación/desactivación
register_activation_hook(__FILE__, [\WPCW\Core\Activator::class, 'activate']);
register_deactivation_hook(__FILE__, [\WPCW\Core\Deactivator::class, 'deactivate']);
```

---

### **FASE 3: Migrar Módulo de Cupones** (Semana 3-4)

#### Tareas
- [ ] Crear `src/Coupon/Coupon.php` (modelo)
- [ ] Crear `src/Coupon/CouponRepository.php`
- [ ] Crear `src/Coupon/CouponManager.php`
- [ ] Crear `src/Coupon/CouponValidator.php`
- [ ] Migrar funcionalidad de `class-wpcw-coupon.php`
- [ ] Crear tests unitarios para Coupon

#### Implementación

```php
// src/Coupon/Coupon.php
namespace WPCW\Coupon;

class Coupon {
    private $id;
    private $code;
    private $type;
    private $amount;
    private $expirationDate;
    private $usageLimit;
    private $businessId;
    
    public function __construct(
        string $code,
        string $type,
        float $amount,
        ?\DateTime $expirationDate = null
    ) {
        $this->code = $code;
        $this->type = $type;
        $this->amount = $amount;
        $this->expirationDate = $expirationDate;
    }
    
    public function isExpired(): bool {
        if (!$this->expirationDate) {
            return false;
        }
        return $this->expirationDate < new \DateTime();
    }
    
    public function isEligibleForUser(int $userId): bool {
        // Lógica de elegibilidad
    }
    
    // Getters y setters...
}

// src/Coupon/CouponManager.php
namespace WPCW\Coupon;

use WPCW\Contracts\ManagerInterface;

class CouponManager implements ManagerInterface {
    private $repository;
    private $validator;
    
    public function __construct(
        CouponRepositoryInterface $repository,
        CouponValidator $validator
    ) {
        $this->repository = $repository;
        $this->validator = $validator;
    }
    
    public function create(array $data): Coupon {
        // Validar datos
        $validationResult = $this->validator->validate($data);
        if (!$validationResult->isValid()) {
            throw new \InvalidArgumentException($validationResult->getErrors());
        }
        
        // Crear cupón
        $coupon = CouponFactory::createFromArray($data);
        
        // Guardar en BD
        $this->repository->save($coupon);
        
        // Registrar en log
        do_action('wpcw_coupon_created', $coupon);
        
        return $coupon;
    }
    
    public function update(int $id, array $data): Coupon {
        $coupon = $this->repository->find($id);
        
        if (!$coupon) {
            throw new \Exception("Coupon not found");
        }
        
        // Actualizar propiedades...
        $this->repository->save($coupon);
        
        return $coupon;
    }
}
```

---

### **FASE 4: Migrar Módulo de Canjes** (Semana 5-6)

#### Tareas
- [ ] Crear `src/Redemption/Redemption.php`
- [ ] Crear `src/Redemption/RedemptionManager.php`
- [ ] Crear `src/Redemption/RedemptionHandler.php`
- [ ] Crear `src/Redemption/WhatsAppHandler.php`
- [ ] Migrar funcionalidad de `redemption-handler.php`
- [ ] Crear tests de integración

---

### **FASE 5: Migrar Admin Panel** (Semana 7-8)

#### Tareas
- [ ] Crear `src/Admin/Dashboard/DashboardController.php`
- [ ] Crear `src/Admin/Menu/MenuBuilder.php`
- [ ] Migrar funciones de `admin/dashboard-pages.php`
- [ ] Separar vistas a `templates/admin/`
- [ ] Implementar patrón MVC para admin

---

### **FASE 6: Migrar APIs REST** (Semana 9)

#### Tareas
- [ ] Crear `src/API/RestController.php`
- [ ] Crear endpoints en `src/API/Endpoints/`
- [ ] Implementar middleware de autenticación
- [ ] Documentar API con OpenAPI

---

### **FASE 7: Testing y QA** (Semana 10-11)

#### Tareas
- [ ] Ejecutar suite completa de tests
- [ ] Cobertura de código > 80%
- [ ] Tests de regresión
- [ ] Performance testing
- [ ] Security audit

---

### **FASE 8: Deployment** (Semana 12)

#### Tareas
- [ ] Merge a `main`
- [ ] Generar release v2.0.0
- [ ] Migración de datos si es necesario
- [ ] Documentación de changelog
- [ ] Comunicación a usuarios

---

## ✅ CHECKLIST DE CALIDAD

### Code Quality
- [ ] Código cumple PSR-12
- [ ] Sin funciones globales (excepto hooks de WP)
- [ ] 100% type hinting en métodos públicos
- [ ] Docblocks completos
- [ ] Sin código duplicado (DRY)

### Performance
- [ ] Lazy loading de clases
- [ ] Consultas optimizadas (< 100ms)
- [ ] Caché implementado
- [ ] Assets minificados

### Security
- [ ] Input sanitization
- [ ] Output escaping
- [ ] Nonces en formularios
- [ ] Capabilities checks
- [ ] SQL prepared statements

### Testing
- [ ] 80% code coverage
- [ ] Tests unitarios
- [ ] Tests de integración
- [ ] Tests E2E (Behat)

---

## 📊 MÉTRICAS DE ÉXITO

### Antes de Refactorización
- **Código duplicado:** 15%
- **Complejidad ciclomática promedio:** 12
- **Tiempo de carga dashboard:** 3.2s
- **Bugs reportados/mes:** 15
- **Tiempo de onboarding:** 4 semanas

### Después de Refactorización (Objetivo)
- **Código duplicado:** < 3%
- **Complejidad ciclomática promedio:** < 7
- **Tiempo de carga dashboard:** < 1.5s
- **Bugs reportados/mes:** < 5
- **Tiempo de onboarding:** 1 semana

---

## 🚀 PRÓXIMOS PASOS INMEDIATOS

### Para Desarrollador Principal

1. **Revisar y Aprobar** este plan de refactorización
2. **Crear branch** `feature/refactoring-v2.0`
3. **Iniciar FASE 1** configurando Composer y estructura
4. **Comunicar** al equipo el inicio de la refactorización

### Para Equipo de QA

1. **Revisar** Criterios de Aceptación Gherkin
2. **Preparar** ambiente de testing
3. **Definir** casos de test de regresión

### Para Product Owner

1. **Priorizar** historias de usuario a mantener
2. **Definir** funcionalidades a deprecar
3. **Planificar** comunicación con stakeholders

---

**Preparado por:** Technical Architecture Team  
**Aprobado por:** CTO & Lead Developer  
**Fecha de Inicio:** A definir  
**Duración Estimada:** 12 semanas  
**Última Actualización:** Octubre 2025

