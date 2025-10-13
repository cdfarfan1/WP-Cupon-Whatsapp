# 📜 INFORMACIÓN SOBRE LICENCIAS - PLUGINS DE CRISTIAN FARFAN

## ⚠️ IMPORTANTE: WordPress.org vs Distribución Privada

### 🔍 Dos Escenarios de Licenciamiento

---

## 1️⃣ PARA WORDPRESS.ORG (GPL-2.0+ REQUERIDO)

**WordPress.org REQUIERE licencia GPL-compatible**

Si planeas publicar en WordPress.org, **DEBES** usar:
- **Licencia**: GPL-2.0 or later
- **License URI**: http://www.gnu.org/licenses/gpl-2.0.html

**Header del plugin**:
```php
* License: GPL-2.0+
* License URI: http://www.gnu.org/licenses/gpl-2.0.txt
```

**readme.txt**:
```
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
```

**composer.json**:
```json
"license": "GPL-2.0-or-later"
```

**Archivo LICENSE.txt**: Copia el texto de GPL-2.0 desde https://www.gnu.org/licenses/gpl-2.0.txt

---

## 2️⃣ PARA DISTRIBUCIÓN PRIVADA/GITHUB/COMERCIAL (MIT)

**Si NO publicas en WordPress.org**, puedes usar MIT:
- **Licencia**: MIT
- **License URI**: https://opensource.org/licenses/MIT

**Header del plugin**:
```php
* License: MIT
* License URI: https://opensource.org/licenses/MIT
```

**readme.txt** (si aplica):
```
License: MIT
License URI: https://opensource.org/licenses/MIT
```

**composer.json**:
```json
"license": "MIT"
```

**Archivo LICENSE.txt**: Usa el template MIT incluido en `.dev-templates/LICENSE.txt`

---

## 🎯 RECOMENDACIÓN PARA CRISTIAN FARFAN

### Estrategia Dual de Licenciamiento

**Opción A - Plugin Comercial/Privado**:
```
Usa: MIT License
Distribución: GitHub privado, venta directa, clientes
Ventaja: Más flexible, permite uso comercial sin restricciones GPL
```

**Opción B - Plugin para WordPress.org**:
```
Usa: GPL-2.0+
Distribución: WordPress.org repository
Ventaja: Compatible con ecosystem WordPress, mayor alcance
```

**Opción C - Dual Licensing (RECOMENDADO)**:
```
Versión Core: GPL-2.0+ (en WordPress.org)
Versión Premium: MIT o Licencia Comercial Propietaria
Distribución: WordPress.org (free) + Sitio propio (premium)
```

---

## 📊 COMPARACIÓN DE LICENCIAS

| Aspecto | GPL-2.0+ | MIT |
|---------|----------|-----|
| **Uso Comercial** | ✅ Permitido | ✅ Permitido |
| **Modificación** | ✅ Permitido | ✅ Permitido |
| **Distribución** | ✅ Permitido | ✅ Permitido |
| **Copyleft** | ✅ Sí (modificaciones deben ser GPL) | ❌ No |
| **WordPress.org** | ✅ Compatible | ❌ No compatible |
| **Flexibilidad** | ⚠️ Media | ✅ Alta |
| **Código Cerrado** | ❌ No permitido | ✅ Permitido |
| **Sublicenciar** | ❌ No | ✅ Sí |

---

## 🔧 CÓMO CAMBIAR DE LICENCIA

### De MIT a GPL-2.0+ (para WordPress.org)

1. **Actualizar header principal**:
```php
// Cambiar:
* License: MIT
// Por:
* License: GPL-2.0+
* License URI: http://www.gnu.org/licenses/gpl-2.0.txt
```

2. **Actualizar LICENSE.txt**:
```bash
# Descargar GPL-2.0
wget https://www.gnu.org/licenses/gpl-2.0.txt -O LICENSE.txt
```

3. **Actualizar composer.json**:
```json
"license": "GPL-2.0-or-later"
```

4. **Actualizar readme.txt**:
```
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
```

### De GPL-2.0+ a MIT (para distribución privada)

1. Seguir los mismos pasos pero en reversa
2. Usar el template LICENSE.txt incluido en `.dev-templates/`

---

## 📝 TEMPLATES DISPONIBLES

En `.dev-templates/` encontrarás:

- ✅ `LICENSE.txt` - Licencia MIT (actual)
- ✅ `plugin-header.php` - Header con MIT (actual)
- ✅ `composer.json` - Configurado para MIT (actual)
- ✅ `package.json` - Configurado para MIT (actual)

Si necesitas GPL-2.0+, ejecuta:
```bash
cd .dev-templates/
# Descargar GPL license
curl https://www.gnu.org/licenses/gpl-2.0.txt -o LICENSE-GPL.txt
```

---

## 🎯 DECISIÓN FINAL PARA TUS PLUGINS

**Configuración Actual**: MIT License

**Razón**: Máxima flexibilidad para:
- Distribución comercial
- Venta de licencias premium
- Código cerrado en versiones premium
- Uso en proyectos propietarios de clientes

**Si cambias a WordPress.org**: Solo necesitas cambiar los headers y LICENSE.txt (5 minutos)

---

## 📞 CONTACTO

**Desarrollador**: Cristian Farfan
**Email**: farfancris@gmail.com
**Web**: https://cristianfarfan.com.ar

---

**📅 Documento Creado**: 8 de Octubre, 2025
**✍️ Autor**: Sistema de Agentes Élite
**📊 Versión**: 1.0.0
