# Plan de Desarrollo de la Plataforma de Beneficios

Este documento describe el plan de desarrollo incremental para construir la plataforma de beneficios, basado en los casos de uso definidos.

## Fase 1: Cimientos y Paneles de Control

**Objetivo:** Construir las interfaces de gestión para los roles clave y la lógica de cupones sin la complejidad de la validación de beneficiarios.

*   **Panel del Administrador:**
    *   Creación y propuesta de "Campañas de Cupones".
    *   Dashboard de seguimiento de adhesiones a campañas.
    *   Gestión de Instituciones y Negocios.
*   **Panel del Gerente de Institución:**
    *   Dashboard con estadísticas agregadas (sin datos de miembros aún).
    *   Herramientas para invitar y supervisar a sus negocios adheridos.
*   **Panel del Dueño de Negocio:**
    *   Creación de cupones locales.
    *   Galería para adherirse (opt-in) a las campañas.
    *   Gestión de empleados (cajeros).

## Fase 2: Portal del Beneficiario y Validación Simple

**Objetivo:** Desarrollar el flujo completo para el usuario final, desde el registro hasta la obtención del cupón, usando un método de validación básico.

*   **Flujo del Beneficiario:**
    *   Formulario de registro/identificación donde el usuario declara a qué institución/empresa pertenece.
*   **Validación Simple:**
    *   El Gerente de Institución podrá subir manualmente un listado (ej. CSV) de IDs o emails de sus miembros válidos.
    *   El sistema validará al beneficiario contra este listado.
*   **Portal del Beneficiario:**
    *   Una vez validado, el usuario accede a su portal donde ve y puede obtener todos los cupones disponibles para él.

## Fase 3: Integración Externa y Validación Avanzada

**Objetivo:** Implementar la funcionalidad más compleja y potente: la validación de miembros en tiempo real contra sistemas externos.

*   **Configuración de API:**
    *   El Gerente de Institución podrá configurar un endpoint de API (URL) y las credenciales necesarias.
*   **Flujo de Validación por API:**
    *   Cuando un beneficiario se registra, la plataforma hará una llamada a la API de la institución para verificar su estado (ej. "¿El miembro con ID '12345' tiene la cuota al día?").
*   **Webhooks:**
    *   Desarrollar un sistema de webhooks para que la plataforma pueda notificar a sistemas externos sobre eventos clave (ej. un canje de cupón).
