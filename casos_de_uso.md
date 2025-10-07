
# Historias de Usuario para la Plataforma de Beneficios "WP Cupón WhatsApp"

Este documento describe los casos de uso de la plataforma, enfocada en la gestión de beneficios para empleados de empresas y miembros de instituciones.

---

### Rol: Administrador del Sitio (Super Admin)

*   **COMO** administrador del sitio, **QUIERO** un panel de control con estadísticas globales de toda la plataforma (canjes, adhesiones, etc.) y la capacidad de exportar cualquier dato en formato estándar (CSV, JSON).
*   **COMO** administrador del sitio, **QUIERO** crear y proponer "campañas de cupones" para que los negocios se adhieran voluntariamente.
*   **COMO** administrador del sitio, **QUIERO** definir los posibles "métodos de validación" de miembros que las instituciones podrán configurar (ej. por API, por dominio de email, subida de listado).
*   **COMO** administrador del sitio, **QUIERO** gestionar los roles, perfiles de negocios e instituciones, y supervisar el correcto funcionamiento de toda la plataforma.

---

### Rol: Gerente de Institución

*   **COMO** gerente de una institución, **QUIERO** un panel para configurar cómo se validarán mis miembros, incluyendo la opción de conectar a un sistema externo para verificar si están al día con sus cuotas.
*   **COMO** gerente de una institución, **QUIERO** poder gestionar los negocios adheridos a mi institución, incluyendo la capacidad de realizar acciones en su nombre (ej. crearles un cupón, ver sus estadísticas) para ofrecerles un servicio de valor añadido.
*   **COMO** gerente de una institución, **QUIERO** invitar nuevos negocios a unirse a mi institución dentro de la plataforma.
*   **COMO** gerente de una institución, **QUIERO** un dashboard con estadísticas agregadas del rendimiento de mi grupo y la opción de exportar todos los datos.
*   **COMO** gerente de una institución, **QUIERO** comunicar a mis miembros los beneficios disponibles y cómo acceder a ellos.

---

### Rol: Dueño de Negocio

*   **COMO** dueño de un negocio, **QUIERO** un panel para gestionar mis cupones locales, mis empleados y ver mis estadísticas detalladas, con opción a exportar.
*   **COMO** dueño de un negocio, **QUIERO** ver la "galería de campañas" propuestas por el administrador para decidir si me adhiero a ellas.
*   **COMO** dueño de un negocio, **QUIERO** entender qué tipo de beneficiarios (empleados de X empresa, miembros de Y institución) están usando los cupones en mi local.

---

### Rol: Empleado de Negocio (Cajero)

*   **COMO** empleado de un negocio, **QUIERO** una interfaz simple para validar el cupón de un beneficiario y registrar el canje, sin importar el tipo de cupón o de beneficiario.

---

### Rol: Beneficiario (Usuario Final)

*   **COMO** beneficiario (empleado o miembro), **QUIERO** un proceso de registro donde pueda identificar a qué empresa o institución pertenezco.
*   **COMO** beneficiario, **QUIERO** que el sistema valide mi pertenencia (y mi estado de cuenta, si aplica) para desbloquear el acceso a los cupones y beneficios.
*   **COMO** beneficiario, **QUIERO**, una vez validado, poder explorar todos los cupones disponibles para mí.
*   **COMO** beneficiario, **QUIERO** seleccionar un cupón y recibirlo en mi WhatsApp para canjearlo fácilmente en el negocio correspondiente.

---

### Rol: Desarrollador / Integrador

*   **COMO** desarrollador, **QUIERO** una API REST completa que me permita interactuar con todos los objetos de datos: instituciones, negocios, campañas, beneficiarios, canjes y estadísticas.
*   **COMO** desarrollador, **QUIERO** que la API incluya webhooks o callbacks para poder reaccionar a eventos (ej. nuevo canje, nuevo miembro validado) desde un sistema externo.
*   **COMO** desarrollador, **QUIERO** disponer de documentación clara sobre la API y el modelo de datos para facilitar las integraciones.
