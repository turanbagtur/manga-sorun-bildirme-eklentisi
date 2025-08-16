jQuery(document).ready(function($) {
    // Modal açma/kapatma
    $('#sbm-open-form').on('click', function(e) {
        e.preventDefault();
        $('#sbm-form-modal').fadeIn();
    });

    $('#sbm-close-form').on('click', function(e) {
        e.preventDefault();
        $('#sbm-form-modal').fadeOut();
    });
    
    $(window).on('click', function(e) {
        if ($(e.target).is('#sbm-form-modal')) {
            $('#sbm-form-modal').fadeOut();
        }
    });

    // Form gönderimini ele alma
    $('#sbm-sorun-formu').on('submit', function(e) {
        e.preventDefault();

        var form = $(this);
        var formData = form.serialize() + '&action=sbm_sorun_bildir&nonce=' + sbm_ajax_obj.nonce;

        form.find('input, select, textarea, button').prop('disabled', true);
        form.find('.sbm-alert').remove();
        form.prepend('<div class="sbm-loading">Lütfen bekleyin...</div>');

        $.post(sbm_ajax_obj.ajax_url, formData, function(response) {
            form.find('.sbm-loading').remove();
            
            if (response.success) {
                form.prepend('<div class="sbm-alert sbm-success">' + response.data + '</div>');
                form[0].reset();
            } else {
                form.find('input, select, textarea, button').prop('disabled', false);
                form.prepend('<div class="sbm-alert sbm-error">' + response.data + '</div>');
            }
        });
    });
});