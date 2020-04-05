function delivery_date_encode(text) {
    return $('<div />').text(text).html();
}

function changeDeliveryDate()
{
    id = parseInt($('input[name="id_carrier"]:checked, input[name^="delivery_option"]:checked').val());
    if (deliveries[id])
    {
        if (deliveries[id]['start'] == deliveries[id]['end'])
            text = delivery_date_encode(delivery_text);
        else
            text = delivery_date_encode(delivery_text_between);

        text = text.replace('[start]', '<b>'+delivery_date_encode(deliveries[id]['start'])+'</b>');
        text = text.replace('[end]', '<b>'+delivery_date_encode(deliveries[id]['end'])+'</b>');
        text = text.replace('[name]', delivery_date_encode(deliveries[id]['name']));
        $('.delivery_date').html(text).slideDown();
    }
    else
        $('.delivery_date').slideUp();
}

function moveDates()
{
    delivery_date_block = $('.delivery_date').first().clone();
    $('.delivery_date').remove();
    $('.delivery_options_address, table#carrierTable').after(delivery_date_block); // 1.5/1.6
    $('.delivery-options').append(delivery_date_block); // 1.7
}

function moveReason()
{
    delivery_reason_block = $('.delivery_reason').first().clone();
    $('.delivery_reason').remove();
    $('.delivery_options_address, table#carrierTable').after(delivery_reason_block); // 1.5/1.6
    $('.delivery-options').append(delivery_reason_block); // 1.7
}

$(document).ready(function() {

    if ((typeof opc == 'undefined' || !+opc) && typeof deliveries !== 'undefined')
    {
        $('input[name="id_carrier"]:visible, input[name^="delivery_option"]').change(function() {
            changeDeliveryDate();
        });
    }
});
