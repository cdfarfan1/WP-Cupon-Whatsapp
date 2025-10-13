# Registro de Desarrollo del Proyecto

Este documento es el diario de a bordo cronológico del proyecto. Cada entrada corresponde a un commit y documenta qué se hizo, por qué, y por quién.

---

## Log Entry: 2025-10-07 18

*   **Feature Slice:** Herramientas de Desarrollo
*   **Action:** Implementación de la lógica completa para el generador de datos de ejemplo (Seeder).
*   **Lead Agent:** El Artesano de WordPress
*   **Files Created:** `admin/developer-tools-page.php`, `includes/debug/class-wpcw-seeder.php`
*   **Files Modified:** `wp-cupon-whatsapp.php`, `admin/developer-tools-page.php`
*   **Reasoning:** Se ha implementado la funcionalidad completa de la herramienta de desarrollo. Esto incluye la lógica para generar un ecosistema de datos de prueba (instituciones, negocios, convenios, cupones, beneficiarios) y la lógica para eliminar de forma segura todos los datos generados. La herramienta solo es accesible en modo debug.
*   **Commit Hash:** 68931e0

---

## Log Entry: 2025-10-07 17

*   **Feature Slice:** Flujo de Canje Presencial
*   **Action:** Creación de la interfaz y el manejador AJAX para la validación de canjes.
*   **Lead Agent:** El Artesano de WordPress
*   **Files Created:** `admin/validate-redemption-page.php`
*   **Files Modified:** `wp-cupon-whatsapp.php`
*   **Reasoning:** Se ha creado la página de administración para que el Staff del Negocio pueda validar códigos de canje. La implementación incluye la interfaz de usuario, el script de jQuery para la llamada AJAX y la función de backend que recibe la petición. La lógica de validación real es un placeholder, pero el flujo de comunicación está completo.
*   **Commit Hash:** bc08f9d

---

## Log Entry: 2025-10-07 16

*   **Feature Slice:** Gestión de Roles y Permisos
*   **Action:** Creación del nuevo rol "Supervisor de Beneficios".
*   **Lead Agent:** El Artesano de WordPress
*   **Supervisor:** El Guardián de la Seguridad
*   **Files Modified:** `includes/roles.php`
*   **Reasoning:** Para permitir la delegación de la aprobación de beneficiarios de forma segura y estructurada, se ha creado el rol `wpcw_benefits_supervisor`. Este rol tiene la capacidad específica `approve_beneficiary_requests`, que también ha sido añadida a los roles de gestión superiores. Esto evita sobrecargar el rol del "Staff de Negocio" con responsabilidades administrativas.
*   **Commit Hash:** d654bea

---

## Log Entry: 2025-10-07 15

*   **Feature Slice:** Onboarding y Configuración
*   **Action:** Mejora del instalador para la creación automática de páginas de beneficiarios.
*   **Lead Agent:** El Artesano de WordPress
*   **Files Modified:** `includes/class-wpcw-installer-fixed.php`
*   **Reasoning:** Para mejorar la experiencia de usuario y facilitar el despliegue masivo, se ha actualizado la función `create_pages` del instalador. Ahora, durante la activación del plugin, se crearán automáticamente las páginas "Registro de Beneficiarios" y "Portal de Beneficios", cada una con su shortcode correspondiente ya insertado.
*   **Commit Hash:** 5340def

---

## Log Entry: 2025-10-07 14

*   **Feature Slice:** Fase de Refactorización
*   **Action:** Pago de la Deuda Técnica TD001 - Encriptación de API Keys.
*   **Lead Agent:** El Guardián de la Seguridad
*   **Files Created:** `includes/utils.php`
*   **Files Modified:** `includes/class-wpcw-installer-fixed.php`, `admin/institution-dashboard-page.php`, `wp-cupon-whatsapp.php`
*   **Reasoning:** Se ha implementado un sistema de autogestión de claves de encriptación en la activación del plugin. La lógica de guardado y lectura de la API key en el panel de la institución ha sido refactorizada para usar encriptación (openssl_encrypt/decrypt), saldando la deuda técnica y eliminando el almacenamiento de secretos en texto plano.
*   **Commit Hash:** 84af228

---

## Log Entry: 2025-10-07 13

*   **Feature Slice:** MVP 3.0 - Configuración de API de Validación (COMPLETADO)
*   **Action:** Implementación de la herramienta de prueba de conexión de API.
*   **Lead Agent:** El Ingeniero de Datos
*   **Supervisor:** El Guardián de la Seguridad
*   **Files Modified:** `admin/institution-dashboard-page.php`
*   **Reasoning:** Se ha añadido la interfaz y la lógica AJAX para que el Gerente de Institución pueda probar su configuración de API en tiempo real. Esto es crucial para que los usuarios puedan auto-diagnosticar problemas de conexión antes de activar la validación por API para sus beneficiarios. Esto completa el primer entregable de la Fase 3.
*   **Commit Hash:** 63c18ad

---

## Log Entry: 2025-10-07 12

*   **Feature Slice:** MVP 3.0 - Configuración de API de Validación
*   **Action:** Implementación de la UI y la lógica de guardado para la configuración de la API de validación externa.
*   **Lead Agent:** El Artesano de WordPress
*   **Reasoning:** Se ha añadido al panel del Gerente de Institución el formulario para configurar la validación por API. La lógica de guardado se ha implementado de forma directa para acelerar la entrega, incurriendo en deuda técnica (TD001) al no encriptar la API key en este paso. El siguiente paso será implementar la herramienta de prueba de conexión.
*   **Commit Hash:** 292578b

---

## Log Entry: 2025-10-07 11

*   **Feature Slice:** MVP 2.2 - Capa de Personalización (FASE 2 COMPLETADA)
*   **Action:** Implementación de la gestión de categorías preferidas y la vista ordenada en el portal del beneficiario.
*   **Lead Agent:** La Diseñadora de Experiencias, El Artesano de WordPress
*   **Files Modified:** `includes/class-wpcw-shortcodes.php`
*   **Reasoning:** Se ha añadido un formulario para que los usuarios guarden sus categorías de interés. La consulta de cupones del portal ahora prioriza estas categorías, mostrando primero los resultados más relevantes para el usuario. Esto completa la funcionalidad planificada para la Fase 2.
*   **Commit Hash:** 82ed51c

---

## Log Entry: 2025-10-07 10

*   **Feature Slice:** MVP 2.1 - Catálogo General de Beneficios (COMPLETADO)
*   **Action:** Implementación del shortcode `[wpcw_portal_beneficiario]`.
*   **Lead Agent:** El Artesano de WordPress
*   **Supervisor:** El Arquitecto
*   **Files Modified:** `includes/class-wpcw-shortcodes.php`
*   **Reasoning:** Se ha creado la lógica principal que permite a un beneficiario validado ver su catálogo de cupones. La función consulta la cadena completa de Usuario -> Institución -> Convenios -> Cupones, sentando las bases para la futura personalización. Esto completa la primera parte del portal del beneficiario.
*   **Commit Hash:** cd1eb20

---

## Log Entry: 2025-10-07 9

*   **Feature Slice:** MVP 2.0 - Registro y Validación Simple (Parte B - COMPLETADO)
*   **Action:** Implementación del shortcode y formulario público para el registro de beneficiarios.
*   **Lead Agent:** El Artesano de WordPress, La Diseñadora de Experiencias
*   **Supervisor:** El Guardián de la Seguridad
*   **Files Modified:** `includes/class-wpcw-shortcodes.php`
*   **Reasoning:** Se ha creado el shortcode `[wpcw_registro_beneficiario]` que renderiza un formulario de registro. La lógica del formulario valida el email del usuario contra la lista de miembros válidos (subida por el Gerente de Institución) antes de crear la cuenta de usuario. Esto completa el flujo de validación simple y finaliza el MVP de la Fase 2.
*   **Commit Hash:** 96f37f1

---

## Log Entry: 2025-10-07 8

*   **Feature Slice:** MVP 2.0 - Registro y Validación Simple (Parte A)
*   **Action:** Implementación del panel y la lógica para la gestión de miembros vía subida de CSV.
*   **Lead Agent:** El Artesano de WordPress, La Diseñadora de Experiencias
*   **Supervisor:** El Guardián de la Seguridad
*   **Files Modified:** `admin/institution-dashboard-page.php`
*   **Reasoning:** Se ha añadido la interfaz y la lógica de backend para que un Gerente de Institución pueda subir un archivo CSV con los emails de sus miembros. El sistema procesa y guarda esta lista, estableciendo la base para el sistema de validación de beneficiarios. Esto completa la primera mitad del MVP de la Fase 2.
*   **Commit Hash:** a155486

---

## Log Entry: 2025-10-07 7

*   **Feature Slice:** Gestión de Cupones por Convenio
*   **Action:** Refactorización del metabox de cupones para asociarlos a convenios.
*   **Lead Agent:** El Artesano de WordPress
*   **Supervisor:** El Arquitecto
*   **Files Modified:** `admin/coupon-meta-boxes.php`
*   **Reasoning:** Se ha reescrito por completo el metabox de la pantalla de edición de cupones. Se eliminó la asociación directa a un negocio y se reemplazó por un selector que solo muestra los convenios activos del usuario. La lógica de guardado ahora almacena `_wpcw_associated_convenio_id` y se añadió una columna a la lista de cupones para visualizar esta nueva relación. Esto alinea el sistema de cupones con la nueva arquitectura.
*   **Commit Hash:** 62ac71b

---

## Log Entry: 2025-10-07 6

*   **Feature Slice:** MVP 1.1 - La Autopista de Respuesta (COMPLETADO)
*   **Action:** Implementación de la lógica de acción para aceptar y rechazar convenios desde la página virtual.
*   **Lead Agent:** El Artesano de WordPress
*   **Supervisor:** El Guardián de la Seguridad
*   **Files Modified:** `public/response-handler.php`
*   **Reasoning:** Se ha completado el flujo de respuesta. La página virtual ahora procesa los parámetros de acción (`accept`/`reject`), actualiza el estado del post del convenio, e invalida el token de seguridad eliminándolo. Esto completa la funcionalidad del MVP 1.1, permitiendo un ciclo completo de propuesta y respuesta.
*   **Commit Hash:** 5ea99c4

---

## Log Entry: 2025-10-07 5

*   **Feature Slice:** MVP 1.1 - La Autopista de Respuesta
*   **Action:** Creación del manejador de página virtual para `/responder-convenio/`.
*   **Lead Agent:** El Artesano de WordPress
*   **Supervisor:** El Guardián de la Seguridad
*   **Files Created:** `public/response-handler.php`
*   **Files Modified:** `wp-cupon-whatsapp.php`, `includes/roles.php`
*   **Reasoning:** Se ha implementado la lógica para que WordPress reconozca la URL `/responder-convenio/` sin necesidad de un archivo físico. El manejador intercepta la petición, valida el token de seguridad y el estado del convenio, y muestra una interfaz básica de respuesta. Se ha añadido `flush_rewrite_rules()` a la activación para asegurar que la nueva URL funcione inmediatamente.
*   **Commit Hash:** 4b1951d

---

## Log Entry: 2025-10-07 4

*   **Feature Slice:** MVP 1.1 - La Autopista de Respuesta
*   **Action:** Implementación del sistema de generación y almacenamiento de tokens de seguridad.
*   **Lead Agent:** El Ingeniero de Datos
*   **Supervisor:** El Guardián de la Seguridad
*   **Files Modified:** `admin/business-convenios-page.php`
*   **Reasoning:** Se ha modificado el flujo de creación de convenios para generar un token criptográficamente seguro de un solo uso. Este token se almacena como metadato del convenio y se incluye en un enlace dentro del email de notificación. Este es el primer paso indispensable para construir el flujo de aceptación/rechazo ágil y seguro.
*   **Commit Hash:** 12f3e1f

---

## Log Entry: 2025-10-07 3

*   **Feature Slice:** MVP 1.0 - Motor de Propuestas de Convenio (COMPLETADO)
*   **Action:** Implementación de la lógica de backend para el envío del formulario de propuesta.
*   **Lead Agent:** El Artesano de WordPress
*   **Supervisor:** El Guardián de la Seguridad
*   **Files Modified:** `admin/business-convenios-page.php`
*   **Reasoning:** Se ha añadido la función `wpcw_handle_propose_convenio_form` que se ejecuta en `admin_init`. La función gestiona la seguridad (nonce, permisos), validación, creación del CPT `wpcw_convenio`, almacenamiento de metadatos y el envío de la notificación por email. Con esto, el flujo de propuesta de convenios es funcional de principio a fin.
*   **Commit Hash:** 022fdaa

---

## Log Entry: 2025-10-07 2

*   **Feature Slice:** MVP 1.0 - Motor de Propuestas de Convenio
*   **Action:** Implementación de la interfaz de usuario (UI) y la lógica de carga de datos para el formulario de propuesta de convenios.
*   **Lead Agents:** La Diseñadora de Experiencias, El Artesano de WordPress
*   **Supervisor:** El Guardián de la Seguridad
*   **Files Created:** `includes/managers/class-wpcw-institution-manager.php`
*   **Files Modified:** `admin/business-convenios-page.php`, `wp-cupon-whatsapp.php`
*   **Reasoning:** Se ha creado la clase `WPCW_Institution_Manager` para manejar la lógica de consulta de instituciones de forma centralizada y reutilizable. La página de gestión de convenios ahora contiene un formulario (oculto por defecto) con un selector poblado dinámicamente y un nonce de seguridad, listo para la implementación de la lógica de envío.
*   **Commit Hash:** f40e9a2

---

## Log Entry: 2025-10-07 1

*   **Feature Slice:** MVP 1.0 - Motor de Propuestas de Convenio
*   **Action:** Creación del esqueleto de la interfaz de usuario (UI) para la página de "Gestión de Convenios" del Dueño de Negocio.
*   **Lead Agent:** La Diseñadora de Experiencias
*   **Files Created:** `admin/business-convenios-page.php`
*   **Files Modified:** `wp-cupon-whatsapp.php` (para incluir el nuevo archivo).
*   **Reasoning:** Se establece la página de administración dedicada donde un Dueño de Negocio podrá gestionar y proponer convenios. El acceso a esta página está restringido al rol `business_owner` o superior, sentando las bases para la futura implementación del formulario de propuestas.
*   **Commit Hash:** e918299
