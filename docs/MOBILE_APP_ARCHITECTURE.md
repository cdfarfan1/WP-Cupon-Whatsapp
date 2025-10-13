# 🏗️ ARQUITECTURA TÉCNICA: APP MÓVIL
## WP CUPÓN WHATSAPP - MOBILE APP

**Versión:** 1.0.0
**Fecha:** 11 de Octubre, 2025
**Arquitectos:** @CTO (Dr. Viktor Petrov) + @FRONTEND (Sophie Laurent)
**Estado:** 📋 Diseño - No implementado

---

## 📊 DIAGRAMA DE ARQUITECTURA GENERAL

```
┌─────────────────────────────────────────────────────────────────┐
│                        MOBILE APPS LAYER                         │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐          │
│  │ Beneficiario │  │   Comercio   │  │  Institución │          │
│  │     App      │  │     App      │  │     App      │          │
│  │  (iOS/Android)  │  (iOS/Android)  │  (iOS/Android)  │       │
│  └──────┬───────┘  └──────┬───────┘  └──────┬───────┘          │
└─────────┼──────────────────┼──────────────────┼─────────────────┘
          │                  │                  │
          │    REST API      │   WebSockets     │   Push (FCM)
          │                  │                  │
┌─────────▼──────────────────▼──────────────────▼─────────────────┐
│                      API GATEWAY LAYER                           │
│  ┌───────────────────────────────────────────────────────────┐  │
│  │  WordPress REST API                                        │  │
│  │  - /wp-json/wpcw/v1/*         (12 endpoints existentes)   │  │
│  │  - /wp-json/wpcw-mobile/v1/*  (8 endpoints nuevos)        │  │
│  └───────────────────────────────────────────────────────────┘  │
│  ┌───────────────────────────────────────────────────────────┐  │
│  │  WebSocket Server (Ratchet PHP)                           │  │
│  │  - ws://domain:8080                                        │  │
│  │  - Real-time notifications                                 │  │
│  └───────────────────────────────────────────────────────────┘  │
│  ┌───────────────────────────────────────────────────────────┐  │
│  │  Firebase Cloud Functions                                  │  │
│  │  - Push notification sender                                │  │
│  │  - Analytics processor                                     │  │
│  └───────────────────────────────────────────────────────────┘  │
└─────────┬────────────────────────────────────────────────────────┘
          │
          │    MySQL Queries
          │
┌─────────▼──────────────────────────────────────────────────────┐
│                      DATABASE LAYER                             │
│  ┌──────────────────────────────────────────────────────────┐  │
│  │  MySQL 5.7+ / MariaDB 10.3+                              │  │
│  │                                                           │  │
│  │  Tablas Existentes:                                      │  │
│  │  - wp_wpcw_instituciones                                 │  │
│  │  - wp_wpcw_adhesiones                                    │  │
│  │  - wp_wpcw_canjes                                        │  │
│  │  - wp_posts (shop_coupon con metadatos WPCW)            │  │
│  │                                                           │  │
│  │  Tablas Nuevas (Mobile):                                 │  │
│  │  - wp_wpcw_device_tokens                                 │  │
│  │  - wp_wpcw_app_sessions                                  │  │
│  │  - wp_wpcw_offline_queue                                 │  │
│  │  - wp_wpcw_notifications                                 │  │
│  └──────────────────────────────────────────────────────────┘  │
└───────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────┐
│                   EXTERNAL SERVICES LAYER                        │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐          │
│  │   Firebase   │  │   WhatsApp   │  │  CloudFlare  │          │
│  │     FCM      │  │ Business API │  │     CDN      │          │
│  └──────────────┘  └──────────────┘  └──────────────┘          │
└─────────────────────────────────────────────────────────────────┘
```

---

## 📱 ARQUITECTURA REACT NATIVE APP

### Estructura de Carpetas

```
mobile-app/
│
├── android/                    # Android native code
├── ios/                        # iOS native code
│
├── src/
│   │
│   ├── api/                    # API layer
│   │   ├── client.js           # Axios instance configurado
│   │   ├── auth.js             # Auth endpoints
│   │   ├── cupones.js          # Cupones endpoints
│   │   ├── canjes.js           # Canjes endpoints
│   │   ├── beneficiarios.js    # Beneficiarios endpoints
│   │   ├── websocket.js        # WebSocket client
│   │   └── interceptors.js     # Request/response interceptors
│   │
│   ├── assets/                 # Static assets
│   │   ├── images/
│   │   ├── fonts/
│   │   └── icons/
│   │
│   ├── components/             # Reusable components
│   │   ├── Buttons/
│   │   │   ├── PrimaryButton.jsx
│   │   │   ├── SecondaryButton.jsx
│   │   │   └── IconButton.jsx
│   │   ├── Cards/
│   │   │   ├── CouponCard.jsx
│   │   │   ├── CanjeCard.jsx
│   │   │   └── StatCard.jsx
│   │   ├── Forms/
│   │   │   ├── Input.jsx
│   │   │   ├── Select.jsx
│   │   │   └── DatePicker.jsx
│   │   ├── QR/
│   │   │   ├── QRDisplay.jsx
│   │   │   └── QRScanner.jsx
│   │   └── Shared/
│   │       ├── Loading.jsx
│   │       ├── EmptyState.jsx
│   │       └── ErrorBoundary.jsx
│   │
│   ├── config/                 # Configuration
│   │   ├── constants.js        # App constants
│   │   ├── theme.js            # Theme configuration
│   │   └── firebase.js         # Firebase config
│   │
│   ├── hooks/                  # Custom React hooks
│   │   ├── useAuth.js
│   │   ├── useCupones.js
│   │   ├── useCanjes.js
│   │   ├── useQRScanner.js
│   │   ├── usePushNotifications.js
│   │   └── useOfflineSync.js
│   │
│   ├── navigation/             # Navigation structure
│   │   ├── AppNavigator.jsx    # Root navigator
│   │   ├── AuthNavigator.jsx   # Auth screens navigator
│   │   ├── MainNavigator.jsx   # Main app navigator
│   │   └── RoleNavigator.jsx   # Role-based navigator
│   │
│   ├── redux/                  # State management
│   │   ├── store.js            # Redux store configuration
│   │   ├── slices/
│   │   │   ├── authSlice.js
│   │   │   ├── cuponesSlice.js
│   │   │   ├── canjesSlice.js
│   │   │   ├── notificationsSlice.js
│   │   │   └── offlineSlice.js
│   │   └── api/
│   │       ├── authApi.js      # RTK Query API
│   │       ├── cuponesApi.js
│   │       └── canjesApi.js
│   │
│   ├── screens/                # App screens (by role)
│   │   ├── Auth/
│   │   │   ├── LoginScreen.jsx
│   │   │   ├── RegisterScreen.jsx
│   │   │   └── ForgotPasswordScreen.jsx
│   │   ├── Beneficiario/
│   │   │   ├── DashboardScreen.jsx
│   │   │   ├── CuponesListScreen.jsx
│   │   │   ├── CuponDetailScreen.jsx
│   │   │   ├── HistorialScreen.jsx
│   │   │   └── PerfilScreen.jsx
│   │   ├── Comercio/
│   │   │   ├── DashboardScreen.jsx
│   │   │   ├── QRScannerScreen.jsx
│   │   │   ├── ValidacionScreen.jsx
│   │   │   └── HistorialScreen.jsx
│   │   └── Institucion/
│   │       ├── DashboardScreen.jsx
│   │       ├── BeneficiariosScreen.jsx
│   │       ├── CuponesScreen.jsx
│   │       └── ReportesScreen.jsx
│   │
│   ├── services/               # Business logic services
│   │   ├── AuthService.js
│   │   ├── CuponService.js
│   │   ├── CanjeService.js
│   │   ├── QRService.js
│   │   ├── NotificationService.js
│   │   └── OfflineSyncService.js
│   │
│   ├── utils/                  # Utility functions
│   │   ├── validators.js
│   │   ├── formatters.js
│   │   ├── storage.js
│   │   ├── biometrics.js
│   │   └── permissions.js
│   │
│   └── App.jsx                 # Root component
│
├── __tests__/                  # Tests
│   ├── unit/
│   ├── integration/
│   └── e2e/
│
├── .env                        # Environment variables
├── .env.production
├── app.json
├── babel.config.js
├── metro.config.js
├── package.json
└── README.md
```

---

## 🔄 FLUJO DE DATOS (REDUX + RTK QUERY)

### Redux Store Structure

```javascript
// Redux State Tree
{
  // Auth slice
  auth: {
    user: {
      id: 123,
      nombre: "Juan Pérez",
      role: "beneficiario", // beneficiario | comercio | institucion
      codigo_beneficiario: "BEN-001-ABC123",
      telefono: "+54 9 11 1234-5678",
      email: "juan@email.com"
    },
    tokens: {
      access_token: "eyJhbGc...",
      refresh_token: "eyJhbGc...",
      expires_at: 1234567890
    },
    isAuthenticated: true,
    biometricEnabled: true
  },

  // Cupones slice (RTK Query cache)
  cuponesApi: {
    queries: {
      "getCupones(undefined)": {
        status: "fulfilled",
        data: [
          {
            id: 1,
            codigo: "BIENVENIDA20",
            tipo: "descuento_porcentaje",
            valor: 20,
            descripcion: "20% OFF de bienvenida",
            comercio: "RestaurantX",
            fecha_vencimiento: "2025-12-31",
            ya_usado: false
          }
        ]
      }
    }
  },

  // Canjes slice
  canjes: {
    historial: [],
    currentCanje: null,
    totalAhorrado: 0
  },

  // Notifications slice
  notifications: {
    unreadCount: 3,
    list: [
      {
        id: 1,
        tipo: "cupon.nuevo",
        titulo: "Nuevo cupón disponible",
        mensaje: "20% OFF en RestaurantX",
        leida: false,
        fecha: "2025-10-11T10:30:00Z"
      }
    ]
  },

  // Offline slice
  offline: {
    isOnline: true,
    syncQueue: [],
    lastSyncAt: "2025-10-11T10:00:00Z"
  },

  // UI slice
  ui: {
    loading: false,
    error: null,
    theme: "light" // light | dark
  }
}
```

### Data Flow Diagram

```
┌──────────────┐
│  Component   │
│  (Screen)    │
└──────┬───────┘
       │
       │ dispatch(action)
       │
┌──────▼───────┐
│  Redux       │
│  Middleware  │  ← RTK Query, Redux Persist, Logger
└──────┬───────┘
       │
       │ reducer
       │
┌──────▼───────┐
│  Redux Store │
└──────┬───────┘
       │
       │ selector
       │
┌──────▼───────┐
│  Component   │
│  (re-render) │
└──────────────┘
```

---

## 🔐 SISTEMA DE AUTENTICACIÓN

### Authentication Flow

```
┌────────────────────────────────────────────────────────────────┐
│                      LOGIN FLOW                                 │
└────────────────────────────────────────────────────────────────┘

1. User Input
   ↓
┌──────────────────┐
│ LoginScreen      │ → Formulario: código/email + password
│ - Input código   │ → Validación local (Formik + Yup)
│ - Input password │
│ - Submit         │
└────────┬─────────┘
         │
         │ dispatch(login({ codigo, password }))
         │
┌────────▼─────────┐
│ authSlice        │ → Estado: loading
│ - loginThunk     │
└────────┬─────────┘
         │
         │ POST /wp-json/wpcw-mobile/v1/auth/login
         │
┌────────▼─────────┐
│ WordPress API    │ → Validar credenciales
│ - Verifica user  │ → Genera JWT tokens
│ - Genera tokens  │ → Devuelve user data
└────────┬─────────┘
         │
         │ Response: { user, access_token, refresh_token }
         │
┌────────▼─────────┐
│ authSlice        │ → Guarda user + tokens en state
│ - fulfilled      │ → Redux Persist guarda en storage
└────────┬─────────┘
         │
         │ Navigation.navigate('Dashboard')
         │
┌────────▼─────────┐
│ DashboardScreen  │ → Usuario autenticado
└──────────────────┘
```

### JWT Token Management

```javascript
// Token refresh strategy
class TokenManager {
  constructor() {
    this.accessToken = null;
    this.refreshToken = null;
    this.expiresAt = null;
  }

  isTokenExpired() {
    if (!this.expiresAt) return true;
    return Date.now() >= this.expiresAt - 60000; // Refresh 1 min before expiry
  }

  async refreshAccessToken() {
    try {
      const response = await api.post('/auth/refresh', {
        refresh_token: this.refreshToken
      });

      this.accessToken = response.data.access_token;
      this.expiresAt = response.data.expires_at;

      return this.accessToken;
    } catch (error) {
      // Refresh token inválido → logout
      this.logout();
      throw error;
    }
  }

  logout() {
    this.accessToken = null;
    this.refreshToken = null;
    this.expiresAt = null;
    // Clear Redux state
    // Navigate to Login
  }
}
```

### Axios Interceptor para Auto-Refresh

```javascript
// api/interceptors.js
import { store } from '../redux/store';
import { refreshToken, logout } from '../redux/slices/authSlice';

// Request interceptor: Añadir token a headers
api.interceptors.request.use(
  async (config) => {
    const state = store.getState();
    const { access_token, expires_at } = state.auth.tokens;

    // Check if token expired
    if (Date.now() >= expires_at - 60000) {
      // Token expiring soon, refresh it
      await store.dispatch(refreshToken());
      const newToken = store.getState().auth.tokens.access_token;
      config.headers.Authorization = `Bearer ${newToken}`;
    } else {
      config.headers.Authorization = `Bearer ${access_token}`;
    }

    return config;
  },
  (error) => Promise.reject(error)
);

// Response interceptor: Handle 401
api.interceptors.response.use(
  (response) => response,
  async (error) => {
    const originalRequest = error.config;

    if (error.response?.status === 401 && !originalRequest._retry) {
      originalRequest._retry = true;

      try {
        // Try to refresh token
        await store.dispatch(refreshToken());
        const newToken = store.getState().auth.tokens.access_token;
        originalRequest.headers.Authorization = `Bearer ${newToken}`;
        return api(originalRequest);
      } catch (refreshError) {
        // Refresh failed → logout
        store.dispatch(logout());
        return Promise.reject(refreshError);
      }
    }

    return Promise.reject(error);
  }
);
```

---

## 📡 SISTEMA DE SINCRONIZACIÓN OFFLINE

### Offline-First Architecture

```
┌────────────────────────────────────────────────────────────────┐
│                    OFFLINE SYNC FLOW                            │
└────────────────────────────────────────────────────────────────┘

ESCENARIO: Comercio valida canje SIN internet

1. User Action (Offline)
   ↓
┌──────────────────┐
│ QRScannerScreen  │ → Scanner detecta QR
│ - Scan QR        │ → Valida beneficiario localmente
│ - Select cupón   │ → Confirma canje
└────────┬─────────┘
         │
         │ dispatch(createCanjeOffline({ data }))
         │
┌────────▼─────────┐
│ offlineSlice     │ → Estado: isOnline = false
│ - Añadir a queue │ → Guarda canje en syncQueue
└────────┬─────────┘
         │
         │ Redux Persist → AsyncStorage
         │
┌────────▼─────────┐
│ AsyncStorage     │ → Canje guardado localmente
│ - syncQueue: [   │
│     {             │
│       id: temp-1, │
│       type: canje,│
│       data: {...},│
│       timestamp   │
│     }             │
│   ]               │
└────────┬─────────┘
         │
         │ Confirmation UI → Usuario ve "Canje registrado (pendiente sync)"
         │
┌────────▼─────────┐
│ CanjeConfirm     │ → Badge: "⏳ Pendiente sincronización"
│ Screen           │
└──────────────────┘

───────────────────────────────────────────────────────────────────

ESCENARIO: Internet vuelve

1. Network Change
   ↓
┌──────────────────┐
│ NetInfo Listener │ → Detecta conexión
└────────┬─────────┘
         │
         │ dispatch(setOnline(true))
         │
┌────────▼─────────┐
│ offlineSlice     │ → Estado: isOnline = true
│ - Trigger sync   │
└────────┬─────────┘
         │
         │ dispatch(syncOfflineQueue())
         │
┌────────▼─────────┐
│ OfflineSyncService│→ Itera syncQueue
│ - Process queue  │ → POST cada item a API
└────────┬─────────┘
         │
         │ Parallel requests
         │
┌────────▼─────────┐
│ WordPress API    │ → Procesa cada canje
│ - Save to DB     │ → Devuelve confirmación
└────────┬─────────┘
         │
         │ Response: { success: true, canje_id: 456 }
         │
┌────────▼─────────┐
│ offlineSlice     │ → Elimina de syncQueue
│ - Remove item    │ → Actualiza lastSyncAt
└────────┬─────────┘
         │
         │ Show notification
         │
┌────────▼─────────┐
│ Notification     │ → "✅ Canjes sincronizados"
└──────────────────┘
```

### Conflict Resolution Strategy

```javascript
// services/OfflineSyncService.js
class OfflineSyncService {
  async syncQueue() {
    const queue = store.getState().offline.syncQueue;

    for (const item of queue) {
      try {
        // Intentar sincronizar
        const response = await this.syncItem(item);

        if (response.success) {
          // Éxito: eliminar de queue
          store.dispatch(removeFromQueue(item.id));
        } else if (response.error === 'CONFLICT') {
          // Conflicto: item fue modificado en servidor
          const resolution = await this.resolveConflict(item, response.serverData);

          if (resolution === 'SERVER_WINS') {
            // Descartar cambios locales
            store.dispatch(removeFromQueue(item.id));
          } else if (resolution === 'CLIENT_WINS') {
            // Re-intentar con forzar update
            await this.syncItem(item, { force: true });
            store.dispatch(removeFromQueue(item.id));
          } else {
            // MERGE: combinar cambios
            const merged = this.mergeChanges(item, response.serverData);
            await this.syncItem(merged, { force: true });
            store.dispatch(removeFromQueue(item.id));
          }
        }
      } catch (error) {
        // Error de red: mantener en queue
        console.error('Sync error:', error);

        // Si lleva más de 7 días en queue, eliminar
        if (Date.now() - item.timestamp > 7 * 24 * 60 * 60 * 1000) {
          store.dispatch(removeFromQueue(item.id));
        }
      }
    }
  }

  async resolveConflict(clientItem, serverItem) {
    // Para canjes: SERVER_WINS siempre (no puede haber conflictos reales)
    if (clientItem.type === 'canje') {
      return 'SERVER_WINS';
    }

    // Para perfil de usuario: mostrar modal de resolución
    if (clientItem.type === 'perfil') {
      return await this.showConflictModal(clientItem, serverItem);
    }

    // Default: SERVER_WINS
    return 'SERVER_WINS';
  }
}
```

---

## 🔔 SISTEMA DE NOTIFICACIONES

### Push Notifications Architecture

```
┌────────────────────────────────────────────────────────────────┐
│              PUSH NOTIFICATION FLOW                             │
└────────────────────────────────────────────────────────────────┘

1. App Install & First Launch
   ↓
┌──────────────────┐
│ App.jsx          │ → Request permission
│ - useEffect      │ → Get device token (FCM)
└────────┬─────────┘
         │
         │ FCM.getToken()
         │
┌────────▼─────────┐
│ Firebase SDK     │ → Genera device token
│ - iOS: APNs      │ → Token: "dA3...XyZ"
│ - Android: FCM   │
└────────┬─────────┘
         │
         │ dispatch(registerDevice({ token, platform, role }))
         │
┌────────▼─────────┐
│ Redux → API      │ → POST /push/register
│ - Save token     │ → { device_token, user_id, platform }
└────────┬─────────┘
         │
         │ Save to wp_wpcw_device_tokens
         │
┌────────▼─────────┐
│ WordPress DB     │ → Token guardado
│ - device_token   │
│ - user_id        │
│ - platform       │
│ - active: true   │
└──────────────────┘

───────────────────────────────────────────────────────────────────

2. Evento Trigger (Ejemplo: Nuevo cupón creado)
   ↓
┌──────────────────┐
│ WordPress Admin  │ → Admin crea cupón
│ - Save cupón     │ → Trigger action: wpcw_cupon_created
└────────┬─────────┘
         │
         │ do_action('wpcw_cupon_created', $cupon_id)
         │
┌────────▼─────────┐
│ WPCW Plugin      │ → Hook listener
│ - Push handler   │ → Obtiene beneficiarios de institución
└────────┬─────────┘
         │
         │ Query device_tokens de beneficiarios
         │
┌────────▼─────────┐
│ WordPress DB     │ → Devuelve tokens
│ - SELECT tokens  │
│   WHERE user_id  │
│   IN (benef_ids) │
└────────┬─────────┘
         │
         │ Prepare notification payload
         │
┌────────▼─────────┐
│ Firebase Cloud   │ → Recibe payload
│ Functions        │ → Envía a FCM
│ - sendPush()     │
└────────┬─────────┘
         │
         │ FCM.sendMulticast({ tokens, notification, data })
         │
┌────────▼─────────┐
│ Firebase Cloud   │ → Distribuye notificación
│ Messaging (FCM)  │ → iOS: APNs
│                  │ → Android: FCM native
└────────┬─────────┘
         │
         │ Notificación enviada a devices
         │
┌────────▼─────────┐
│ Mobile Apps      │ → Foreground: In-app notification
│ (iOS/Android)    │ → Background: System notification
└──────────────────┘
```

### Notification Handler (App)

```javascript
// App.jsx
import { useEffect } from 'react';
import messaging from '@react-native-firebase/messaging';
import { useDispatch } from 'react-redux';
import { addNotification } from './redux/slices/notificationsSlice';

function App() {
  const dispatch = useDispatch();

  useEffect(() => {
    // Request permission (iOS)
    const requestPermission = async () => {
      const authStatus = await messaging().requestPermission();
      const enabled =
        authStatus === messaging.AuthorizationStatus.AUTHORIZED ||
        authStatus === messaging.AuthorizationStatus.PROVISIONAL;

      if (enabled) {
        console.log('Push notification permission granted');

        // Get token
        const token = await messaging().getToken();
        console.log('FCM Token:', token);

        // Save token to backend
        dispatch(registerDevice({ token, platform: Platform.OS }));
      }
    };

    requestPermission();

    // Handle foreground notifications
    const unsubscribeForeground = messaging().onMessage(async (remoteMessage) => {
      console.log('Foreground notification:', remoteMessage);

      // Add to in-app notification center
      dispatch(addNotification({
        id: remoteMessage.messageId,
        tipo: remoteMessage.data.type,
        titulo: remoteMessage.notification.title,
        mensaje: remoteMessage.notification.body,
        data: remoteMessage.data,
        leida: false,
        fecha: new Date().toISOString()
      }));

      // Show in-app notification (toast)
      showInAppNotification(remoteMessage.notification);
    });

    // Handle background/quit state notifications
    messaging().setBackgroundMessageHandler(async (remoteMessage) => {
      console.log('Background notification:', remoteMessage);
      // Process notification in background
    });

    // Handle notification tap (when app was in background/quit)
    messaging().onNotificationOpenedApp((remoteMessage) => {
      console.log('Notification opened app:', remoteMessage);

      // Navigate to appropriate screen based on notification type
      navigateFromNotification(remoteMessage);
    });

    // Check if app was opened from notification (quit state)
    messaging()
      .getInitialNotification()
      .then((remoteMessage) => {
        if (remoteMessage) {
          console.log('App opened from notification:', remoteMessage);
          navigateFromNotification(remoteMessage);
        }
      });

    return () => {
      unsubscribeForeground();
    };
  }, []);

  function navigateFromNotification(remoteMessage) {
    const { type, cupon_id, canje_id } = remoteMessage.data;

    switch (type) {
      case 'cupon.nuevo':
        Navigation.navigate('CuponDetail', { id: cupon_id });
        break;
      case 'cupon.vencimiento':
        Navigation.navigate('CuponDetail', { id: cupon_id });
        break;
      case 'canje.confirmado':
        Navigation.navigate('CanjeDetail', { id: canje_id });
        break;
      default:
        Navigation.navigate('Dashboard');
    }
  }

  return <AppNavigator />;
}
```

---

## 🔒 SEGURIDAD: IMPLEMENTACIÓN DETALLADA

### 1. Encrypted Storage

```javascript
// utils/storage.js
import EncryptedStorage from 'react-native-encrypted-storage';

class SecureStorage {
  static async setItem(key, value) {
    try {
      await EncryptedStorage.setItem(
        key,
        JSON.stringify(value)
      );
    } catch (error) {
      console.error('SecureStorage setItem error:', error);
    }
  }

  static async getItem(key) {
    try {
      const value = await EncryptedStorage.getItem(key);
      return value ? JSON.parse(value) : null;
    } catch (error) {
      console.error('SecureStorage getItem error:', error);
      return null;
    }
  }

  static async removeItem(key) {
    try {
      await EncryptedStorage.removeItem(key);
    } catch (error) {
      console.error('SecureStorage removeItem error:', error);
    }
  }

  static async clear() {
    try {
      await EncryptedStorage.clear();
    } catch (error) {
      console.error('SecureStorage clear error:', error);
    }
  }
}

// Usage
await SecureStorage.setItem('auth_tokens', {
  access_token: 'eyJhbGc...',
  refresh_token: 'eyJhbGc...'
});

const tokens = await SecureStorage.getItem('auth_tokens');
```

### 2. Biometric Authentication

```javascript
// utils/biometrics.js
import ReactNativeBiometrics from 'react-native-biometrics';

class BiometricAuth {
  static async isAvailable() {
    const rnBiometrics = new ReactNativeBiometrics();
    const { available, biometryType } = await rnBiometrics.isSensorAvailable();

    return {
      available,
      type: biometryType // FaceID | TouchID | Fingerprint
    };
  }

  static async authenticate(reason = 'Autenticación requerida') {
    const rnBiometrics = new ReactNativeBiometrics();

    try {
      const { success } = await rnBiometrics.simplePrompt({
        promptMessage: reason,
        cancelButtonText: 'Cancelar'
      });

      return success;
    } catch (error) {
      console.error('Biometric auth error:', error);
      return false;
    }
  }

  static async createKeys() {
    const rnBiometrics = new ReactNativeBiometrics();
    const { publicKey } = await rnBiometrics.createKeys();
    return publicKey;
  }

  static async deleteKeys() {
    const rnBiometrics = new ReactNativeBiometrics();
    await rnBiometrics.deleteKeys();
  }
}

// Usage in LoginScreen
const handleBiometricLogin = async () => {
  const { available } = await BiometricAuth.isAvailable();

  if (!available) {
    Alert.alert('Error', 'Autenticación biométrica no disponible');
    return;
  }

  const success = await BiometricAuth.authenticate('Inicia sesión con biometría');

  if (success) {
    // Get saved credentials from secure storage
    const credentials = await SecureStorage.getItem('saved_credentials');

    if (credentials) {
      dispatch(login(credentials));
    }
  }
};
```

### 3. SSL Pinning (Certificate Pinning)

```javascript
// api/client.js (iOS)
// Configurar en Info.plist

// Android: network_security_config.xml
<?xml version="1.0" encoding="utf-8"?>
<network-security-config>
    <domain-config cleartextTrafficPermitted="false">
        <domain includeSubdomains="true">api.tusitio.com</domain>
        <pin-set expiration="2026-12-31">
            <pin digest="SHA-256">7HIpactkIAq2Y49orFOOQKurWxmmSFZhBCoQYcRhJ3Y=</pin>
            <!-- Backup pin -->
            <pin digest="SHA-256">YLh1dUR9y6Kja30RrAn7JKnbQG/uEtLMkBgFF2Fuihg=</pin>
        </pin-set>
    </domain-config>
</network-security-config>
```

### 4. Jailbreak/Root Detection

```javascript
// utils/security.js
import JailMonkey from 'jail-monkey';

class SecurityChecks {
  static isDeviceCompromised() {
    return {
      isJailBroken: JailMonkey.isJailBroken(),
      canMockLocation: JailMonkey.canMockLocation(), // Android
      isOnExternalStorage: JailMonkey.isOnExternalStorage(), // Android
      isDebuggedMode: JailMonkey.isDebuggedMode(),
      hookDetected: JailMonkey.hookDetected()
    };
  }

  static async checkSecurityBeforeAuth() {
    const checks = this.isDeviceCompromised();

    if (checks.isJailBroken) {
      Alert.alert(
        'Dispositivo No Seguro',
        'Este dispositivo ha sido modificado (jailbreak/root). Por seguridad, no puedes usar la app.',
        [{ text: 'OK', onPress: () => BackHandler.exitApp() }]
      );
      return false;
    }

    if (checks.isDebuggedMode && !__DEV__) {
      Alert.alert(
        'Modo Debug Detectado',
        'Por seguridad, no puedes usar la app en modo debug.',
        [{ text: 'OK', onPress: () => BackHandler.exitApp() }]
      );
      return false;
    }

    return true;
  }
}

// Usage in App.jsx
useEffect(() => {
  SecurityChecks.checkSecurityBeforeAuth();
}, []);
```

---

## 📊 NUEVAS TABLAS DE BASE DE DATOS

### Schema SQL

```sql
-- Tabla para device tokens (Push notifications)
CREATE TABLE IF NOT EXISTS wp_wpcw_device_tokens (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id BIGINT UNSIGNED NOT NULL,
  device_token VARCHAR(255) NOT NULL,
  platform ENUM('ios', 'android') NOT NULL,
  app_version VARCHAR(20),
  os_version VARCHAR(20),
  active TINYINT(1) DEFAULT 1,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  last_used_at DATETIME,

  PRIMARY KEY (id),
  UNIQUE KEY idx_device_token (device_token),
  KEY idx_user_id (user_id),
  KEY idx_active (active),
  FOREIGN KEY (user_id) REFERENCES wp_users(ID) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla para sesiones de app
CREATE TABLE IF NOT EXISTS wp_wpcw_app_sessions (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id BIGINT UNSIGNED NOT NULL,
  device_token VARCHAR(255) NOT NULL,
  session_token VARCHAR(255) NOT NULL,
  ip_address VARCHAR(45),
  user_agent TEXT,
  started_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  ended_at DATETIME NULL,
  active TINYINT(1) DEFAULT 1,

  PRIMARY KEY (id),
  KEY idx_user_id (user_id),
  KEY idx_session_token (session_token),
  KEY idx_active (active),
  FOREIGN KEY (user_id) REFERENCES wp_users(ID) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla para cola de sincronización offline
CREATE TABLE IF NOT EXISTS wp_wpcw_offline_queue (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id BIGINT UNSIGNED NOT NULL,
  action_type ENUM('canje', 'perfil', 'cupon') NOT NULL,
  action_data JSON NOT NULL,
  status ENUM('pending', 'processing', 'completed', 'failed') DEFAULT 'pending',
  attempts INT DEFAULT 0,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  processed_at DATETIME NULL,
  error_message TEXT NULL,

  PRIMARY KEY (id),
  KEY idx_user_id (user_id),
  KEY idx_status (status),
  KEY idx_created_at (created_at),
  FOREIGN KEY (user_id) REFERENCES wp_users(ID) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla para notificaciones in-app
CREATE TABLE IF NOT EXISTS wp_wpcw_notifications (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id BIGINT UNSIGNED NOT NULL,
  tipo ENUM('cupon.nuevo', 'cupon.vencimiento', 'canje.confirmado', 'canje.rechazado', 'promo.especial', 'system') NOT NULL,
  titulo VARCHAR(255) NOT NULL,
  mensaje TEXT NOT NULL,
  data JSON NULL,
  leida TINYINT(1) DEFAULT 0,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  read_at DATETIME NULL,

  PRIMARY KEY (id),
  KEY idx_user_id (user_id),
  KEY idx_leida (leida),
  KEY idx_created_at (created_at),
  FOREIGN KEY (user_id) REFERENCES wp_users(ID) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

## 📄 CONCLUSIÓN

Este documento de arquitectura técnica proporciona la base completa para iniciar el desarrollo de la aplicación móvil en la próxima sesión.

### Próximos Pasos

1. **Crear wireframes en Figma**
2. **Setup proyecto React Native**
3. **Implementar estructura de carpetas**
4. **Configurar Redux + RTK Query**
5. **Desarrollar sistema de autenticación**

---

**🏗️ Arquitectura Técnica: Mobile App - WP Cupón WhatsApp**
**© 2025 Pragmatic Solutions - Innovación Aplicada**
**Documento creado: 11 de Octubre, 2025**
