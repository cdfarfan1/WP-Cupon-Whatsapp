# 🚀 GUÍA DE HANDOFF - WP CUPÓN WHATSAPP

> **PROPÓSITO**: Guía práctica paso a paso para que cualquier desarrollador, agente o IA pueda tomar el proyecto y empezar a trabajar en **15 minutos**.

**Creado por**: Dr. Maria Santos (Documentador Técnico) + Marcus Chen (Arquitecto)
**Fecha**: Octubre 2025
**Versión**: 1.0 - Guía de Transferencia Completa
**Tiempo estimado de setup**: 15-30 minutos

---

## 🎯 OBJETIVO DE ESTA GUÍA

Esta guía está diseñada para que **TÚ** (nuevo desarrollador, agente IA, o colaborador) puedas:

1. ✅ **Configurar tu entorno** de desarrollo en menos de 30 minutos
2. ✅ **Entender la estructura** del código inmediatamente
3. ✅ **Saber qué agente activar** para cada tipo de tarea
4. ✅ **Seguir el flujo de trabajo** correcto desde día 1
5. ✅ **Evitar errores comunes** que nos costaron 40+ horas
6. ✅ **Hacer tu primer commit** con confianza

---

## 📚 PASO 0: DOCUMENTOS QUE DEBES LEER PRIMERO (15 minutos)

**ORDEN OBLIGATORIO** (no saltar ninguno):

1. **[README.md](../README.md)** - Visión general del plugin (5 min)
2. **[docs/CONTEXT.md](CONTEXT.md)** - Contexto completo del proyecto (10 min de lectura rápida)
3. **[docs/LESSONS_LEARNED.md](LESSONS_LEARNED.md)** - Errores que NO debes repetir (5 min)
4. **[agents/PROJECT_STAFF.md](agents/PROJECT_STAFF.md)** - Sistema de agentes (3 min)
5. **[troubleshooting/REFACTORIZACION_COMPLETADA.md](troubleshooting/REFACTORIZACION_COMPLETADA.md)** - Estado actual (5 min)

**Total**: ~28 minutos de lectura enfocada

**💡 TIP**: Si tienes poco tiempo, lee al menos CONTEXT.md y LESSONS_LEARNED.md antes de tocar código.

---

## 🛠️ PASO 1: CONFIGURAR ENTORNO DE DESARROLLO (15-30 minutos)

### **1.1 Requisitos del Sistema**

**Software necesario**:
```bash
# Verificar versiones instaladas
php -v          # Necesitas PHP 7.4+
mysql --version # MySQL 5.6+
node -v         # Node 14+ (para assets)
git --version   # Git 2.0+
```

**Stack recomendado**:
- **Local by Flywheel** (recomendado) - setup más rápido
- **XAMPP** - alternativa clásica
- **MAMP** (Mac) - alternativa
- **Docker** (avanzado) - para ambientes aislados

### **1.2 Clonar el Repositorio**

```bash
# Navegar a plugins de WordPress
cd /path/to/wordpress/wp-content/plugins/

# Clonar repo
git clone https://github.com/cristianfarfan/wp-cupon-whatsapp.git
cd wp-cupon-whatsapp

# Verificar branch actual
git branch
# Debe mostrar: * master (o main)

# Verificar último commit
git log --oneline -1
```

### **1.3 Instalar Dependencias de WordPress**

**Plugins requeridos** (instalar desde admin):
1. **WooCommerce** 6.0+ (OBLIGATORIO)
2. **Elementor** 3.0+ (Opcional pero recomendado)

```bash
# O instalar vía WP-CLI
wp plugin install woocommerce --activate
wp plugin install elementor --activate
```

### **1.4 Activar el Plugin**

**Opción A: Desde Admin de WordPress**
```
1. Ir a Plugins > Plugins Instalados
2. Buscar "WP Cupón WhatsApp"
3. Click en "Activar"
4. Verificar que no hay errores fatales
```

**Opción B: Vía WP-CLI**
```bash
wp plugin activate wp-cupon-whatsapp

# Verificar activación
wp plugin list | grep wp-cupon-whatsapp
# Debe mostrar: active
```

### **1.5 Verificar Instalación Correcta**

**Checklist de verificación**:
```bash
# 1. Verificar tabla custom creada
wp db query "SHOW TABLES LIKE 'wp_wpcw_canjes'"
# Debe retornar: wp_wpcw_canjes

# 2. Verificar CPTs registrados
wp post-type list | grep wpcw
# Debe mostrar: wpcw_business, wpcw_institution, wpcw_application

# 3. Verificar menú admin
# Ir a Admin → Debe aparecer menú "WP Cupón WhatsApp"

# 4. Verificar logs
tail -f wp-content/debug.log
# NO debe haber errores fatales
```

**Si hay errores**:
- Revisar [troubleshooting/README.md](troubleshooting/README.md)
- Consultar [LESSONS_LEARNED.md](LESSONS_LEARNED.md) para errores conocidos

---

## 📂 PASO 2: ENTENDER LA ESTRUCTURA DEL CÓDIGO (10 minutos)

### **2.1 Mapa de Archivos Críticos**

```
wp-cupon-whatsapp/
│
├── wp-cupon-whatsapp.php            # 🔴 ARCHIVO PRINCIPAL - Bootstrap del plugin
│                                    # 740 líneas, hooks WordPress, requires
│
├── includes/                        # 🟢 CORE BUSINESS LOGIC
│   ├── class-wpcw-business-manager.php       # Gestión de comercios (bulk)
│   ├── class-wpcw-coupon-manager.php         # Gestión de cupones (bulk, import/export)
│   ├── class-wpcw-redemption-manager.php     # Reportes y operaciones masivas
│   ├── redemption-handler.php                # ⭐ Redención INDIVIDUAL
│   ├── ajax-handlers.php                     # ⭐ 12+ endpoints AJAX
│   ├── class-wpcw-coupon.php                 # Extensión de WC_Coupon
│   ├── class-wpcw-dashboard.php              # Dashboard widgets
│   ├── class-wpcw-rest-api.php               # REST API endpoints
│   ├── class-wpcw-shortcodes.php             # Shortcodes públicos
│   ├── class-wpcw-logger.php                 # Sistema de logging
│   ├── post-types.php                        # Registro de CPTs
│   ├── roles.php                             # Roles y capacidades
│   └── class-wpcw-installer-fixed.php        # Instalación y migración
│
├── admin/                           # 🔵 PANEL DE ADMINISTRACIÓN
│   ├── admin-menu.php                        # ⭐ Registro de menús
│   ├── business-management.php               # ⭐ Gestión de comercios
│   ├── coupon-meta-boxes.php                 # Meta boxes de cupones
│   ├── dashboard-pages.php                   # Funciones render de páginas
│   ├── js/admin.js                           # JavaScript admin (148 líneas)
│   └── css/admin.css                         # Estilos admin
│
├── public/                          # 🟣 FRONTEND PÚBLICO
│   ├── shortcodes.php                        # Shortcodes legacy
│   ├── js/public.js                          # JavaScript público (177 líneas)
│   └── css/public.css                        # Estilos frontend
│
├── docs/                            # 📚 DOCUMENTACIÓN
│   ├── INDEX.md                              # ⭐ Índice maestro (EMPIEZA AQUÍ)
│   ├── CONTEXT.md                            # ⭐ Contexto completo
│   ├── LESSONS_LEARNED.md                    # ⭐ Errores históricos
│   ├── HANDOFF_GUIDE.md                      # ⭐ Esta guía
│   ├── agents/PROJECT_STAFF.md               # Sistema de agentes
│   ├── architecture/ARCHITECTURE.md          # Arquitectura técnica
│   └── troubleshooting/                      # Resolución de problemas
│
└── tests/                           # 🧪 TESTING (WIP)
    ├── unit/                                 # Tests unitarios PHPUnit
    ├── integration/                          # Tests de integración
    └── performance/                          # Tests de performance
```

### **2.2 ¿Dónde Está Qué?**

| Necesito... | Buscar en... |
|-------------|-------------|
| **Lógica de redención individual** | `includes/redemption-handler.php` |
| **Operaciones masivas/reportes** | `includes/class-wpcw-redemption-manager.php` |
| **Endpoints AJAX** | `includes/ajax-handlers.php` |
| **Extensión de cupones WC** | `includes/class-wpcw-coupon.php` |
| **Páginas del admin** | `admin/business-management.php` + `admin/dashboard-pages.php` |
| **Registro de menús** | `admin/admin-menu.php` |
| **JavaScript admin** | `admin/js/admin.js` |
| **JavaScript público** | `public/js/public.js` |
| **REST API** | `includes/class-wpcw-rest-api.php` |
| **Shortcodes** | `includes/class-wpcw-shortcodes.php` |
| **Widgets Elementor** | `includes/widgets/` |
| **CPTs** | `includes/post-types.php` |
| **Roles y permisos** | `includes/roles.php` |

### **2.3 Flujo de Redención (Caso de Uso Principal)**

```
Usuario → Frontend → AJAX (`wpcw_redeem_coupon`)
                   ↓
        includes/ajax-handlers.php
                   ↓
        WPCW_Redemption_Handler::initiate_redemption()
                   ↓
        Validación → Generar token → Insertar en wp_wpcw_canjes
                   ↓
        Generar URL WhatsApp → Retornar al frontend
                   ↓
        Usuario click → Redirige a WhatsApp (wa.me)
                   ↓
        Comercio confirma manualmente
                   ↓
        WPCW_Redemption_Handler::confirm_redemption()
                   ↓
        Actualizar estado → Aplicar cupón en WooCommerce
```

---

## 🎭 PASO 3: SISTEMA DE AGENTES - ¿QUIÉN HACE QUÉ? (5 minutos)

### **3.1 Matriz de Decisión Rápida**

| Tarea | Agente Responsable | Archivo |
|-------|-------------------|---------|
| **Decisión arquitectónica** | Marcus Chen (Arquitecto) | [PROJECT_STAFF.md](agents/PROJECT_STAFF.md#1%EF%B8%8F⃣-el-arquitecto) |
| **Código PHP/WordPress** | Sarah Thompson (Backend) | [PROJECT_STAFF.md](agents/PROJECT_STAFF.md#2%EF%B8%8F⃣-el-artesano-wordpress) |
| **JavaScript/UX** | Elena Rodriguez (Frontend) | [PROJECT_STAFF.md](agents/PROJECT_STAFF.md#3%EF%B8%8F⃣-la-diseñadora-ux) |
| **Base de datos/Queries** | Dr. Rajesh Kumar (Database) | [PROJECT_STAFF.md](agents/PROJECT_STAFF.md#4%EF%B8%8F⃣-el-ingeniero-de-datos) |
| **Validación seguridad** | Alex Petrov (Security) | [PROJECT_STAFF.md](agents/PROJECT_STAFF.md#5%EF%B8%8F⃣-el-guardián-de-seguridad) |
| **Testing/QA** | Jennifer Wu (QA) | [PROJECT_STAFF.md](agents/PROJECT_STAFF.md#6%EF%B8%8F⃣-la-verificadora) |
| **WooCommerce** | Thomas Müller (WC Specialist) | [PROJECT_STAFF.md](agents/PROJECT_STAFF.md#7%EF%B8%8F⃣-el-mago-woocommerce) |
| **Performance** | Kenji Tanaka (Optimizer) | [PROJECT_STAFF.md](agents/PROJECT_STAFF.md#8%EF%B8%8F⃣-el-optimizador) |
| **Documentación** | Dr. Maria Santos (Writer) | [PROJECT_STAFF.md](agents/PROJECT_STAFF.md#9%EF%B8%8F⃣-la-documentadora-técnica) |
| **Convenios/Negocio** | Isabella Lombardi (Strategy) | [PROJECT_STAFF.md](agents/PROJECT_STAFF.md#🔟-la-estratega-de-convenios) |

### **3.2 Reglas de Activación**

**🚨 REGLA DE ORO**: **UN AGENTE, UNA TAREA** (nunca múltiples agentes para lo mismo)

**Ejemplos prácticos**:

```
❓ Necesito agregar una nueva tabla a la BD
✅ Activar: Dr. Rajesh Kumar (Database)
❌ NO activar: Marcus Chen (solo si impacta arquitectura global)

❓ Necesito refactorizar un Manager
✅ Activar: Marcus Chen → Sarah Thompson
   (Chen diseña, Thompson implementa)

❓ Necesito optimizar queries lentas
✅ Activar: Kenji Tanaka (Performance)
   Puede consultar a Rajesh si necesita

❓ Necesito agregar AJAX endpoint con datos sensibles
✅ Activar: Sarah Thompson (implementa)
   + Alex Petrov (valida seguridad)
```

---

## 🔄 PASO 4: FLUJO DE TRABAJO DE DESARROLLO (10 minutos)

### **4.1 Flujo Estándar (SIEMPRE SEGUIR)**

```
1. LEER
   ↓
2. PLANIFICAR
   ↓
3. ACTIVAR AGENTE(S) APROPIADO(S)
   ↓
4. IMPLEMENTAR
   ↓
5. TESTEAR
   ↓
6. DOCUMENTAR
   ↓
7. COMMIT
   ↓
8. REPEAT
```

### **4.2 Paso 1: LEER (5-10 minutos antes de codear)**

**Antes de tocar CUALQUIER código**:

```markdown
- [ ] ¿Leí la documentación relacionada?
- [ ] ¿Entiendo el contexto de esta funcionalidad?
- [ ] ¿Verifiqué si ya existe código similar?
- [ ] ¿Busqué en [INDEX.md](INDEX.md) si hay docs relacionadas?
- [ ] ¿Revisé [LESSONS_LEARNED.md](LESSONS_LEARNED.md) por errores relacionados?
```

**Comandos útiles para buscar**:

```bash
# Buscar una función/clase
grep -r "function nombre" --include="*.php"

# Buscar texto en todo el código
grep -r "texto_a_buscar" --include="*.php"

# Buscar en documentación
grep -r "palabra_clave" docs/

# Ver archivos modificados recientemente
git log --name-only --oneline -10
```

### **4.3 Paso 2: PLANIFICAR (5 minutos)**

**Crear plan mental o escrito**:

```markdown
## Plan: [Descripción breve]

### Objetivo
[Qué quieres lograr]

### Archivos a modificar
- [ ] archivo1.php - [qué cambios]
- [ ] archivo2.php - [qué cambios]

### Archivos a crear (si aplica)
- [ ] nuevo-archivo.php - [propósito]

### Agentes a activar
- [ ] Agente X - [por qué]
- [ ] Agente Y - [por qué]

### Riesgos
- [Qué podría salir mal]
- [Cómo mitigar]

### Testing
- [ ] Test 1: [descripción]
- [ ] Test 2: [descripción]
```

### **4.4 Paso 3: ACTIVAR AGENTE(S)**

**Consultar**: [agents/PROJECT_STAFF.md](agents/PROJECT_STAFF.md)

**Ejemplo**:
```
Tarea: Agregar endpoint AJAX para aprobar aplicaciones

Agentes:
1. Sarah Thompson (implementar endpoint)
2. Alex Petrov (validar seguridad)
3. Dr. Rajesh Kumar (consulta sobre queries)
```

### **4.5 Paso 4: IMPLEMENTAR**

**Principios de código**:

1. **DRY (Don't Repeat Yourself)**:
   ```php
   // ❌ MAL
   function funcion_a() { /* lógica duplicada */ }
   function funcion_b() { /* lógica duplicada */ }

   // ✅ BIEN
   function logica_compartida() { /* lógica común */ }
   function funcion_a() { logica_compartida(); }
   function funcion_b() { logica_compartida(); }
   ```

2. **SRP (Single Responsibility Principle)**:
   ```php
   // ❌ MAL - hace demasiadas cosas
   function procesar_todo() {
       validar();
       insertar_bd();
       enviar_email();
       actualizar_wc();
   }

   // ✅ BIEN - delegación clara
   function procesar_solicitud() {
       if ( ! validar() ) return false;
       $id = insertar_bd();
       enviar_email( $id );
       actualizar_wc( $id );
   }
   ```

3. **Naming Conventions**:
   ```php
   // Funciones
   wpcw_nombre_descriptivo()  // Prefijo wpcw_

   // Clases
   class WPCW_Nombre_Clase  // Mayúsculas con guiones bajos

   // Archivos
   class-wpcw-nombre-clase.php  // Clases
   nombre-handler.php           // Handlers
   nombre-functions.php         // Funciones helper
   ```

4. **Documentación inline**:
   ```php
   /**
    * Descripción breve de qué hace
    *
    * Descripción detallada si es necesario.
    *
    * @param int    $param1 Descripción
    * @param string $param2 Descripción
    * @return bool|WP_Error True o error
    * @since 1.5.0
    */
   function wpcw_mi_funcion( $param1, $param2 ) {
       // Código
   }
   ```

### **4.6 Paso 5: TESTEAR**

**Checklist de testing mínimo**:

```markdown
### Testing Manual
- [ ] Funciona en navegador sin errores de consola (F12)
- [ ] No hay errores en wp-content/debug.log
- [ ] Funciona con usuario admin
- [ ] Funciona con usuario no-admin (si aplica)
- [ ] Funciona con permisos correctos
- [ ] Valida datos correctamente
- [ ] Muestra mensajes de error apropiados

### Testing de Integración
- [ ] No rompe funcionalidad existente
- [ ] Compatible con WooCommerce
- [ ] Compatible con Elementor (si aplica)

### Testing de Seguridad (si aplica)
- [ ] Validado por Alex Petrov (Security)
- [ ] Nonces implementados
- [ ] Sanitización de inputs
- [ ] Escape de outputs
- [ ] Verificación de permisos
```

**Comandos de testing**:

```bash
# Testear sintaxis PHP
php -l archivo.php

# Testear con WP-CLI
wp eval 'echo "Test passed\n";'

# Ver logs en tiempo real
tail -f wp-content/debug.log

# Testear AJAX endpoints
curl -X POST http://localhost/wp-admin/admin-ajax.php \
  -d "action=wpcw_test_action&nonce=xxx"
```

### **4.7 Paso 6: DOCUMENTAR**

**Qué documentar**:

1. **Código inline** (siempre):
   ```php
   // Docblocks en funciones/clases
   /**
    * @param ...
    * @return ...
    */
   ```

2. **Actualizar arquitectura** (si aplica):
   - Si agregaste clase nueva → actualizar [architecture/ARCHITECTURE.md](architecture/ARCHITECTURE.md)
   - Si modificaste BD → actualizar [architecture/DATABASE_SCHEMA.md](architecture/DATABASE_SCHEMA.md)

3. **Actualizar INDEX.md** (si agregaste docs):
   ```markdown
   # Agregar entrada en docs/INDEX.md
   - [Nombre Documento](path/to/doc.md) - Descripción
   ```

4. **Agregar a CHANGELOG** (features importantes):
   ```markdown
   # CHANGELOG.md
   ## [Unreleased]
   ### Added
   - Nueva funcionalidad X que hace Y
   ```

### **4.8 Paso 7: COMMIT**

**Convención de commits**:

```bash
# Formato
<tipo>: <descripción breve>

<descripción detallada opcional>

# Tipos
feat:     Nueva funcionalidad
fix:      Corrección de bug
docs:     Solo documentación
refactor: Refactorización sin cambio funcional
test:     Agregar tests
style:    Cambios de formato
perf:     Mejora de performance
chore:    Mantenimiento, configuración
```

**Ejemplos**:

```bash
# Feature nueva
git commit -m "feat: Agregar endpoint AJAX para aprobar aplicaciones

- Nuevo endpoint wpcw_approve_application
- Validación de nonce y permisos
- Actualización de estado en BD
- Notificación por email"

# Fix de bug
git commit -m "fix: Corregir error de Headers Already Sent

- Agregado ob_start() al inicio del archivo principal
- Resuelve #42"

# Documentación
git commit -m "docs: Actualizar ARCHITECTURE.md con nuevo Manager

- Agregada documentación de WPCW_Application_Manager
- Diagramas de flujo actualizados"

# Refactorización
git commit -m "refactor: Extraer funciones render a dashboard-pages.php

- Reducido archivo principal de 1013 a 740 líneas
- Mejora mantenibilidad y organización"
```

**Comandos Git útiles**:

```bash
# Ver estado
git status

# Ver cambios
git diff

# Agregar archivos
git add archivo.php
git add .  # Todos los archivos

# Commit
git commit -m "feat: Descripción"

# Push (SOLO si tienes permisos)
git push origin master

# Ver log
git log --oneline -10

# Ver cambios de un commit
git show <commit-hash>
```

---

## ⚡ PASO 5: COMANDOS ÚTILES (Referencia Rápida)

### **5.1 WordPress (WP-CLI)**

```bash
# Información del sitio
wp --info

# Plugins
wp plugin list
wp plugin activate wp-cupon-whatsapp
wp plugin deactivate wp-cupon-whatsapp

# Base de datos
wp db query "SELECT * FROM wp_wpcw_canjes LIMIT 5"
wp db export backup.sql

# Cache
wp cache flush

# Verificar rewrite rules
wp rewrite list
wp rewrite flush

# Usuarios
wp user list
wp user create usuario email@example.com --role=administrator

# CPTs
wp post-type list
wp post list --post_type=wpcw_business

# Debug
wp eval 'var_dump(get_option("wpcw_version"));'
```

### **5.2 Git**

```bash
# Clonar
git clone https://github.com/cristianfarfan/wp-cupon-whatsapp.git

# Branches
git branch                    # Ver branches
git checkout -b mi-feature    # Crear branch
git checkout master           # Cambiar branch
git merge mi-feature          # Merge branch

# Stash (guardar cambios temporalmente)
git stash                     # Guardar cambios
git stash list                # Ver stash
git stash pop                 # Recuperar cambios

# Reset (CUIDADO)
git reset --hard HEAD         # Descartar TODOS los cambios
git reset --soft HEAD~1       # Deshacer último commit

# Ver cambios
git diff                      # Cambios no staged
git diff --staged             # Cambios staged
git diff HEAD~1               # Comparar con commit anterior

# Log avanzado
git log --graph --oneline --all
git log --author="nombre"
git log --since="2 weeks ago"
```

### **5.3 PHP / Debugging**

```bash
# Verificar sintaxis
php -l archivo.php

# Ejecutar script
php archivo.php

# Ver errores de PHP
tail -f /path/to/php-error.log

# Info de PHP
php -i | grep "Configuration File"

# Extensiones PHP
php -m
```

### **5.4 MySQL**

```bash
# Conectar a BD
mysql -u usuario -p nombre_bd

# Consultas comunes
mysql> SHOW TABLES;
mysql> DESCRIBE wp_wpcw_canjes;
mysql> SELECT COUNT(*) FROM wp_wpcw_canjes;
mysql> SELECT * FROM wp_wpcw_canjes WHERE estado_canje = 'pendiente_confirmacion' LIMIT 10;

# Backup
mysqldump -u usuario -p nombre_bd > backup.sql

# Restore
mysql -u usuario -p nombre_bd < backup.sql
```

---

## ✅ PASO 6: CHECKLIST ANTES DE COMMIT (OBLIGATORIO)

**SIEMPRE verificar TODOS estos puntos antes de cada commit**:

### **Código**
- [ ] Código sigue WordPress Coding Standards
- [ ] No hay `var_dump()`, `print_r()`, `echo` de debugging
- [ ] Todas las funciones tienen docblocks
- [ ] Variables tienen nombres descriptivos
- [ ] No hay código comentado innecesariamente
- [ ] Archivo tiene menos de 500 líneas (si no, considerar split)

### **Seguridad**
- [ ] Todos los inputs están sanitizados (`sanitize_text_field()`, `absint()`, etc.)
- [ ] Todos los outputs están escaped (`esc_html()`, `esc_attr()`, etc.)
- [ ] Nonces verificados en formularios (`wp_nonce_field()`, `wp_verify_nonce()`)
- [ ] Permisos verificados (`current_user_can()`)
- [ ] Queries usan prepared statements (`$wpdb->prepare()`)

### **Testing**
- [ ] Probado en navegador (sin errores F12 Console)
- [ ] No hay errores en `wp-content/debug.log`
- [ ] Funciona con diferentes roles de usuario
- [ ] No rompe funcionalidad existente

### **Documentación**
- [ ] Actualicé docblocks inline
- [ ] Actualicé `ARCHITECTURE.md` si agregué clases
- [ ] Actualicé `INDEX.md` si agregué documentos
- [ ] Actualicé `CHANGELOG.md` si es feature importante

### **Git**
- [ ] Commit message sigue convención (`feat:`, `fix:`, etc.)
- [ ] Solo incluye cambios relacionados (no mezclar features)
- [ ] No incluye archivos temporales (.DS_Store, *.log, etc.)

**Si marcaste TODAS las casillas**, estás listo para commit ✅

---

## 🎯 PASO 7: TEMPLATES DE CÓDIGO REUTILIZABLES

### **7.1 Template: Nueva Función Helper**

```php
<?php
/**
 * Descripción breve de qué hace esta función
 *
 * Descripción más detallada si es necesario.
 * Puede ser multilinea.
 *
 * @param int    $param1 Descripción del parámetro 1
 * @param string $param2 Descripción del parámetro 2
 * @return bool|WP_Error True on success, WP_Error on failure
 * @since 1.5.0
 */
function wpcw_nombre_funcion( $param1, $param2 ) {
    // 1. Validación de parámetros
    if ( empty( $param1 ) ) {
        return new WP_Error( 'invalid_param', __( 'Parámetro inválido', 'wp-cupon-whatsapp' ) );
    }

    // 2. Lógica principal
    $resultado = hacer_algo( $param1, $param2 );

    // 3. Manejar errores
    if ( is_wp_error( $resultado ) ) {
        WPCW_Logger::log( 'error', 'Error en wpcw_nombre_funcion', array(
            'param1' => $param1,
            'error' => $resultado->get_error_message(),
        ) );
        return $resultado;
    }

    // 4. Log de éxito (opcional)
    WPCW_Logger::log( 'info', 'wpcw_nombre_funcion ejecutada exitosamente', array(
        'param1' => $param1,
        'resultado' => $resultado,
    ) );

    // 5. Retornar resultado
    return true;
}
```

### **7.2 Template: Nuevo Endpoint AJAX**

```php
<?php
// En includes/ajax-handlers.php

class WPCW_AJAX_Handlers {

    public static function init() {
        // ... otros endpoints ...

        // Agregar nuevo endpoint
        add_action( 'wp_ajax_wpcw_mi_accion', array( __CLASS__, 'mi_accion_handler' ) );
        add_action( 'wp_ajax_nopriv_wpcw_mi_accion', array( __CLASS__, 'mi_accion_handler' ) ); // Si es público
    }

    /**
     * Handler para mi acción AJAX
     *
     * @return void (usa wp_send_json_*)
     * @since 1.5.0
     */
    public static function mi_accion_handler() {
        // 1. Verificar nonce
        check_ajax_referer( 'wpcw_public_nonce', 'nonce' );

        // 2. Verificar permisos (si aplica)
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array(
                'message' => __( 'Permisos insuficientes', 'wp-cupon-whatsapp' ),
            ) );
        }

        // 3. Obtener y sanitizar datos
        $dato1 = isset( $_POST['dato1'] ) ? sanitize_text_field( $_POST['dato1'] ) : '';
        $dato2 = isset( $_POST['dato2'] ) ? absint( $_POST['dato2'] ) : 0;

        // 4. Validar datos
        if ( empty( $dato1 ) || $dato2 <= 0 ) {
            wp_send_json_error( array(
                'message' => __( 'Datos incompletos o inválidos', 'wp-cupon-whatsapp' ),
            ) );
        }

        // 5. Delegar a lógica de negocio
        $resultado = Mi_Clase_Manager::procesar( $dato1, $dato2 );

        // 6. Manejar errores
        if ( is_wp_error( $resultado ) ) {
            wp_send_json_error( array(
                'message' => $resultado->get_error_message(),
            ) );
        }

        // 7. Respuesta exitosa
        wp_send_json_success( array(
            'message' => __( 'Operación completada exitosamente', 'wp-cupon-whatsapp' ),
            'data' => $resultado,
        ) );
    }
}
```

**JavaScript correspondiente**:

```javascript
// En admin/js/admin.js o public/js/public.js

jQuery(document).ready(function($) {
    $('#mi-boton').on('click', function(e) {
        e.preventDefault();

        var $button = $(this);
        var originalText = $button.text();

        // Deshabilitar botón
        $button.prop('disabled', true).text('Procesando...');

        $.ajax({
            url: wpcw_admin.ajax_url, // o wpcw_public.ajax_url
            type: 'POST',
            data: {
                action: 'wpcw_mi_accion',
                nonce: wpcw_admin.nonce,
                dato1: $('#campo1').val(),
                dato2: $('#campo2').val()
            },
            success: function(response) {
                if (response.success) {
                    alert(response.data.message);
                    // Hacer algo con response.data
                } else {
                    alert('Error: ' + response.data.message);
                }
            },
            error: function() {
                alert('Error de conexión');
            },
            complete: function() {
                // Rehabilitar botón
                $button.prop('disabled', false).text(originalText);
            }
        });
    });
});
```

### **7.3 Template: Nueva Clase Manager**

```php
<?php
/**
 * WP Cupón WhatsApp - Nombre Manager
 *
 * RESPONSABILIDAD: Gestión MASIVA de [entidad]
 * - Listar con filtros y paginación
 * - Operaciones bulk
 * - Reportes y estadísticas
 * - Exportar a CSV
 *
 * NOTA: Para operaciones INDIVIDUALES usar Nombre_Handler
 *
 * @package WP_Cupon_WhatsApp
 * @since 1.5.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WPCW_Nombre_Manager {

    /**
     * Get items with filtering and pagination
     *
     * @param array $args Query arguments
     * @return array Items data
     * @since 1.5.0
     */
    public static function get_items( $args = array() ) {
        $defaults = array(
            'posts_per_page' => 20,
            'paged' => 1,
            'orderby' => 'date',
            'order' => 'DESC',
        );

        $args = wp_parse_args( $args, $defaults );

        // Implementar query

        return array(
            'items' => $items,
            'total' => $total,
            'pages' => $pages,
            'current_page' => $args['paged'],
        );
    }

    /**
     * Bulk process items
     *
     * @param array $item_ids Item IDs
     * @param string $action Action to perform
     * @return array Results
     * @since 1.5.0
     */
    public static function bulk_process( $item_ids, $action ) {
        $results = array(
            'processed' => 0,
            'failed' => 0,
        );

        foreach ( $item_ids as $item_id ) {
            // Delegar a Handler para operación individual
            $result = Nombre_Handler::process_single( $item_id, $action );

            if ( is_wp_error( $result ) ) {
                $results['failed']++;
            } else {
                $results['processed']++;
            }
        }

        return $results;
    }

    /**
     * Get statistics
     *
     * @param array $filters Filters
     * @return array Statistics
     * @since 1.5.0
     */
    public static function get_statistics( $filters = array() ) {
        // Implementar queries de estadísticas

        return $stats;
    }

    /**
     * Export to CSV
     *
     * @param array $filters Export filters
     * @return string CSV content
     * @since 1.5.0
     */
    public static function export_csv( $filters = array() ) {
        // Implementar exportación

        return $csv_content;
    }
}
```

---

## 🚨 PASO 8: TROUBLESHOOTING RÁPIDO

### **8.1 Errores Comunes y Soluciones**

#### **Error: Headers Already Sent**

```
Warning: Cannot modify header information - headers already sent
```

**Solución**:
```php
// Verificar que al inicio de wp-cupon-whatsapp.php esté:
ob_start();

// Y al final:
ob_end_flush();

// Verificar que NO haya espacios antes de <?php
// Verificar que NO haya echo/var_dump antes de headers
```

#### **Error: Class Not Found**

```
Fatal error: Class 'WPCW_Algo' not found
```

**Solución**:
```php
// Verificar que el archivo esté incluido en wp-cupon-whatsapp.php
require_once WPCW_PLUGIN_DIR . 'includes/class-wpcw-algo.php';

// Verificar nombre de archivo coincida con nombre de clase
// Archivo: class-wpcw-algo.php
// Clase: class WPCW_Algo {}
```

#### **Error: 404 en Asset JS/CSS**

```
GET /wp-content/plugins/.../admin.js 404 (Not Found)
```

**Solución**:
```php
// Verificar que archivo existe
file_exists( WPCW_PLUGIN_DIR . 'admin/js/admin.js' );

// Agregar verificación antes de encolar
if ( file_exists( $script_path ) ) {
    wp_enqueue_script( ... );
}
```

#### **Error: AJAX Retorna 0**

```javascript
// Response: 0
```

**Solución**:
```php
// Verificar que acción esté registrada
add_action( 'wp_ajax_wpcw_mi_accion', 'mi_handler' );

// Verificar que handler exista y no esté vacío
function mi_handler() {
    // DEBE tener código
    wp_send_json_success( array( 'message' => 'OK' ) );
}

// Verificar nonce correcto
check_ajax_referer( 'wpcw_admin_nonce', 'nonce' );
```

#### **Error: Tabla no existe**

```
Table 'wp_wpcw_canjes' doesn't exist
```

**Solución**:
```bash
# Reactivar plugin para crear tabla
wp plugin deactivate wp-cupon-whatsapp
wp plugin activate wp-cupon-whatsapp

# O ejecutar instalador manualmente
wp eval 'WPCW_Installer_Fixed::run_installation_checks();'
```

### **8.2 Debugging Avanzado**

**Habilitar WP_DEBUG**:

```php
// En wp-config.php
define( 'WP_DEBUG', true );
define( 'WP_DEBUG_LOG', true );
define( 'WP_DEBUG_DISPLAY', false );
define( 'SCRIPT_DEBUG', true );
```

**Ver logs en tiempo real**:

```bash
# Linux/Mac
tail -f wp-content/debug.log

# Windows (PowerShell)
Get-Content wp-content/debug.log -Wait -Tail 50
```

**Agregar logging custom**:

```php
// En cualquier parte del código
WPCW_Logger::log( 'info', 'Mensaje de debug', array(
    'variable1' => $var1,
    'variable2' => $var2,
) );

// O usar error_log directamente
error_log( 'WPCW Debug: ' . print_r( $variable, true ) );
```

---

## 📞 PASO 9: CONTACTOS Y RECURSOS

### **9.1 Stakeholder Principal**

**Cristian Farfan**
Pragmatic Solutions
📧 Email: info@pragmaticsolutions.com.ar
🌐 Web: [www.pragmaticsolutions.com.ar](https://www.pragmaticsolutions.com.ar)
🐙 GitHub: [@cristianfarfan](https://github.com/cristianfarfan)

### **9.2 Repositorio**

**URL**: [https://github.com/cristianfarfan/wp-cupon-whatsapp](https://github.com/cristianfarfan/wp-cupon-whatsapp)
**Branch principal**: `master` (o `main`)
**Versión actual**: v1.5.0

### **9.3 Documentación Crítica (Links Directos)**

| Documento | Propósito | Tiempo Lectura |
|-----------|-----------|----------------|
| [README.md](../README.md) | Inicio, overview | 5 min |
| [docs/INDEX.md](INDEX.md) | Índice maestro | 2 min |
| [docs/CONTEXT.md](CONTEXT.md) | Contexto completo | 10 min |
| [docs/LESSONS_LEARNED.md](LESSONS_LEARNED.md) | Errores históricos | 10 min |
| [agents/PROJECT_STAFF.md](agents/PROJECT_STAFF.md) | Sistema de agentes | 5 min |
| [architecture/ARCHITECTURE.md](architecture/ARCHITECTURE.md) | Arquitectura técnica | 15 min |
| [troubleshooting/REFACTORIZACION_COMPLETADA.md](troubleshooting/REFACTORIZACION_COMPLETADA.md) | Estado actual | 10 min |

### **9.4 Recursos Externos Útiles**

**WordPress**:
- [WordPress Coding Standards](https://developer.wordpress.org/coding-standards/wordpress-coding-standards/)
- [Plugin Handbook](https://developer.wordpress.org/plugins/)
- [Codex](https://codex.wordpress.org/)

**WooCommerce**:
- [WooCommerce Docs](https://woocommerce.com/documentation/)
- [WC Developer Docs](https://github.com/woocommerce/woocommerce/wiki)

**Tools**:
- [WP-CLI](https://wp-cli.org/)
- [Query Monitor Plugin](https://wordpress.org/plugins/query-monitor/)
- [Debug Bar Plugin](https://wordpress.org/plugins/debug-bar/)

---

## ✅ CHECKLIST FINAL: ¿ESTÁS LISTO?

Marca TODAS las casillas antes de empezar a codear:

### **Documentación**
- [ ] Leí [CONTEXT.md](CONTEXT.md) completo
- [ ] Leí [LESSONS_LEARNED.md](LESSONS_LEARNED.md) completo
- [ ] Consulté [agents/PROJECT_STAFF.md](agents/PROJECT_STAFF.md)
- [ ] Revisé [troubleshooting/REFACTORIZACION_COMPLETADA.md](troubleshooting/REFACTORIZACION_COMPLETADA.md)

### **Entorno**
- [ ] Tengo WordPress instalado y funcionando
- [ ] WooCommerce instalado y activado
- [ ] Plugin clonado en `/wp-content/plugins/`
- [ ] Plugin activado sin errores
- [ ] Verificado tabla `wp_wpcw_canjes` existe

### **Herramientas**
- [ ] WP_DEBUG habilitado
- [ ] Puedo ver `debug.log`
- [ ] Tengo acceso a base de datos
- [ ] Git configurado correctamente

### **Conocimiento**
- [ ] Entiendo la arquitectura Manager vs Handler
- [ ] Sé qué agente activar para mi tarea
- [ ] Conozco el flujo de trabajo (Leer → Planificar → Implementar → Testear → Documentar → Commit)
- [ ] Tengo el checklist antes de commit memorizado

**Si marcaste TODAS las casillas**, estás 100% listo para empezar ✅🚀

---

## 🎉 ¡FELICITACIONES!

Has completado la guía de handoff. Ahora tienes TODO lo necesario para:

✅ Configurar tu entorno en 30 minutos
✅ Entender la estructura del código
✅ Saber qué agente activar
✅ Seguir el flujo de trabajo correcto
✅ Evitar errores que costaron 40+ horas
✅ Hacer tu primer commit con confianza

**Próximos pasos recomendados**:

1. **Configurar entorno** (Paso 1) si aún no lo hiciste
2. **Leer código existente** de un archivo pequeño para familiarizarte
3. **Hacer una modificación pequeña** (ej: agregar un log)
4. **Testear el cambio**
5. **Hacer tu primer commit**

**Recuerda**: Cuando tengas dudas, consulta [INDEX.md](INDEX.md) para encontrar documentación específica.

---

**📅 Última Actualización**: Octubre 2025
**✍️ Autores**: Dr. Maria Santos (Documentador) + Marcus Chen (Arquitecto)
**📊 Versión**: 1.0 - Guía de Handoff Completa
**🎯 Propósito**: Transferencia de conocimiento inmediata (< 30 min)

---

> **"El mejor código es el que el próximo desarrollador puede entender en 15 minutos."**
> — Marcus Chen, Lead Architect

**¡Éxito en tu desarrollo! 🚀**
