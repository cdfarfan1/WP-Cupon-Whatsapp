-- =====================================================
-- MIGRACIÓN: Actualizar tabla wp_wpcw_canjes
-- De: Esquema v1.0 (5 columnas)
-- A: Esquema v1.5 (17 columnas)
-- =====================================================
-- Autor: Dr. Rajesh Kumar (Database Specialist)
-- Fecha: 7 de Octubre, 2025
-- Aprobado por: Marcus Chen (PM)
-- Testeado por: Jennifer Wu (QA)
-- =====================================================

-- INSTRUCCIONES PARA CRISTIAN:
-- 1. Hacer BACKUP de la tabla actual primero
-- 2. Ir a phpMyAdmin → Base de datos "tienda"
-- 3. Click en pestaña "SQL"
-- 4. Copiar y pegar este script COMPLETO
-- 5. Click en "Continuar"
-- 6. Refrescar plugin en WordPress

-- =====================================================
-- PASO 1: BACKUP (Seguridad)
-- =====================================================
-- Crear tabla de backup antes de modificar
CREATE TABLE IF NOT EXISTS wp_wpcw_canjes_backup_20251007 
LIKE wp_wpcw_canjes;

INSERT INTO wp_wpcw_canjes_backup_20251007 
SELECT * FROM wp_wpcw_canjes;

-- Verificar backup
SELECT 'Backup creado:', COUNT(*) as registros_respaldados 
FROM wp_wpcw_canjes_backup_20251007;

-- =====================================================
-- PASO 2: AGREGAR COLUMNAS NUEVAS
-- =====================================================

-- Agregar coupon_id (reemplaza coupon_code antiguo)
ALTER TABLE wp_wpcw_canjes 
ADD COLUMN coupon_id bigint(20) UNSIGNED NULL AFTER user_id;

-- Agregar numero_canje
ALTER TABLE wp_wpcw_canjes 
ADD COLUMN numero_canje varchar(20) NULL AFTER coupon_id;

-- Agregar token_confirmacion
ALTER TABLE wp_wpcw_canjes 
ADD COLUMN token_confirmacion varchar(64) NULL AFTER numero_canje;

-- Agregar estado_canje (COLUMNA CRÍTICA)
ALTER TABLE wp_wpcw_canjes 
ADD COLUMN estado_canje varchar(50) NOT NULL DEFAULT 'pendiente_confirmacion' AFTER token_confirmacion;

-- Agregar fecha_solicitud_canje (COLUMNA CRÍTICA)
ALTER TABLE wp_wpcw_canjes 
ADD COLUMN fecha_solicitud_canje datetime NULL AFTER estado_canje;

-- Agregar fecha_confirmacion_canje
ALTER TABLE wp_wpcw_canjes 
ADD COLUMN fecha_confirmacion_canje datetime NULL AFTER fecha_solicitud_canje;

-- Agregar comercio_id (reemplaza business_id)
ALTER TABLE wp_wpcw_canjes 
ADD COLUMN comercio_id bigint(20) UNSIGNED NULL AFTER fecha_confirmacion_canje;

-- Agregar whatsapp_url
ALTER TABLE wp_wpcw_canjes 
ADD COLUMN whatsapp_url text NULL AFTER comercio_id;

-- Agregar codigo_cupon_wc
ALTER TABLE wp_wpcw_canjes 
ADD COLUMN codigo_cupon_wc varchar(100) NULL AFTER whatsapp_url;

-- Agregar id_pedido_wc
ALTER TABLE wp_wpcw_canjes 
ADD COLUMN id_pedido_wc bigint(20) UNSIGNED NULL AFTER codigo_cupon_wc;

-- Agregar origen_canje
ALTER TABLE wp_wpcw_canjes 
ADD COLUMN origen_canje varchar(50) DEFAULT 'webapp' AFTER id_pedido_wc;

-- Agregar notas_internas
ALTER TABLE wp_wpcw_canjes 
ADD COLUMN notas_internas text NULL AFTER origen_canje;

-- Agregar fecha_rechazo
ALTER TABLE wp_wpcw_canjes 
ADD COLUMN fecha_rechazo datetime NULL AFTER notas_internas;

-- Agregar fecha_cancelacion
ALTER TABLE wp_wpcw_canjes 
ADD COLUMN fecha_cancelacion datetime NULL AFTER fecha_rechazo;

-- Agregar created_at
ALTER TABLE wp_wpcw_canjes 
ADD COLUMN created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER fecha_cancelacion;

-- Agregar updated_at
ALTER TABLE wp_wpcw_canjes 
ADD COLUMN updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER created_at;

-- =====================================================
-- PASO 3: MIGRAR DATOS ANTIGUOS A COLUMNAS NUEVAS
-- =====================================================

-- Migrar business_id → comercio_id (si existe)
UPDATE wp_wpcw_canjes 
SET comercio_id = business_id 
WHERE business_id IS NOT NULL;

-- Migrar redeemed_at → fecha_solicitud_canje (si existe)
UPDATE wp_wpcw_canjes 
SET fecha_solicitud_canje = redeemed_at 
WHERE redeemed_at IS NOT NULL;

-- Migrar redeemed_at → fecha_confirmacion_canje (asumir confirmados)
UPDATE wp_wpcw_canjes 
SET fecha_confirmacion_canje = redeemed_at,
    estado_canje = 'confirmado_por_negocio'
WHERE redeemed_at IS NOT NULL;

-- =====================================================
-- PASO 4: CREAR ÍNDICES PARA PERFORMANCE
-- =====================================================

-- Índice en user_id
ALTER TABLE wp_wpcw_canjes 
ADD INDEX idx_user_id (user_id);

-- Índice en coupon_id
ALTER TABLE wp_wpcw_canjes 
ADD INDEX idx_coupon_id (coupon_id);

-- Índice en numero_canje (búsquedas frecuentes)
ALTER TABLE wp_wpcw_canjes 
ADD INDEX idx_numero_canje (numero_canje);

-- Índice en estado_canje (filtros frecuentes)
ALTER TABLE wp_wpcw_canjes 
ADD INDEX idx_estado_canje (estado_canje);

-- Índice en fecha_solicitud_canje (reportes por fecha)
ALTER TABLE wp_wpcw_canjes 
ADD INDEX idx_fecha_solicitud (fecha_solicitud_canje);

-- Índice en comercio_id
ALTER TABLE wp_wpcw_canjes 
ADD INDEX idx_comercio_id (comercio_id);

-- =====================================================
-- PASO 5: VERIFICACIÓN
-- =====================================================

-- Mostrar estructura actualizada
DESCRIBE wp_wpcw_canjes;

-- Contar columnas (debería ser 19)
SELECT COUNT(*) as total_columnas 
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_SCHEMA = 'tienda' 
  AND TABLE_NAME = 'wp_wpcw_canjes';

-- Verificar que datos se migraron
SELECT 
    COUNT(*) as total_registros,
    COUNT(comercio_id) as con_comercio_id,
    COUNT(fecha_solicitud_canje) as con_fecha_solicitud
FROM wp_wpcw_canjes;

-- =====================================================
-- RESULTADO ESPERADO:
-- =====================================================
-- ✅ Backup creado con datos originales
-- ✅ 13 columnas nuevas agregadas
-- ✅ Datos antiguos migrados
-- ✅ 6 índices creados
-- ✅ Tabla lista para usar
-- =====================================================

-- Si algo sale mal, ROLLBACK:
-- DROP TABLE wp_wpcw_canjes;
-- RENAME TABLE wp_wpcw_canjes_backup_20251007 TO wp_wpcw_canjes;

