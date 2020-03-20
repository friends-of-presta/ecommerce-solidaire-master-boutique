{**
 * Copyright © Lyra Network.
 * This file is part of Lyra Collect plugin for PrestaShop. See COPYING.md for license details.
 *
 * @author    Lyra Network (https://www.lyra.com/)
 * @copyright Lyra Network
 * @license   https://opensource.org/licenses/afl-3.0.php Academic Free License (AFL 3.0)
 *}

{extends file='checkout/checkout.tpl'}
{block name='content'}
  <section id="content">
    <div class="row">
      <div class="col-md-8">
        <section id="lyra_content" class="checkout-step -current">
          <h1 class="step-title h3">
            <span class="step-number"></span>
            {l s='Redirection to payment gateway' mod='lyra'}
          </h1>

          <div class="content">
            <form action="{$lyra_url|escape:'html':'UTF-8'}" method="post" id="lyra_form" name="lyra_form" onsubmit="lyraDisablePayment();">
              {foreach from=$lyra_params key='key' item='value'}
                <input type="hidden" name="{$key|escape:'html':'UTF-8'}" value="{$value|escape:'html':'UTF-8'}" />
              {/foreach}

              <p>
                <img src="{$lyra_logo|escape:'html':'UTF-8'}" alt="Lyra Collect" style="margin-bottom: 5px" />
                <br />

                {l s='Please wait, you will be redirected to the payment gateway.' mod='lyra'}

                <br /> <br />
                {l s='If nothing happens in 10 seconds, please click the button below.' mod='lyra'}
                <br /><br />
              </p>

              <p class="cart_navigation clearfix">
                <button type="submit" id="lyra_submit_payment" class="button btn btn-default standard-checkout button-medium" >
                  <span>{l s='Pay' mod='lyra'}</span>
                </button>
              </p>
            </form>
          </div>
        </section>
      </div>

       <div class="col-md-4">
      </div>
    </div>

    <script type="text/javascript">
      function lyraDisablePayment() {
        document.getElementById('lyra_submit_payment').disabled = true;
      }

      function lyraSubmitForm() {
        document.getElementById('lyra_submit_payment').click();
      }

      if (window.addEventListener) { // for most browsers
        window.addEventListener('load', lyraSubmitForm, false);
      } else if (window.attachEvent) { // for IE 8 and earlier versions
        window.attachEvent('onload', lyraSubmitForm);
      }
    </script>
  </section>
{/block}