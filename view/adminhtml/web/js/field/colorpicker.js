define(['jquery','jscolor'], function($) {
    return function(htmlId, id, renderer, parameterName, data, index, isModal = false, modal = null) {
        $(window).on('mageos_row_modal_edit_'  + htmlId, function(event, data) {
            $(data.modal).find('[data-type="colorpicker"]').each(function() {
                let value = $(this).attr('value');
                let defaultColor = $(this).attr('data-default') || '#FFFFFF';
                new JSColor(this, {
                    value: value ? value : defaultColor,
                    format:'rgba',
                    required: false
                });
            });
        });
    }
});
