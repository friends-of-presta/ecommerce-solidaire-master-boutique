{**
 * Copyright © Lyra Network.
 * This file is part of Lyra Collect plugin for PrestaShop. See COPYING.md for license details.
 *
 * @author    Lyra Network (https://www.lyra.com/)
 * @copyright Lyra Network
 * @license   https://opensource.org/licenses/afl-3.0.php Academic Free License (AFL 3.0)
 *}

{capture name=path}Lyra Collect{/capture}
{if version_compare($smarty.const._PS_VERSION_, '1.6', '<')}
  {include file="$tpl_dir./breadcrumb.tpl"}
{/if}

  <h1>{l s='Redirection to payment gateway' mod='lyra'}</h1>

{assign var='current_step' value='payment'}
{include file="$tpl_dir./order-steps.tpl"}

<div id="lyra_content">
  <h3>{$lyra_title|escape:'html':'UTF-8'}</h3>

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

  {if version_compare($smarty.const._PS_VERSION_, '1.6', '<')}
    <p class="cart_navigation">
      <input type="submit" id="lyra_submit_payment" value="{l s='Pay' mod='lyra'}" class="exclusive" />
    </p>
  {else}
    <p class="cart_navigation clearfix">
      <button type="submit" id="lyra_submit_payment" class="button btn btn-default standard-checkout button-medium" >
        <span>{l s='Pay' mod='lyra'}</span>
      </button>
    </p>
  {/if}
  </form>
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
