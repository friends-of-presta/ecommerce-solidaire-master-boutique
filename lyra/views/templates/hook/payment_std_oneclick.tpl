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

<section style="margin-bottom: 2rem;">
<div id="lyra_oneclick_payment_description">
  <ul id="lyra_oneclick_payment_description_1">
    <li>
      <span>{l s='You will pay with your registered means of payment' mod='lyra'}<b> {$lyra_saved_payment_mean|escape:'html':'UTF-8'}. </b>{l s='No data entry is needed.' mod='lyra'}</span>
    </li>

    <li style="margin: 8px 0px 8px;">
      <span>{l s='OR' mod='lyra'}</span>
    </li>

    <li>
      <a href="javascript: void(0);" onclick="lyraOneclickPaymentSelect(0)">{l s='Click here to pay with another means of payment.' mod='lyra'}</a>
    </li>
  </ul>
{if ($lyra_std_card_data_mode == '2')}
  </div>
    <script type="text/javascript">
      function lyraOneclickPaymentSelect(paymentByIdentifier) {
        if (paymentByIdentifier) {
          $("#lyra_oneclick_payment_description_1").show();
          $("#lyra_standard").hide();
          $("#lyra_payment_by_identifier").val("1");
        } else {
          $("#lyra_oneclick_payment_description_1").hide();
          $("#lyra_standard").show();
          $("#lyra_payment_by_identifier").val("0");
         }
       }
     </script>
{else}
    <ul id="lyra_oneclick_payment_description_2" style="display: none;">
      {if ($lyra_std_card_data_mode != '5') || $lyra_rest_popin}
        <li>{l s='You will enter payment data after order confirmation.' mod='lyra'}</li>
      {/if}

      <li style="margin: 8px 0px 8px;">
        <span>{l s='OR' mod='lyra'}</span>
      </li>
      <li>
        <a href="javascript: void(0);" onclick="lyraOneclickPaymentSelect(1)">{l s='Click here to pay with your registered means of payment.' mod='lyra'}</a>
      </li>
    </ul>
  </div>

  <script type="text/javascript">
    function lyraOneclickPaymentSelect(paymentByIdentifier) {
      if (paymentByIdentifier) {
        $("#lyra_oneclick_payment_description_1").show();
        $("#lyra_oneclick_payment_description_2").hide()
        $("#lyra_payment_by_identifier").val("1");
      } else {
        $("#lyra_oneclick_payment_description_1").hide();
        $("#lyra_oneclick_payment_description_2").show();
        $("#lyra_payment_by_identifier").val("0");
      }

      {if ($lyra_std_card_data_mode == '5')}
         lyraUpdateRestToken();
           setTimeout(function () {
             lyraInitRestEvents();
           }, 200);
      {/if}
    }

    function lyraUpdateRestToken() {
      KR.removeForms();

      if ($("#lyra_payment_by_identifier").val() == '1') {
        var token = "{$lyra_rest_identifier_token|escape:'html':'UTF-8'}";
      } else {
        var token = "{$lyra_rest_form_token|escape:'html':'UTF-8'}";
      }

      var isPopin = document.getElementsByClassName('kr-popin-button');
      if (isPopin.length !== 0) {
        var button =  '<button type="button" id="lyra_hidden_button" class="kr-payment-button"></button>';
      } else {
        var button = '<div style="display: none;">'
                   + '    <button type="button" id="lyra_hidden_button" class="kr-payment-button"></button>'
                   + '</div>';
      }

      $("#lyra_standard_rest_wrapper").html(
            '  <div class="lyra kr-embedded" {if $lyra_rest_popin} kr-popin{/if} kr-form-token="' + token + '" >'
            + '  <div class="kr-pan"></div>'
            + '  <div class="kr-expiry"></div>'
            + '  <div class="kr-security-code"></div>'

            + button

            + '  <div class="kr-field processing" style="display: none; border: none !important;">'
            + '      <div style="background-image: url({$smarty.const._MODULE_DIR_|escape:'html':'UTF-8'}lyra/views/img/loading_big.gif);'
            + '                  margin: 0 auto; display: block; height: 35px; background-color: #ffffff; background-position: center;'
            + '                  background-repeat: no-repeat; background-size: 35px;">'
            + '      </div>'
            + '  </div>'
            + '  <div class="kr-form-error"></div>'
            + '</div>');
    }
  </script>
{/if}
</section>

{block name='javascript_bottom'}
  {include file="_partials/javascript.tpl" javascript=$javascript.bottom}
{/block}

<script type="text/javascript">
$(document).ready(function() {
  $("input[data-module-name=lyra]").change(function() {
    if ($(this).is(':checked')) {
      lyraOneclickPaymentSelect(1);
    }
  });
});
</script>