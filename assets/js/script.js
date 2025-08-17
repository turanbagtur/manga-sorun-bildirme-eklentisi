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
        var submitButton = form.find('input[type="submit"]');
        var originalButtonText = submitButton.val();
        var formData = form.serialize() + '&action=sbm_sorun_bildir&nonce=' + sbm_ajax_obj.nonce;

        form.find('input, select, textarea, button').prop('disabled', true);
        form.find('.sbm-alert').remove();
        submitButton.val(sbm_ajax_obj.sending_text);

        $.post(sbm_ajax_obj.ajax_url, formData, function(response) {
            submitButton.val(originalButtonText);
            
            if (response.success) {
                form.prepend('<div class="sbm-alert sbm-success">' + sbm_ajax_obj.success_message + '</div>');
                form[0].reset();
            } else {
                form.find('input, select, textarea, button').prop('disabled', false);
                form.prepend('<div class="sbm-alert sbm-error">' + sbm_ajax_obj.error_message + '</div>');
            }
        });
    });
});