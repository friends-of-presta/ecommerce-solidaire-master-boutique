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

  <div class="payment_module lyra lyra_choozeo {$lyra_tag|escape:'html':'UTF-8'}">
    {if {$lyra_choozeo_options|@count} == 1}
      <a href="javascript: $('#lyra_choozeo').submit();" title="{l s='Click here to pay with Choozeo' mod='lyra'}">
    {else}
      <a class="unclickable" title="{l s='Click on a payment option to pay with Choozeo' mod='lyra'}" href="javascript: void(0);">
    {/if}
        <img class="logo" src="{$lyra_logo|escape:'html':'UTF-8'}" alt="Lyra Collect" />{$lyra_title|escape:'html':'UTF-8'}

        <form action="{$link->getModuleLink('lyra', 'redirect', array(), true)|escape:'html':'UTF-8'}" method="post" id="lyra_choozeo">
          <input type="hidden" name="lyra_payment_type" value="choozeo" />
          <br />

          {foreach from=$lyra_choozeo_options key="key" item="option"}
            <label class="lyra_card_click" for="lyra_card_type_{$key|escape:'html':'UTF-8'}">
              <input type="radio"
                     name="lyra_card_type"
                     id="lyra_card_type_{$key|escape:'html':'UTF-8'}"
                     value="{$key|escape:'html':'UTF-8'}" />
              <img src="{$smarty.const._MODULE_DIR_|escape:'html':'UTF-8'}lyra/views/img/{$key|lower|escape:'html':'UTF-8'}.png"
                   alt="{$option|escape:'html':'UTF-8'}"
                   title="{$option|escape:'html':'UTF-8'}" />
            </label>
          {/foreach}
        </form>
      </a>
  </div>

  <script type="text/javascript">
  // <![CDATA[
    $('div.payment_module.lyra_choozeo a img').on('click', function(e) {
      $(this).parent().find('input').prop("checked", true); 
      $('#lyra_choozeo').submit();
    });
  // ]]>
  </script>

  {if {$lyra_choozeo_options|@count} == 1}
    <script type="text/javascript">
    // <![CDATA[
      $('div.payment_module.lyra_choozeo a').on('hover', function(e) {
        $('div.payment_module.lyra_choozeo a form .lyra_card_click img').toggleClass('hover');
      });
    // ]]>
    </script>
  {/if}

{if version_compare($smarty.const._PS_VERSION_, '1.6', '>=')}
  </div></div>
{/if}