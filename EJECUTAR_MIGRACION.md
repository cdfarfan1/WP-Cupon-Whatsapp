# 🔧 EJECUTAR MIGRACIÓN DE BASE DE DATOS

## ⚡ SOLUCIÓN RÁPIDA (2 minutos)

### Opción 1: Script Automático (RECOMENDADO)

1. **Abre tu navegador** y ve a:
   ```
   http://localhost/wp-content/plugins/WP-Cupon-Whatsapp/run-migration.php
   ```

2. **Espera 1-2 minutos** mientras se ejecuta la migración

3. **¡Listo!** Verás mensaje de éxito en verde

4. **Borra el archivo** `run-migration.php` por seguridad

---

### Opción 2: Desde WordPress Admin

1. **Ve al admin de WordPress**: `http://localhost/tienda/wp-admin/`

2. **Verás un aviso grande en rojo** arriba que dice:
   ```
   🔧 WP Cupón WhatsApp - Migración de Base de Datos Requerida
   ```

3. **Click en el botón verde**: "▶️ Ejecutar Migración Ahora"

4. **Espera 1-2 minutos**

5. **¡Listo!** Refresca el admin y ya no habrá errores

---

### Opción 3: phpMyAdmin (Manual)

1. **Ir a**: http://localhost/phpmyadmin/

2. **Seleccionar base de datos**: `tienda` (panel izquierdo)

3. **Click en pestaña**: `SQL` (arriba)

4. **Abrir archivo**: `database/migration-update-canjes-table.sql`

5. **Copiar TODO** el contenido (Ctrl+A, Ctrl+C)

6. **Pegar** en el cuadro de texto de phpMyAdmin (Ctrl+V)

7. **Click en botón**: `Continuar` (abajo derecha)

8. **Esperar** mensaje de éxito

---

## ✅ ¿Cómo saber si funcionó?

Después de ejecutar cualquiera de las 3 opciones:

1. Ve al **Dashboard del plugin**:
   ```
   http://localhost/tienda/wp-admin/admin.php?page=wp-cupon-whatsapp
   ```

2. **NO deberías ver más errores** como:
   ```
   ❌ Unknown column 'estado_canje' in 'where clause'
   ```

3. El dashboard debería **cargar correctamente** con estadísticas

---

## 🔍 Verificar Estado (Diagnóstico)

Si quieres ver el estado de la base de datos **antes o después**:

```
http://localhost/wp-content/plugins/WP-Cupon-Whatsapp/check-database.php
```

Esto te mostrará:
- ✅ Qué columnas existen
- ❌ Qué columnas faltan
- 📊 Cuántos registros hay
- 🔄 Si la migración ya se ejecutó

---

## 🚨 Si Algo Sale Mal

La migración **crea un backup automático** antes de cambiar nada.

Si ves un error, ejecuta esto en phpMyAdmin para restaurar:

```sql
DROP TABLE wp_wpcw_canjes;
RENAME TABLE wp_wpcw_canjes_backup_YYYYMMDD_HHMMSS TO wp_wpcw_canjes;
```

(Reemplaza `YYYYMMDD_HHMMSS` con la fecha que aparece en el mensaje de error)

---

## 📞 ¿Necesitas Ayuda?

Si nada de esto funciona, copia el error completo y repórtalo.

---

**Tiempo estimado**: 2-3 minutos
**Riesgo**: BAJO (backup automático)
**Dificultad**: FÁCIL (copy-paste)
