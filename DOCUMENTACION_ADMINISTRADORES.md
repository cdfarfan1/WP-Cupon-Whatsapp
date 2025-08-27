# Documentación para Administradores - WP Cupón WhatsApp

## Guía Completa para Despliegue Masivo en Múltiples Sitios WordPress

### Versión: 1.4.0
### Fecha: Enero 2025

---

## 📋 Índice

1. [Introducción](#introducción)
2. [Requisitos del Sistema](#requisitos-del-sistema)
3. [Instalación Masiva](#instalación-masiva)
4. [Configuración Regional Automática](#configuración-regional-automática)
5. [Optimización de Rendimiento](#optimización-de-rendimiento)
6. [Sistema de Logs Centralizado](#sistema-de-logs-centralizado)
7. [Migración y Backup](#migración-y-backup)
8. [Monitoreo y Mantenimiento](#monitoreo-y-mantenimiento)
9. [Solución de Problemas](#solución-de-problemas)
10. [API y Automatización](#api-y-automatización)
11. [Mejores Prácticas](#mejores-prácticas)
12. [Soporte y Contacto](#soporte-y-contacto)

---

## 🚀 Introducción

WP Cupón WhatsApp v1.4.0 ha sido diseñado específicamente para **despliegue masivo** en múltiples sitios WordPress. Esta versión incluye herramientas avanzadas de automatización, configuración regional inteligente, y sistemas de monitoreo centralizados que facilitan la gestión de cientos o miles de instalaciones.

### Características Principales para Producción Masiva

- ✅ **Instalación Automática**: Scripts de instalación y configuración automatizada
- ✅ **Detección Regional**: Configuración automática según país/idioma
- ✅ **Optimización de Rendimiento**: Perfiles de optimización para diferentes tipos de sitio
- ✅ **Logs Centralizados**: Sistema de monitoreo y debugging remoto
- ✅ **Migración Masiva**: Herramientas de backup y migración de configuraciones
- ✅ **API Completa**: Endpoints para automatización y gestión remota

---

## 🔧 Requisitos del Sistema

### Requisitos Mínimos

| Componente | Versión Mínima | Recomendada |
|------------|----------------|-------------|
| **PHP** | 7.4 | 8.1+ |
| **WordPress** | 5.0 | 6.0+ |
| **MySQL** | 5.6 | 8.0+ |
| **Memoria PHP** | 128MB | 256MB+ |
| **Tiempo de Ejecución** | 30s | 60s+ |

### Extensiones PHP Requeridas

```php
- json (requerida)
- curl (requerida)
- mbstring (requerida)
- gd (recomendada para optimización de imágenes)
- zip (recomendada para backups)
```

### Permisos de Archivos

```bash
# Directorios que necesitan permisos de escritura
wp-content/uploads/
wp-content/cache/
wp-content/plugins/wp-cupon-whatsapp/logs/
```

---

## 📦 Instalación Masiva

### 1. Instalación Individual Automatizada

#### Activación con Auto-Instalación

```php
// En wp-config.php, antes de la instalación
define('WPCW_AUTO_INSTALL', true);
define('WPCW_INSTALL_PROFILE', 'standard'); // minimal, standard, enterprise, development
```

#### Perfiles de Instalación Disponibles

| Perfil | Descripción | Tiempo Estimado | Uso Recomendado |
|--------|-------------|-----------------|------------------|
| **minimal** | Solo funcionalidades básicas | 2-3 min | Sitios pequeños, blogs |
| **standard** | Configuración completa estándar | 5-7 min | Mayoría de sitios comerciales |
| **enterprise** | Todas las funcionalidades | 10-15 min | Sitios de alto tráfico |
| **development** | Con datos de prueba y debug | 8-12 min | Entornos de desarrollo |

### 2. Instalación Vía WP-CLI

```bash
# Instalar plugin
wp plugin install wp-cupon-whatsapp --activate

# Ejecutar instalación automática
wp wpcw install --profile=standard

# Verificar instalación
wp wpcw status
```

### 3. Instalación Masiva con Scripts

#### Script Bash para Múltiples Sitios

```bash
#!/bin/bash
# install-multiple-sites.sh

SITES=(
    "https://sitio1.com"
    "https://sitio2.com"
    "https://sitio3.com"
)

PROFILE="standard"

for site in "${SITES[@]}"; do
    echo "Instalando en $site..."
    
    # Descargar plugin
    wp --url="$site" plugin install wp-cupon-whatsapp --activate
    
    # Ejecutar instalación automática
    wp --url="$site" wpcw install --profile="$PROFILE"
    
    # Verificar estado
    wp --url="$site" wpcw status
    
    echo "✅ Instalación completada en $site"
done
```

### 4. API de Instalación Remota

```php
// Endpoint: /wp-json/wpcw/v1/install
$response = wp_remote_post('https://sitio.com/wp-json/wpcw/v1/install', [
    'body' => [
        'profile' => 'standard',
        'options' => [
            'create_sample_data' => false,
            'enable_logging' => true
        ]
    ],
    'headers' => [
        'Authorization' => 'Bearer ' . $api_token
    ]
]);
```

---

## 🌍 Configuración Regional Automática

### Detección Automática de País

El sistema detecta automáticamente el país basándose en:

1. **Idioma de WordPress** (40% peso)
2. **Zona horaria** (30% peso)
3. **Moneda configurada** (20% peso)
4. **Análisis de contenido** (10% peso)

### Configuraciones Regionales Predefinidas

#### Argentina
```php
'AR' => [
    'currency' => 'ARS',
    'phone_format' => '+54 9 %s',
    'whatsapp_format' => '+549%s',
    'date_format' => 'd/m/Y',
    'timezone' => 'America/Argentina/Buenos_Aires',
    'language' => 'es_AR'
]
```

#### México
```php
'MX' => [
    'currency' => 'MXN',
    'phone_format' => '+52 %s',
    'whatsapp_format' => '+52%s',
    'date_format' => 'd/m/Y',
    'timezone' => 'America/Mexico_City',
    'language' => 'es_MX'
]
```

#### España
```php
'ES' => [
    'currency' => 'EUR',
    'phone_format' => '+34 %s',
    'whatsapp_format' => '+34%s',
    'date_format' => 'd/m/Y',
    'timezone' => 'Europe/Madrid',
    'language' => 'es_ES'
]
```

### Configuración Manual

```php
// Forzar configuración específica
WPCW_Auto_Config::get_instance()->apply_country_config('AR');

// Configuración personalizada
WPCW_Auto_Config::get_instance()->apply_custom_config([
    'currency' => 'USD',
    'phone_format' => '+1 %s',
    'whatsapp_format' => '+1%s'
]);
```

---

## ⚡ Optimización de Rendimiento

### Perfiles de Optimización

#### 1. Perfil Básico
- ✅ Caché de consultas
- ❌ Caché de objetos
- ❌ Minificación
- **Mejora esperada**: 10-20%

#### 2. Perfil Estándar
- ✅ Caché de consultas y objetos
- ✅ Minificación CSS/JS
- ✅ Lazy loading
- ✅ Optimización de base de datos
- **Mejora esperada**: 30-50%

#### 3. Perfil Agresivo
- ✅ Todas las optimizaciones estándar
- ✅ Integración CDN
- ✅ Precarga de recursos
- ✅ Compresión de respuestas
- **Mejora esperada**: 50-80%

### Aplicar Optimización

```php
// Via código
$optimizer = WPCW_Performance_Optimizer::get_instance();
$result = $optimizer->apply_optimization_profile('standard');

// Via WP-CLI
wp wpcw optimize --profile=standard

// Via API REST
POST /wp-json/wpcw/v1/optimize
{
    "profile": "standard"
}
```

### Monitoreo de Rendimiento

```php
// Habilitar monitoreo
update_option('wpcw_performance_monitoring_enabled', true);

// Obtener estadísticas
$stats = $optimizer->get_performance_stats(7); // últimos 7 días

echo "Tiempo promedio: " . $stats->avg_execution_time . "s";
echo "Memoria promedio: " . $stats->avg_memory_usage . " bytes";
echo "Consultas promedio: " . $stats->avg_query_count;
```

---

## 📊 Sistema de Logs Centralizado

### Configuración de Logging

```php
// Habilitar logging centralizado
update_option('wpcw_centralized_logging', true);
update_option('wpcw_log_to_database', true);
update_option('wpcw_log_to_file', true);
update_option('wpcw_log_to_remote', true);
update_option('wpcw_min_log_level', 'info'); // debug, info, warning, error, critical
```

### Niveles de Log

| Nivel | Descripción | Uso |
|-------|-------------|-----|
| **debug** | Información detallada | Solo desarrollo |
| **info** | Información general | Operaciones normales |
| **warning** | Advertencias | Problemas menores |
| **error** | Errores | Problemas que requieren atención |
| **critical** | Errores críticos | Problemas graves del sistema |

### Uso del Sistema de Logs

```php
// Registrar eventos
wpcw_log('info', 'Usuario aplicó a cupón', [
    'user_email' => 'usuario@ejemplo.com',
    'coupon_id' => 123,
    'site_id' => get_current_blog_id()
]);

wpcw_log('error', 'Error en validación de WhatsApp', [
    'phone' => '+5491123456789',
    'error' => 'Formato inválido'
]);
```

### Logs Remotos

```php
// Configurar endpoint remoto
update_option('wpcw_remote_log_endpoint', 'https://logs.miempresa.com/api/logs');
update_option('wpcw_remote_log_api_key', 'tu-api-key-aqui');

// Los logs se envían automáticamente cada hora
```

### Consultar Logs

```php
// Via código
$logger = WPCW_Centralized_Logger::get_instance();
$logs = $logger->get_logs([
    'level' => 'error',
    'limit' => 100,
    'date_from' => '2025-01-01',
    'date_to' => '2025-01-31'
]);

// Via WP-CLI
wp wpcw logs --level=error --limit=50

// Via API REST
GET /wp-json/wpcw/v1/logs?level=error&limit=50
```

---

## 💾 Migración y Backup

### Exportar Configuración

```php
// Exportar configuración completa
$migration = WPCW_Migration_Tools::get_instance();
$export = $migration->export_configuration();

// Guardar en archivo
file_put_contents('wpcw-config-backup.json', json_encode($export));
```

### Importar Configuración

```php
// Importar desde archivo
$config = json_decode(file_get_contents('wpcw-config-backup.json'), true);
$result = $migration->import_configuration($config);

if ($result['success']) {
    echo "Configuración importada exitosamente";
}
```

### Backup Completo

```php
// Crear backup completo
$backup = $migration->create_full_backup([
    'include_database' => true,
    'include_files' => true,
    'include_logs' => false
]);

echo "Backup creado: " . $backup['file_path'];
```

### Migración Masiva

```bash
#!/bin/bash
# migrate-multiple-sites.sh

SOURCE_SITE="https://sitio-origen.com"
TARGET_SITES=(
    "https://sitio1.com"
    "https://sitio2.com"
    "https://sitio3.com"
)

# Exportar configuración del sitio origen
wp --url="$SOURCE_SITE" wpcw export-config > config.json

# Importar en sitios destino
for site in "${TARGET_SITES[@]}"; do
    echo "Migrando configuración a $site..."
    wp --url="$site" wpcw import-config < config.json
    echo "✅ Migración completada en $site"
done
```

---

## 📈 Monitoreo y Mantenimiento

### Dashboard de Monitoreo

Accede al dashboard centralizado en:
`/wp-admin/admin.php?page=wpcw-monitoring`

### Métricas Clave

1. **Rendimiento**
   - Tiempo de carga promedio
   - Uso de memoria
   - Número de consultas SQL

2. **Uso del Plugin**
   - Cupones creados/día
   - Aplicaciones recibidas
   - Tasa de conversión

3. **Errores y Logs**
   - Errores críticos
   - Advertencias
   - Eventos de seguridad

### Alertas Automáticas

```php
// Configurar alertas por email
update_option('wpcw_alerts_enabled', true);
update_option('wpcw_alert_email', 'admin@miempresa.com');
update_option('wpcw_alert_threshold_errors', 10); // 10 errores por hora
update_option('wpcw_alert_threshold_response_time', 5); // 5 segundos
```

### Mantenimiento Automático

```php
// Tareas programadas automáticas
- Limpieza de logs antiguos (diaria)
- Optimización de base de datos (semanal)
- Backup de configuración (diaria)
- Envío de estadísticas (diaria)
- Verificación de actualizaciones (diaria)
```

---

## 🔧 Solución de Problemas

### Problemas Comunes

#### 1. Error de Instalación

**Síntoma**: La instalación automática falla

**Solución**:
```php
// Verificar requisitos
wp wpcw check-requirements

// Reinstalar manualmente
wp wpcw install --profile=minimal --force

// Verificar logs
wp wpcw logs --level=error --limit=10
```

#### 2. Problemas de Rendimiento

**Síntoma**: El sitio está lento después de la instalación

**Solución**:
```php
// Cambiar a perfil básico
wp wpcw optimize --profile=basic

// Limpiar caché
wp wpcw clear-cache

// Verificar métricas
wp wpcw performance-stats
```

#### 3. Logs No Se Envían

**Síntoma**: Los logs no llegan al servidor remoto

**Solución**:
```php
// Verificar configuración
wp option get wpcw_remote_log_endpoint
wp option get wpcw_remote_log_api_key

// Probar conexión
wp wpcw test-remote-logging

// Enviar logs manualmente
wp wpcw send-logs --force
```

### Modo Debug

```php
// Habilitar modo debug
define('WPCW_DEBUG', true);
define('WPCW_LOG_LEVEL', 'debug');

// En wp-config.php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('SAVEQUERIES', true);
```

### Herramientas de Diagnóstico

```bash
# Verificar estado general
wp wpcw status

# Diagnóstico completo
wp wpcw diagnose

# Verificar base de datos
wp wpcw check-database

# Verificar archivos
wp wpcw check-files
```

---

## 🔌 API y Automatización

### Endpoints Principales

#### Instalación
```http
POST /wp-json/wpcw/v1/install
Content-Type: application/json

{
    "profile": "standard",
    "options": {
        "create_sample_data": false,
        "enable_logging": true
    }
}
```

#### Estado del Sistema
```http
GET /wp-json/wpcw/v1/status
```

#### Optimización
```http
POST /wp-json/wpcw/v1/optimize
Content-Type: application/json

{
    "profile": "standard"
}
```

#### Logs
```http
GET /wp-json/wpcw/v1/logs?level=error&limit=50
POST /wp-json/wpcw/v1/logs/clear
```

#### Configuración
```http
GET /wp-json/wpcw/v1/config/export
POST /wp-json/wpcw/v1/config/import
```

### Autenticación API

```php
// Generar token de API
$token = wp_generate_password(32, false);
update_option('wpcw_api_token', $token);

// Usar en requests
$headers = [
    'Authorization' => 'Bearer ' . $token,
    'Content-Type' => 'application/json'
];
```

### Scripts de Automatización

#### Monitoreo con Python
```python
import requests
import json

def check_site_status(site_url, api_token):
    headers = {
        'Authorization': f'Bearer {api_token}',
        'Content-Type': 'application/json'
    }
    
    response = requests.get(f'{site_url}/wp-json/wpcw/v1/status', headers=headers)
    
    if response.status_code == 200:
        data = response.json()
        print(f"✅ {site_url}: {data['status']}")
    else:
        print(f"❌ {site_url}: Error {response.status_code}")

# Lista de sitios a monitorear
sites = [
    'https://sitio1.com',
    'https://sitio2.com',
    'https://sitio3.com'
]

api_token = 'tu-token-aqui'

for site in sites:
    check_site_status(site, api_token)
```

---

## 📋 Mejores Prácticas

### 1. Planificación del Despliegue

- **Prueba en Staging**: Siempre prueba en un entorno de staging primero
- **Despliegue Gradual**: Implementa en lotes pequeños (10-20 sitios)
- **Monitoreo Continuo**: Supervisa métricas durante las primeras 24-48 horas
- **Plan de Rollback**: Ten un plan para revertir cambios si es necesario

### 2. Configuración de Seguridad

```php
// Restringir acceso a API
update_option('wpcw_api_ip_whitelist', [
    '192.168.1.100',
    '10.0.0.50'
]);

// Habilitar rate limiting
update_option('wpcw_api_rate_limit', 100); // 100 requests por hora

// Logs de seguridad
update_option('wpcw_security_logging', true);
```

### 3. Optimización de Base de Datos

```sql
-- Índices recomendados para alto volumen
CREATE INDEX idx_wpcw_coupons_status_date ON wp_wpcw_coupons(status, created_at);
CREATE INDEX idx_wpcw_applications_email_date ON wp_wpcw_applications(user_email, created_at);
CREATE INDEX idx_wpcw_logs_level_date ON wp_wpcw_logs(level, created_at);
```

### 4. Backup y Recuperación

- **Backups Automáticos**: Configura backups diarios de configuración
- **Versionado**: Mantén versiones de configuración para rollback
- **Pruebas de Restauración**: Prueba regularmente el proceso de restauración
- **Documentación**: Documenta todos los cambios de configuración

### 5. Monitoreo Proactivo

```php
// Configurar alertas proactivas
update_option('wpcw_proactive_monitoring', [
    'check_disk_space' => true,
    'check_memory_usage' => true,
    'check_database_size' => true,
    'check_error_rates' => true,
    'check_response_times' => true
]);
```

---

## 📞 Soporte y Contacto

### Canales de Soporte

- **Email**: soporte@wpcuponwhatsapp.com
- **Documentación**: https://docs.wpcuponwhatsapp.com
- **GitHub Issues**: https://github.com/tu-usuario/wp-cupon-whatsapp/issues
- **Slack**: #wp-cupon-whatsapp en tu workspace

### Información para Soporte

Cuando contactes soporte, incluye:

```bash
# Información del sistema
wp wpcw system-info

# Logs recientes
wp wpcw logs --level=error --limit=20

# Estado del plugin
wp wpcw status
```

### Actualizaciones

- **Canal Estable**: Actualizaciones probadas y estables
- **Canal Beta**: Nuevas funcionalidades en prueba
- **Canal Desarrollo**: Últimos cambios (no recomendado para producción)

```php
// Configurar canal de actualizaciones
update_option('wpcw_update_channel', 'stable'); // stable, beta, development
```

---

## 📄 Licencia y Términos

Este plugin está licenciado bajo GPL v2 o posterior. Para uso comercial masivo, considera adquirir una licencia comercial que incluye:

- ✅ Soporte prioritario
- ✅ Actualizaciones automáticas
- ✅ Herramientas adicionales de gestión
- ✅ Consultoría de implementación

---

**© 2025 WP Cupón WhatsApp - Todos los derechos reservados**

*Esta documentación se actualiza regularmente. Versión actual: 1.4.0*