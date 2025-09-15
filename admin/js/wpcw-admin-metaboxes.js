jQuery(document).ready(function($) {
    // Manejar clic en el botón de subir/seleccionar imagen
    $('#wpcw_upload_image_button').click(function(e) {
        e.preventDefault();
        var button = $(this);
        var imageIdInput = $('#wpcw_coupon_image_id');
        var imagePreview = $('#wpcw_coupon_image_preview');
        var removeButton = $('#wpcw_remove_image_button');

        var frame = wp.media({
            title: wpcw_metabox_opts.frameTitle, // Usar objeto localizado
            button: {
                text: wpcw_metabox_opts.buttonText // Usar objeto localizado
            },
            multiple: false // Solo permitir seleccionar una imagen
        });

        frame.on('select', function() {
            var attachment = frame.state().get('selection').first().toJSON();
            imageIdInput.val(attachment.id);
            imagePreview.html('<img src="' + attachment.sizes.thumbnail.url + '" alt="' + wpcw_metabox_opts.imageAlt + '" style="max-width:150px; height:auto;"/>');
            removeButton.show(); // Mostrar el botón de quitar imagen
        });

        frame.open();
    });

    // Manejar clic en el botón de quitar imagen
    $('body').on('click', '#wpcw_remove_image_button', function(e) {
        e.preventDefault();
        $('#wpcw_coupon_image_id').val('');
        $('#wpcw_coupon_image_preview').html('');
        $(this).hide(); // Ocultar el botón de quitar
    });
});
