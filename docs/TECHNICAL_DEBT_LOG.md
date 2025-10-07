# Registro de Deuda Técnica

Este documento lista las decisiones de implementación que se tomaron para acelerar la entrega, con el entendimiento de que serán revisadas y refactorizadas en el futuro.

---

### Deuda #1: Almacenamiento de API Keys

*   **ID de Deuda:** TD001
*   **Fecha:** 2025-10-07
*   **Descripción:** En la implementación inicial del MVP de la Fase 3, la API Key para la validación externa se guardará en la base de datos como texto plano (`post_meta`).
*   **Riesgo Técnico:** Alto. Almacenar secretos en texto plano es una mala práctica de seguridad.
*   **Motivo del Aplazamiento:** Acelerar la entrega del flujo de configuración de API, según la directiva del stakeholder. La implementación de un sistema de encriptación/desencriptación se abordará por separado.
*   **Prioridad de Pago:** **Crítica.** Debe ser la primera tarea a resolver en el ciclo de refactorización post-Fase 3.

---
