# Sistema de Formularios Interactivos

## Descripción

Este sistema proporciona formularios interactivos y editables para la gestión de comercios, instituciones y clientes en el plugin WP Cupón WhatsApp. Los formularios incluyen validación en tiempo real, autoguardado, y diferentes niveles de permisos según el rol del usuario.

## Características Principales

### 🔧 Funcionalidades

- **Validación en tiempo real**: Los campos se validan mientras el usuario escribe
- **Autoguardado**: Los cambios se guardan automáticamente como borrador
- **Permisos por roles**: Diferentes niveles de acceso según el rol del usuario
- **Interfaz responsive**: Adaptable a diferentes tamaños de pantalla
- **Accesibilidad**: Cumple con estándares de accesibilidad web
- **Feedback visual**: Indicadores claros del estado de validación y guardado

### 📋 Tipos de Formularios

#### 1. Formularios de Comercios (`wpcw_business`)

**Metaboxes disponibles:**
- **Detalles del Comercio**: Información básica (razón social, CUIT, categoría, etc.)
- **Información de Contacto**: Datos de contacto y ubicación
- **Configuración**: Opciones avanzadas (solo para administradores)

**Campos principales:**
- Razón Social (requerido)
- Nombre de Fantasía
- CUIT (con validación)
- Categoría del Comercio (taxonomía `wpcw_business_category` - v1.3.0+)
- Descripción
- Sitio Web
- Email de Contacto (requerido)
- Teléfono y WhatsApp (validación mejorada - v1.3.0+)
- Dirección completa
- Estado del comercio
- Auto-aprobación de cupones
- Límite mensual de cupones

**Mejoras v1.3.0:**
- **Sistema de Categorías Mejorado**: Las categorías ahora utilizan la taxonomía nativa `wpcw_business_category` en lugar de campos meta, proporcionando mejor organización y funcionalidad de filtrado.
- **Guardado Automático**: Implementado hook `save_post` que guarda automáticamente los datos sin necesidad de AJAX.
- **Validación de WhatsApp**: Mejorada la validación de números de WhatsApp con soporte específico para Argentina (prefijo 54).
- **Interfaz Optimizada**: Mejor integración con el sistema de metaboxes nativo de WordPress.

#### 2. Formularios de Instituciones (`wpcw_institution`)

**Metaboxes disponibles:**
- **Detalles de la Institución**: Información básica
- **Gestor y Contacto**: Asignación de gestor y datos de contacto
- **Permisos y Configuración**: Configuraciones avanzadas (solo administradores)

**Campos principales:**
- Nombre Legal (requerido)
- Nombre Corto/Siglas
- Tipo de Institución (requerido)
- CUIT (opcional)
- Descripción
- Sitio Web
- Logo (URL)
- Gestor Asignado
- Email de Contacto (requerido)
- Teléfono de Contacto
- Dirección completa
- Estado de la institución
- Límite de estudiantes
- Categorías de cupón permitidas
- Auto-aprobación de estudiantes

#### 3. Formularios de Clientes (Usuarios)

**Metabox disponible:**
- **Detalles del Cliente**: Información específica del cliente

**Campos principales:**
- DNI
- Fecha de Nacimiento
- WhatsApp
- Institución asociada
- Número de Estudiante
- Estado del Cliente
- Categorías de cupón favoritas

## Estructura de Archivos

```
admin/
├── interactive-forms.php      # Lógica principal de formularios
├── css/
│   └── interactive-forms.css  # Estilos CSS
└── js/
    └── interactive-forms.js   # JavaScript para interactividad
```

## Permisos y Roles

### Comercios
- **Administradores**: Acceso completo a todos los campos
- **Propietarios de Comercio** (`wpcw_business_owner`): Pueden editar su propio comercio (campos básicos)
- **Staff de Comercio** (`wpcw_business_staff`): Solo lectura

### Instituciones
- **Administradores**: Acceso completo a todos los campos
- **Gestores de Institución** (`wpcw_institution_manager`): Pueden editar su institución asignada (campos básicos)
- **Otros roles**: Solo lectura

### Clientes
- **Administradores**: Acceso completo
- **Editores de usuarios**: Pueden editar información de clientes
- **Otros roles**: Sin acceso

## Validaciones Implementadas

### Validaciones de Campos

- **Email**: Formato de email válido
- **URL**: Formato de URL válido
- **Teléfono**: Formato argentino (+54 XX XXXX-XXXX)
- **WhatsApp**: Formato argentino (+54 9 XX XXXX-XXXX)
- **CUIT**: Validación con algoritmo de dígito verificador
- **DNI**: Formato numérico argentino
- **Código Postal**: Formato argentino (4 dígitos)
- **Fecha**: Formato de fecha válido
- **Longitud**: Mínimo y máximo de caracteres
- **Requerido**: Campos obligatorios

### Validaciones Personalizadas

```javascript
// Ejemplo de validación de CUIT
function validateCUIT(cuit) {
    // Implementación del algoritmo de validación
    // Retorna true/false
}
```

## Uso de AJAX

### Endpoints Disponibles

- `wpcw_ajax_validate_field`: Validación individual de campos
- `wpcw_ajax_save_draft`: Guardado automático de borradores
- `wpcw_ajax_get_related_data`: Obtención de datos relacionados para autocompletado

### Ejemplo de Llamada AJAX

```javascript
jQuery.post(ajaxurl, {
    action: 'wpcw_ajax_validate_field',
    field_name: 'email',
    field_value: 'usuario@ejemplo.com',
    post_id: 123,
    nonce: wpcw_ajax_nonce
}, function(response) {
    // Manejar respuesta
});
```

## Personalización

### Agregar Nuevas Validaciones

1. **En JavaScript** (`admin/js/interactive-forms.js`):
```javascript
// Agregar nueva regla de validación
validationRules.custom_rule = function(value, params) {
    // Lógica de validación
    return { valid: true/false, message: 'Mensaje de error' };
};
```

2. **En PHP** (`admin/interactive-forms.php`):
```php
// Agregar validación del lado del servidor
function wpcw_validate_custom_field($value) {
    // Lógica de validación
    return array('valid' => true, 'message' => '');
}
```

### Agregar Nuevos Campos

1. **Agregar campo en el metabox**:
```php
<tr>
    <th scope="row">
        <label for="nuevo_campo"><?php _e('Nuevo Campo', 'wp-cupon-whatsapp'); ?></label>
    </th>
    <td>
        <input type="text" 
               id="nuevo_campo" 
               name="nuevo_campo" 
               class="regular-text wpcw-validate" 
               data-validation="required" 
               data-field="nuevo_campo" />
        <div class="wpcw-field-feedback"></div>
    </td>
</tr>
```

2. **Agregar en la función de guardado**:
```php
$fields['_wpcw_nuevo_campo'] = 'nuevo_campo';
```

## Estilos CSS

### Clases Principales

- `.wpcw-interactive-form`: Contenedor principal del formulario
- `.wpcw-form-table`: Tabla de formulario estilizada
- `.wpcw-validate`: Campo con validación
- `.wpcw-field-feedback`: Contenedor de mensajes de validación
- `.wpcw-form-actions`: Contenedor de botones de acción
- `.wpcw-valid`: Campo válido
- `.wpcw-invalid`: Campo inválido
- `.wpcw-loading`: Estado de carga

### Personalización de Estilos

```css
/* Personalizar colores de validación */
.wpcw-field-feedback.success {
    color: #46b450;
}

.wpcw-field-feedback.error {
    color: #dc3232;
}

/* Personalizar estados de campos */
.wpcw-validate.wpcw-valid {
    border-color: #46b450;
}

.wpcw-validate.wpcw-invalid {
    border-color: #dc3232;
}
```

## Accesibilidad

### Características Implementadas

- **Navegación por teclado**: Soporte completo para Tab, Enter, Escape
- **Lectores de pantalla**: Etiquetas ARIA apropiadas
- **Alto contraste**: Soporte para modo de alto contraste
- **Indicadores visuales**: Estados claros de validación y foco

### Atributos ARIA

```html
<input type="text" 
       aria-describedby="campo-descripcion"
       aria-invalid="false"
       aria-required="true" />
<div id="campo-descripcion" class="description">
    Descripción del campo
</div>
```

## Troubleshooting

### Problemas Comunes

1. **Los formularios no se cargan**:
   - Verificar que `interactive-forms.php` esté incluido en el archivo principal
   - Comprobar permisos de usuario
   - Revisar errores de JavaScript en la consola

2. **La validación no funciona**:
   - Verificar que los scripts estén encolados correctamente
   - Comprobar que los campos tengan la clase `wpcw-validate`
   - Revisar las reglas de validación en `data-validation`

3. **El autoguardado no funciona**:
   - Verificar que AJAX esté funcionando
   - Comprobar nonces de seguridad
   - Revisar permisos de usuario

### Debug

```javascript
// Habilitar debug en JavaScript
window.wpcwDebug = true;

// Los mensajes de debug aparecerán en la consola
console.log('WPCW Debug: Validando campo...', fieldData);
```

## Futuras Mejoras

- [ ] Soporte para campos condicionales
- [ ] Validación de archivos subidos
- [ ] Integración con API externa para validación de CUIT
- [ ] Historial de cambios
- [ ] Notificaciones push para cambios importantes
- [ ] Exportación de datos en diferentes formatos
- [ ] Plantillas personalizables de formularios

## Contribución

Para contribuir al desarrollo de este sistema:

1. Seguir las convenciones de código de WordPress
2. Documentar nuevas funciones y métodos
3. Incluir pruebas para nuevas validaciones
4. Mantener la compatibilidad con versiones anteriores
5. Actualizar esta documentación con los cambios realizados