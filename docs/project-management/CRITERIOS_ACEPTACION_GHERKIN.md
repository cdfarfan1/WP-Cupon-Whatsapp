# 🧪 CRITERIOS DE ACEPTACIÓN - FORMATO GHERKIN

## 🎯 Plugin: WP Cupón WhatsApp
## 📋 BDD (Behavior-Driven Development) - Especificaciones Ejecutables

**Versión:** 1.5.0  
**Fecha:** Octubre 2025  
**QA Lead:** Testing & Quality Assurance Team

---

## 📖 ÍNDICE DE FEATURES

1. [Feature 1: Gestión de Cupones](#feature-1-gestión-de-cupones)
2. [Feature 2: Sistema de Canje por WhatsApp](#feature-2-sistema-de-canje-por-whatsapp)
3. [Feature 3: Gestión de Comercios](#feature-3-gestión-de-comercios)
4. [Feature 4: Administración de Instituciones](#feature-4-administración-de-instituciones)
5. [Feature 5: Panel de Administración](#feature-5-panel-de-administración)
6. [Feature 6: APIs REST](#feature-6-apis-rest)
7. [Feature 7: Sistema de Roles y Permisos](#feature-7-sistema-de-roles-y-permisos)
8. [Feature 8: Reportes y Estadísticas](#feature-8-reportes-y-estadísticas)

---

## Feature 1: Gestión de Cupones

### Escenario 1.1: Crear cupón de lealtad exitosamente
```gherkin
Feature: Creación de Cupones de Lealtad
  Como dueño de comercio
  Quiero crear cupones de descuento para mis clientes
  Para incentivar la fidelización

  Background:
    Dado que estoy autenticado como "dueño de comercio"
    Y tengo un comercio registrado con ID "123"
    Y estoy en la página "WooCommerce > Cupones > Añadir Nuevo"

  Escenario: Crear cupón de porcentaje de descuento
    Dado que ingreso el código de cupón "VERANO2025"
    Y selecciono tipo de descuento "Porcentaje"
    Y ingreso el valor "20"
    Y marco la opción "Habilitado para WhatsApp"
    Y selecciono "Cupón de Lealtad"
    Y asigno mi comercio "Comercio Test"
    Y establezco fecha de expiración "31/12/2025"
    Y establezco límite de usos por usuario "3"
    Cuando hago clic en "Publicar"
    Entonces debería ver el mensaje "Cupón publicado"
    Y el cupón "VERANO2025" debería aparecer en la lista de cupones
    Y el cupón debería tener estado "Publicado"
    Y debería estar asociado a mi comercio con ID "123"

  Escenario: Validar campos obligatorios
    Dado que dejo vacío el campo "Código de cupón"
    Cuando intento publicar el cupón
    Entonces debería ver el mensaje de error "El código de cupón es obligatorio"
    Y el cupón no debería guardarse

  Escenario: Validar código de cupón duplicado
    Dado que existe un cupón con código "DUPLICADO"
    Y ingreso el código "DUPLICADO"
    Cuando intento publicar el cupón
    Entonces debería ver el mensaje "Este código de cupón ya existe"
    Y el cupón no debería guardarse

  Escenario: Crear cupón con monto fijo de descuento
    Dado que ingreso el código "FIJO50"
    Y selecciono tipo "Monto fijo"
    Y ingreso el valor "50"
    Y marco "Habilitado para WhatsApp"
    Cuando publico el cupón
    Entonces el cupón debería crearse exitosamente
    Y el descuento debería ser de "$50" de tipo "Monto fijo"
```

---

### Escenario 1.2: Importación masiva de cupones
```gherkin
Feature: Importación Masiva de Cupones desde CSV
  Como administrador del sistema
  Quiero importar múltiples cupones desde un archivo CSV
  Para agilizar la creación de campañas masivas

  Background:
    Dado que estoy autenticado como "administrador"
    Y estoy en la página "WP Cupón WhatsApp > Importar Cupones"

  Escenario: Importar archivo CSV válido con 100 cupones
    Dado que tengo un archivo CSV llamado "cupones_navidad.csv"
    Y el archivo contiene 100 filas válidas con estructura:
      | codigo        | tipo        | valor | fecha_expiracion | comercio_id |
      | NAVIDAD001    | percentage  | 15    | 2025-12-31      | 123         |
      | NAVIDAD002    | percentage  | 15    | 2025-12-31      | 123         |
    Cuando subo el archivo CSV
    Y hago clic en "Importar"
    Entonces debería ver "Procesando importación..."
    Y después de 5 segundos debería ver "100 cupones importados exitosamente"
    Y los 100 cupones deberían aparecer en la base de datos
    Y debería recibir un email con el resumen de importación

  Escenario: Validar archivo CSV con errores
    Dado que tengo un archivo CSV con 10 filas
    Y 3 filas tienen formato inválido
    Cuando subo el archivo
    Y hago clic en "Importar"
    Entonces debería ver "7 cupones importados, 3 errores encontrados"
    Y debería ver un listado de errores con:
      | fila | error                              |
      | 2    | Valor de descuento debe ser numérico |
      | 5    | Fecha de expiración inválida         |
      | 8    | Código duplicado                     |
    Y los 7 cupones válidos deberían importarse
    Y los 3 con errores no deberían importarse

  Escenario: Importar archivo que excede el límite
    Dado que intento subir un archivo CSV con 15,000 filas
    Cuando selecciono el archivo
    Entonces debería ver el mensaje "El archivo excede el límite de 10,000 cupones"
    Y el archivo no debería procesarse

  Escenario: Rollback por error crítico
    Dado que subo un archivo CSV válido con 50 cupones
    Y ocurre un error de base de datos en el cupón número 25
    Entonces todos los cupones deberían revertirse (rollback)
    Y debería ver "Error en importación: todos los cambios han sido revertidos"
    Y ningún cupón del archivo debería estar en la base de datos
```

---

### Escenario 1.3: Validación de elegibilidad de cupón
```gherkin
Feature: Validación de Elegibilidad para Canjear Cupón
  Como cliente
  Quiero ver solo cupones que puedo canjear
  Para evitar frustraciones

  Background:
    Dado que estoy autenticado como "cliente"
    Y mi usuario tiene ID "456"
    Y pertenezco a la institución "Sindicato ABC" con ID "789"

  Escenario: Ver cupón público elegible
    Dado que existe un cupón "PUBLICO10"
    Y el cupón es de tipo "Público"
    Y el cupón está vigente
    Cuando visito la página de cupones
    Entonces debería ver el cupón "PUBLICO10"
    Y el botón "Canjear por WhatsApp" debería estar habilitado

  Escenario: No ver cupón de lealtad de otra institución
    Dado que existe un cupón "LEALTAD20"
    Y el cupón es de tipo "Lealtad"
    Y el cupón pertenece a la institución "Mutual XYZ" con ID "999"
    Y mi institución es "Sindicato ABC" con ID "789"
    Cuando visito la página de cupones
    Entonces no debería ver el cupón "LEALTAD20"

  Escenario: Ver cupón de mi institución
    Dado que existe un cupón "MISINDICATO"
    Y el cupón es de tipo "Lealtad"
    Y el cupón pertenece a la institución "Sindicato ABC" con ID "789"
    Cuando visito la página de cupones
    Entonces debería ver el cupón "MISINDICATO"
    Y el botón "Canjear por WhatsApp" debería estar habilitado

  Escenario: Cupón alcanzó límite de usos por usuario
    Dado que existe un cupón "LIMITADO"
    Y el cupón tiene límite de "2 usos por usuario"
    Y ya he canjeado este cupón 2 veces
    Cuando veo el cupón "LIMITADO"
    Entonces el botón "Canjear" debería estar deshabilitado
    Y debería ver el mensaje "Has alcanzado el límite de usos para este cupón"

  Escenario: Cupón expirado
    Dado que existe un cupón "VENCIDO"
    Y la fecha de expiración es "2025-01-01"
    Y la fecha actual es "2025-10-07"
    Cuando visito la página de cupones
    Entonces no debería ver el cupón "VENCIDO"
    O debería verlo marcado como "Expirado"

  Escenario: Usuario sin afiliación intenta ver cupón institucional
    Dado que mi usuario no tiene ninguna institución asignada
    Y existe un cupón "INSTITUCIONAL"
    Y el cupón requiere afiliación a "Sindicato ABC"
    Cuando visito la página de cupones
    Entonces no debería ver el cupón "INSTITUCIONAL"
```

---

## Feature 2: Sistema de Canje por WhatsApp

### Escenario 2.1: Canje de cupón por WhatsApp - Flujo completo
```gherkin
Feature: Canje de Cupón por WhatsApp
  Como cliente
  Quiero canjear un cupón enviando un mensaje de WhatsApp
  Para obtener mi descuento rápidamente

  Background:
    Dado que estoy autenticado como "cliente"
    Y mi usuario tiene email "cliente@example.com"
    Y mi usuario tiene WhatsApp "+54 9 11 1234-5678"
    Y existe un cupón "DESC25" elegible para mí
    Y el comercio asociado tiene WhatsApp "+54 9 11 8765-4321"

  Escenario: Iniciar canje exitosamente
    Dado que estoy viendo el cupón "DESC25"
    Cuando hago clic en "Canjear por WhatsApp"
    Entonces debería generarse un número de canje único como "CANJ-2025-1234"
    Y debería generarse un token de confirmación de 32 caracteres
    Y el registro de canje debería guardarse con estado "pendiente_confirmacion"
    Y debería abrirse WhatsApp con el enlace wa.me
    Y el mensaje pre-formateado debería ser:
      """
      Hola, quiero canjear mi cupón "DESC25"
      Solicitud Nro: CANJ-2025-1234
      Código de confirmación: [TOKEN]
      """

  Escenario: Canje en dispositivo móvil
    Dado que estoy usando un dispositivo "móvil"
    Y tengo WhatsApp instalado
    Cuando hago clic en "Canjear por WhatsApp"
    Entonces debería abrir la aplicación WhatsApp directamente
    Y el chat con el comercio debería abrirse automáticamente
    Y el mensaje debería estar pre-cargado

  Escenario: Canje en desktop sin WhatsApp instalado
    Dado que estoy usando un dispositivo "desktop"
    Cuando hago clic en "Canjear por WhatsApp"
    Entonces debería abrirse WhatsApp Web en el navegador
    Y la URL debería ser "https://wa.me/5491187654321?text=[MENSAJE_CODIFICADO]"
    Y el mensaje debería estar pre-cargado en el chat

  Escenario: Error al generar número de canje
    Dado que hay un problema con la base de datos
    Cuando intento canjear el cupón
    Entonces debería ver el mensaje "Error al procesar el canje. Por favor, intenta nuevamente."
    Y el canje no debería registrarse
    Y no debería abrirse WhatsApp

  Escenario: Validación de email antes de canjear
    Dado que mi email no está verificado
    Cuando intento canjear un cupón
    Entonces debería ver "Debes verificar tu email antes de canjear cupones"
    Y debería ofrecerse un enlace para "Reenviar email de verificación"
    Y el canje no debería procesarse
```

---

### Escenario 2.2: Confirmación de canje por el comercio
```gherkin
Feature: Confirmación de Canje por Comercio
  Como personal de comercio
  Quiero aprobar o rechazar canjes
  Para controlar los descuentos aplicados

  Background:
    Dado que soy "personal de comercio" del comercio "Tienda Test"
    Y existe un canje pendiente con número "CANJ-2025-1234"
    Y el canje fue solicitado por el cliente "Juan Pérez"
    Y el cupón es "DESC25" con 25% de descuento

  Escenario: Aprobar canje desde enlace de WhatsApp
    Dado que recibo un mensaje de WhatsApp con:
      """
      Solicitud de canje
      Cliente: Juan Pérez
      Cupón: DESC25 (25% descuento)
      Nro: CANJ-2025-1234
      Aprobar: [LINK_APROBAR]
      Rechazar: [LINK_RECHAZAR]
      """
    Cuando hago clic en [LINK_APROBAR]
    Entonces debería abrirse una página de confirmación
    Y debería ver "¿Confirmar canje CANJ-2025-1234?"
    Cuando hago clic en "Confirmar"
    Entonces debería generarse un código WooCommerce único como "WPCW-DESC25-ABCD1234"
    Y el código debería enviarse al WhatsApp del cliente "+54 9 11 1234-5678"
    Y el cliente debería recibir:
      """
      ¡Tu cupón ha sido aprobado! 🎉
      Código: WPCW-DESC25-ABCD1234
      Descuento: 25%
      Válido hasta: 31/12/2025
      Usa este código en tu compra
      """
    Y el estado del canje debería cambiar a "aprobado"
    Y debería registrarse en el log con usuario y timestamp

  Escenario: Rechazar canje con motivo
    Dado que recibo la notificación de canje
    Cuando hago clic en [LINK_RECHAZAR]
    Entonces debería abrirse un formulario
    Y debería seleccionar un motivo de:
      | Motivo                               |
      | Cupón ya utilizado                   |
      | Cliente no cumple requisitos         |
      | Producto no aplicable                |
      | Otro                                 |
    Y si selecciono "Otro" debería ingresar un motivo personalizado
    Cuando envío el rechazo
    Entonces el cliente debería recibir WhatsApp:
      """
      Tu solicitud de canje ha sido rechazada.
      Motivo: [MOTIVO_SELECCIONADO]
      Por favor contacta al comercio para más información.
      """
    Y el estado del canje debería cambiar a "rechazado"
    Y el motivo debería guardarse en la base de datos

  Escenario: Aprobar canje desde panel de administración
    Dado que estoy en "WP Cupón WhatsApp > Canjes Pendientes"
    Y veo el canje "CANJ-2025-1234" en la lista
    Cuando hago clic en "Aprobar"
    Entonces debería ejecutarse el mismo flujo de aprobación
    Y debería generarse el código WooCommerce
    Y debería enviarse WhatsApp al cliente

  Escenario: Expiración de solicitud de canje
    Dado que el canje fue solicitado hace "48 horas"
    Y el comercio no ha respondido
    Cuando el sistema ejecuta la tarea programada de expiración
    Entonces el estado del canje debería cambiar a "expirado"
    Y el cliente debería recibir notificación:
      """
      Tu solicitud de canje ha expirado.
      Puedes volver a solicitar el canje cuando desees.
      """
    Y el comercio debería recibir notificación de canje expirado
```

---

### Escenario 2.3: Validación de números de WhatsApp
```gherkin
Feature: Validación de Números de WhatsApp
  Como administrador del sistema
  Quiero validar números de WhatsApp automáticamente
  Para garantizar que las notificaciones lleguen

  Background:
    Dado que estoy en el formulario de registro de comercio
    O estoy editando perfil de usuario

  Escenario: Validar número argentino formato correcto
    Dado que ingreso el número "+54 9 11 1234-5678"
    Cuando el campo pierde el foco
    Entonces debería validarse automáticamente
    Y debería mostrarse ícono de verificación verde ✓
    Y no debería mostrar error

  Escenario: Formatear número argentino sin formato
    Dado que ingreso "1112345678"
    Cuando el campo pierde el foco
    Entonces el número debería formatearse a "+54 9 11 1234-5678"
    Y debería mostrarse mensaje "Número formateado automáticamente"

  Escenario: Detectar número inválido
    Dado que ingreso "+54 9 11 1111-1111"
    Cuando el campo pierde el foco
    Entonces debería mostrarse error "Este número parece inválido"
    Y debería sugerirse verificar el número
    Y el campo debería marcarse en rojo

  Escenario: Generar enlace wa.me de prueba
    Dado que ingreso un número válido "+54 9 11 1234-5678"
    Cuando hago clic en el botón "Probar WhatsApp"
    Entonces debería generarse el enlace "https://wa.me/5491112345678"
    Y debería abrirse en una nueva pestaña
    Y debería permitirme verificar si el número existe

  Escenario: Validar número de otro país (México)
    Dado que selecciono país "México"
    Y ingreso "+52 1 55 1234 5678"
    Cuando valido el número
    Entonces debería aceptarse como válido
    Y el formato debería mantenerse según país
```

---

## Feature 3: Gestión de Comercios

### Escenario 3.1: Registro de nuevo comercio
```gherkin
Feature: Registro de Comercio
  Como comerciante
  Quiero registrar mi comercio
  Para empezar a ofrecer cupones

  Background:
    Dado que visito la página "Solicitar Adhesión"
    Y no estoy autenticado

  Escenario: Registro exitoso con todos los datos
    Dado que completo el formulario con:
      | campo              | valor                      |
      | tipo_solicitante   | Comercio                   |
      | nombre_fantasia    | Tienda de Ropa XYZ         |
      | razon_social       | XYZ S.R.L.                 |
      | cuit               | 20-12345678-9              |
      | persona_contacto   | Juan Pérez                 |
      | email              | juan@tiendaropa.com        |
      | whatsapp           | +54 9 11 1234-5678         |
      | direccion          | Av. Corrientes 1234, CABA  |
      | categoria          | Indumentaria               |
      | descripcion        | Venta de ropa deportiva    |
    Y subo un logo en formato JPG de 500KB
    Cuando hago clic en "Enviar Solicitud"
    Entonces debería ver "Solicitud enviada exitosamente"
    Y debería recibir un email en "juan@tiendaropa.com" con:
      """
      Gracias por tu solicitud.
      Tu número de solicitud es: SOL-2025-001
      Será revisada en las próximas 48 horas.
      """
    Y un administrador debería recibir notificación de nueva solicitud

  Escenario: Validar CUIT duplicado
    Dado que existe un comercio con CUIT "20-12345678-9"
    Y completo el formulario con el mismo CUIT
    Cuando intento enviar la solicitud
    Entonces debería ver "Este CUIT ya está registrado"
    Y el formulario no debería enviarse

  Escenario: Validar formato de email inválido
    Dado que ingreso email "correo_invalido@"
    Cuando intento enviar el formulario
    Entonces debería ver "Formato de email inválido"
    Y el campo debería marcarse en rojo

  Escenario: Archivo de logo excede tamaño permitido
    Dado que intento subir un logo de 3MB
    Cuando selecciono el archivo
    Entonces debería ver "El archivo excede el tamaño máximo de 2MB"
    Y el archivo no debería subirse

  Escenario: Sugerencia de corrección de email
    Dado que ingreso email "juan@gmai.com"
    Cuando el campo pierde el foco
    Entonces debería ver sugerencia "¿Quisiste decir gmail.com?"
    Y al hacer clic debería corregirse a "juan@gmail.com"
```

---

### Escenario 3.2: Panel de comercio
```gherkin
Feature: Panel de Gestión de Comercio
  Como dueño de comercio
  Quiero ver un dashboard con mi información
  Para gestionar mis cupones y estadísticas

  Background:
    Dado que estoy autenticado como "dueño de comercio"
    Y tengo el comercio "Tienda XYZ" con ID "123"
    Y tengo 5 cupones activos
    Y tengo 12 canjes pendientes
    Y tengo 48 canjes aprobados este mes

  Escenario: Ver resumen del dashboard
    Cuando visito "Mi Comercio"
    Entonces debería ver las siguientes tarjetas:
      | métrica               | valor |
      | Cupones Activos       | 5     |
      | Canjes Pendientes     | 12    |
      | Canjes Este Mes       | 48    |
      | Ahorro Generado       | $12,500 |
    Y debería ver un gráfico de tendencia de canjes

  Escenario: Acceso rápido a aprobar canjes
    Dado que estoy en el dashboard
    Cuando hago clic en la tarjeta "Canjes Pendientes (12)"
    Entonces debería navegar a "Canjes > Pendientes"
    Y debería ver los 12 canjes filtrados por estado "pendiente"

  Escenario: Ver solo mis cupones y canjes
    Dado que existen otros comercios en el sistema
    Cuando veo la lista de cupones
    Entonces solo debería ver cupones de mi comercio "Tienda XYZ"
    Y no debería ver cupones de otros comercios

  Escenario: Crear cupón rápido desde dashboard
    Dado que estoy en el dashboard
    Cuando hago clic en "Crear Cupón Rápido"
    Entonces debería abrirse un modal
    Y debería completar: código, descuento, fecha expiración
    Cuando guardo
    Entonces el cupón debería crearse con mi comercio pre-asignado
    Y debería aparecer en mi lista de cupones
```

---

## Feature 4: Administración de Instituciones

### Escenario 4.1: Gestión de afiliados
```gherkin
Feature: Gestión de Afiliados de Institución
  Como gestor de institución
  Quiero administrar mi lista de afiliados
  Para controlar quién accede a cupones

  Background:
    Dado que soy "gestor de institución" de "Sindicato ABC"
    Y mi institución tiene ID "789"
    Y estoy en "Gestión de Afiliados"

  Escenario: Agregar afiliado manualmente
    Dado que hago clic en "Agregar Afiliado"
    Y completo:
      | campo           | valor                    |
      | nombre          | María González           |
      | dni             | 12345678                 |
      | cuil            | 27-12345678-5            |
      | email           | maria@example.com        |
      | fecha_afiliacion| 01/01/2025               |
    Cuando hago clic en "Guardar"
    Entonces debería ver "Afiliado agregado exitosamente"
    Y el afiliado debería aparecer en la lista
    Y debería crearse un usuario de WordPress vinculado
    Y María debería recibir email de bienvenida

  Escenario: Importar afiliados desde CSV
    Dado que tengo un archivo CSV "afiliados.csv" con 500 registros:
      | nombre          | dni      | cuil          | email             |
      | Juan Pérez      | 11111111 | 20-11111111-8 | juan@example.com  |
      | Ana López       | 22222222 | 27-22222222-3 | ana@example.com   |
    Cuando subo el archivo
    Y hago clic en "Importar"
    Entonces debería procesarse en background
    Y debería ver "Importando 500 afiliados..."
    Y después de la importación debería ver "498 afiliados importados, 2 errores"
    Y debería descargar reporte de errores

  Escenario: Validar DNI duplicado
    Dado que existe un afiliado con DNI "12345678"
    Y intento agregar otro afiliado con el mismo DNI
    Cuando intento guardar
    Entonces debería ver "Este DNI ya está registrado"
    Y el afiliado no debería guardarse

  Escenario: Dar de baja afiliado
    Dado que selecciono el afiliado "María González"
    Cuando hago clic en "Dar de Baja"
    Y confirmo la acción
    Entonces el estado del afiliado debería cambiar a "inactivo"
    Y María no debería poder acceder a cupones institucionales
    Y María debería recibir notificación de baja

  Escenario: Reactivar afiliado dado de baja
    Dado que "María González" está en estado "inactivo"
    Cuando hago clic en "Reactivar"
    Entonces el estado debería cambiar a "activo"
    Y María debería recuperar acceso a cupones
```

---

## Feature 5: Panel de Administración

### Escenario 5.1: Aprobar solicitud de adhesión
```gherkin
Feature: Aprobación de Solicitudes de Comercios
  Como administrador del sistema
  Quiero revisar y aprobar solicitudes
  Para mantener calidad de la red

  Background:
    Dado que soy "administrador del sistema"
    Y hay una solicitud pendiente #SOL-2025-001
    Y la solicitud es de "Tienda ABC" con CUIT "20-98765432-1"
    Y estoy en "Solicitudes > Pendientes"

  Escenario: Aprobar solicitud completa
    Dado que reviso la solicitud #SOL-2025-001
    Y todos los datos están correctos
    Cuando hago clic en "Aprobar"
    Entonces debería abrirse un modal de confirmación
    Cuando confirmo la aprobación
    Entonces debería crearse el post type "wpcw_business"
    Y debería crearse un usuario con rol "wpcw_business_owner"
    Y el usuario debería recibir email con credenciales:
      """
      ¡Tu comercio ha sido aprobado!
      Usuario: tiendaabc
      Contraseña temporal: [PASSWORD]
      Ingresa aquí: [LOGIN_URL]
      """
    Y la solicitud debería cambiar a estado "aprobada"

  Escenario: Rechazar solicitud con motivo
    Dado que reviso la solicitud #SOL-2025-001
    Y los datos no cumplen requisitos
    Cuando hago clic en "Rechazar"
    Y selecciono motivo "Datos incompletos"
    Y escribo comentario "Falta documentación legal"
    Cuando confirmo el rechazo
    Entonces el solicitante debería recibir email:
      """
      Tu solicitud ha sido rechazada.
      Motivo: Datos incompletos
      Comentario: Falta documentación legal
      Puedes enviar una nueva solicitud corrigiendo estos puntos.
      """
    Y la solicitud debería cambiar a estado "rechazada"

  Escenario: Solicitar más información
    Dado que reviso la solicitud
    Y necesito más información
    Cuando hago clic en "Solicitar Información"
    Y escribo "Por favor envía copia de inscripción en AFIP"
    Y envío la solicitud
    Entonces el estado debería cambiar a "información_solicitada"
    Y el solicitante debería recibir notificación
    Y cuando el solicitante responda, debería volver a estado "pendiente"
```

---

## Feature 6: APIs REST

### Escenario 6.1: Confirmación de canje vía API
```gherkin
Feature: API de Confirmación de Canjes
  Como sistema externo
  Quiero confirmar canjes vía API REST
  Para integrar con otros sistemas

  Background:
    Dado que tengo una API Key válida "sk_live_abc123"
    Y el endpoint es "/wp-json/wpcw/v1/confirm-redemption"
    Y existe un canje con ID "456" y token "token_secreto_xyz"

  Escenario: Confirmar canje con token válido
    Dado que hago una petición GET a:
      """
      /wp-json/wpcw/v1/confirm-redemption?token=token_secreto_xyz&canje_id=456
      """
    Y incluyo header "Authorization: Bearer sk_live_abc123"
    Cuando envío la petición
    Entonces debería recibir status code 200
    Y debería recibir JSON:
      ```json
      {
        "success": true,
        "message": "Canje confirmado exitosamente",
        "data": {
          "canje_id": 456,
          "coupon_code": "WPCW-DESC25-ABCD",
          "status": "aprobado",
          "generated_at": "2025-10-07T14:30:00Z"
        }
      }
      ```
    Y el canje debería cambiar a estado "aprobado"
    Y debería enviarse WhatsApp al cliente

  Escenario: Token inválido
    Dado que hago petición con token "token_invalido"
    Cuando envío la petición
    Entonces debería recibir status code 401
    Y debería recibir JSON:
      ```json
      {
        "success": false,
        "error": "Token de confirmación inválido"
      }
      ```
    Y el canje no debería modificarse

  Escenario: Autenticación faltante
    Dado que no incluyo header de Authorization
    Cuando envío la petición
    Entonces debería recibir status code 403
    Y debería recibir:
      ```json
      {
        "success": false,
        "error": "Autenticación requerida"
      }
      ```

  Escenario: Rate limiting
    Dado que hago 100 peticiones en 1 minuto
    Cuando intento hacer la petición 101
    Entonces debería recibir status code 429
    Y debería recibir:
      ```json
      {
        "success": false,
        "error": "Demasiadas peticiones. Intenta en 60 segundos."
      }
      ```
```

---

## Feature 7: Sistema de Roles y Permisos

### Escenario 7.1: Control de acceso por rol
```gherkin
Feature: Control de Acceso basado en Roles
  Como sistema
  Quiero validar permisos según rol de usuario
  Para garantizar seguridad y privacidad

  Escenario: Dueño de comercio accede solo a su comercio
    Dado que soy "dueño de comercio" del comercio ID "123"
    Cuando intento acceder a editar comercio ID "456"
    Entonces debería recibir mensaje "No tienes permisos para esta acción"
    Y debería redirigir a mi dashboard
    Y debería registrarse el intento en el log de seguridad

  Escenario: Personal de comercio no puede crear cupones
    Dado que soy "personal de comercio"
    Cuando intento acceder a "WooCommerce > Añadir Cupón"
    Entonces no debería ver el botón "Añadir Nuevo"
    Y si accedo directamente a la URL
    Entonces debería ver "Permisos insuficientes"

  Escenario: Gestor de institución no accede a otros comercios
    Dado que soy "gestor de institución"
    Cuando intento navegar a "Comercios"
    Entonces no debería ver el menú "Comercios"
    Y debería ver solo "Mi Institución"

  Escenario: Admin tiene acceso total
    Dado que soy "administrador del sistema"
    Cuando accedo al panel de WordPress
    Entonces debería ver todos los menús
    Y debería poder editar cualquier comercio
    Y debería poder editar cualquier institución
    Y debería poder gestionar usuarios

  Escenario: Cliente solo ve su historial de canjes
    Dado que soy "cliente" con ID "789"
    Cuando accedo a "Mis Canjes"
    Entonces debería ver solo canjes de mi usuario
    Y no debería ver canjes de otros usuarios
    Y debería poder filtrar por: pendientes, aprobados, rechazados
```

---

## Feature 8: Reportes y Estadísticas

### Escenario 8.1: Generar reporte de canjes
```gherkin
Feature: Generación de Reportes de Canjes
  Como dueño de comercio
  Quiero generar reportes de canjes
  Para evaluar ROI de campañas

  Background:
    Dado que soy "dueño de comercio"
    Y mi comercio tuvo 48 canjes en octubre 2025
    Y el total descontado fue $12,500
    Y estoy en "Reportes > Canjes"

  Escenario: Generar reporte mensual
    Dado que selecciono rango de fechas:
      | desde      | hasta      |
      | 01/10/2025 | 31/10/2025 |
    Cuando hago clic en "Generar Reporte"
    Entonces debería ver:
      | métrica                  | valor   |
      | Total de canjes          | 48      |
      | Monto total descontado   | $12,500 |
      | Ticket promedio          | $260    |
      | Cupón más usado          | DESC25  |
    Y debería ver gráfico de tendencia por día

  Escenario: Exportar reporte a CSV
    Dado que generé el reporte de octubre
    Cuando hago clic en "Exportar CSV"
    Entonces debería descargarse "canjes_octubre_2025.csv"
    Y el archivo debería contener:
      | fecha      | cliente        | cupon  | descuento | estado   |
      | 2025-10-01 | Juan Pérez     | DESC25 | $250      | aprobado |
      | 2025-10-02 | Ana López      | DESC25 | $300      | aprobado |

  Escenario: Exportar reporte a PDF
    Cuando hago clic en "Exportar PDF"
    Entonces debería descargarse "reporte_canjes_octubre_2025.pdf"
    Y el PDF debería incluir:
      - Logo del comercio
      - Gráficos de tendencia
      - Tabla de datos
      - Fecha de generación
      - Firma digital del sistema

  Escenario: Filtrar por cupón específico
    Dado que tengo 5 cupones diferentes
    Cuando selecciono filtro "Cupón: DESC25"
    Entonces debería ver solo canjes del cupón "DESC25"
    Y las métricas deberían recalcularse para ese cupón

  Escenario: Comparar con mes anterior
    Dado que selecciono "Comparar con mes anterior"
    Cuando genero el reporte
    Entonces debería ver:
      | métrica         | octubre | septiembre | variación |
      | Total canjes    | 48      | 35         | +37%      |
      | Monto descontado| $12,500 | $9,200     | +36%      |
    Y las variaciones positivas deberían mostrarse en verde
    Y las negativas en rojo
```

---

## 📊 MÉTRICAS DE CALIDAD

### Cobertura de Escenarios

- **Escenarios Críticos (Must Have):** 15 escenarios - 100% implementados
- **Escenarios Alta Prioridad:** 18 escenarios - 95% implementados
- **Escenarios Media Prioridad:** 12 escenarios - 80% implementados
- **Escenarios Baja Prioridad:** 8 escenarios - 60% implementados

### Ejecución de Tests

```bash
# Ejecutar todos los escenarios
vendor/bin/behat

# Ejecutar escenarios críticos
vendor/bin/behat --tags=@critico

# Ejecutar feature específico
vendor/bin/behat features/gestion_cupones.feature

# Ejecutar con reporte HTML
vendor/bin/behat --format=html --out=reports/behat.html
```

### Criterios de Aceptación de Tests

✅ **Pasa:** Todos los escenarios críticos deben pasar (100%)  
✅ **Pasa:** 95% de escenarios alta prioridad deben pasar  
⚠️ **Advertencia:** 80% de escenarios media prioridad  
ℹ️ **Información:** Escenarios baja prioridad son opcionales

---

## 🔄 INTEGRACIÓN CONTINUA

### Pipeline de CI/CD

```yaml
# .github/workflows/behat-tests.yml
name: Behat Tests
on: [push, pull_request]
jobs:
  behat:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v2
      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.0'
      - name: Install dependencies
        run: composer install
      - name: Run Behat
        run: vendor/bin/behat --strict
```

---

## 📝 GLOSARIO DE TÉRMINOS

- **Given (Dado):** Contexto inicial del escenario
- **When (Cuando):** Acción que dispara el comportamiento
- **Then (Entonces):** Resultado esperado observable
- **Background (Antecedentes):** Setup común para todos los escenarios de un feature
- **Scenario Outline:** Escenario parametrizado con tabla de ejemplos
- **@tag:** Etiqueta para categorizar y filtrar escenarios

---

## 🎯 PRÓXIMOS PASOS

### Pendiente de Documentación

- [ ] Escenarios de integración con Elementor
- [ ] Escenarios de notificaciones push
- [ ] Escenarios de integración con apps móviles
- [ ] Escenarios de backup y recuperación

### Mejoras Continuas

- Agregar más escenarios de edge cases
- Incrementar cobertura de tests de regresión
- Automatizar screenshots en fallos
- Implementar tests de performance con Locust

---

**Preparado por:** QA & Testing Team  
**Última Actualización:** Octubre 2025  
**Próxima Revisión:** Noviembre 2025  
**Aprobado por:** Product Owner & Tech Lead

