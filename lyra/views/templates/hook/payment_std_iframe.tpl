{**
 * Copyright © Lyra Network.
 * This file is part of Lyra Collect plugin for PrestaShop. See COPYING.md for license details.
 *
 * @author    Lyra Network (https://www.lyra.com/)
 * @copyright Lyra Network
 * @license   https://opensource.org/licenses/afl-3.0.php Academic Free License (AFL 3.0)
 *}

<!-- This meta tag is mandatory to avoid encoding problems caused by \PrestaShop\PrestaShop\Core\Payment\PaymentOptionFormDecorator -->
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />

<section style="margin-top: -12px;">
  <iframe class="lyra-iframe" id="lyra_iframe" src="{$link->getModuleLink('lyra', 'iframe', array(), true)|escape:'html':'UTF-8'}" style="display: none;">
  </iframe>

   {if $lyra_can_cancel_iframe}
       <a id="lyra_cancel_iframe" class="lyra-iframe" style="margin-bottom: 8px; display: none;" href="javascript:lyraInit();">
           {l s='< Cancel and return to payment choice' mod='lyra'}
       </a>
   {/if}
</section>
<br />

<script type="text/javascript">
  var lyraSubmit = function(e) {
    e.preventDefault();

    if (!$('#lyra_standard').data('submitted')) {
      $('#lyra_standard').data('submitted', true);
      $('#payment-confirmation button').attr('disabled', 'disabled');
      $('.lyra-iframe').show();
      $('#lyra_oneclick_payment_description').hide();

      var url = decodeURIComponent("{$link->getModuleLink('lyra', 'redirect', ['content_only' => 1], true)|escape:'url':'UTF-8'}") + '&' + Date.now();
      {if $lyra_saved_identifier}
        url = url + '&lyra_payment_by_identifier=' + $('#lyra_payment_by_identifier').val();
      {/if}

      $('#lyra_iframe').attr('src', url);
    }

    return false;
  }

  setTimeout(function() {
    $('input[type="radio"][name="payment-option"]').change(function() {
      lyraInit();
    });
  }, 0);

  function lyraInit() {
    if (!$('#lyra_standard').data('submitted')) {
      return;
    }

    $('#lyra_standard').data('submitted', false);
    $('#payment-confirmation button').removeAttr('disabled');
    $('.lyra-iframe').hide();
    $('#lyra_oneclick_payment_description').show();

    var url = decodeURIComponent("{$link->getModuleLink('lyra', 'iframe', ['content_only' => 1], true)|escape:'url':'UTF-8'}") + '&' + Date.now();
    $('#lyra_iframe').attr('src', url);
  }
</script>