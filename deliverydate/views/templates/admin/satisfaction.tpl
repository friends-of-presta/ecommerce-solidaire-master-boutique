<div class="panel panel-default">
		<h3>{l s='Are you satisfied about this module?' mod='deliverydate'}</h3>
		<div class="satisfied_text">
	  	<a class="btn btn-default not_satisfied"><i class="fa fa-frown-o"></i> {l s='NO' mod='deliverydate'}</a>
			<a class="btn btn-default satisfied"><i class="fa fa-smile-o"></i> {l s='YES' mod='deliverydate'}</a>
			<div class="clearfix"></div>
	  </div>
	</div>
	<div class="clearfix"></div>
<script type="text/javascript">
  $(".satisfied").click(function() {
		$(this).remove();
		$(".not_satisfied").remove();
		$(".satisfied_text").html("<a target=\'_blank\' href=\'http://addons.prestashop.com/fr/ratings.php\'>{l s='Happy to hear that! Please click here to leave a review on PrestaShop Addons. I would greatly appreciate!' mod='deliverydate'}</a>");
  });
  $(".not_satisfied").click(function() {
		$(this).remove();
		$(".satisfied").remove();
		$(".satisfied_text").html("<a target=\'_blank\' href=\'http://addons.prestashop.com/contact-community.php?id_product=16652\'>{l s='Sorry to hear that. Please click here to send me a message in order to look into your problem.' mod='deliverydate'}</a>");
	});
</script>
