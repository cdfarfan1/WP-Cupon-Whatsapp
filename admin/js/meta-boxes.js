jQuery(document).ready(function($) {
    // Manejar el cambio de tabs
    $('.wpcw-tabs a').on('click', function(e) {
        e.preventDefault();
        
        var $tab = $(this);
        var $tabContainer = $tab.closest('.wpcw-meta-box-tabs');
        var target = $tab.attr('href');

        // Actualizar estado activo de las tabs
        $tabContainer.find('.wpcw-tabs li').removeClass('active');
        $tab.parent().addClass('active');

        // Mostrar el contenido de la tab seleccionada
        $tabContainer.find('.wpcw-tab-content').removeClass('active');
        $tabContainer.find(target).addClass('active');
    });
});
