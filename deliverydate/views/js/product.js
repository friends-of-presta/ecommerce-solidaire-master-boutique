
function toggleDeliveryDates(qty)
{
	if (!allowBuyWhenOutOfStock && qty == 0 && stock_management)
	{
		$('#dd_carriers_list').parent().addClass('hidden');
		$('p.dd_available, p.dd_oot').addClass('hidden');
	}
	else
	{
		$('#dd_carriers_list').parent().removeClass('hidden');
		$('p.dd_available, p.dd_oot').removeClass('hidden');
	}

	if (qty > 0 || !stock_management)
	{
		$('.dd_available').removeClass('hidden');
		$('.dd_oot').addClass('hidden');
	}
	else
	{
		$('.dd_available').addClass('hidden');
		$('.dd_oot').removeClass('hidden');
	}
}

$(document).ready(function()
{
	if (typeof(quantityAvailable) == 'undefined')
		quantityAvailable = 1;

	if (typeof(combinations) != 'undefined' && combinations.length)
	{
		$('#buy_block input, #buy_block select').change(function() {
			setTimeout(function(){
				toggleDeliveryDates(quantityAvailable)
			}, 100);
		});

		$('#color_to_pick_list a').click(function() {
			setTimeout(function(){
				toggleDeliveryDates(quantityAvailable);
			}, 100);
		});
	}

	toggleDeliveryDates(quantityAvailable);

});
