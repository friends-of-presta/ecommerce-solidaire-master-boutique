/**
 * Copyright © Lyra Network.
 * This file is part of Lyra Collect plugin for PrestaShop. See COPYING.md for license details.
 *
 * @author    Lyra Network (https://www.lyra.com/)
 * @copyright Lyra Network
 * @license   https://opensource.org/licenses/afl-3.0.php Academic Free License (AFL 3.0)
 */

/**
 * Misc JavaScript functions.
 */

function lyraAddMultiOption(first) {
    if (first) {
        $('#lyra_multi_options_btn').hide();
        $('#lyra_multi_options_table').show();
    }

    var timestamp = new Date().getTime();

    var rowTpl = $('#lyra_multi_row_option').html();
    rowTpl = rowTpl.replace(/LYRA_MULTI_KEY/g, '' + timestamp);

    $(rowTpl).insertBefore('#lyra_multi_option_add');
}

function lyraDeleteMultiOption(key) {
    $('#lyra_multi_option_' + key).remove();

    if ($('#lyra_multi_options_table tbody tr').length === 1) {
        $('#lyra_multi_options_btn').show();
        $('#lyra_multi_options_table').hide();
        $('#lyra_multi_options_table').append("<input type=\"hidden\" id=\"LYRA_MULTI_OPTIONS\" name=\"LYRA_MULTI_OPTIONS\" value=\"\">");
    }
}

function lyraAddOneyOption(first, suffix = '') {
    if (first) {
        $('#lyra_oney' + suffix + '_options_btn').hide();
        $('#lyra_oney' + suffix + '_options_table').show();
    }

    var timestamp = new Date().getTime();
    var key = suffix != '' ? /LYRA_ONEY34_KEY/g : /LYRA_ONEY_KEY/g;
    var rowTpl = $('#lyra_oney' + suffix + '_row_option').html();
    rowTpl = rowTpl.replace(key, '' + timestamp);

    $(rowTpl).insertBefore('#lyra_oney' + suffix + '_option_add');
}

function lyraDeleteOneyOption(key, suffix = '') {
    $('#lyra_oney' + suffix + '_option_' + key).remove();

    if ($('#lyra_oney' + suffix + '_options_table tbody tr').length === 1) {
        $('#lyra_oney' + suffix + '_options_btn').show();
        $('#lyra_oney' + suffix + '_options_table').hide();
        $('#lyra_oney' + suffix + '_options_table').append("<input type=\"hidden\" id=\"LYRA_ONEY" + suffix + "_OPTIONS\" name=\"LYRA_ONEY" + suffix + "_OPTIONS\" value=\"\">");
    }
}

function lyraAdditionalOptionsToggle(legend) {
    var fieldset = $(legend).parent();

    $(legend).children('span').toggleClass('ui-icon-triangle-1-e ui-icon-triangle-1-s');
    fieldset.find('section').slideToggle();
}

function lyraCategoryTableVisibility() {
    var category = $('select#LYRA_COMMON_CATEGORY option:selected').val();

    if (category === 'CUSTOM_MAPPING') {
        $('.lyra_category_mapping').show();
        $('.lyra_category_mapping select').removeAttr('disabled');
    } else {
        $('.lyra_category_mapping').hide();
        $('.lyra_category_mapping select').attr('disabled', 'disabled');
    }
}

function lyraDeliveryTypeChanged(key) {
    var type = $('#LYRA_ONEY_SHIP_OPTIONS_' + key + '_type').val();

    if (type === 'RECLAIM_IN_SHOP') {
        $('#LYRA_ONEY_SHIP_OPTIONS_' + key + '_address').show();
        $('#LYRA_ONEY_SHIP_OPTIONS_' + key + '_zip').show();
        $('#LYRA_ONEY_SHIP_OPTIONS_' + key + '_city').show();
    } else {
        $('#LYRA_ONEY_SHIP_OPTIONS_' + key + '_address').val('');
        $('#LYRA_ONEY_SHIP_OPTIONS_' + key + '_zip').val('');
        $('#LYRA_ONEY_SHIP_OPTIONS_' + key + '_city').val('');

        $('#LYRA_ONEY_SHIP_OPTIONS_' + key + '_address').hide();
        $('#LYRA_ONEY_SHIP_OPTIONS_' + key + '_zip').hide();
        $('#LYRA_ONEY_SHIP_OPTIONS_' + key + '_city').hide();
    }

    var speed = $('#LYRA_ONEY_SHIP_OPTIONS_' + key + '_speed').val();
    if (speed === 'PRIORITY') {
        $('#LYRA_ONEY_SHIP_OPTIONS_' + key + '_delay').show();
    } else {
        $('#LYRA_ONEY_SHIP_OPTIONS_' + key + '_delay').hide();
    }
}

function lyraDeliverySpeedChanged(key) {
    var speed = $('#LYRA_ONEY_SHIP_OPTIONS_' + key + '_speed').val();
    var type = $('#LYRA_ONEY_SHIP_OPTIONS_' + key + '_type').val();

    if (speed === 'PRIORITY') {
        $('#LYRA_ONEY_SHIP_OPTIONS_' + key + '_delay').show();
    } else {
        $('#LYRA_ONEY_SHIP_OPTIONS_' + key + '_delay').hide();
    }
}

function lyraRedirectChanged() {
    var redirect = $('select#LYRA_REDIRECT_ENABLED option:selected').val();

    if (redirect === 'True') {
        $('#lyra_redirect_settings').show();
        $('#lyra_redirect_settings select, #lyra_redirect_settings input').removeAttr('disabled');
    } else {
        $('#lyra_redirect_settings').hide();
        $('#lyra_redirect_settings select, #lyra_redirect_settings input').attr('disabled', 'disabled');
    }
}

function lyraOneyEnableOptionsChanged() {
    var enable = $('select#LYRA_ONEY_ENABLE_OPTIONS option:selected').val();

    if (enable === 'True') {
        $('#lyra_oney_options_settings').show();
        $('#lyra_oney_options_settings select, #lyra_oney_options_settings input').removeAttr('disabled');
    } else {
        $('#lyra_oney_options_settings').hide();
        $('#lyra_oney_options_settings select, #lyra_oney_options_settings input').attr('disabled', 'disabled');
    }
}

function lyraFullcbEnableOptionsChanged() {
    var enable = $('select#LYRA_FULLCB_ENABLE_OPTS option:selected').val();

    if (enable === 'True') {
        $('#lyra_fullcb_options_settings').show();
        $('#lyra_fullcb_options_settings select, #lyra_fullcb_options_settings input').removeAttr('disabled');
    } else {
        $('#lyra_fullcb_options_settings').hide();
        $('#lyra_fullcb_options_settings select, #lyra_fullcb_options_settings input').attr('disabled', 'disabled');
    }
}

function lyraHideOtherLanguage(id, name) {
    $('.translatable-field').hide();
    $('.lang-' + id).css('display', 'inline');

    $('.translation-btn button span').text(name);

    var id_old_language = id_language;
    id_language = id;

    if (id_old_language !== id) {
        changeEmployeeLanguage();
    }
}

function lyraCardEntryChanged() {
    var cardDataMode = $('select#LYRA_STD_CARD_DATA_MODE option:selected').val();

    switch (cardDataMode) {
        case '4':
            $('#LYRA_REST_SETTINGS').hide();
            $('#LYRA_STD_CANCEL_IFRAME_MENU').show();
            break;
        case '5':
            $('#LYRA_REST_SETTINGS').show();
            $('#LYRA_STD_CANCEL_IFRAME_MENU').hide();
            break;
        default:
            $('#LYRA_REST_SETTINGS').hide();
            $('#LYRA_STD_CANCEL_IFRAME_MENU').hide();
    }
}

function lyraAddOtherPaymentMeansOption(first) {
    if (first) {
        $('#lyra_other_payment_means_options_btn').hide();
        $('#lyra_other_payment_means_options_table').show();
        $('#LYRA_OTHER_PAYMENT_MEANS').remove();
    }

    var timestamp = new Date().getTime();

    var rowTpl = $('#lyra_other_payment_means_row_option').html();
    rowTpl = rowTpl.replace(/LYRA_OTHER_PAYMENT_SCRIPT_MEANS_KEY/g, '' + timestamp);

    $(rowTpl).insertBefore('#lyra_other_payment_means_option_add');
}

function lyraDeleteOtherPaymentMeansOption(key) {
    $('#lyra_other_payment_means_option_' + key).remove();

    if ($('#lyra_other_payment_means_options_table tbody tr').length === 1) {
        $('#lyra_other_payment_means_options_btn').show();
        $('#lyra_other_payment_means_options_table').hide();
        $('#lyra_other_payment_means_options_table').append("<input type=\"hidden\" id=\"LYRA_OTHER_PAYMENT_MEANS\" name=\"LYRA_OTHER_PAYMENT_MEANS\" value=\"\">");
    }
}

function lyraCountriesRestrictMenuDisplay(retrictCountriesPaymentId) {
    var countryRestrict = $('#' + retrictCountriesPaymentId).val();
    if (countryRestrict === '2') {
        $('#' + retrictCountriesPaymentId + '_MENU').show();
    } else {
        $('#' + retrictCountriesPaymentId + '_MENU').hide();
    }
}

function lyraOneClickMenuDisplay() {
    var oneClickPayment = $('#LYRA_STD_1_CLICK_PAYMENT').val();
    if (oneClickPayment == 'True') {
        $('#LYRA_STD_1_CLICK_MENU').show();
    } else {
        $('#LYRA_STD_1_CLICK_MENU').hide();
    }
}

function lyraDisplayMultiSelect(selectId) {
    $('#' + selectId).show();
    $('#' + selectId).focus();
    $('#LABEL_' + selectId).hide();
}

function lyraDisplayLabel(selectId, clickMessage) {
    $('#' + selectId).hide();
    $('#LABEL_' + selectId).show();
    $('#LABEL_' + selectId).text(lyraGetLabelText(selectId, clickMessage));
}

function lyraGetLabelText(selectId, clickMessage) {
    var select = document.getElementById(selectId);
    var labelText = '', option;

    for (var i = 0, len = select.options.length; i < len; i++) {
        option = select.options[i];

        if (option.selected) {
            labelText += option.text + ', ';
        }
    }

    labelText = labelText.substring(0, labelText.length - 2);
    if (!labelText) {
        labelText = clickMessage;
    }

    return labelText;
}
