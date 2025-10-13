/**
 * WP Cupón WhatsApp SDK - JavaScript
 *
 * SDK oficial para integración con WP Cupón WhatsApp desde frontend
 *
 * @package     WPCuponWhatsappSDK
 * @version     1.0.0
 * @author      Pragmatic Solutions - Innovación Aplicada
 * @copyright   2025 Pragmatic Solutions
 * @license     MIT
 *
 * Desarrollado por:
 * @FRONTEND - Sophie Laurent ($790M)
 * @API - Carlos Mendoza ($820M)
 */

(function(window) {
    'use strict';

    /**
     * Clase principal del SDK
     */
    class WPCuponWhatsappSDK {
        /**
         * Constructor
         *
         * @param {Object} config - Configuración del SDK
         * @param {string} config.apiUrl - URL base de la instalación WordPress
         * @param {string} config.apiKey - API Key
         * @param {string} config.apiSecret - API Secret
         * @param {string} [config.apiVersion='v1'] - Versión de la API
         * @param {boolean} [config.debug=false] - Modo debug
         */
        constructor(config) {
            if (!config.apiUrl || !config.apiKey || !config.apiSecret) {
                throw new Error('apiUrl, apiKey y apiSecret son requeridos');
            }

            this.apiUrl = config.apiUrl.replace(/\/$/, '');
            this.apiKey = config.apiKey;
            this.apiSecret = config.apiSecret;
            this.apiVersion = config.apiVersion || 'v1';
            this.debug = config.debug || false;
            this.log = [];
        }

        // ========================================
        // BENEFICIARIOS
        // ========================================

        /**
         * Crear beneficiario
         *
         * @param {Object} data - Datos del beneficiario
         * @returns {Promise<Object>}
         */
        async createBeneficiario(data) {
            this._validateRequired(data, ['nombre_completo', 'telefono_whatsapp', 'institucion_id']);
            return this._request('POST', '/beneficiarios', data);
        }

        /**
         * Obtener beneficiario por ID
         *
         * @param {number} id - ID del beneficiario
         * @returns {Promise<Object>}
         */
        async getBeneficiario(id) {
            return this._request('GET', `/beneficiarios/${id}`);
        }

        /**
         * Obtener beneficiario por teléfono
         *
         * @param {string} telefono - Teléfono del beneficiario
         * @returns {Promise<Object>}
         */
        async getBeneficiarioByPhone(telefono) {
            return this._request('GET', '/beneficiarios', { telefono });
        }

        /**
         * Listar beneficiarios
         *
         * @param {Object} [filters={}] - Filtros opcionales
         * @returns {Promise<Array>}
         */
        async listBeneficiarios(filters = {}) {
            return this._request('GET', '/beneficiarios', filters);
        }

        /**
         * Actualizar beneficiario
         *
         * @param {number} id - ID del beneficiario
         * @param {Object} data - Datos a actualizar
         * @returns {Promise<Object>}
         */
        async updateBeneficiario(id, data) {
            return this._request('PUT', `/beneficiarios/${id}`, data);
        }

        /**
         * Desactivar beneficiario
         *
         * @param {number} id - ID del beneficiario
         * @returns {Promise<Object>}
         */
        async deactivateBeneficiario(id) {
            return this._request('POST', `/beneficiarios/${id}/deactivate`);
        }

        // ========================================
        // CUPONES
        // ========================================

        /**
         * Crear cupón
         *
         * @param {Object} data - Datos del cupón
         * @returns {Promise<Object>}
         */
        async createCupon(data) {
            this._validateRequired(data, ['codigo', 'tipo_descuento', 'monto', 'institucion_id']);
            return this._request('POST', '/cupones', data);
        }

        /**
         * Obtener cupón por código
         *
         * @param {string} codigo - Código del cupón
         * @returns {Promise<Object>}
         */
        async getCupon(codigo) {
            return this._request('GET', `/cupones/${codigo}`);
        }

        /**
         * Listar cupones disponibles
         *
         * @param {Object} [filters={}] - Filtros opcionales
         * @returns {Promise<Array>}
         */
        async listCupones(filters = {}) {
            return this._request('GET', '/cupones', filters);
        }

        /**
         * Listar cupones por beneficiario
         *
         * @param {number} beneficiarioId - ID del beneficiario
         * @returns {Promise<Array>}
         */
        async getCuponesByBeneficiario(beneficiarioId) {
            return this._request('GET', `/beneficiarios/${beneficiarioId}/cupones`);
        }

        /**
         * Validar si un cupón puede ser usado
         *
         * @param {string} codigo - Código del cupón
         * @param {number} beneficiarioId - ID del beneficiario
         * @returns {Promise<Object>}
         */
        async validateCupon(codigo, beneficiarioId) {
            return this._request('POST', '/cupones/validate', {
                codigo,
                beneficiario_id: beneficiarioId
            });
        }

        // ========================================
        // CANJES
        // ========================================

        /**
         * Registrar canje
         *
         * @param {Object} data - Datos del canje
         * @returns {Promise<Object>}
         */
        async createCanje(data) {
            this._validateRequired(data, ['beneficiario_id', 'cupon_id', 'tipo_canje']);
            return this._request('POST', '/canjes', data);
        }

        /**
         * Obtener canje por ID
         *
         * @param {number} id - ID del canje
         * @returns {Promise<Object>}
         */
        async getCanje(id) {
            return this._request('GET', `/canjes/${id}`);
        }

        /**
         * Listar canjes
         *
         * @param {Object} [filters={}] - Filtros opcionales
         * @returns {Promise<Array>}
         */
        async listCanjes(filters = {}) {
            return this._request('GET', '/canjes', filters);
        }

        /**
         * Obtener historial de canjes de un beneficiario
         *
         * @param {number} beneficiarioId - ID del beneficiario
         * @param {Object} [filters={}] - Filtros opcionales
         * @returns {Promise<Array>}
         */
        async getHistorialBeneficiario(beneficiarioId, filters = {}) {
            return this._request('GET', `/beneficiarios/${beneficiarioId}/historial`, filters);
        }

        // ========================================
        // INSTITUCIONES
        // ========================================

        /**
         * Obtener información de institución
         *
         * @param {number} id - ID de la institución
         * @returns {Promise<Object>}
         */
        async getInstitucion(id) {
            return this._request('GET', `/instituciones/${id}`);
        }

        /**
         * Listar instituciones
         *
         * @returns {Promise<Array>}
         */
        async listInstituciones() {
            return this._request('GET', '/instituciones');
        }

        /**
         * Obtener estadísticas de institución
         *
         * @param {number} id - ID de la institución
         * @param {Object} [filters={}] - Filtros opcionales
         * @returns {Promise<Object>}
         */
        async getStatsInstitucion(id, filters = {}) {
            return this._request('GET', `/instituciones/${id}/stats`, filters);
        }

        // ========================================
        // WHATSAPP
        // ========================================

        /**
         * Enviar mensaje de WhatsApp
         *
         * @param {string} telefono - Teléfono destino
         * @param {string} mensaje - Mensaje a enviar
         * @returns {Promise<Object>}
         */
        async sendWhatsapp(telefono, mensaje) {
            return this._request('POST', '/whatsapp/send', {
                telefono,
                mensaje
            });
        }

        /**
         * Enviar template de WhatsApp
         *
         * @param {string} telefono - Teléfono destino
         * @param {string} templateName - Nombre del template
         * @param {Object} [params={}] - Parámetros del template
         * @returns {Promise<Object>}
         */
        async sendWhatsappTemplate(telefono, templateName, params = {}) {
            return this._request('POST', '/whatsapp/send-template', {
                telefono,
                template: templateName,
                params
            });
        }

        // ========================================
        // WEBHOOKS
        // ========================================

        /**
         * Registrar webhook
         *
         * @param {string} event - Evento a escuchar
         * @param {string} url - URL del webhook
         * @returns {Promise<Object>}
         */
        async registerWebhook(event, url) {
            return this._request('POST', '/webhooks', { event, url });
        }

        /**
         * Listar webhooks registrados
         *
         * @returns {Promise<Array>}
         */
        async listWebhooks() {
            return this._request('GET', '/webhooks');
        }

        /**
         * Eliminar webhook
         *
         * @param {number} id - ID del webhook
         * @returns {Promise<Object>}
         */
        async deleteWebhook(id) {
            return this._request('DELETE', `/webhooks/${id}`);
        }

        // ========================================
        // MÉTODOS PRIVADOS
        // ========================================

        /**
         * Realizar request a la API
         *
         * @private
         * @param {string} method - Método HTTP
         * @param {string} endpoint - Endpoint de la API
         * @param {Object} [data=null] - Datos a enviar
         * @returns {Promise<Object>}
         */
        async _request(method, endpoint, data = null) {
            const url = `${this.apiUrl}/wp-json/wpcw/${this.apiVersion}${endpoint}`;
            const token = this._generateToken();

            const options = {
                method,
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Content-Type': 'application/json',
                    'X-API-Key': this.apiKey
                }
            };

            // Agregar body para POST/PUT
            if (data && (method === 'POST' || method === 'PUT')) {
                options.body = JSON.stringify(data);
            }

            // Agregar query params para GET
            if (data && method === 'GET') {
                const params = new URLSearchParams(data);
                url += `?${params.toString()}`;
            }

            try {
                const response = await fetch(url, options);

                // Log si debug está activo
                if (this.debug) {
                    this.log.push({
                        method,
                        url,
                        data,
                        status: response.status,
                        timestamp: new Date().toISOString()
                    });
                }

                if (!response.ok) {
                    const error = await response.json();
                    throw new Error(error.message || `HTTP Error ${response.status}`);
                }

                return await response.json();

            } catch (error) {
                if (this.debug) {
                    console.error('SDK Error:', error);
                }
                throw error;
            }
        }

        /**
         * Generar token de autenticación
         *
         * @private
         * @returns {string} Token JWT
         */
        _generateToken() {
            const header = {
                typ: 'JWT',
                alg: 'HS256'
            };

            const payload = {
                iss: this.apiKey,
                iat: Math.floor(Date.now() / 1000),
                exp: Math.floor(Date.now() / 1000) + 3600 // Válido por 1 hora
            };

            const headerEncoded = this._base64urlEncode(JSON.stringify(header));
            const payloadEncoded = this._base64urlEncode(JSON.stringify(payload));

            // Nota: En producción, la firma debería generarse en el backend
            // Esta es una implementación simplificada
            const signature = this._hmacSha256(
                `${headerEncoded}.${payloadEncoded}`,
                this.apiSecret
            );

            return `${headerEncoded}.${payloadEncoded}.${signature}`;
        }

        /**
         * Encode Base64 URL-safe
         *
         * @private
         * @param {string} data - Datos a encodear
         * @returns {string}
         */
        _base64urlEncode(data) {
            return btoa(data)
                .replace(/\+/g, '-')
                .replace(/\//g, '_')
                .replace(/=/g, '');
        }

        /**
         * HMAC SHA256 (implementación simplificada)
         *
         * @private
         * @param {string} data - Datos
         * @param {string} key - Llave
         * @returns {string}
         */
        async _hmacSha256(data, key) {
            // Nota: En producción usar Web Crypto API
            const enc = new TextEncoder();
            const algorithm = { name: 'HMAC', hash: 'SHA-256' };

            const cryptoKey = await crypto.subtle.importKey(
                'raw',
                enc.encode(key),
                algorithm,
                false,
                ['sign']
            );

            const signature = await crypto.subtle.sign(
                algorithm.name,
                cryptoKey,
                enc.encode(data)
            );

            return this._arrayBufferToBase64url(signature);
        }

        /**
         * Convertir ArrayBuffer a Base64 URL-safe
         *
         * @private
         * @param {ArrayBuffer} buffer
         * @returns {string}
         */
        _arrayBufferToBase64url(buffer) {
            const bytes = new Uint8Array(buffer);
            let binary = '';
            for (let i = 0; i < bytes.length; i++) {
                binary += String.fromCharCode(bytes[i]);
            }
            return btoa(binary)
                .replace(/\+/g, '-')
                .replace(/\//g, '_')
                .replace(/=/g, '');
        }

        /**
         * Validar campos requeridos
         *
         * @private
         * @param {Object} data - Datos a validar
         * @param {Array} required - Campos requeridos
         * @throws {Error}
         */
        _validateRequired(data, required) {
            for (const field of required) {
                if (!data[field]) {
                    throw new Error(`Campo requerido faltante: ${field}`);
                }
            }
        }

        // ========================================
        // MÉTODOS PÚBLICOS DE UTILIDAD
        // ========================================

        /**
         * Obtener log de requests
         *
         * @returns {Array}
         */
        getLog() {
            return this.log;
        }

        /**
         * Limpiar log
         */
        clearLog() {
            this.log = [];
        }

        /**
         * Activar modo debug
         */
        enableDebug() {
            this.debug = true;
        }

        /**
         * Desactivar modo debug
         */
        disableDebug() {
            this.debug = false;
        }

        /**
         * Obtener versión del SDK
         *
         * @returns {string}
         */
        getVersion() {
            return '1.0.0';
        }

        /**
         * Ping a la API
         *
         * @returns {Promise<Object>}
         */
        async ping() {
            return this._request('GET', '/ping');
        }
    }

    // Exportar SDK
    if (typeof module !== 'undefined' && module.exports) {
        // Node.js
        module.exports = WPCuponWhatsappSDK;
    } else {
        // Browser
        window.WPCuponWhatsappSDK = WPCuponWhatsappSDK;
    }

})(typeof window !== 'undefined' ? window : global);
