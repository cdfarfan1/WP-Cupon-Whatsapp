# 📋 GUÍA DE INSTALACIÓN Y ACTUALIZACIÓN
## Plugin WP-Cupon-Whatsapp

---

## 🚀 **INSTALACIÓN INICIAL**

### **Requisitos del Sistema**
- WordPress 5.0 o superior
- PHP 7.4 o superior
- MySQL 5.6 o superior
- Permisos de escritura en `/wp-content/plugins/`

### **Pasos de Instalación**

1. **Descarga del Plugin**
   - Descarga el archivo ZIP del plugin
   - O clona el repositorio en tu servidor

2. **Subida al Servidor**
   ```bash
   # Opción 1: Subir carpeta completa
   /wp-content/plugins/WP-Cupon-Whatsapp/
   
   # Opción 2: Descomprimir ZIP
   unzip wp-cupon-whatsapp.zip -d /wp-content/plugins/
   ```

3. **Activación**
   - Ir a **Plugins** → **Plugins Instalados**
   - Buscar "WP Cupón WhatsApp"
   - Hacer clic en **Activar**

4. **Configuración Inicial**
   - Ir a **Cupones** → **Configuración**
   - Completar el asistente de configuración
   - Configurar integración con WhatsApp

---

## 🔄 **ACTUALIZACIÓN DEL PLUGIN**

### **Antes de Actualizar**

1. **Backup Completo**
   ```bash
   # Respaldar base de datos
   mysqldump -u usuario -p base_datos > backup_$(date +%Y%m%d).sql
   
   # Respaldar archivos del plugin
   cp -r /wp-content/plugins/WP-Cupon-Whatsapp/ backup_plugin_$(date +%Y%m%d)/
   ```

2. **Verificar Compatibilidad**
   - Revisar versión de WordPress
   - Verificar versión de PHP
   - Comprobar otros plugins activos

### **Proceso de Actualización**

1. **Desactivar Plugin**
   - Ir a **Plugins** → **Plugins Instalados**
   - **Desactivar** WP Cupón WhatsApp

2. **Reemplazar Archivos**
   ```bash
   # Eliminar versión anterior (mantener backup)
   rm -rf /wp-content/plugins/WP-Cupon-Whatsapp/
   
   # Subir nueva versión
   unzip nueva-version.zip -d /wp-content/plugins/
   ```

3. **Reactivar Plugin**
   - **Activar** el plugin nuevamente
   - Verificar configuraciones

---

## 🔍 **VERIFICACIÓN POST-INSTALACIÓN**

### **Script de Verificación Automática**

Usa el archivo `verify-server-file.php` incluido:

```php
// Subir verify-server-file.php a la raíz del sitio
// Acceder a: https://tu-sitio.com/verify-server-file.php?verify=1
```

### **Verificaciones Manuales**

1. **Estructura de Archivos**
   ```
   /wp-content/plugins/WP-Cupon-Whatsapp/
   ├── wp-cupon-whatsapp.php ✅
   ├── debug-headers.php ✅
   ├── admin/ ✅
   ├── includes/ ✅
   └── public/ ✅
   ```

2. **Permisos de Archivos**
   ```bash
   # Verificar permisos
   find /wp-content/plugins/WP-Cupon-Whatsapp/ -type f -exec chmod 644 {} \;
   find /wp-content/plugins/WP-Cupon-Whatsapp/ -type d -exec chmod 755 {} \;
   ```

3. **Logs de Error**
   ```bash
   # Revisar logs de WordPress
   tail -f /wp-content/debug.log
   
   # Revisar logs del servidor
   tail -f /var/log/apache2/error.log
   ```

---

## ⚠️ **SOLUCIÓN DE PROBLEMAS COMUNES**

### **Error: "Parse error: syntax error"**

**Causa:** Archivo PHP corrupto o incompleto

**Solución:**
1. Verificar integridad de archivos
2. Re-subir archivos desde backup limpio
3. Verificar permisos de archivos

```bash
# Verificar sintaxis PHP
php -l /wp-content/plugins/WP-Cupon-Whatsapp/debug-headers.php
```

### **Error: "Plugin could not be activated"**

**Causa:** Conflicto con otros plugins o tema

**Solución:**
1. Desactivar todos los plugins
2. Activar WP-Cupon-Whatsapp
3. Reactivar plugins uno por uno

### **Error: "Headers already sent"**

**Causa:** Salida antes de headers HTTP

**Solución:**
1. Verificar espacios en blanco antes de `<?php`
2. Revisar archivos con BOM UTF-8
3. Usar `debug-headers.php` para diagnóstico

---

## 🛠️ **HERRAMIENTAS DE DIAGNÓSTICO**

### **Archivos de Diagnóstico Incluidos**

1. **verify-server-file.php**
   - Verificación completa del plugin
   - Análisis de sintaxis PHP
   - Reporte de estado detallado

2. **debug-headers.php**
   - Diagnóstico de headers HTTP
   - Detección de salida prematura
   - Análisis de conflictos

3. **syntax-checker.php** ⭐ **NUEVO**
   - Verificación automática de sintaxis PHP
   - Análisis completo de todos los archivos del plugin
   - Detección de errores y advertencias
   - Generación de reportes detallados

### **Sistema de Verificación Automática**

#### **Uso Local (Línea de Comandos)**
```bash
# Opción 1: Ejecutar directamente
php syntax-checker.php

# Opción 2: Usar script batch (Windows)
run-syntax-check.bat

# Opción 3: Con XAMPP/WAMP
C:\xampp\php\php.exe syntax-checker.php
```

#### **Uso Web (Servidor)**
```
# Subir syntax-checker.php al servidor
# Acceder via navegador:
https://tu-sitio.com/wp-content/plugins/WP-Cupon-Whatsapp/syntax-checker.php?check=1
```

#### **Características del Verificador**
- ✅ Verificación de sintaxis PHP en todos los archivos
- ✅ Detección de BOM UTF-8
- ✅ Verificación de espacios antes de `<?php`
- ✅ Análisis de llaves desbalanceadas `{}`
- ✅ Generación de logs detallados
- ✅ Reporte de estadísticas completas
- ✅ Códigos de salida para automatización

### **Comandos Útiles**

```bash
# Verificar versión de PHP
php -v

# Verificar módulos PHP
php -m | grep -E '(curl|json|mbstring)'

# Verificar permisos WordPress
ls -la /wp-content/plugins/

# Verificar logs en tiempo real
tail -f /wp-content/debug.log
```

---

## 📞 **SOPORTE TÉCNICO**

### **Información a Proporcionar**

Antes de solicitar soporte, recopila:

1. **Información del Sistema**
   - Versión de WordPress
   - Versión de PHP
   - Tema activo
   - Plugins activos

2. **Logs de Error**
   - Últimas 50 líneas de `/wp-content/debug.log`
   - Logs del servidor web
   - Resultado de `verify-server-file.php`

3. **Pasos para Reproducir**
   - Descripción detallada del problema
   - Pasos exactos para reproducir
   - Comportamiento esperado vs actual

### **Canales de Soporte**

- **Documentación:** Revisar `README.md` y `docs/`
- **Issues:** Crear issue en el repositorio
- **Email:** Contactar al desarrollador

---

## ✅ **CHECKLIST DE INSTALACIÓN**

- [ ] Backup completo realizado
- [ ] Requisitos del sistema verificados
- [ ] Plugin descargado/clonado
- [ ] Archivos subidos correctamente
- [ ] Permisos configurados (644/755)
- [ ] Plugin activado sin errores
- [ ] Configuración inicial completada
- [ ] Verificación con `verify-server-file.php`
- [ ] Logs de error revisados
- [ ] Funcionalidad básica probada
- [ ] Backup post-instalación creado

---

**Última actualización:** 2025-08-16  
**Versión del plugin:** 1.3.1  
**Compatibilidad:** WordPress 5.0+ | PHP 7.4+