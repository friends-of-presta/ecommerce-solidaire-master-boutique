/**
 * Project : everpsseo
 * @author Team Ever
 * @copyright Team Ever
 * @license   Tous droits réservés / Le droit d'auteur s'applique (All rights reserved / French copyright law applies)
 * @link http://team-ever.com
 */
$(document).ready(function() {
    // Default settings
    var defaultType = $('#type').find('option:selected').val();
    if (defaultType == 'radio' || defaultType == 'select' || defaultType == 'checkbox') {
        $('#quantity').parent().parent().hide();
        $('#manage_quantity_on').parent().parent().parent().hide();
    } else {
        $('#quantity').parent().parent().show();
        $('#manage_quantity_on').parent().parent().parent().show();
    }
    // User actions
    $('label[for=manage_quantity_on]').click(function(){
        $('#quantity').parent().parent().slideDown();
    });
    $('label[for=manage_quantity_off]').click(function(){
        $('#quantity').parent().parent().slideUp();
    });
    $('#type').change(function() {
        var $option = $(this).find('option:selected');
        var value = $option.val();
        if (value == 'radio' || value == 'select' || value == 'checkbox') {
            $('#quantity').parent().parent().hide();
            $('#manage_quantity_on').parent().parent().parent().hide();
        } else {
            $('#quantity').parent().parent().show();
            $('#manage_quantity_on').parent().parent().parent().show();
        }
    });
});
