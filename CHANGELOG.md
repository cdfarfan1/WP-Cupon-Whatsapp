# WP Cupón WhatsApp - CHANGELOG

## Registro de Bugs y Soluciones

### Versión 1.1.0 (Actual)

#### Bugs Corregidos
1. **[WPCW-001] Error en validación de canjes**
   - **Descripción**: Los canjes quedaban en estado pendiente incluso después de la aprobación del comercio
   - **Causa**: Error en la actualización del estado en la base de datos
   - **Solución**: Corregido el query de actualización en `redemption-handler.php`
   - **Impacto**: Alto
   - **Fecha**: 2025-07-18

2. **[WPCW-002] WhatsApp no abre en móviles**
   - **Descripción**: Links de WhatsApp no funcionaban en dispositivos móviles
   - **Causa**: URL mal formateada para protocolo `whatsapp://`
   - **Solución**: Implementada detección de dispositivo y uso del protocolo correcto
   - **Impacto**: Medio
   - **Fecha**: 2025-07-19

#### Bugs Conocidos
1. **[WPCW-003] Duplicación de notificaciones**
   - **Descripción**: Las notificaciones de canje se envían dos veces en algunos casos
   - **Estado**: En investigación
   - **Workaround**: Ninguno por el momento
   - **Prioridad**: Media

2. **[WPCW-004] Caché de listados**
   - **Descripción**: Los listados de cupones no se actualizan inmediatamente después de un canje
   - **Estado**: Pendiente
   - **Workaround**: Refrescar la página manualmente
   - **Prioridad**: Baja

### Bugs por Componente

#### Gestión de Cupones
1. **[WPCW-005] Validación de fechas**
   - **Descripción**: Las fechas de expiración no consideran la zona horaria del sitio
   - **Estado**: Corregido en próxima versión
   - **Solución Temporal**: Ajustar fechas manualmente considerando el offset

2. **[WPCW-006] Límites de uso**
   - **Descripción**: El contador de usos no se incrementa correctamente en canjes múltiples
   - **Estado**: En desarrollo
   - **Impacto**: Bajo

#### Integración WhatsApp
1. **[WPCW-007] Formato de números**
   - **Descripción**: Números internacionales no se formatean correctamente
   - **Estado**: Corregido
   - **Solución**: Implementada librería de formateo de números

2. **[WPCW-008] Mensajes largos**
   - **Descripción**: Mensajes largos se truncan en algunos dispositivos
   - **Estado**: En investigación
   - **Workaround**: Usar mensajes más cortos

#### Base de Datos
1. **[WPCW-009] Índices faltantes**
   - **Descripción**: Consultas lentas en tablas de canjes con muchos registros
   - **Estado**: Corregido
   - **Solución**: Añadidos índices para optimización

2. **[WPCW-010] Transacciones incompletas**
   - **Descripción**: Registros huérfanos en casos de error
   - **Estado**: En desarrollo
   - **Workaround**: Script de limpieza manual

### Problemas de Compatibilidad

#### WooCommerce
1. **[WPCW-011] Versiones antiguas**
   - **Descripción**: Incompatibilidad con WooCommerce < 6.0
   - **Estado**: No se corregirá
   - **Solución**: Actualizar WooCommerce

#### Elementor
1. **[WPCW-012] Widgets personalizados**
   - **Descripción**: Widgets no aparecen en algunas versiones
   - **Estado**: Corregido
   - **Solución**: Actualizada la registración de widgets

### Problemas de Rendimiento

1. **[WPCW-013] Carga de listados**
   - **Descripción**: Tiempo de carga excesivo en listados grandes
   - **Estado**: En optimización
   - **Solución Temporal**: Implementada paginación

2. **[WPCW-014] Memoria en exportaciones**
   - **Descripción**: Uso excesivo de memoria en exportaciones grandes
   - **Estado**: Corregido
   - **Solución**: Implementado procesamiento por lotes

## Guía de Reporte de Bugs

### Formato de Reporte
```
[WPCW-XXX] Título descriptivo
- Descripción: Detalle del problema
- Pasos para reproducir:
  1. Paso 1
  2. Paso 2
  3. ...
- Comportamiento esperado:
- Comportamiento actual:
- Entorno:
  * Versión de WordPress:
  * Versión de WooCommerce:
  * Versión de PHP:
  * Navegador:
```

### Prioridades
- **Crítica**: Afecta funcionalidad core, necesita solución inmediata
- **Alta**: Afecta operaciones importantes, necesita solución pronta
- **Media**: Afecta algunas funcionalidades, puede esperar
- **Baja**: No afecta funcionalidad principal

### Estados de Bug
- **Nuevo**: Recién reportado
- **Confirmado**: Verificado y reproducible
- **En Desarrollo**: Solución en proceso
- **Resuelto**: Corregido y verificado
- **Cerrado**: Documentado y cerrado
- **No Reproducible**: No se puede reproducir
- **No Se Corregirá**: Decisión de no corregir

## Contacto y Soporte

### Reportar Nuevos Bugs
- Email: soporte@pragmaticsolutions.com.ar
- Sistema de tickets: https://support.pragmaticsolutions.com.ar
- GitHub Issues: https://github.com/cdfarfan1/wp-cupon-whatsapp/issues

### Actualizaciones de Seguridad
- Las actualizaciones de seguridad se publican inmediatamente
- Se notifica por email a los usuarios registrados
- Se actualiza este registro con detalles post-solución

## [1.2.0] - 2025-07-18

### Agregado
- Integración completa con MongoDB:
  * Sistema de sincronización bidireccional
  * Respaldo automático de datos
  * Exportación en formatos JSON, CSV y XML
  * Panel de configuración en ajustes
- Nueva documentación detallada de MongoDB (docs/MONGODB_INTEGRATION.md)
- Sistema de monitoreo de sincronización
- Mejoras en la seguridad de datos

### Mejorado
- Sistema de respaldo y recuperación
- Rendimiento en operaciones masivas
- Documentación general del plugin
- Interfaz de configuración

## [1.1.0] - 2025-07-16

### Agregado
- SDK oficial para integraciones de terceros
- API REST completa con documentación
- Sistema de webhooks para eventos
- Integración con Elementor:
  * Widget de Lista de Cupones
  * Widget de Formulario de Adhesión
- Sistema de caché y optimización
- Soporte para múltiples proveedores de WhatsApp
- Integración con sistemas CRM
- Sistema de tracking y analytics

### Mejorado
- Rendimiento general del plugin
- Interfaz de administración
- Sistema de gestión de canjes
- Documentación técnica
- Seguridad y validaciones

### Corregido
- Problemas de memoria en grandes instalaciones
- Errores en la sincronización de datos
- Conflictos con otros plugins
- Problemas de compatibilidad con WooCommerce
- Errores en el proceso de canje

### Seguridad
- Implementada validación de webhooks
- Mejorada la sanitización de datos
- Añadido sistema de rate limiting
- Implementado sistema de tokens seguros
- Actualizado sistema de permisos

## [1.0.1] - 2025-06-15

### Mejorado
- Rendimiento en la gestión de cupones
- Interfaz de administración
- Mensajes de error

### Corregido
- Error en la validación de números de WhatsApp
- Problema con los recordatorios de vencimiento
- Conflictos con temas personalizados

## [1.0.0] - 2025-06-01

### Características Iniciales
- Gestión básica de cupones
- Integración con WooCommerce
- Envío de mensajes por WhatsApp
- Panel de administración
- Sistema de roles y permisos
- Estadísticas básicas

[1.1.0]: https://github.com/wpcw/wp-cupon-whatsapp/compare/v1.0.1...v1.1.0
[1.0.1]: https://github.com/wpcw/wp-cupon-whatsapp/compare/v1.0.0...v1.0.1
[1.0.0]: https://github.com/wpcw/wp-cupon-whatsapp/releases/tag/v1.0.0
