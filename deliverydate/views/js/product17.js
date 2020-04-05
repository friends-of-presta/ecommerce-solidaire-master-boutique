function updateDeliveryDates(e) {
    var elem = $('#dd_carriers_list');
    if (!elem.length) {
        console.error('DeliveryDate: #dd_carriers_list not found. Make sure that the DisplayProductExtraContent hook is available in your theme.')
        return;
    }
    var qties = JSON.parse(elem.attr('data-qties'));
    var id_product_attribute = e.id_product_attribute || elem.attr('data-id-product-attribute');
    var isOutOfStock = !qties[id_product_attribute];
    var id = $('.deliverydate-content').attr('id');
    if (isOutOfStock && !+elem.attr('data-allow-oosp')) {
        $('[href="#'+id+'"]').parent().toggleClass('hidden', true);
        $('.dd_available, .dd_oot').toggleClass('hidden', true);
    } else {
        $('[href="#'+id+'"]').parent().toggleClass('hidden', false);
        $('.dd_available').toggleClass('hidden', isOutOfStock);
        $('.dd_oot').toggleClass('hidden', !isOutOfStock);
    }
}

prestashop.on('updatedProduct', updateDeliveryDates);
$(document).ready(updateDeliveryDates);
