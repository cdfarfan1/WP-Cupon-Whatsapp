# Arquitectura de Datos: Modelo de Convenios

## 1. Resumen Ejecutivo

Esta documentación define la nueva arquitectura de datos de la plataforma, que pivota de un modelo jerárquico a un **modelo de red de alianzas (muchos-a-muchos)**. El objeto central de esta arquitectura es el Custom Post Type (CPT) `wpcw_convenio`.

## 2. Objeto Central: `wpcw_convenio`

Este CPT no es un contenido visible para el público, sino una "caja de conexión" que representa un acuerdo de beneficios entre dos entidades.

*   **Post Type Name:** `wpcw_convenio`
*   **Propósito:** Formalizar una relación de beneficio bidireccional o unidireccional.

### 2.1. Campos Personalizados (Post Meta)

Cada "Convenio" tendrá los siguientes metadatos:

*   `_convenio_provider_id`: (ID de Post) El ID del **Negocio** que **OFRECE** el descuento o beneficio. Es el que "paga" la promoción.
*   `_convenio_recipient_id`: (ID de Post) El ID de la entidad cuyos miembros **RECIBEN** el beneficio. Puede ser una **Institución** u otro **Negocio**.
*   `_convenio_status`: (String) El estado actual del acuerdo. Valores posibles:
    *   `pending`: Propuesto, esperando aceptación de la contraparte.
    *   `active`: Aceptado por ambas partes y en vigor.
    *   `rejected`: Propuesta rechazada por la contraparte.
    *   `terminated`: Un convenio que estuvo activo pero fue finalizado por una de las partes.
*   `_convenio_terms`: (String) Un campo de texto para describir los términos del convenio (ej. "15% de descuento en todos los productos", "2x1 los martes").
*   `_convenio_originator_id`: (ID de Usuario) El ID del usuario (Gerente o Dueño) que **inició** la propuesta de convenio.

### 2.2. Diagrama de Relaciones

Este modelo permite una flexibilidad total para crear redes de colaboración.

**Ejemplo 1: Institución consigue un descuento para sus miembros.**

`[ Negocio "Restaurante ABC" ] --- (Ofrece Beneficio en) ---> [ Convenio #123 ] <--- (Recibe Beneficio para sus miembros) --- [ Institución "Club de Leones" ]`

**Ejemplo 2: Alianza entre dos negocios (Cross-promotion).**

`[ Negocio "Gimnasio FIT" ] --- (Ofrece Beneficio en) ---> [ Convenio #124 ] <--- (Recibe Beneficio para sus empleados) --- [ Negocio "Tienda de Suplementos" ]`

## 3. Impacto en la Lógica de Negocio

*   La jerarquía de "padre-hijo" queda obsoleta.
*   Para determinar los beneficios de un `Beneficiario`, el sistema ya no mirará a qué "grupo" pertenece, sino que consultará la tabla de `wpcw_convenio` para encontrar todos los convenios `activos` donde la institución/negocio del beneficiario sea el `_convenio_recipient_id`.
