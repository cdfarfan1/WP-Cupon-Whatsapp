# WP Cupón WhatsApp - Guía de Despliegue Masivo

## 🚀 Introducción

Esta guía está diseñada para administradores que necesitan desplegar el plugin **WP Cupón WhatsApp v1.4.0** en múltiples sitios WordPress de manera eficiente y automatizada.

## 📋 Requisitos Previos

### Sistema
- **PHP**: 7.4+ (Recomendado: 8.0+)
- **WordPress**: 5.0+ (Recomendado: 6.0+)
- **Memoria**: 128MB mínimo (Recomendado: 256MB+)
- **WP-CLI**: Instalado y configurado
- **Acceso SSH**: Para scripts automatizados

### Herramientas Necesarias
- **WP-CLI**: [Descargar aquí](https://wp-cli.org/)
- **Git**: Para control de versiones
- **Bash/PowerShell**: Según el sistema operativo
- **curl**: Para validaciones HTTP

## 🛠️ Métodos de Despliegue

### 1. Despliegue Individual

#### Usando WP-CLI
```bash
# Instalar plugin
wp plugin install wp-cupon-whatsapp --activate

# Configurar automáticamente
wp wpcw install --profile=standard --optimization=standard

# Verificar estado
wp wpcw status
```

#### Usando Scripts
```bash
# Linux/Mac
./scripts/mass-deployment.sh sitios.txt

# Windows
.\scripts\mass-deployment.ps1 -SitesFile sitios.txt
```

### 2. Despliegue Masivo

#### Preparar Archivo de Sitios
Crear `sitios.txt` con el formato:
```
# Formato: URL [perfil] [optimización]
https://sitio1.com standard standard
https://sitio2.com enterprise aggressive
https://sitio3.com minimal basic
https://sitio4.com  # Usará valores por defecto
```

#### Ejecutar Despliegue
```bash
# Despliegue estándar
./scripts/mass-deployment.sh sitios.txt

# Despliegue con configuración personalizada
./scripts/mass-deployment.sh -p enterprise -o aggressive sitios.txt

# Modo de prueba (sin cambios reales)
./scripts/mass-deployment.sh --test sitios.txt
```

### 3. Despliegue con API

#### Endpoint de Instalación
```bash
curl -X POST https://sitio.com/wp-json/wpcw/v1/install \
  -H "Content-Type: application/json" \
  -d '{
    "profile": "standard",
    "optimization": "standard",
    "region": "AR"
  }'
```

## ⚙️ Perfiles de Configuración

### Minimal
- **Uso**: Sitios básicos con pocos recursos
- **Características**: Configuración mínima, sin optimizaciones
- **Memoria**: 128MB

### Standard (Recomendado)
- **Uso**: Mayoría de sitios WordPress
- **Características**: Configuración completa, optimizaciones básicas
- **Memoria**: 256MB

### Enterprise
- **Uso**: Sitios de alto tráfico
- **Características**: Todas las funciones, optimizaciones agresivas
- **Memoria**: 512MB+

### Development
- **Uso**: Entornos de desarrollo
- **Características**: Logs detallados, modo debug
- **Memoria**: 256MB

## 🌍 Configuración Regional

### Países Soportados
- **AR**: Argentina (+54)
- **MX**: México (+52)
- **CO**: Colombia (+57)
- **ES**: España (+34)
- **US**: Estados Unidos (+1)
- **BR**: Brasil (+55)

### Detección Automática
El plugin detecta automáticamente:
- Idioma de WordPress
- Zona horaria
- Moneda configurada
- Contenido del sitio
- Dominio geográfico

## 📊 Monitoreo y Logs

### Comandos de Estado
```bash
# Ver estado general
wp wpcw status

# Ver logs recientes
wp wpcw logs view --lines=100

# Ver solo errores
wp wpcw logs view --level=error

# Estadísticas de logs
wp wpcw logs stats
```

### Logs Centralizados
```bash
# Configurar logging remoto
wp wpcw configure centralized_logging true

# Enviar logs a servidor central
wp wpcw logs export
```

## 🔧 Optimización de Rendimiento

### Niveles de Optimización

#### Basic
- Minificación CSS/JS
- Lazy loading
- Cache de archivos

#### Standard
- Todo lo anterior +
- Optimización de imágenes
- Optimización de base de datos
- Compresión GZIP

#### Aggressive
- Todo lo anterior +
- CDN integration
- Cache Redis
- Critical CSS
- Defer JavaScript

### Aplicar Optimización
```bash
# Aplicar optimización
wp wpcw optimize --level=standard

# Limpiar cache
wp wpcw optimize --clear-cache

# Ver métricas
wp wpcw status
```

## 💾 Backup y Migración

### Crear Backup
```bash
# Backup completo
wp wpcw backup create --description="Antes de actualización"

# Listar backups
wp wpcw backup list

# Restaurar backup
wp wpcw backup restore --backup-id=123
```

### Migración Entre Sitios
```bash
# Exportar configuración
wp wpcw export-config config-sitio1.json

# Importar en otro sitio
wp wpcw import-config config-sitio1.json
```

## 🔍 Solución de Problemas

### Problemas Comunes

#### Plugin No Se Activa
```bash
# Verificar permisos
ls -la wp-content/plugins/wp-cupon-whatsapp/

# Verificar logs de PHP
tail -f /var/log/php/error.log

# Verificar requisitos
wp wpcw status
```

#### Configuración No Se Aplica
```bash
# Limpiar cache
wp cache flush

# Verificar base de datos
wp db check

# Revisar logs del plugin
wp wpcw logs view --level=error
```

#### Problemas de Rendimiento
```bash
# Verificar optimización
wp wpcw status

# Aplicar optimización básica
wp wpcw optimize --level=basic

# Verificar memoria
wp eval "echo 'Memoria: ' . ini_get('memory_limit');"
```

### Logs de Debug
```bash
# Habilitar debug
wp wpcw configure debug_mode true

# Ver logs detallados
wp wpcw logs view --level=debug

# Deshabilitar debug
wp wpcw configure debug_mode false
```

## 🔐 Seguridad

### Mejores Prácticas
- Usar HTTPS en todos los sitios
- Configurar rate limiting
- Mantener WordPress actualizado
- Usar contraseñas fuertes
- Configurar backups automáticos

### Configuración de Seguridad
```bash
# Habilitar rate limiting
wp wpcw configure api_rate_limiting true

# Configurar whitelist de IPs
wp wpcw configure ip_whitelist "192.168.1.0/24,10.0.0.0/8"
```

## 📈 Escalabilidad

### Para Múltiples Sitios
- Usar configuración centralizada
- Implementar monitoreo automático
- Configurar alertas por email
- Usar CDN para recursos estáticos

### Automatización
```bash
# Cron job para backups diarios
0 2 * * * wp wpcw backup create --description="Backup automático"

# Cron job para limpieza de logs
0 3 * * 0 wp wpcw logs clear

# Cron job para optimización semanal
0 4 * * 0 wp wpcw optimize --level=standard
```

## 📞 Soporte

### Recursos
- **Documentación**: `DOCUMENTACION_ADMINISTRADORES.md`
- **Changelog**: `CHANGELOG.md`
- **Mejoras**: `MEJORAS_VALIDACION.md`

### Contacto
- **Issues**: GitHub Issues
- **Email**: soporte@plugin.com
- **Documentación**: Wiki del proyecto

## 🎯 Casos de Uso

### Agencia Web
```bash
# Despliegue para 50 sitios de clientes
./scripts/mass-deployment.sh -p standard -o standard clientes.txt
```

### Empresa Multinacional
```bash
# Despliegue por regiones
./scripts/mass-deployment.sh -p enterprise -o aggressive sitios-latam.txt
./scripts/mass-deployment.sh -p enterprise -o aggressive sitios-europa.txt
```

### Proveedor de Hosting
```bash
# Instalación automática en nuevos sitios
wp wpcw install --profile=standard --region=auto
```

## 📋 Checklist de Despliegue

- [ ] Verificar requisitos del sistema
- [ ] Preparar archivo de sitios
- [ ] Configurar perfiles apropiados
- [ ] Ejecutar despliegue en modo prueba
- [ ] Ejecutar despliegue real
- [ ] Verificar estado en todos los sitios
- [ ] Configurar monitoreo
- [ ] Programar backups automáticos
- [ ] Documentar configuración específica
- [ ] Entrenar al equipo de soporte

---

**WP Cupón WhatsApp v1.4.0** - Optimizado para producción masiva 🚀