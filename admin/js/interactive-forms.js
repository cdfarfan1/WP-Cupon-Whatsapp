/**
 * WP Cupón WhatsApp - Interactive Forms JavaScript
 *
 * Handles real-time validation, AJAX submissions, and form interactivity
 */

(function ($) {
    'use strict';

    // Global variables
    let validationTimeout = {};
    let formData          = {};
    let validationRules   = {};

    // Initialize when document is ready
    $( document ).ready(
        function () {
            initInteractiveForms();
        }
    );

    /**
     * Initialize interactive forms
     */
    function initInteractiveForms() {
        bindEvents();
        initializeValidation();
        setupAutoSave();
        initializeFormProgress();
    }

    /**
     * Bind form events
     */
    function bindEvents() {
        // Real-time validation on input
        $( document ).on(
            'input blur',
            '.wpcw-validate',
            function () {
                const $field    = $( this );
                const fieldName = $field.data( 'field' );

                // Clear previous timeout
                if (validationTimeout[fieldName]) {
                    clearTimeout( validationTimeout[fieldName] );
                }

                // Set new timeout for validation
                validationTimeout[fieldName] = setTimeout(
                    function () {
                        validateField( $field );
                    },
                    500
                );
            }
        );

        // Immediate validation on change for selects
        $( document ).on(
            'change',
            '.wpcw-validate',
            function () {
                validateField( $( this ) );
            }
        );

        // Save draft button
        $( document ).on(
            'click',
            '.wpcw-save-draft',
            function (e) {
                e.preventDefault();
                saveDraft( $( this ).closest( '.wpcw-interactive-form' ) );
            }
        );

        // Validate all button
        $( document ).on(
            'click',
            '.wpcw-validate-all',
            function (e) {
                e.preventDefault();
                validateAllFields( $( this ).closest( '.wpcw-interactive-form' ) );
            }
        );

        // Form submission
        $( document ).on(
            'submit',
            'form',
            function (e) {
                const $form = $( this );
                if ($form.find( '.wpcw-interactive-form' ).length > 0) {
                    if ( ! validateAllFields( $form.find( '.wpcw-interactive-form' ) )) {
                        e.preventDefault();
                        showMessage( 'Por favor, corrija los errores antes de continuar.', 'error' );
                    }
                }
            }
        );

        // Auto-complete functionality
        $( document ).on(
            'input',
            '[data-autocomplete]',
            function () {
                const $field   = $( this );
                const dataType = $field.data( 'autocomplete' );
                const query    = $field.val();

                if (query.length >= 2) {
                    getRelatedData( dataType, query, $field );
                }
            }
        );

        // CUIT formatting
        $( document ).on(
            'input',
            '[data-validation*="cuit"]',
            function () {
                formatCUIT( $( this ) );
            }
        );

        // Phone formatting
        $( document ).on(
            'input',
            '[data-validation*="phone"], [data-validation*="whatsapp"]',
            function () {
                formatPhone( $( this ) );
            }
        );
    }

    /**
     * Initialize validation rules
     */
    function initializeValidation() {
        $( '.wpcw-validate' ).each(
            function () {
                const $field    = $( this );
                const fieldName = $field.data( 'field' );
                const rules     = $field.data( 'validation' );

                if (fieldName && rules) {
                    validationRules[fieldName] = rules;
                }
            }
        );
    }

    /**
     * Setup auto-save functionality
     */
    function setupAutoSave() {
        let autoSaveTimeout;

        $( document ).on(
            'input change',
            '.wpcw-validate',
            function () {
                const $form = $( this ).closest( '.wpcw-interactive-form' );

                // Clear previous timeout
                if (autoSaveTimeout) {
                    clearTimeout( autoSaveTimeout );
                }

                // Set new timeout for auto-save (5 seconds after last change)
                autoSaveTimeout = setTimeout(
                    function () {
                        if (isFormValid( $form )) {
                            saveDraft( $form, true ); // Silent save
                        }
                    },
                    5000
                );
            }
        );
    }

    /**
     * Initialize form progress indicator
     */
    function initializeFormProgress() {
        $( '.wpcw-interactive-form' ).each(
            function () {
                updateFormProgress( $( this ) );
            }
        );
    }

    /**
     * Validate individual field
     */
    function validateField($field) {
        const fieldName  = $field.data( 'field' );
        const value      = $field.val();
        const validation = $field.data( 'validation' );
        const postType   = $field.closest( '.wpcw-interactive-form' ).data( 'post-type' ) || 'business';

        if ( ! validation) {
            return true;
        }

        // Show validating state
        setFieldState( $field, 'validating' );

        // Perform AJAX validation
        $.ajax(
            {
                url: wpcwInteractiveForms.ajaxurl,
                type: 'POST',
                data: {
                    action: 'wpcw_validate_field',
                    nonce: wpcwInteractiveForms.nonce,
                    field: fieldName,
                    value: value,
                    validation: validation,
                    post_type: postType
                },
                success: function (response) {
                    if (response.valid) {
                        setFieldState( $field, 'valid' );
                    } else {
                        setFieldState( $field, 'invalid', response.errors );
                    }

                    // Update form progress
                    updateFormProgress( $field.closest( '.wpcw-interactive-form' ) );
                },
                error: function () {
                    setFieldState( $field, 'invalid', [wpcwInteractiveForms.messages.error] );
                }
            }
        );
    }

    /**
     * Set field validation state
     */
    function setFieldState($field, state, errors = []) {
        const $feedback = $field.siblings( '.wpcw-field-feedback' );

        // Remove all state classes
        $field.removeClass( 'wpcw-validating wpcw-valid wpcw-invalid' );
        $feedback.removeClass( 'wpcw-feedback-validating wpcw-feedback-valid wpcw-feedback-invalid' );

        // Add current state class
        $field.addClass( 'wpcw-' + state );
        $feedback.addClass( 'wpcw-feedback-' + state );

        // Update feedback message
        let message = '';
        if (state === 'validating') {
            message = wpcwInteractiveForms.messages.validating;
        } else if (state === 'valid') {
            message = wpcwInteractiveForms.messages.valid;
        } else if (state === 'invalid' && errors.length > 0) {
            if (errors.length === 1) {
                message = errors[0];
            } else {
                message = '<ul><li>' + errors.join( '</li><li>' ) + '</li></ul>';
            }
        }

        $feedback.html( message );
    }

    /**
     * Validate all fields in form
     */
    function validateAllFields($form) {
        let allValid  = true;
        const $fields = $form.find( '.wpcw-validate' );

        $fields.each(
            function () {
                const $field = $( this );
                validateField( $field );

                // Check if field is valid (we'll need to wait for AJAX, but for now check classes)
                setTimeout(
                    function () {
                        if ($field.hasClass( 'wpcw-invalid' )) {
                            allValid = false;
                        }
                    },
                    100
                );
            }
        );

        return allValid;
    }

    /**
     * Check if form is valid
     */
    function isFormValid($form) {
        const $invalidFields = $form.find( '.wpcw-validate.wpcw-invalid' );
        return $invalidFields.length === 0;
    }

    /**
     * Save form as draft
     */
    function saveDraft($form, silent = false) {
        const postId   = $form.data( 'post-id' );
        const postType = $form.data( 'post-type' );
        const $status  = $form.find( '.wpcw-form-status' );

        if ( ! silent) {
            $status.removeClass( 'wpcw-status-saved wpcw-status-error' )
                    .addClass( 'wpcw-status-saving' )
                    .text( wpcwInteractiveForms.messages.saving )
                    .show();
        }

        // Collect form data
        const data = {
            action: 'wpcw_save_draft',
            nonce: wpcwInteractiveForms.nonce,
            post_id: postId,
            post_type: postType
        };

        // Add all form fields
        $form.find( 'input, select, textarea' ).each(
            function () {
                const $field = $( this );
                const name   = $field.attr( 'name' );

                if (name) {
                    if ($field.attr( 'type' ) === 'checkbox') {
                        data[name] = $field.is( ':checked' ) ? '1' : '0';
                    } else {
                        data[name] = $field.val();
                    }
                }
            }
        );

        $.ajax(
            {
                url: wpcwInteractiveForms.ajaxurl,
                type: 'POST',
                data: data,
                success: function (response) {
                    if ( ! silent) {
                        if (response.success) {
                            $status.removeClass( 'wpcw-status-saving wpcw-status-error' )
                                .addClass( 'wpcw-status-saved' )
                                .text( wpcwInteractiveForms.messages.saved );

                            setTimeout(
                                function () {
                                    $status.fadeOut();
                                },
                                3000
                            );
                        } else {
                            $status.removeClass( 'wpcw-status-saving wpcw-status-saved' )
                                .addClass( 'wpcw-status-error' )
                                .text( response.data || wpcwInteractiveForms.messages.error );
                        }
                    }
                },
                error: function () {
                    if ( ! silent) {
                        $status.removeClass( 'wpcw-status-saving wpcw-status-saved' )
                            .addClass( 'wpcw-status-error' )
                            .text( wpcwInteractiveForms.messages.error );
                    }
                }
            }
        );
    }

    /**
     * Get related data for autocomplete
     */
    function getRelatedData(dataType, query, $field) {
        $.ajax(
            {
                url: wpcwInteractiveForms.ajaxurl,
                type: 'POST',
                data: {
                    action: 'wpcw_get_related_data',
                    nonce: wpcwInteractiveForms.nonce,
                    data_type: dataType,
                    query: query
                },
                success: function (response) {
                    if (response.success) {
                        showAutocompleteResults( $field, response.data );
                    }
                }
            }
        );
    }

    /**
     * Show autocomplete results
     */
    function showAutocompleteResults($field, results) {
        // Remove existing dropdown
        $( '.wpcw-autocomplete-dropdown' ).remove();

        if (results.length === 0) {
            return;
        }

        const $dropdown = $( '<div class="wpcw-autocomplete-dropdown"></div>' );

        results.forEach(
            function (item) {
                const $item = $( '<div class="wpcw-autocomplete-item"></div>' )
                .text( item.title )
                .data( 'item', item )
                .on(
                    'click',
                    function () {
                        $field.val( item.title ).trigger( 'change' );
                        $dropdown.remove();
                    }
                );

                $dropdown.append( $item );
            }
        );

        // Position dropdown
        const fieldOffset = $field.offset();
        $dropdown.css(
            {
                position: 'absolute',
                top: fieldOffset.top + $field.outerHeight(),
                left: fieldOffset.left,
                width: $field.outerWidth(),
                zIndex: 1000
            }
        );

        $( 'body' ).append( $dropdown );

        // Hide dropdown when clicking outside
        $( document ).on(
            'click.autocomplete',
            function (e) {
                if ( ! $( e.target ).closest( '.wpcw-autocomplete-dropdown, .wpcw-validate' ).length) {
                    $dropdown.remove();
                    $( document ).off( 'click.autocomplete' );
                }
            }
        );
    }

    /**
     * Format CUIT input
     */
    function formatCUIT($field) {
        let value = $field.val().replace( /[^0-9]/g, '' );

        if (value.length >= 2) {
            value = value.substring( 0, 2 ) + '-' + value.substring( 2 );
        }
        if (value.length >= 11) {
            value = value.substring( 0, 11 ) + '-' + value.substring( 11, 12 );
        }

        $field.val( value );
    }

    /**
     * Format phone input
     */
    function formatPhone($field) {
        let value = $field.val().replace( /[^0-9+]/g, '' );

        // Add +54 if not present
        if (value.length > 0 && ! value.startsWith( '+54' )) {
            if (value.startsWith( '54' )) {
                value = '+' + value;
            } else if (value.startsWith( '9' )) {
                value = '+54 ' + value;
            } else {
                value = '+54 9 ' + value;
            }
        }

        // Format as +54 9 11 1234-5678
        if (value.startsWith( '+54' )) {
            const numbers = value.substring( 3 ).replace( /[^0-9]/g, '' );
            if (numbers.length >= 1) {
                value = '+54 ' + numbers.substring( 0, 1 );
                if (numbers.length >= 3) {
                    value += ' ' + numbers.substring( 1, 3 );
                    if (numbers.length >= 7) {
                        value += ' ' + numbers.substring( 3, 7 );
                        if (numbers.length >= 11) {
                            value += '-' + numbers.substring( 7, 11 );
                        }
                    }
                }
            }
        }

        $field.val( value );
    }

    /**
     * Update form progress
     */
    function updateFormProgress($form) {
        const $progressBar = $form.find( '.wpcw-form-progress-bar' );

        if ($progressBar.length === 0) {
            return;
        }

        const $fields              = $form.find( '.wpcw-validate' );
        const $validFields         = $form.find( '.wpcw-validate.wpcw-valid' );
        const $requiredFields      = $form.find( '.wpcw-validate[data-validation*="required"]' );
        const $validRequiredFields = $requiredFields.filter( '.wpcw-valid' );

        // Calculate progress based on required fields
        let progress = 0;
        if ($requiredFields.length > 0) {
            progress = ($validRequiredFields.length / $requiredFields.length) * 100;
        } else if ($fields.length > 0) {
            progress = ($validFields.length / $fields.length) * 100;
        }

        $progressBar.css( 'width', progress + '%' );
    }

    /**
     * Show message to user
     */
    function showMessage(message, type = 'info') {
        const $message = $( '<div class="wpcw-message wpcw-message-' + type + '"></div>' )
            .text( message )
            .hide();

        $( '.wpcw-interactive-form' ).first().prepend( $message );
        $message.slideDown();

        // Auto-hide after 5 seconds
        setTimeout(
            function () {
                $message.slideUp(
                    function () {
                        $message.remove();
                    }
                );
            },
            5000
        );
    }

    /**
     * Utility function to debounce function calls
     */
    function debounce(func, wait, immediate) {
        let timeout;
        return function () {
            const context = this;
            const args    = arguments;
            const later   = function () {
                timeout = null;
                if ( ! immediate) {
                    func.apply( context, args );
                }
            };
            const callNow = immediate && ! timeout;
            clearTimeout( timeout );
            timeout = setTimeout( later, wait );
            if (callNow) {
                func.apply( context, args );
            }
        };
    }

    /**
     * Initialize tooltips
     */
    function initTooltips() {
        $( '.wpcw-tooltip' ).each(
            function () {
                const $tooltip = $( this );
                const text     = $tooltip.data( 'tooltip' );

                if (text) {
                    $tooltip.attr( 'title', text );
                }
            }
        );
    }

    /**
     * Handle keyboard navigation
     */
    function initKeyboardNavigation() {
        $( document ).on(
            'keydown',
            '.wpcw-validate',
            function (e) {
                // Enter key moves to next field
                if (e.key === 'Enter' && ! $( this ).is( 'textarea' )) {
                    e.preventDefault();
                    const $fields      = $( '.wpcw-validate' );
                    const currentIndex = $fields.index( this );
                    const $nextField   = $fields.eq( currentIndex + 1 );

                    if ($nextField.length) {
                        $nextField.focus();
                    }
                }
            }
        );
    }

    /**
     * Initialize accessibility features
     */
    function initAccessibility() {
        // Add ARIA labels
        $( '.wpcw-validate' ).each(
            function () {
                const $field = $( this );
                const $label = $( 'label[for="' + $field.attr( 'id' ) + '"]' );

                if ($label.length) {
                    $field.attr( 'aria-labelledby', $label.attr( 'id' ) || $field.attr( 'id' ) + '_label' );
                }

                // Add aria-describedby for feedback
                const $feedback = $field.siblings( '.wpcw-field-feedback' );
                if ($feedback.length) {
                    const feedbackId = $field.attr( 'id' ) + '_feedback';
                    $feedback.attr( 'id', feedbackId );
                    $field.attr( 'aria-describedby', feedbackId );
                }
            }
        );

        // Add role attributes
        $( '.wpcw-field-feedback' ).attr( 'role', 'alert' );
        $( '.wpcw-form-status' ).attr( 'role', 'status' );
    }

    // Initialize additional features
    $( document ).ready(
        function () {
            initTooltips();
            initKeyboardNavigation();
            initAccessibility();
        }
    );

    // Expose functions globally for external use
    window.wpcwInteractiveForms = {
        validateField: validateField,
        validateAllFields: validateAllFields,
        saveDraft: saveDraft,
        showMessage: showMessage,
        updateFormProgress: updateFormProgress
    };

})( jQuery );

/**
 * CSS for autocomplete dropdown (injected via JavaScript)
 */
if ( ! document.getElementById( 'wpcw-autocomplete-styles' )) {
    const style       = document.createElement( 'style' );
    style.id          = 'wpcw-autocomplete-styles';
    style.textContent = `
        .wpcw - autocomplete - dropdown {
            background: white;
            border: 1px solid #ddd;
            border - radius: 4px;
            box - shadow: 0 2px 8px rgba( 0,0,0,0.1 );
            max - height: 200px;
            overflow - y: auto;
    }

        .wpcw - autocomplete - item {
            padding: 10px 12px;
            cursor: pointer;
            border - bottom: 1px solid #f0f0f0;
    }

        .wpcw - autocomplete - item:last - child {
            border - bottom: none;
    }

        .wpcw - autocomplete - item:hover {
            background: #f0f0f0;
    }
    `;
    document.head.appendChild( style );
}