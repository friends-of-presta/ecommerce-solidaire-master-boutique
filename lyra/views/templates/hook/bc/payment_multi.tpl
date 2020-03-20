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

{if {$lyra_multi_options|@count} == 1 AND ($lyra_multi_card_mode == 1)}
  <div class="payment_module lyra {$lyra_tag|escape:'html':'UTF-8'} multi">
    {foreach from=$lyra_multi_options key="key" item="option"}
      <a href="javascript: $('#lyra_opt').val('{$key|escape:'html':'UTF-8'}'); $('#lyra_multi').submit();"
         title="{l s='Click to pay in installments' mod='lyra'}">

        <img class="logo" src="{$lyra_logo|escape:'html':'UTF-8'}" alt="Lyra Collect" />{$lyra_title|escape:'html':'UTF-8'}
        ({$option.localized_label|escape:'html':'UTF-8'})

        <form action="{$link->getModuleLink('lyra', 'redirect', array(), true)|escape:'html':'UTF-8'}" method="post" id="lyra_multi">
          <input type="hidden" name="lyra_payment_type" value="multi" />
          <input type="hidden" name="lyra_opt" value="" id="lyra_opt" />
        </form>
      </a>
    {/foreach}
  </div>
{else}
  <div class="payment_module lyra {$lyra_tag|escape:'html':'UTF-8'} multi">
    <a class="unclickable" title="{l s='Click on a payment option to pay in installments' mod='lyra'}" href="javascript: void(0);">
      <img class="logo" src="{$lyra_logo|escape:'html':'UTF-8'}" alt="Lyra Collect" />{$lyra_title|escape:'html':'UTF-8'}

      <form action="{$link->getModuleLink('lyra', 'redirect', array(), true)|escape:'html':'UTF-8'}" method="post" id="lyra_multi">
        <input type="hidden" name="lyra_payment_type" value="multi" />
        <input type="hidden" name="lyra_opt" value="" id="lyra_opt" />

        <br />
        {if $lyra_multi_card_mode == 2}
          <p class="tip">{if $lyra_avail_cards|@count == 1}{l s='Payment Mean' mod='lyra'}{else}{l s='Choose your payment mean' mod='lyra'}{/if}</p>

          {assign var=first value=true}
          {foreach from=$lyra_avail_cards key="key" item="label"}
            <div style="display: inline-block;">
              {if $lyra_avail_cards|@count == 1}
                <input type="hidden" id="lyra_multi_card_type_{$key|escape:'html':'UTF-8'}" name="lyra_card_type" value="{$key|escape:'html':'UTF-8'}" >
              {else}
                <input type="radio" id="lyra_multi_card_type_{$key|escape:'html':'UTF-8'}" name="lyra_card_type" value="{$key|escape:'html':'UTF-8'}" style="vertical-align: middle;"{if $first == true} checked="checked"{/if} >
              {/if}

              <label for="lyra_multi_card_type_{$key|escape:'html':'UTF-8'}" class="lyra_card">
                {assign var=img_file value=$smarty.const._PS_MODULE_DIR_|cat:'lyra/views/img/':{$key|lower|escape:'html':'UTF-8'}:'.png'}

                {if file_exists($img_file)}
                  <img src="{$base_dir_ssl|escape:'html':'UTF-8'}modules/lyra/views/img/{$key|lower}.png"
                       alt="{$label|escape:'html':'UTF-8'}"
                       title="{$label|escape:'html':'UTF-8'}">
                {else}
                  <span>{$label|escape:'html':'UTF-8'}</span>
                {/if}
              </label>

              {assign var=first value=false}
            </div>
          {/foreach}
          <div style="margin-bottom: 12px;"></div>
        {/if}

        <p class="tip">{l s='Choose your payment option' mod='lyra'}</p>
        <ul>
          {foreach from=$lyra_multi_options key="key" item="option"}
            <li onclick="javascript: $('#lyra_opt').val('{$key|escape:'html':'UTF-8'}'); $('#lyra_multi').submit();">
              {$option.localized_label|escape:'html':'UTF-8'}
            </li>
          {/foreach}
        </ul>
      </form>
    </a>
  </div>
{/if}

{if version_compare($smarty.const._PS_VERSION_, '1.6', '>=')}
  </div></div>
{/if}