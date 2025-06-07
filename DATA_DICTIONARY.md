# Diccionario de Datos - WP Canje Cupon Whatsapp

Este documento detalla las estructuras de datos personalizadas, opciones y metadatos utilizados por el plugin WP Canje Cupon Whatsapp.

## 1. User Meta (Metadatos de Usuario)

Prefijo general: `_wpcw_`

| Clave                                  | Tipo                          | Descripción/Propósito                                                                 | Usado en/Asociado a                     |
|----------------------------------------|-------------------------------|---------------------------------------------------------------------------------------|-----------------------------------------|
| `_wpcw_dni_number`                     | String                        | Número de DNI del cliente.                                                            | `customer` (rol)                        |
| `_wpcw_birth_date`                     | String (YYYY-MM-DD)           | Fecha de nacimiento del cliente.                                                      | `customer` (rol)                        |
| `_wpcw_whatsapp_number`                | String                        | Número de WhatsApp del cliente (esencial para canje).                                 | `customer` (rol)                        |
| `_wpcw_user_institution_id`            | Integer (ID de Post)          | ID del CPT `wpcw_institution` al que el cliente está afiliado.                    | `customer` (rol)                        |
| `_wpcw_user_favorite_coupon_categories`| Array (de term_ids)           | IDs de términos de `wpcw_coupon_category` marcadas como favoritas por el cliente.     | `customer` (rol)                        |
| `_wpcw_associated_entity_id`           | Integer (ID de Post)          | ID del CPT (`wpcw_business` o `wpcw_institution`) asociado al rol del usuario.      | `wpcw_business_owner`, `wpcw_institution_manager` |
| `_wpcw_associated_entity_type`         | String (`wpcw_business` etc.) | Tipo de CPT asociado al rol del usuario.                                              | `wpcw_business_owner`, `wpcw_institution_manager` |

## 2. Post Meta (Metadatos de Posts)

Prefijo general: `_wpcw_`

### 2.1. CPT: `wpcw_application` (Solicitudes de Adhesión)

| Clave                               | Tipo                          | Descripción/Propósito                                                                 |
|-------------------------------------|-------------------------------|---------------------------------------------------------------------------------------|
| `_wpcw_applicant_type`              | String (`comercio`/`institucion`)| Tipo de solicitante.                                                                  |
| `_wpcw_legal_name`                  | String                        | Nombre legal del solicitante.                                                         |
| `_wpcw_cuit`                        | String                        | CUIT del solicitante.                                                                 |
| `_wpcw_contact_person`              | String                        | Persona de contacto.                                                                  |
| `_wpcw_email`                       | String (email)                | Email de contacto de la solicitud.                                                    |
| `_wpcw_whatsapp`                    | String                        | Número de WhatsApp de contacto de la solicitud.                                       |
| `_wpcw_address_main`                | String                        | Dirección principal de la solicitud.                                                  |
| `_wpcw_application_status`          | String                        | Estado: `pendiente_revision`, `aprobada`, `rechazada`, `completada`, `error_procesamiento`. |
| `_wpcw_created_user_id`             | Integer (ID de Usuario)       | ID del usuario WP que envió el formulario (si estaba logueado).                       |
| `_wpcw_processed_entity_id`         | Integer (ID de Post)          | ID del CPT `wpcw_business` o `wpcw_institution` creado tras aprobación.               |
| `_wpcw_processing_error`            | String                        | Mensaje de error si el procesamiento automático falló.                                |
| `_wpcw_application_processed`       | String (`true`)               | Flag para indicar si la solicitud ya fue procesada (aprobada/rechazada y acciones tomadas). |
| `_wpcw_processing_log`              | String                        | Log de texto sobre el resultado del procesamiento.                                    |
| `_wpcw_logo_image_id`               | Integer (ID de Adjunto)       | ID del logo (subido por admin). _(Previsto, UI no implementada para carga en solicitud)_ |
| `_wpcw_rejection_notified`          | String (`true`)               | Flag para indicar si se notificó el rechazo. _(Previsto, no implementado)_             |

*Nota: `_wpcw_fantasy_name` se guarda como `post_title` y `_wpcw_description` como `post_content` del CPT `wpcw_application`.*

### 2.2. CPT: `wpcw_business` (Comercios)

| Clave                               | Tipo                          | Descripción/Propósito                                                                 |
|-------------------------------------|-------------------------------|---------------------------------------------------------------------------------------|
| `_wpcw_owner_user_id`               | Integer (ID de Usuario)       | ID del usuario WordPress (`wpcw_business_owner`) dueño de este comercio.              |
| `_wpcw_legal_name`                  | String                        | Nombre legal del comercio.                                                            |
| `_wpcw_cuit`                        | String                        | CUIT del comercio.                                                                    |
| `_wpcw_contact_person`              | String                        | Persona de contacto.                                                                  |
| `_wpcw_email`                       | String (email)                | Email de contacto del comercio.                                                       |
| `_wpcw_whatsapp`                    | String                        | Número de WhatsApp del comercio.                                                      |
| `_wpcw_address_main`                | String                        | Dirección principal del comercio.                                                     |
| `_wpcw_original_application_id`     | Integer (ID de Post)          | ID de la `wpcw_application` original desde la que se creó este comercio.            |
| `_wpcw_logo_image_id`               | Integer (ID de Adjunto)       | ID del logo del comercio. _(Previsto, UI no implementada para carga directa)_         |

*Nota: El nombre de fantasía es `post_title` y la descripción es `post_content`.*

### 2.3. CPT: `wpcw_institution` (Instituciones)

| Clave                               | Tipo                          | Descripción/Propósito                                                                     |
|-------------------------------------|-------------------------------|-------------------------------------------------------------------------------------------|
| `_wpcw_manager_user_id`             | Integer (ID de Usuario)       | ID del usuario WordPress (`wpcw_institution_manager`) gestor de esta institución.       |
| `_wpcw_legal_name`                  | String                        | Nombre legal de la institución.                                                           |
| `_wpcw_cuit`                        | String                        | CUIT de la institución.                                                                   |
| `_wpcw_contact_person`              | String                        | Persona de contacto.                                                                      |
| `_wpcw_email`                       | String (email)                | Email de contacto de la institución.                                                      |
| `_wpcw_whatsapp`                    | String                        | Número de WhatsApp de la institución.                                                     |
| `_wpcw_address_main`                | String                        | Dirección principal de la institución.                                                    |
| `_wpcw_original_application_id`     | Integer (ID de Post)          | ID de la `wpcw_application` original desde la que se creó esta institución.               |
| `_wpcw_logo_image_id`               | Integer (ID de Adjunto)       | ID del logo de la institución. _(Previsto, UI no implementada para carga directa)_        |

*Nota: El nombre de fantasía es `post_title` y la descripción es `post_content`.*

### 2.4. CPT: `shop_coupon` (Cupones de WooCommerce)

| Clave                                      | Tipo                          | Descripción/Propósito                                                                    |
|--------------------------------------------|-------------------------------|------------------------------------------------------------------------------------------|
| `_wpcw_is_loyalty_coupon`                  | String (`yes`/`no`)           | Indica si es un cupón de lealtad.                                                        |
| `_wpcw_is_public_coupon`                   | String (`yes`/`no`)           | Indica si es un cupón público.                                                           |
| `_wpcw_associated_business_id`             | Integer (ID de Post)          | ID del `wpcw_business` al que está asociado este cupón (si es específico de un comercio). |
| `_wpcw_coupon_category_id`                 | Integer (term_id)             | ID del término de `wpcw_coupon_category` al que pertenece el cupón.                      |
| `_wpcw_coupon_image_id`                    | Integer (ID de Adjunto)       | ID de la imagen destacada para el cupón.                                                 |
| `_wpcw_instit_coupon_applicable_businesses`| Array (de IDs de Post)        | Para cupones de institución, IDs de `wpcw_business` a los que aplica. _(UI no implementada)_ |
| `_wpcw_instit_coupon_applicable_categories`| Array (de term_ids)           | Para cupones de institución, IDs de `wpcw_coupon_category` a los que aplica. _(UI no implementada)_ |
| `_wpcw_is_redeemed_coupon`                 | String (`yes`)                | Marca un cupón WC dinámico como generado por un canje WPCW.                                |
| `_wpcw_original_canje_id`                  | Integer (ID de registro `wpcw_canjes`) | Vincula cupón WC dinámico con el registro de canje original.                           |
| `_wpcw_original_shop_coupon_id`            | Integer (ID de Post)          | Vincula cupón WC dinámico con el `shop_coupon` original que se canjeó.                     |

## 3. Opciones de WordPress (wp_options)

Prefijo general: `wpcw_`

| Clave                                  | Tipo          | Descripción/Propósito                                                              |
|----------------------------------------|---------------|------------------------------------------------------------------------------------|
| `wpcw_recaptcha_site_key`              | String        | Site Key de Google reCAPTCHA v2.                                                   |
| `wpcw_recaptcha_secret_key`            | String        | Secret Key de Google reCAPTCHA v2.                                                 |
| `wpcw_required_fields_settings`        | Array         | Configuración de qué campos de cliente son obligatorios (claves: `dni_number`, etc. valores: `1` o `0`). |
| `wpcw_page_id_mis_cupones`             | Integer (ID de Página) | ID de la página que contiene el shortcode `[wpcw_mis_cupones]`.                  |
| `wpcw_page_id_cupones_publicos`        | Integer (ID de Página) | ID de la página que contiene el shortcode `[wpcw_cupones_publicos]`.             |
| `wpcw_page_id_solicitud_adhesion`      | Integer (ID de Página) | ID de la página que contiene el shortcode `[wpcw_solicitud_adhesion_form]`.      |

## 4. Tabla Personalizada: `{$wpdb->prefix}wpcw_canjes`

| Columna                     | Tipo SQL                             | Descripción/Propósito                                                               |
|-----------------------------|--------------------------------------|-------------------------------------------------------------------------------------|
| `id`                        | `BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT` | Clave primaria.                                                                     |
| `numero_canje`              | `VARCHAR(255) NOT NULL`              | Identificador único y legible del canje.                                            |
| `cliente_id`                | `BIGINT(20) UNSIGNED NOT NULL`       | ID del usuario WordPress (cliente) que solicitó el canje.                           |
| `cupon_id`                  | `BIGINT(20) UNSIGNED NOT NULL`       | ID del `shop_coupon` original canjeado.                                             |
| `comercio_id`               | `BIGINT(20) UNSIGNED DEFAULT NULL`   | ID del `wpcw_business` donde se realiza/confirma el canje.                          |
| `fecha_solicitud_canje`     | `DATETIME NOT NULL`                  | Fecha y hora de la solicitud de canje.                                              |
| `fecha_confirmacion_canje`  | `DATETIME DEFAULT NULL`              | Fecha y hora de la confirmación por el negocio.                                     |
| `estado_canje`              | `VARCHAR(50) NOT NULL DEFAULT 'pendiente_confirmacion'` | Estado: `pendiente_confirmacion`, `confirmado_por_negocio`, `utilizado_en_pedido_wc`, `cancelado_por_usuario`, `cancelado_por_admin`, `vencido`. |
| `token_confirmacion`        | `VARCHAR(255) NOT NULL`              | Token único para que el negocio confirme el canje.                                  |
| `codigo_cupon_wc`           | `VARCHAR(255) DEFAULT NULL`          | Código del cupón WooCommerce de un solo uso generado tras la confirmación.          |
| `id_pedido_wc`              | `BIGINT(20) UNSIGNED DEFAULT NULL`   | ID del pedido WooCommerce donde se usó el `codigo_cupon_wc`.                        |
| `origen_canje`              | `VARCHAR(50) DEFAULT NULL`           | Origen de la solicitud (ej. `mis_cupones`).                                         |
| `notas_internas`            | `TEXT DEFAULT NULL`                  | Notas administrativas sobre el canje.                                               |

## 5. Constantes del Plugin

| Constante                 | Descripción/Propósito                                                     |
|---------------------------|---------------------------------------------------------------------------|
| `WPCW_VERSION`            | Versión actual del plugin.                                                |
| `WPCW_PLUGIN_DIR`         | Ruta absoluta al directorio del plugin.                                   |
| `WPCW_PLUGIN_URL`         | URL al directorio del plugin.                                             |
| `WPCW_TEXT_DOMAIN`        | Text domain para internacionalización (`wp-cupon-whatsapp`).                |
| `WPCW_PLUGIN_FILE`        | Ruta absoluta al archivo principal del plugin.                            |
| `WPCW_CANJES_TABLE_NAME`  | Nombre completo de la tabla de canjes (incluyendo prefijo `$wpdb->prefix`). |

## 6. Roles de Usuario Personalizados

| Rol                          | Descripción/Propósito                                                              | Capacidades Clave (además de las base)                                                                                                |
|------------------------------|------------------------------------------------------------------------------------|---------------------------------------------------------------------------------------------------------------------------------------|
| `wpcw_business_owner`        | Para dueños de Comercios.                                                          | Control sobre su CPT `wpcw_business`, control sobre `shop_coupon` (crear/editar/borrar propios), `wpcw_view_own_business_stats`.        |
| `wpcw_institution_manager`   | Para gestores de Instituciones.                                                    | Control sobre su CPT `wpcw_institution`, control total sobre `shop_coupon` (crear/editar/borrar propios, definir aplicabilidad), `wpcw_view_own_institution_stats`. |

## 7. Custom Post Types (CPTs)

| Slug (`post_type`)     | Nombre Singular         | Propósito                                                                | Soporta (Features)                               |
|------------------------|-------------------------|--------------------------------------------------------------------------|--------------------------------------------------|
| `wpcw_business`        | Comercio                | Representa un comercio adherido.                                         | `title`, `editor`, `thumbnail`, `custom-fields`. |
| `wpcw_institution`     | Institución             | Representa una institución adherida.                                     | `title`, `editor`, `thumbnail`, `custom-fields`. |
| `wpcw_application`     | Solicitud de Adhesión   | Almacena las solicitudes de adhesión de nuevos comercios/instituciones.  | `title`, `editor`, `custom-fields`. (No público) |

## 8. Taxonomías Personalizadas

| Slug (`taxonomy`)        | Nombre Singular         | Asociada a CPT | Propósito                                              | Jerárquica |
|--------------------------|-------------------------|----------------|--------------------------------------------------------|------------|
| `wpcw_coupon_category`   | Categoría de Cupón WPCW | `shop_coupon`  | Para categorizar los cupones dentro del sistema WPCW.  | Sí         |
