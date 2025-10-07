# Historias de Usuario para la Plataforma de Beneficios y Convenios

Este documento describe los casos de uso de la plataforma, centrada en la creación de una red de convenios de beneficios entre instituciones y negocios.

---

### Rol: Administrador del Sitio (Super Admin)

*   **COMO** administrador del sitio, **QUIERO** un panel para supervisar todos los "Convenios" que se han creado en la plataforma, su estado (pendientes, activos, rechazados) y las entidades involucradas.
*   **COMO** administrador del sitio, **QUIERO** poder moderar o eliminar convenios que no cumplan con las políticas de la plataforma.
*   **COMO** administrador del sitio, **QUIERO** un dashboard con estadísticas globales sobre la red de convenios (ej. los negocios más activos, las instituciones con más convenios) y poder exportar todos los datos.
*   **COMO** administrador del sitio, **QUIERO** gestionar los perfiles de Instituciones y Negocios en la plataforma.

---

### Rol: Gerente de Institución

*   **COMO** gerente de una institución, **QUIERO** poder proponer un "Convenio" a un negocio **PARA** que ofrezca descuentos a los miembros de mi institución.
*   **COMO** gerente de una institución, **QUIERO** recibir notificaciones y poder aceptar o rechazar las propuestas de "Convenio" que los negocios me envían.
*   **COMO** gerente de una institución, **QUIERO** un panel para gestionar todos mis convenios (pendientes, activos, finalizados).
*   **COMO** gerente de una institución, **QUIERO** configurar el método de validación de mis miembros (ej. conectar a un sistema externo de cuotas) **PARA** asegurar que solo los miembros activos accedan a los beneficios de los convenios.
*   **COMO** gerente de una institución, **QUIERO** poder exportar los datos de mis convenios y su rendimiento.

---

### Rol: Dueño de Negocio

*   **COMO** dueño de un negocio, **QUIERO** poder proponer un "Convenio" a una institución **PARA** ofrecerles descuentos a sus miembros y atraerlos como clientes.
*   **COMO** dueño de un negocio, **QUIERO** poder proponer un "Convenio" a otro negocio **PARA** crear una alianza de promoción cruzada para nuestros empleados.
*   **COMO** dueño de un negocio, **QUIERO** recibir notificaciones y poder aceptar o rechazar las propuestas de "Convenio" que otras entidades me envían.
*   **COMO** dueño de un negocio, **QUIERO** un panel para gestionar todos mis convenios, tanto los que ofrezco como de los que soy beneficiario.
*   **COMO** dueño de un negocio, **QUIERO** gestionar a mis empleados **PARA** que puedan validar los cupones generados por los convenios.

---

### Rol: Beneficiario (Empleado o Miembro)

*   **COMO** beneficiario, **QUIERO** un proceso de registro donde pueda identificar a qué empresa o institución pertenezco para que el sistema me valide.
*   **COMO** beneficiario validado, **QUIERO** explorar un catálogo de todos los cupones y beneficios disponibles para mí a través de los convenios activos de mi institución/empresa.
*   **COMO** beneficiario, **QUIERO** seleccionar un cupón y recibirlo en mi WhatsApp **PARA** canjearlo fácilmente en el negocio proveedor.

---

### Rol: Desarrollador / Integrador

*   **COMO** desarrollador, **QUIERO** una API REST que me permita gestionar programáticamente los "Convenios" (crear, aceptar, consultar) **PARA** integrar la plataforma con sistemas de gestión externos (CRM, ERP).
*   **COMO** desarrollador, **QUIERO** que la API exponga endpoints para la validación de miembros, permitiendo a las instituciones conectar sus propios sistemas de autenticación.