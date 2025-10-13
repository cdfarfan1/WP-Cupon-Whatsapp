# 📱 PLANIFICACIÓN: APLICACIÓN MÓVIL NATIVA
## WP CUPÓN WHATSAPP - MOBILE APP v1.0.0

**Fecha de Planificación:** 11 de Octubre, 2025
**Responsable:** @CTO (Dr. Viktor Petrov) + @FRONTEND (Sophie Laurent)
**Estado:** 📋 Planificación - No iniciado
**Estimación:** 6 meses de desarrollo (Q1-Q2 2026)

---

## 🎯 VISIÓN GENERAL

### Objetivo

Desarrollar una **aplicación móvil nativa multiplataforma** (iOS + Android) que se integre con el plugin WordPress **WP Cupón WhatsApp**, permitiendo a beneficiarios, comercios e instituciones gestionar cupones y canjes desde sus dispositivos móviles.

### Propuesta de Valor

**Para Beneficiarios:**
- 📱 Ver cupones disponibles en app nativa
- 🎫 Código QR siempre disponible (funciona sin internet)
- 🔔 Notificaciones push de nuevos cupones
- 🛒 Canjear cupones online y presenciales
- 📊 Historial completo de canjes
- 💬 Chat directo con institución

**Para Comercios:**
- 📷 Scanner QR integrado para validación
- ✅ Validación offline (sincroniza después)
- 📊 Dashboard con estadísticas del día
- 🔔 Notificaciones de canjes pendientes
- 💼 Gestión de cupones propios

**Para Instituciones:**
- 📊 Dashboard ejecutivo móvil
- 📈 Métricas en tiempo real
- 👥 Gestión de beneficiarios
- 🔔 Alertas de actividad

---

## 🏗️ ARQUITECTURA TÉCNICA

### Stack Tecnológico Propuesto

```
Frontend Mobile:
├── Framework: React Native 0.74+ (iOS + Android)
├── State Management: Redux Toolkit + RTK Query
├── Navigation: React Navigation 6.x
├── UI Library: React Native Paper (Material Design)
├── QR Scanner: react-native-camera + react-native-qrcode-scanner
├── Push Notifications: Firebase Cloud Messaging (FCM)
├── Offline Storage: Redux Persist + AsyncStorage
├── Biometrics: react-native-biometrics
└── Analytics: Firebase Analytics + Mixpanel

Backend Integration:
├── API: REST API del plugin WP Cupón WhatsApp
├── Authentication: JWT (JSON Web Tokens)
├── Real-time: WebSockets (Socket.io) para notificaciones
├── CDN: CloudFlare para assets
└── Push Server: Firebase Cloud Functions

DevOps:
├── CI/CD: GitHub Actions
├── Testing: Jest + Detox (E2E)
├── Code Quality: ESLint + Prettier
├── Version Control: Git + GitHub
└── Distribution: App Store + Google Play
```

### Arquitectura de 3 Capas

```
┌─────────────────────────────────────────┐
│      CAPA DE PRESENTACIÓN (Mobile)      │
│  React Native App (iOS + Android)       │
│  - Components                           │
│  - Screens                              │
│  - Navigation                           │
└──────────────────┬──────────────────────┘
                   │
                   │ REST API + WebSockets
                   │
┌──────────────────▼──────────────────────┐
│      CAPA DE NEGOCIO (WordPress)        │
│  WP Cupón WhatsApp Plugin v1.5.1+       │
│  - REST API Endpoints (12+)             │
│  - WebSocket Server                     │
│  - Push Notification Manager           │
└──────────────────┬──────────────────────┘
                   │
                   │ MySQL Queries
                   │
┌──────────────────▼──────────────────────┐
│      CAPA DE DATOS (Database)           │
│  MySQL 5.7+ / MariaDB 10.3+             │
│  - wp_wpcw_instituciones                │
│  - wp_wpcw_adhesiones                   │
│  - wp_wpcw_canjes                       │
└─────────────────────────────────────────┘
```

---

## 📊 FUNCIONALIDADES POR ROL

### 1. APP BENEFICIARIO (👤)

#### Features Core

**Autenticación (v1.0)**
- ✅ Login con código de beneficiario + DNI
- ✅ Login con email + password
- ✅ Biometric authentication (Face ID / Touch ID)
- ✅ Recuperación de contraseña por email
- ✅ Registro de nuevo beneficiario desde app

**Dashboard (v1.0)**
- ✅ Tarjeta con código QR del beneficiario
- ✅ Lista de cupones disponibles (cards)
- ✅ Saldo de puntos (si gamificación activa)
- ✅ Próximos cupones a vencer
- ✅ Banner promocional

**Cupones (v1.0)**
- ✅ Lista completa de cupones disponibles
- ✅ Filtros: por comercio, por categoría, por fecha
- ✅ Búsqueda por texto
- ✅ Detalle de cupón (descripción, restricciones, fecha vencimiento)
- ✅ Botón "Usar ahora" → Genera código QR temporal
- ✅ Botón "Compartir" → Share via WhatsApp/Email
- ✅ Badge "Ya usado" en cupones canjeados

**Canje Online (v1.0)**
- ✅ Ver tienda online (WebView del sitio WooCommerce)
- ✅ Aplicar cupón desde lista
- ✅ Copiar código al portapapeles
- ✅ Confirmar compra

**Canje Presencial (v1.0)**
- ✅ Mostrar QR del beneficiario al comercio
- ✅ QR funciona offline (datos en caché)
- ✅ Confirmación visual de canje exitoso
- ✅ Notificación push de confirmación

**Historial (v1.0)**
- ✅ Lista de todos los canjes realizados
- ✅ Detalle: fecha, comercio, cupón, descuento
- ✅ Filtros por fecha, comercio, tipo
- ✅ Total ahorrado acumulado

**Perfil (v1.0)**
- ✅ Ver datos personales
- ✅ Editar teléfono y email
- ✅ Cambiar contraseña
- ✅ Configuración de notificaciones
- ✅ Preferencias de idioma
- ✅ Cerrar sesión

**Notificaciones (v1.0)**
- ✅ Push: Nuevo cupón disponible
- ✅ Push: Cupón próximo a vencer (48h antes)
- ✅ Push: Canje confirmado
- ✅ Push: Promociones especiales
- ✅ In-app notification center

#### Features Avanzadas (v1.1+)

**Wallet de Cupones (v1.1)**
- ✅ Cupones favoritos
- ✅ Agregar a Apple Wallet / Google Pay
- ✅ Cupones guardados para usar después

**Gamificación (v1.2)**
- ✅ Sistema de puntos por canjes
- ✅ Niveles: Bronce, Plata, Oro, Platinum
- ✅ Achievements/Badges
- ✅ Leaderboard de ahorradores
- ✅ Recompensas por invitar amigos

**Social (v1.3)**
- ✅ Compartir cupones con amigos
- ✅ Invitar amigos (referral program)
- ✅ Ver qué cupones usan tus amigos
- ✅ Chat con soporte de institución

---

### 2. APP COMERCIO (🏪)

#### Features Core (v1.0)

**Autenticación**
- ✅ Login con usuario de comercio + password
- ✅ Biometric authentication

**Dashboard**
- ✅ Canjes del día (contador)
- ✅ Descuento total aplicado hoy
- ✅ Gráfico de canjes últimos 7 días
- ✅ Top 3 cupones más usados

**Scanner QR (v1.0)** ⭐ **Feature Principal**
- ✅ Abrir cámara para escanear QR
- ✅ Detectar QR del beneficiario automáticamente
- ✅ Mostrar datos del beneficiario
- ✅ Listar cupones disponibles para usar
- ✅ Seleccionar cupón a aplicar
- ✅ Ingresar monto de la compra
- ✅ Calcular descuento en tiempo real
- ✅ Confirmar canje
- ✅ Mostrar pantalla de éxito
- ✅ Funciona offline (sincroniza después)

**Validación Manual (v1.0)**
- ✅ Ingresar código de beneficiario manualmente
- ✅ Búsqueda por DNI
- ✅ Flujo igual que scanner QR

**Mis Cupones (v1.0)**
- ✅ Ver cupones del comercio
- ✅ Crear nuevo cupón (requiere aprobación)
- ✅ Desactivar cupón propio
- ✅ Estadísticas por cupón

**Historial de Canjes (v1.0)**
- ✅ Lista de canjes realizados
- ✅ Filtros por fecha, cupón, beneficiario
- ✅ Detalle de cada canje
- ✅ Exportar a CSV

**Perfil**
- ✅ Datos del comercio
- ✅ Cambiar contraseña
- ✅ Configuración de notificaciones

#### Features Avanzadas (v1.1+)

**Reportes (v1.1)**
- ✅ Reportes mensuales automáticos
- ✅ Comparación mes vs mes anterior
- ✅ Exportar PDF

**Offline Mode (v1.1)**
- ✅ Validar canjes sin internet
- ✅ Cola de sincronización
- ✅ Sincronizar cuando vuelve conexión

---

### 3. APP INSTITUCIÓN (🏢)

#### Features Core (v1.0)

**Dashboard Ejecutivo**
- ✅ Métricas principales (4 tarjetas)
- ✅ Gráfico de canjes por tipo
- ✅ Gráfico de adhesiones por mes
- ✅ Top 10 comercios
- ✅ Alertas de actividad

**Beneficiarios**
- ✅ Lista completa con búsqueda
- ✅ Agregar nuevo beneficiario
- ✅ Editar/desactivar beneficiario
- ✅ Ver historial de canjes por beneficiario

**Cupones**
- ✅ Lista de cupones activos
- ✅ Crear nuevo cupón
- ✅ Editar/desactivar cupón
- ✅ Aprobar cupones de comercios

**Comercios**
- ✅ Lista de comercios adheridos
- ✅ Agregar nuevo comercio
- ✅ Ver estadísticas por comercio

**Reportes**
- ✅ Reportes preconstruidos
- ✅ Exportar a Excel/PDF
- ✅ Programar reportes automáticos

**Notificaciones**
- ✅ Enviar notificación masiva
- ✅ Segmentar por grupo de beneficiarios
- ✅ Programar notificaciones

#### Features Avanzadas (v1.1+)

**Analytics (v1.1)**
- ✅ Dashboard avanzado con múltiples gráficos
- ✅ Filtros personalizados
- ✅ Comparaciones temporales

**Campañas (v1.2)**
- ✅ Crear campañas de cupones
- ✅ Segmentación avanzada
- ✅ A/B testing de cupones

---

## 🔌 INTEGRACIÓN CON EL PLUGIN

### Endpoints REST API Necesarios

El plugin **WP Cupón WhatsApp v1.5.1** ya tiene 12 endpoints. Para la app móvil se requieren **8 endpoints adicionales**:

#### Nuevos Endpoints Requeridos

```
1. POST /wp-json/wpcw-mobile/v1/auth/login
   - Autenticación de usuarios de la app
   - Devuelve: JWT token + refresh token

2. POST /wp-json/wpcw-mobile/v1/auth/register
   - Registro de nuevo beneficiario desde app
   - Devuelve: Usuario creado + JWT token

3. POST /wp-json/wpcw-mobile/v1/auth/refresh
   - Renovar JWT token expirado
   - Devuelve: Nuevo JWT token

4. GET /wp-json/wpcw-mobile/v1/user/profile
   - Obtener perfil del usuario logueado
   - Devuelve: Datos completos del usuario

5. PUT /wp-json/wpcw-mobile/v1/user/profile
   - Actualizar perfil del usuario
   - Devuelve: Usuario actualizado

6. POST /wp-json/wpcw-mobile/v1/push/register
   - Registrar device token para push notifications
   - Devuelve: Confirmación de registro

7. GET /wp-json/wpcw-mobile/v1/notifications
   - Obtener notificaciones in-app
   - Devuelve: Lista de notificaciones

8. POST /wp-json/wpcw-mobile/v1/canje/offline
   - Registrar canje realizado offline
   - Devuelve: Confirmación de registro
```

#### Endpoints Existentes que Usará la App

```
1. GET /wp-json/wpcw/v1/beneficiarios/{id}
2. GET /wp-json/wpcw/v1/beneficiarios/{id}/cupones
3. GET /wp-json/wpcw/v1/beneficiarios/{id}/historial
4. POST /wp-json/wpcw/v1/cupones/validate
5. POST /wp-json/wpcw/v1/canjes
6. GET /wp-json/wpcw/v1/instituciones/{id}/stats
7. POST /wp-json/wpcw/v1/whatsapp/send
```

### WebSockets para Real-Time

Para notificaciones en tiempo real, se implementará un servidor WebSocket:

```javascript
// Eventos WebSocket
Events:
- connection: Nueva conexión de cliente
- disconnect: Cliente desconectado
- authenticate: Autenticación con JWT

// Eventos del servidor → cliente
- notification: Nueva notificación
- cupon.nuevo: Nuevo cupón disponible
- cupon.vencimiento: Cupón próximo a vencer
- canje.confirmado: Canje confirmado
- canje.rechazado: Canje rechazado

// Eventos del cliente → servidor
- subscribe: Suscribirse a canal
- unsubscribe: Desuscribirse de canal
```

**Implementación:**
```php
// Archivo: includes/class-wpcw-websocket-server.php
// Librería: Ratchet (PHP WebSocket library)
// Puerto: 8080 (configurable)
```

### Sistema de Push Notifications

#### Arquitectura

```
┌────────────────┐
│  Mobile App    │
│  (iOS/Android) │
└────────┬───────┘
         │ 1. Register device token
         │
┌────────▼────────────────┐
│  WordPress Plugin       │
│  POST /push/register    │
│  Guarda token en BD     │
└────────┬────────────────┘
         │ 2. Trigger event (ej: nuevo cupón)
         │
┌────────▼────────────────┐
│  Firebase Cloud         │
│  Functions              │
│  - Envia push a FCM     │
└────────┬────────────────┘
         │ 3. Push notification
         │
┌────────▼────────────────┐
│  Firebase Cloud         │
│  Messaging (FCM)        │
│  - iOS: APNs            │
│  - Android: FCM native  │
└────────┬────────────────┘
         │ 4. Notificación en device
         │
┌────────▼───────┐
│  Mobile App    │
│  (foreground/  │
│   background)  │
└────────────────┘
```

#### Tipos de Notificaciones

```javascript
// 1. Nuevo cupón disponible
{
  title: "🎉 Nuevo cupón disponible",
  body: "20% OFF en RestaurantX",
  data: {
    type: "cupon.nuevo",
    cupon_id: 123,
    screen: "CuponDetail"
  }
}

// 2. Cupón próximo a vencer
{
  title: "⏰ Tu cupón vence pronto",
  body: "DESCUENTO20 vence en 48 horas",
  data: {
    type: "cupon.vencimiento",
    cupon_id: 123,
    screen: "CuponDetail"
  }
}

// 3. Canje confirmado
{
  title: "✅ Canje confirmado",
  body: "Ahorraste $500 en RestaurantX",
  data: {
    type: "canje.confirmado",
    canje_id: 456,
    screen: "CanjeDetail"
  }
}

// 4. Promoción especial
{
  title: "🔥 Promoción especial",
  body: "50% OFF solo hoy en ComercioY",
  data: {
    type: "promo.especial",
    cupon_id: 789,
    screen: "CuponDetail"
  }
}
```

---

## 📱 DISEÑO DE LA APP

### Design System

**Tema Visual:**
- Colores primarios: Del branding de cada institución (configurable)
- Colores secundarios: Complementarios
- Tipografía: Inter (sans-serif moderna)
- Iconos: Material Icons + FontAwesome
- Animaciones: React Native Reanimated 3

**Componentes Reutilizables:**
```
Components/
├── Buttons/
│   ├── PrimaryButton.jsx
│   ├── SecondaryButton.jsx
│   └── IconButton.jsx
├── Cards/
│   ├── CouponCard.jsx
│   ├── CanjeCard.jsx
│   └── StatCard.jsx
├── Forms/
│   ├── Input.jsx
│   ├── Select.jsx
│   └── DatePicker.jsx
├── Navigation/
│   ├── BottomTab.jsx
│   └── Header.jsx
├── QR/
│   ├── QRDisplay.jsx
│   └── QRScanner.jsx
└── Shared/
    ├── Loading.jsx
    ├── EmptyState.jsx
    └── ErrorBoundary.jsx
```

### Screens Principales

#### Beneficiario App

```
Screens/
├── Auth/
│   ├── LoginScreen.jsx
│   ├── RegisterScreen.jsx
│   └── ForgotPasswordScreen.jsx
├── Home/
│   └── DashboardScreen.jsx
├── Cupones/
│   ├── CuponesListScreen.jsx
│   ├── CuponDetailScreen.jsx
│   └── CuponQRScreen.jsx (mostrar QR para usar)
├── Historial/
│   ├── HistorialListScreen.jsx
│   └── CanjeDetailScreen.jsx
├── Perfil/
│   ├── PerfilScreen.jsx
│   ├── EditPerfilScreen.jsx
│   └── ConfiguracionScreen.jsx
└── Notifications/
    └── NotificationsScreen.jsx
```

#### Comercio App

```
Screens/
├── Auth/
│   └── LoginScreen.jsx
├── Home/
│   └── DashboardScreen.jsx
├── Scanner/
│   ├── QRScannerScreen.jsx (⭐ pantalla principal)
│   ├── ValidacionManualScreen.jsx
│   └── ConfirmacionCanjeScreen.jsx
├── Cupones/
│   ├── MisCuponesScreen.jsx
│   └── CrearCuponScreen.jsx
├── Historial/
│   └── HistorialCanjsScreen.jsx
└── Perfil/
    └── PerfilScreen.jsx
```

#### Institución App

```
Screens/
├── Auth/
│   └── LoginScreen.jsx
├── Dashboard/
│   └── DashboardEjecutivoScreen.jsx
├── Beneficiarios/
│   ├── BeneficiariosListScreen.jsx
│   ├── BeneficiarioDetailScreen.jsx
│   └── AddBeneficiarioScreen.jsx
├── Cupones/
│   ├── CuponesListScreen.jsx
│   ├── CrearCuponScreen.jsx
│   └── AprobarCuponScreen.jsx
├── Comercios/
│   └── ComerciosListScreen.jsx
└── Reportes/
    └── ReportesScreen.jsx
```

---

## 🗓️ ROADMAP DE DESARROLLO

### Fase 1: MVP - Core Features (3 meses)

**Mes 1: Setup & Autenticación**
- Semana 1-2: Setup proyecto React Native
  - Configurar estructura de carpetas
  - Setup Redux + RTK Query
  - Configurar navigation
  - Setup Firebase (iOS + Android)

- Semana 3-4: Autenticación
  - Login con código + DNI
  - Login con email + password
  - Registro de beneficiario
  - JWT token management
  - Biometric auth

**Mes 2: Features Beneficiario**
- Semana 5-6: Dashboard y Cupones
  - Dashboard con QR beneficiario
  - Lista de cupones
  - Detalle de cupón
  - Filtros y búsqueda

- Semana 7-8: Canje y Perfil
  - Canje online (WebView)
  - Historial de canjes
  - Perfil de usuario
  - Configuración

**Mes 3: Features Comercio & Testing**
- Semana 9-10: App Comercio
  - QR Scanner
  - Validación manual
  - Dashboard comercio
  - Historial de canjes

- Semana 11-12: Testing & Polish
  - Testing E2E con Detox
  - Fixing bugs
  - Performance optimization
  - UI/UX polish

**Entregable Fase 1:** App funcional para beneficiarios y comercios con features core.

---

### Fase 2: Features Avanzadas (2 meses)

**Mes 4: Notificaciones y Offline**
- Semana 13-14: Push Notifications
  - Implementar FCM
  - Notificaciones in-app
  - WebSocket real-time

- Semana 15-16: Modo Offline
  - Redux Persist
  - Cola de sincronización
  - Conflict resolution

**Mes 5: App Institución**
- Semana 17-18: Dashboard Ejecutivo
  - Métricas y gráficos
  - Lista de beneficiarios
  - Gestión de cupones

- Semana 19-20: Features Admin
  - Aprobar cupones
  - Reportes
  - Notificaciones masivas

**Entregable Fase 2:** App completa para los 3 roles con features avanzadas.

---

### Fase 3: Gamificación & Lanzamiento (1 mes)

**Mes 6: Polish & Deploy**
- Semana 21-22: Gamificación
  - Sistema de puntos
  - Niveles y badges
  - Leaderboard

- Semana 23-24: Deployment
  - App Store submission
  - Google Play submission
  - Marketing materials
  - Documentation

**Entregable Fase 3:** App publicada en App Store y Google Play.

---

## 💰 ESTIMACIÓN DE COSTOS

### Desarrollo (6 meses)

| Rol | Horas/Semana | Semanas | Rate | Total |
|-----|--------------|---------|------|-------|
| **React Native Developer (Senior)** | 40h | 24 | $80/h | $76,800 |
| **Backend Developer (PHP/WordPress)** | 20h | 16 | $70/h | $22,400 |
| **UI/UX Designer** | 20h | 8 | $60/h | $9,600 |
| **QA Engineer** | 20h | 12 | $50/h | $12,000 |
| **DevOps Engineer** | 10h | 8 | $75/h | $6,000 |
| **Project Manager** | 10h | 24 | $90/h | $21,600 |

**Total Desarrollo:** $148,400

### Infraestructura (Anual)

| Servicio | Costo/Mes | Costo/Año |
|----------|-----------|-----------|
| **Firebase** (Push + Analytics) | $100 | $1,200 |
| **Apple Developer Account** | - | $99 |
| **Google Play Developer** | - | $25 (one-time) |
| **CDN (CloudFlare Pro)** | $20 | $240 |
| **Hosting WebSocket Server** (DigitalOcean) | $40 | $480 |
| **SSL Certificates** | $0 | $0 (Let's Encrypt) |

**Total Infraestructura Año 1:** $2,044

### Mantenimiento (Mensual Post-Launch)

| Concepto | Horas/Mes | Rate | Total/Mes |
|----------|-----------|------|-----------|
| **Bug fixes & Updates** | 40h | $70/h | $2,800 |
| **New features** | 20h | $80/h | $1,600 |
| **DevOps & Monitoring** | 10h | $75/h | $750 |

**Total Mantenimiento:** $5,150/mes = $61,800/año

### TOTAL INVERSIÓN INICIAL (Año 1)

```
Desarrollo (6 meses):        $148,400
Infraestructura:              $2,044
Mantenimiento (6 meses):     $30,900
--------------------------------
TOTAL AÑO 1:                $181,344
```

---

## 📊 MODELO DE MONETIZACIÓN

### Opciones de Revenue Stream

#### Opción 1: Freemium

**Free Tier:**
- App básica gratis
- Notificaciones limitadas (10/día)
- Sin modo offline
- Ads ligeros

**Premium ($4.99/mes):**
- Sin límite de notificaciones
- Modo offline completo
- Sin ads
- Soporte prioritario
- Wallet de cupones favoritos

**Proyección:** 10% conversión free → premium
- 1,000 usuarios activos = 100 premium = $499/mes

#### Opción 2: Licencia por Institución

**Modelo B2B:**
- Institución paga por cantidad de beneficiarios

| Beneficiarios | Precio/Mes |
|---------------|------------|
| 0 - 500 | $49 |
| 501 - 2,000 | $149 |
| 2,001 - 10,000 | $399 |
| 10,001+ | $999 |

**Proyección:** 10 instituciones promedio
- 5 instituciones (500 benef) = $245/mes
- 3 instituciones (2K benef) = $447/mes
- 2 instituciones (10K benef) = $798/mes
- **Total:** $1,490/mes = $17,880/año

#### Opción 3: Revenue Share con Comercios

**Comisión por Canje:**
- App cobra 2% del descuento aplicado
- Ejemplo: Canje de $1,000 con 20% OFF = $200 descuento
- App cobra: $200 × 2% = $4

**Proyección:** 500 canjes/mes promedio
- 500 canjes × $4 promedio = $2,000/mes = $24,000/año

---

## 🎯 KPIs Y MÉTRICAS DE ÉXITO

### Métricas de Adopción

| Métrica | Objetivo Mes 3 | Objetivo Mes 6 | Objetivo Año 1 |
|---------|----------------|----------------|----------------|
| **Downloads** | 1,000 | 5,000 | 20,000 |
| **MAU (Monthly Active Users)** | 500 | 2,500 | 10,000 |
| **DAU (Daily Active Users)** | 100 | 500 | 2,000 |
| **Retention (30 días)** | 30% | 40% | 50% |
| **Session duration** | 3 min | 5 min | 7 min |

### Métricas de Engagement

| Métrica | Objetivo |
|---------|----------|
| **Canjes por usuario/mes** | 3 |
| **Cupones visualizados/sesión** | 5 |
| **Push notifications CTR** | 15% |
| **QR scans por comercio/día** | 10 |

### Métricas Técnicas

| Métrica | Objetivo |
|---------|----------|
| **App size** | < 50 MB |
| **Crash rate** | < 1% |
| **App load time** | < 2s |
| **API response time** | < 300ms |
| **Offline sync success** | > 95% |

---

## 🔒 SEGURIDAD DE LA APP

### Medidas de Seguridad

#### 1. Autenticación Segura
```javascript
// JWT con refresh token
- Access token: 15 min expiry
- Refresh token: 30 días expiry
- Storage: Encrypted AsyncStorage (react-native-encrypted-storage)
- Biometric: Face ID / Touch ID / Fingerprint
```

#### 2. Comunicación Segura
```javascript
// SSL/TLS
- Todas las requests con HTTPS
- Certificate pinning
- Verificación de certificado

// Request encryption
- Sensitive data encrypted en transit
- AES-256 encryption
```

#### 3. Datos Sensibles
```javascript
// Storage seguro
- No guardar passwords en plain text
- Tokens en encrypted storage
- QR codes temporales (5 min expiry)
- Biometric para operaciones críticas
```

#### 4. Code Obfuscation
```javascript
// React Native
- ProGuard (Android)
- Code obfuscation (iOS)
- Hermes engine (mejor performance + seguridad)
```

#### 5. App Security Checks
```javascript
// Runtime checks
- Jailbreak detection (iOS)
- Root detection (Android)
- Debugger detection
- SSL pinning verification
```

---

## 🧪 TESTING STRATEGY

### Tipos de Testing

#### 1. Unit Tests (Jest)
```javascript
// Coverage objetivo: 80%
Tests:
- Redux reducers
- Utility functions
- Business logic
- API calls (mocked)
```

#### 2. Integration Tests (Jest + RTK Query)
```javascript
Tests:
- API integration
- Redux store
- Navigation flow
- State management
```

#### 3. E2E Tests (Detox)
```javascript
// Critical flows
Tests:
- Login flow
- Cupón redemption flow
- QR scanning flow
- Profile update flow
```

#### 4. Manual QA
```javascript
Checklist:
- [ ] UI/UX en diferentes devices
- [ ] iOS: iPhone 12, 13, 14, 15
- [ ] Android: Samsung, Xiaomi, Motorola
- [ ] Orientación portrait/landscape
- [ ] Dark mode / Light mode
- [ ] Accessibility (VoiceOver, TalkBack)
```

---

## 📱 DEPLOYMENT & CI/CD

### Pipeline Automatizado (GitHub Actions)

```yaml
name: Mobile App CI/CD

on:
  push:
    branches: [main, develop]
  pull_request:
    branches: [main]

jobs:
  test:
    runs-on: ubuntu-latest
    steps:
      - Checkout code
      - Setup Node.js 20
      - Install dependencies
      - Run linter (ESLint)
      - Run unit tests (Jest)
      - Run E2E tests (Detox)
      - Generate coverage report

  build-ios:
    runs-on: macos-latest
    steps:
      - Checkout code
      - Setup Xcode 15
      - Install dependencies
      - Build iOS app
      - Run iOS tests
      - Archive IPA
      - Upload to TestFlight (if main branch)

  build-android:
    runs-on: ubuntu-latest
    steps:
      - Checkout code
      - Setup Java 17
      - Install dependencies
      - Build Android app
      - Run Android tests
      - Build APK/AAB
      - Upload to Google Play (if main branch)
```

### Distribución

**TestFlight (iOS):**
- Beta testing con usuarios seleccionados
- 90 días de testing
- Max 10,000 beta testers

**Google Play Internal Testing:**
- Alpha track para testing interno
- Beta track para usuarios seleccionados
- Production track para release público

**OTA Updates (CodePush):**
- Updates de JavaScript sin pasar por stores
- Fixes de bugs menores
- Actualizaciones UI

---

## 📚 DOCUMENTACIÓN TÉCNICA NECESARIA

### Documentos a Crear (Próxima Sesión)

#### 1. API Specification (OpenAPI/Swagger)
```
Archivo: docs/api/mobile-api-spec.yaml
Contenido:
- Todos los endpoints de la API
- Request/Response schemas
- Authentication flow
- Error codes
- Rate limiting
```

#### 2. Database Schema Extensions
```
Archivo: database/mobile-tables.sql
Nuevas tablas:
- wp_wpcw_device_tokens (push notifications)
- wp_wpcw_app_sessions (tracking de sesiones)
- wp_wpcw_offline_queue (cola de sincronización)
- wp_wpcw_notifications (notificaciones in-app)
```

#### 3. Mobile App Architecture Document
```
Archivo: docs/mobile/ARCHITECTURE.md
Contenido:
- Estructura de carpetas detallada
- State management flow
- Navigation structure
- Component hierarchy
- Data flow diagrams
```

#### 4. Developer Setup Guide
```
Archivo: docs/mobile/SETUP.md
Contenido:
- Environment setup (Node, Xcode, Android Studio)
- Dependencies installation
- iOS setup (CocoaPods, signing)
- Android setup (Gradle, keystore)
- Firebase configuration
- Running on simulators/devices
```

#### 5. Component Library Storybook
```
Archivo: docs/mobile/COMPONENTS.md
Contenido:
- Lista completa de componentes
- Props documentation
- Usage examples
- Screenshots
```

#### 6. Testing Guide
```
Archivo: docs/mobile/TESTING.md
Contenido:
- Unit testing examples
- Integration testing guide
- E2E testing with Detox
- Manual QA checklist
```

#### 7. Deployment Guide
```
Archivo: docs/mobile/DEPLOYMENT.md
Contenido:
- Build process
- App Store submission
- Google Play submission
- OTA updates con CodePush
```

#### 8. User Stories & Acceptance Criteria
```
Archivo: docs/mobile/USER_STORIES.md
Contenido:
- User stories por rol (Beneficiario, Comercio, Institución)
- Acceptance criteria en formato Gherkin
- Mockups/Wireframes
```

---

## 🎨 DISEÑO UI/UX

### Wireframes Necesarios (A crear en próxima sesión)

#### Beneficiario App (15 screens)
1. Login / Register
2. Dashboard (Home)
3. Cupones List
4. Cupón Detail
5. QR Cupón (para mostrar al comercio)
6. Canje Online (WebView)
7. Historial List
8. Canje Detail
9. Perfil
10. Edit Perfil
11. Configuración
12. Notifications
13. Wallet de Cupones Favoritos
14. Gamificación (Puntos y Badges)
15. Invitar Amigos

#### Comercio App (10 screens)
1. Login
2. Dashboard
3. QR Scanner (⭐ principal)
4. Validación Manual
5. Confirmación Canje
6. Mis Cupones
7. Crear Cupón
8. Historial
9. Perfil
10. Reportes

#### Institución App (12 screens)
1. Login
2. Dashboard Ejecutivo
3. Beneficiarios List
4. Beneficiario Detail
5. Add Beneficiario
6. Cupones List
7. Crear Cupón
8. Aprobar Cupón
9. Comercios List
10. Reportes
11. Notificaciones Masivas
12. Configuración

**Tool Recomendado:** Figma (diseño colaborativo)

---

## 🚀 PRÓXIMOS PASOS (Para Siguiente Sesión)

### Checklist de Preparación

#### Fase de Diseño
- [ ] Crear wireframes en Figma (45 screens totales)
- [ ] Definir design system completo
- [ ] Crear componentes en Figma
- [ ] Hacer prototipos interactivos

#### Fase de Especificación
- [ ] Escribir user stories detalladas (50+)
- [ ] Crear acceptance criteria en Gherkin
- [ ] Documentar todos los flujos de usuario
- [ ] Crear diagramas de secuencia

#### Fase de Arquitectura
- [ ] Diseñar estructura de carpetas React Native
- [ ] Definir Redux store structure
- [ ] Crear API specification (OpenAPI)
- [ ] Documentar database schema extensions

#### Fase de Setup
- [ ] Crear repositorio GitHub
- [ ] Setup React Native project
- [ ] Configurar CI/CD pipeline
- [ ] Setup Firebase projects (iOS + Android)

---

## 📞 EQUIPO ASIGNADO

### Team para Desarrollo de App Móvil

**Leadership:**
- **@CTO (Dr. Viktor Petrov)** - Arquitectura técnica y decisiones críticas
- **@FRONTEND (Sophie Laurent)** - Lead React Native Developer
- **@API (Carlos Mendoza)** - Backend integration y WebSockets

**Core Team:**
- **React Native Developer** (2) - @FRONTEND + New hire
- **UI/UX Designer** (1) - New hire (bajo supervisión @FRONTEND)
- **Backend Developer** (1) - @WORDPRESS (Thomas Anderson)
- **QA Engineer** (1) - @QA (Rachel Kim)
- **DevOps** (0.5) - @DEVOPS (Alex Kumar) - part-time

**Project Management:**
- **@PM (Marcus Chen)** - Gestión de sprints, timeline, coordinación

**Total Team:** 7 personas (6.5 FTE)

---

## 📊 ANÁLISIS DE RIESGO

### Riesgos Técnicos

| Riesgo | Probabilidad | Impacto | Mitigación |
|--------|--------------|---------|------------|
| **React Native version conflicts** | Media | Alto | Usar versión LTS, lock dependencies |
| **iOS/Android API differences** | Alta | Medio | Abstraer platform-specific code |
| **Push notifications fail** | Media | Alto | Fallback a in-app notifications |
| **Offline sync conflicts** | Media | Alto | Conflict resolution strategy clara |
| **App Store rejection** | Baja | Alto | Seguir guidelines estrictas desde inicio |
| **Performance issues** | Media | Medio | Profiling desde early stages |

### Riesgos de Negocio

| Riesgo | Probabilidad | Impacto | Mitigación |
|--------|--------------|---------|------------|
| **Baja adopción de usuarios** | Media | Alto | Marketing pre-launch, beta testing extensivo |
| **Competencia lanza app similar** | Baja | Alto | Acelerar desarrollo, unique features |
| **Costos exceden presupuesto** | Media | Medio | Fases incrementales, MVP primero |
| **Instituciones no adoptan app** | Baja | Alto | Involucrar instituciones en beta testing |

---

## 📄 CONCLUSIÓN Y RECOMENDACIONES

### Recomendaciones Clave

1. **✅ Empezar con MVP (Fase 1)** - 3 meses de desarrollo
   - Focus en beneficiarios y comercios primero
   - Features core: autenticación, cupones, scanner QR
   - No incluir gamificación ni features avanzadas

2. **✅ React Native como framework** - Mejor opción
   - Un solo codebase para iOS + Android
   - Comunidad grande, muchas librerías
   - Performance adecuada para este tipo de app

3. **✅ Arquitectura modular** - Escalable
   - Separar lógica de negocio de UI
   - Componentes reutilizables
   - Easy testing

4. **✅ API-first approach** - Flexibilidad
   - Plugin expone APIs bien documentadas
   - App consume APIs sin tocar plugin
   - Permite múltiples clientes (app, web, terceros)

5. **✅ Offline-first desde inicio** - UX crucial
   - QR del beneficiario funciona sin internet
   - Comercio puede validar offline
   - Sincroniza cuando vuelve conexión

6. **✅ Security by design** - No negociable
   - JWT + refresh tokens
   - Encrypted storage
   - Biometric authentication
   - Certificate pinning

### Próxima Sesión: Punto de Partida

**Comenzar con:**
1. Crear wireframes en Figma (3-5 días)
2. Setup proyecto React Native (1 día)
3. Configurar estructura de carpetas (1 día)
4. Implementar autenticación (1 semana)

**Archivos a crear:**
- `docs/mobile/WIREFRAMES.md` (link a Figma)
- `docs/mobile/SETUP.md` (setup guide)
- `docs/mobile/ARCHITECTURE.md` (arquitectura detallada)
- `docs/api/mobile-api-spec.yaml` (OpenAPI spec)

---

**📱 Mobile App Planning Document - WP Cupón WhatsApp v1.0.0**
**© 2025 Pragmatic Solutions - Innovación Aplicada**
**Documento creado: 11 de Octubre, 2025**
**Próxima revisión: Inicio de Fase 1 (Q1 2026)**

---

## 📎 ANEXOS

### A. Stack Tecnológico - Comparativa de Opciones

| Framework | Pros | Contras | Decisión |
|-----------|------|---------|----------|
| **React Native** | ✅ Un codebase, ✅ Comunidad grande, ✅ Hot reload | ⚠️ Puentes nativos pueden ser lentos | ✅ **ELEGIDO** |
| Flutter | ✅ Performance nativa, ✅ UI hermosa | ❌ Dart (nuevo lenguaje), ❌ Comunidad menor | ❌ |
| Native (Swift + Kotlin) | ✅ Performance óptima | ❌ Doble desarrollo, ❌ Costo 2x | ❌ |
| Ionic | ✅ Web technologies | ❌ Performance mala, ❌ UX no nativa | ❌ |

### B. Librerías React Native Recomendadas

```json
{
  "dependencies": {
    "react": "18.2.0",
    "react-native": "0.74.1",
    "@react-navigation/native": "^6.1.0",
    "@react-navigation/stack": "^6.3.0",
    "@react-navigation/bottom-tabs": "^6.5.0",
    "@reduxjs/toolkit": "^2.0.0",
    "react-redux": "^9.0.0",
    "redux-persist": "^6.0.0",
    "@react-native-async-storage/async-storage": "^1.21.0",
    "react-native-paper": "^5.11.0",
    "react-native-vector-icons": "^10.0.0",
    "react-native-camera": "^4.2.1",
    "react-native-qrcode-scanner": "^1.5.5",
    "react-native-qrcode-svg": "^6.2.0",
    "@react-native-firebase/app": "^19.0.0",
    "@react-native-firebase/messaging": "^19.0.0",
    "@react-native-firebase/analytics": "^19.0.0",
    "react-native-biometrics": "^3.0.1",
    "react-native-encrypted-storage": "^4.0.3",
    "axios": "^1.6.0",
    "socket.io-client": "^4.6.0",
    "date-fns": "^3.0.0",
    "formik": "^2.4.0",
    "yup": "^1.3.0"
  },
  "devDependencies": {
    "@testing-library/react-native": "^12.4.0",
    "jest": "^29.7.0",
    "detox": "^20.14.0",
    "eslint": "^8.56.0",
    "prettier": "^3.1.0",
    "@typescript-eslint/eslint-plugin": "^6.19.0",
    "typescript": "^5.3.0"
  }
}
```

### C. Firebase Configuration

```javascript
// firebase.config.js
import { initializeApp } from '@react-native-firebase/app';
import messaging from '@react-native-firebase/messaging';
import analytics from '@react-native-firebase/analytics';

const firebaseConfig = {
  apiKey: "YOUR_API_KEY",
  authDomain: "wpcw-app.firebaseapp.com",
  projectId: "wpcw-app",
  storageBucket: "wpcw-app.appspot.com",
  messagingSenderId: "123456789",
  appId: "1:123456789:ios:abc123",
  measurementId: "G-ABC123"
};

const app = initializeApp(firebaseConfig);

export { messaging, analytics };
```

---

**Fin del Documento de Planificación**

Este documento será actualizado conforme avance el desarrollo en próximas sesiones.
