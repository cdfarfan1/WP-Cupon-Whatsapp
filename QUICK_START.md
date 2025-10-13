# ⚡ GUÍA DE INICIO RÁPIDO
## WP CUPÓN WHATSAPP v1.5.1

**Tiempo total:** 30 minutos
**Nivel:** Principiante (no requiere conocimientos técnicos)
**Objetivo:** Tener tu primer programa de beneficios funcionando

---

## 📋 ANTES DE EMPEZAR

### ✅ Requisitos Previos

Asegúrate de tener:
- ✅ WordPress 5.8 o superior instalado
- ✅ WooCommerce 6.0 o superior activado
- ✅ Permisos de administrador en tu sitio
- ✅ 30 minutos de tiempo disponible

### 📱 WhatsApp Business API (Opcional)

Para enviar mensajes automáticos por WhatsApp necesitas:
- WhatsApp Business API Account (puedes obtenerlo en Meta/Facebook)
- API Key, Phone ID y Business Account ID

**💡 Nota:** Puedes instalar y usar el plugin sin WhatsApp. Lo agregas después cuando estés listo.

---

## 🚀 PASO 1: INSTALACIÓN (5 minutos)

### Opción A: Instalación Automática (Recomendada)

1. **Ir al Panel de WordPress**
   ```
   https://tusitio.com/wp-admin
   ```

2. **Navegar a Plugins**
   - En el menú lateral → `Plugins` → `Añadir nuevo`

3. **Buscar el Plugin**
   - En el buscador, escribir: `WP Cupón WhatsApp`

4. **Instalar**
   - Clic en botón `Instalar ahora`
   - Esperar 30 segundos

5. **Activar**
   - Clic en botón `Activar`
   - ¡Plugin instalado! ✅

### Opción B: Instalación Manual

1. **Descargar el Plugin**
   - Ir a [releases](https://github.com/pragmatic-solutions/wp-cupon-whatsapp/releases)
   - Descargar archivo `wp-cupon-whatsapp-v1.5.1.zip`

2. **Subir a WordPress**
   - WP Admin → `Plugins` → `Añadir nuevo`
   - Clic en botón `Subir plugin`
   - Elegir archivo ZIP descargado
   - Clic en `Instalar ahora`

3. **Activar**
   - Clic en `Activar plugin`
   - ¡Listo! ✅

### ✅ Verificación de Instalación

Después de activar, deberías ver:
- ✅ Mensaje de éxito: "Plugin activado"
- ✅ Nuevo menú en sidebar: **"WP Cupón WA"**
- ✅ 4 nuevos roles de usuario creados automáticamente

---

## ⚙️ PASO 2: CONFIGURACIÓN INICIAL (10 minutos)

### 2.1 - Wizard de Configuración

Al activar el plugin, se abre automáticamente el **Setup Wizard** de 5 pasos.

#### Paso 1/5 - Bienvenida
- Leer la bienvenida
- Clic en `Comenzar` →

#### Paso 2/5 - Crear Institución

**Datos requeridos:**
- **Nombre de la Institución** *
  - Ejemplo: `Sindicato de Trabajadores`
- **Identificador único** *
  - Slug URL-friendly, auto-generado
  - Ejemplo: `sindicato-trabajadores`
- **Logo** (opcional)
  - Subir imagen PNG/JPG (max 2MB)
- **Teléfono de Soporte**
  - Ejemplo: `+54 11 1234-5678`
- **Email de Contacto**
  - Ejemplo: `soporte@tuinstitucion.com`

**Clic en `Siguiente` →**

#### Paso 3/5 - Configurar WhatsApp (Opcional)

Si tienes WhatsApp Business API, completa:
- **API Key** * (se encripta automáticamente con AES-256)
- **Phone ID** *
- **Business Account ID** *

**Si no tienes WhatsApp API todavía:**
- ✅ Marcar checkbox: "Configurar WhatsApp más tarde"
- Clic en `Saltar este paso` →

**💡 Consejo:** Puedes agregar WhatsApp después desde `WP Cupón WA` → `Configuración`

#### Paso 4/5 - Páginas Automáticas

El plugin crea automáticamente 3 páginas:
- ✅ **Adhesión de Beneficiarios** (`/adhesion-beneficiarios/`)
- ✅ **Mis Cupones** (`/mis-cupones/`)
- ✅ **Canjear Cupón** (`/canjear-cupon/`)

**Opciones:**
- ✅ Crear páginas (Recomendado)
- ❌ No crear (puedes hacerlo manual después)

**Clic en `Crear Páginas` →**

#### Paso 5/5 - Finalización

**¡Configuración completa! ✅**

Resumen de lo que se creó:
- ✅ 1 Institución configurada
- ✅ 3 Páginas creadas con shortcodes
- ✅ 4 Roles de usuario creados
- ✅ 3 Tablas de base de datos creadas
- ✅ WhatsApp configurado (si completaste paso 3)

**Clic en `Ir al Dashboard` →**

---

## 🎫 PASO 3: CREAR PRIMER CUPÓN (5 minutos)

### 3.1 - Crear Cupón en WooCommerce

1. **Ir a WooCommerce → Cupones**
   ```
   WP Admin → WooCommerce → Cupones
   ```

2. **Clic en `Añadir cupón`**

3. **Completar Datos Básicos de WooCommerce:**

   **Pestaña "General":**
   - **Código del cupón** * → `BIENVENIDA20`
   - **Tipo de descuento** → `Descuento porcentual`
   - **Importe del cupón** → `20` (= 20% OFF)
   - **Fecha de caducidad** → Fecha futura (ej: 31/12/2025)

   **Descripción:**
   ```
   20% de descuento de bienvenida para nuevos beneficiarios
   ```

4. **Scroll Down: Encontrar Meta Box "WP Cupón WhatsApp"**

### 3.2 - Configurar WPCW en el Cupón

Dentro del meta box "WP Cupón WhatsApp":

1. **✅ Marcar checkbox:** "Habilitar en WPCW"

2. **Seleccionar Institución:**
   - Dropdown: Elegir la institución que creaste
   - Ejemplo: `Sindicato de Trabajadores`

3. **Tipo de Beneficio:**
   - Dropdown: `Descuento porcentaje`

4. **Comercio Asignado:** (opcional)
   - Dejar vacío si el cupón es para todos
   - O elegir comercio específico

5. **Restricciones (JSON):** (opcional)
   - Dejar vacío por ahora
   - Ejemplo avanzado:
   ```json
   {
     "uso_unico": true,
     "minimo_compra": 1000,
     "categorias": [15, 22]
   }
   ```

### 3.3 - Publicar Cupón

1. **Clic en botón `Publicar`** (arriba a la derecha)

2. **Verificación:**
   - ✅ Mensaje: "Cupón publicado"
   - ✅ Ver cupón en lista: `WooCommerce` → `Cupones`
   - ✅ Badge verde: "WPCW Habilitado"

**¡Primer cupón creado! 🎉**

---

## 👤 PASO 4: AGREGAR PRIMER BENEFICIARIO (5 minutos)

### Opción A: Manualmente desde Admin

1. **Ir a WP Cupón WA → Beneficiarios**
   ```
   WP Admin → WP Cupón WA → Beneficiarios
   ```

2. **Clic en `Añadir Beneficiario`**

3. **Completar Formulario:**

   - **Nombre Completo** * → `Juan Pérez`
   - **Tipo de Documento** → `DNI`
   - **Número de Documento** * → `12345678`
   - **Teléfono WhatsApp** * → `+54 9 11 1234-5678`
     - 💡 **Importante:** Incluir código de país (+54 para Argentina)
   - **Email** → `juan.perez@email.com` (opcional)
   - **Estado** → `Activa` (por defecto)

4. **Clic en `Guardar`**

5. **Sistema automáticamente:**
   - ✅ Genera código único de beneficiario (ej: `BEN-001-ABC123`)
   - ✅ Envía WhatsApp de bienvenida (si configurado)
   - ✅ Crea usuario WordPress (opcional)

**Beneficiario agregado! ✅**

### Opción B: Auto-registro desde Frontend

1. **Compartir URL de adhesión:**
   ```
   https://tusitio.com/adhesion-beneficiarios/
   ```

2. **El beneficiario completa el formulario:**
   - Nombre completo
   - DNI
   - WhatsApp
   - Email (opcional)
   - ✅ Acepta términos y condiciones

3. **Al enviar:**
   - ✅ Registro automático en sistema
   - ✅ WhatsApp de confirmación enviado
   - ✅ Código de beneficiario en WhatsApp

**¡Auto-registro funcionando! 🎉**

---

## 🛒 PASO 5: PROBAR CANJE ONLINE (5 minutos)

### 5.1 - Agregar Productos al Carrito

1. **Ir a la tienda** (como si fueras un cliente)
   ```
   https://tusitio.com/tienda/
   ```

2. **Elegir un producto**
   - Clic en cualquier producto

3. **Agregar al carrito**
   - Clic en `Añadir al carrito`

4. **Ver carrito**
   - Clic en ícono de carrito o `Ver carrito`

### 5.2 - Aplicar Cupón

En la página del carrito:

1. **Buscar sección "¿Tenés un cupón?"**
   - Generalmente debajo del subtotal

2. **Ingresar código del cupón:**
   ```
   BIENVENIDA20
   ```

3. **Clic en `Aplicar cupón`**

4. **Verificar:**
   - ✅ Mensaje: "Cupón aplicado correctamente"
   - ✅ Descuento aparece: `-20%`
   - ✅ Total actualizado

### 5.3 - Finalizar Compra

1. **Clic en `Finalizar compra`**

2. **Completar datos de envío y pago**

3. **Confirmar orden**

4. **Sistema automáticamente:**
   - ✅ Registra canje en tabla `wp_wpcw_canjes`
   - ✅ Envía WhatsApp de confirmación al beneficiario
   - ✅ Marca cupón como usado (si `uso_unico: true`)
   - ✅ Actualiza estadísticas en dashboard

**¡Primera compra con cupón exitosa! 🎉**

---

## 📊 PASO 6: VER ESTADÍSTICAS (2 minutos)

### 6.1 - Acceder al Dashboard

1. **Ir a WP Cupón WA → Dashboard**
   ```
   WP Admin → WP Cupón WA → Dashboard
   ```

### 6.2 - Métricas Principales

Verás 4 tarjetas con métricas:

#### 1. Beneficiarios Activos
- **Número:** 1 (el que agregaste)
- **Crecimiento:** +100% vs mes anterior

#### 2. Cupones Disponibles
- **Número:** 1 (el que creaste)
- **Próximos a vencer:** 0

#### 3. Canjes Totales
- **Número:** 1 (el que hiciste)
- **Este mes:** 1

#### 4. Ahorro Generado
- **Monto:** Depende del precio del producto
- **Ejemplo:** Si compraste $1,000 con 20% OFF = $200 ahorrado

### 6.3 - Gráficos

#### Gráfico Doughnut: Canjes por Tipo
- 🟢 Online: 1 (100%)
- 🔵 Presencial: 0 (0%)
- 🟡 WhatsApp: 0 (0%)

#### Tabla: Top 5 Comercios
- Lista de comercios con más canjes
- Cantidad de canjes por comercio
- Descuento total generado

### 6.4 - Filtros

Puedes filtrar por:
- 📅 **Rango de fechas** (hoy, semana, mes, año, custom)
- 🏪 **Comercio específico**
- 🎫 **Tipo de canje** (online, presencial, WhatsApp)

**¡Dashboard funcionando! 📊**

---

## ✅ VERIFICACIÓN FINAL

### Checklist de Funcionalidades

Marca cada item que verificaste:

#### Instalación y Configuración
- [ ] Plugin instalado y activado
- [ ] Setup wizard completado
- [ ] Institución creada
- [ ] Roles de usuario creados
- [ ] Páginas automáticas creadas

#### Cupones
- [ ] Primer cupón creado en WooCommerce
- [ ] Cupón habilitado en WPCW
- [ ] Cupón aparece en listado

#### Beneficiarios
- [ ] Primer beneficiario agregado
- [ ] Código único generado
- [ ] WhatsApp enviado (si configurado)

#### Canjes
- [ ] Canje online probado
- [ ] Cupón aplicado correctamente
- [ ] Descuento calculado bien
- [ ] Confirmación recibida

#### Dashboard
- [ ] Métricas actualizadas
- [ ] Gráficos renderizando
- [ ] Filtros funcionando

**Si marcaste todas:** ¡Configuración exitosa! 🎉

**Si falta alguna:** Ver [Solución de Problemas](#-solución-de-problemas-comunes)

---

## 🎓 PRÓXIMOS PASOS

### Nivel Básico (Ya completado ✅)
- ✅ Instalación
- ✅ Primer cupón
- ✅ Primer beneficiario
- ✅ Canje online

### Nivel Intermedio (Recomendado)

1. **Configurar WhatsApp Business API**
   - Seguir guía: [docs/MANUAL_USUARIO.md](docs/MANUAL_USUARIO.md) → Sección Administradores → Configuración WhatsApp

2. **Agregar Widgets de Elementor**
   - Si usas Elementor, añade widgets WPCW a tus páginas
   - Guía: [docs/ELEMENTOR.md](docs/ELEMENTOR.md)

3. **Crear Supervisores de Campo**
   - Para validación presencial con QR
   - `WP Admin` → `Usuarios` → `Añadir nuevo` → Rol: `Benefits Supervisor`

4. **Probar Validación Presencial**
   - Con QR scanner o código manual
   - Guía: Manual de Usuario → Sección Supervisores

5. **Explorar Comandos de WhatsApp**
   - CUPONES, CANJE, HISTORIAL, AYUDA
   - Guía: Manual de Usuario → Sección Beneficiarios

### Nivel Avanzado

1. **Integración con SDKs**
   - PHP SDK: [sdk/php/WPCuponWhatsappSDK.php](sdk/php/WPCuponWhatsappSDK.php)
   - JS SDK: [sdk/javascript/wpcw-sdk.js](sdk/javascript/wpcw-sdk.js)
   - Guía: [sdk/README.md](sdk/README.md)

2. **Webhooks**
   - Configurar eventos automáticos
   - Guía: [sdk/API_DOCUMENTATION.md](sdk/API_DOCUMENTATION.md) → Sección Webhooks

3. **Customización con Hooks**
   - Actions y Filters disponibles
   - Guía: [docs/developer/hooks-reference.md](docs/developer/hooks-reference.md)

4. **Multi-Institución**
   - Configurar múltiples instituciones
   - Asignar cupones por institución

---

## 🆘 SOLUCIÓN DE PROBLEMAS COMUNES

### ❌ Problema: "Plugin no aparece en el menú"

**Causa:** El plugin no se activó correctamente.

**Solución:**
1. Ir a `Plugins` → `Plugins instalados`
2. Buscar "WP Cupón WhatsApp"
3. Si está desactivado, clic en `Activar`
4. Refrescar página

---

### ❌ Problema: "No puedo crear cupones WPCW"

**Causa:** WooCommerce no está instalado o activado.

**Solución:**
1. Verificar: `Plugins` → `Plugins instalados`
2. Buscar "WooCommerce"
3. Si no está: `Plugins` → `Añadir nuevo` → Buscar "WooCommerce" → Instalar + Activar
4. Si está pero inactivo: `Activar`

---

### ❌ Problema: "Meta box WPCW no aparece en cupones"

**Causa:** Cache de navegador o plugin de caché.

**Solución:**
1. Limpiar cache del navegador (Ctrl + Shift + Delete)
2. Si usas plugin de cache (WP Rocket, W3 Total Cache, etc.):
   - Ir a configuración del plugin
   - Limpiar cache
3. Refrescar página de edición de cupón

---

### ❌ Problema: "WhatsApp no se envía"

**Causas posibles:**
1. WhatsApp API no configurado
2. Credenciales incorrectas
3. Rate limit excedido (10 msg/hora)

**Solución:**
1. Verificar configuración:
   - `WP Cupón WA` → `Configuración` → `WhatsApp`
   - Verificar API Key, Phone ID, Business ID

2. Probar mensaje de prueba:
   - En configuración WhatsApp, clic en `Enviar mensaje de prueba`
   - Si falla, revisar credenciales

3. Si excediste rate limit:
   - Esperar 1 hora
   - Los mensajes pendientes se enviarán automáticamente

---

### ❌ Problema: "Cupón no se aplica en checkout"

**Causas posibles:**
1. Cupón no habilitado en WPCW
2. Beneficiario no activo
3. Cupón ya usado (si `uso_unico`)
4. Fecha vencida

**Solución:**
1. Verificar cupón:
   - `WooCommerce` → `Cupones` → Editar cupón
   - Verificar que checkbox "Habilitar en WPCW" esté marcado
   - Verificar fecha de caducidad

2. Verificar beneficiario:
   - `WP Cupón WA` → `Beneficiarios`
   - Estado debe ser "Activa"

3. Si cupón ya usado:
   - Ver historial: `WP Cupón WA` → `Canjes`
   - Si es uso único, crear nuevo cupón

---

### ❌ Problema: "Dashboard no muestra estadísticas"

**Causa:** No hay datos aún, o cache.

**Solución:**
1. Si recién instalaste:
   - Crear al menos 1 beneficiario y 1 canje
   - Refrescar página

2. Si hay datos pero no aparecen:
   - Limpiar cache
   - Verificar: `WP Cupón WA` → `Canjes` → Debe haber registros

3. Si persiste:
   - Ver logs: `WP Cupón WA` → `Configuración` → `Debug`
   - Activar WP_DEBUG en wp-config.php

---

### ❌ Problema: "Error al activar: Base de datos"

**Causa:** Permisos de base de datos insuficientes.

**Solución:**
1. Verificar permisos de usuario MySQL:
   - Debe tener: `CREATE`, `ALTER`, `INSERT`, `SELECT`

2. Si tienes cPanel:
   - Ir a `phpMyAdmin`
   - Seleccionar tu base de datos
   - Usuario debe tener permisos completos

3. Contactar hosting si no puedes modificar permisos

---

## 📚 RECURSOS ADICIONALES

### Documentación Completa

| Documento | Para quién | Link |
|-----------|------------|------|
| **Manual de Usuario** | Beneficiarios, Admins, Comercios | [docs/MANUAL_USUARIO.md](docs/MANUAL_USUARIO.md) |
| **Presentación Técnica** | Developers, CTOs | [presentation/PRESENTACION_DESARROLLO.md](presentation/PRESENTACION_DESARROLLO.md) |
| **SDK Documentation** | Integradores | [sdk/README.md](sdk/README.md) |
| **API Reference** | Developers | [sdk/API_DOCUMENTATION.md](sdk/API_DOCUMENTATION.md) |
| **Índice Maestro** | Todos | [INDEX_DOCUMENTACION_COMPLETA.md](INDEX_DOCUMENTACION_COMPLETA.md) |

### Videos Tutoriales (Próximamente)

- 🎥 Instalación paso a paso (5 min)
- 🎥 Crear primer cupón (3 min)
- 🎥 Configurar WhatsApp (8 min)
- 🎥 Validación presencial con QR (6 min)

### Soporte

**Email:** soporte@pragmatic-solutions.com
**Horario:** Lunes - Viernes, 9:00 - 18:00 hs (GMT-3)
**Respuesta:** < 24 horas (issues críticos)

**Slack Community:** pragmatic-solutions.slack.com
**GitHub Issues:** [github.com/pragmatic-solutions/wp-cupon-whatsapp/issues](https://github.com/pragmatic-solutions/wp-cupon-whatsapp/issues)

---

## 🎉 ¡FELICITACIONES!

Completaste la configuración básica de **WP Cupón WhatsApp**.

### Ya puedes:
- ✅ Crear y gestionar cupones
- ✅ Registrar beneficiarios
- ✅ Procesar canjes online
- ✅ Ver estadísticas en tiempo real

### Próximos pasos recomendados:
1. Configurar WhatsApp Business API
2. Agregar más cupones
3. Invitar beneficiarios a registrarse
4. Explorar features avanzadas

### ¿Necesitas ayuda?
- 📖 [Manual Completo](docs/MANUAL_USUARIO.md)
- 📧 soporte@pragmatic-solutions.com
- 💬 [Slack Community](https://pragmatic-solutions.slack.com)

---

**⚡ Quick Start Guide - WP Cupón WhatsApp v1.5.1**
**© 2025 Pragmatic Solutions - Innovación Aplicada**
**Última actualización: 10 de Octubre, 2025**
