{**
 * Copyright © Lyra Network.
 * This file is part of Lyra Collect plugin for PrestaShop. See COPYING.md for license details.
 *
 * @author    Lyra Network (https://www.lyra.com/)
 * @copyright Lyra Network
 * @license   https://opensource.org/licenses/afl-3.0.php Academic Free License (AFL 3.0)
 *}

{if version_compare($smarty.const._PS_VERSION_, '1.6', '>=')}
  <div class="row"><div class="col-xs-12{if version_compare($smarty.const._PS_VERSION_, '1.6.0.11', '<')} col-md-6{/if}">
{/if}

<div class="payment_module lyra {$lyra_tag|escape:'html':'UTF-8'}">
  <a href="javascript: $('#lyra_other_{$lyra_other_payment_code|escape:'html':'UTF-8'}').submit();" title="{l s='Click here to pay with %s' sprintf=$lyra_other_payment_label mod='lyra'}">
    <img class="logo" src="{$lyra_logo|escape:'html':'UTF-8'}" alt="Lyra Collect" />{$lyra_title|escape:'html':'UTF-8'}

    <form action="{$link->getModuleLink('lyra', 'redirect', array(), true)|escape:'html':'UTF-8'}" method="post" id="lyra_other_{$lyra_other_payment_code|escape:'html':'UTF-8'}">
        <input type="hidden" name="lyra_payment_type" value="other">
        <input type="hidden" name="lyra_payment_code" value="{$lyra_other_payment_code|escape:'html':'UTF-8'}">
        <input type="hidden" name="lyra_payment_title" value="{$lyra_title|escape:'html':'UTF-8'}">
    </form>
  </a>
</div>

{if version_compare($smarty.const._PS_VERSION_, '1.6', '>=')}
  </div></div>
{/if}