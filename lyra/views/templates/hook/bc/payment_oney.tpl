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

{if {$lyra_oney_options|@count} == 0}
  <div class="payment_module lyra {$lyra_tag|escape:'html':'UTF-8'}">
    <a href="javascript: $('#lyra_oney{$suffix|escape:'html':'UTF-8'}').submit();" title={$title|escape:'html':'UTF-8'}>
      <img class="logo" src="{$lyra_logo|escape:'html':'UTF-8'}" alt="Lyra Collect" />{$lyra_title|escape:'html':'UTF-8'}

      <form action="{$link->getModuleLink('lyra', 'redirect', array(), true)|escape:'html':'UTF-8'}" method="post" id="lyra_oney{$suffix|escape:'html':'UTF-8'}">
        <input type="hidden" name="lyra_payment_type" value="oney{$suffix|escape:'html':'UTF-8'}" />
      </form>
    </a>
  </div>
{else}
  <div class="payment_module lyra {$lyra_tag|escape:'html':'UTF-8'}">
    <a class="unclickable" title="{l s='Choose a payment option and click « Pay » button' mod='lyra'}" href="javascript: void(0);">
      <img class="logo" src="{$lyra_logo|escape:'html':'UTF-8'}" alt="Lyra Collect" />{$lyra_title|escape:'html':'UTF-8'}

      <form action="{$link->getModuleLink('lyra', 'redirect', array(), true)|escape:'html':'UTF-8'}" method="post" id="lyra_oney{$suffix|escape:'html':'UTF-8'}">
        <input type="hidden" name="lyra_payment_type" value="oney{$suffix|escape:'html':'UTF-8'}" />

        <br />
        {assign var=first value=true}
        {foreach from=$lyra_oney_options key="key" item="option"}
          <div style="padding-bottom: 5px;">
            {if $lyra_oney_options|@count == 1}
              <input type="hidden" id="lyra_oney{$suffix|escape:'html':'UTF-8'}_option_{$key|escape:'html':'UTF-8'}" name="lyra_oney{$suffix|escape:'html':'UTF-8'}_option" value="{$key|escape:'html':'UTF-8'}" >
            {else}
              <input type="radio"
                     id="lyra_oney{$suffix|escape:'html':'UTF-8'}_option_{$key|escape:'html':'UTF-8'}"
                     name="lyra_oney{$suffix|escape:'html':'UTF-8'}_option"
                     value="{$key|escape:'html':'UTF-8'}"
                     style="vertical-align: middle;"
                     {if $first == true} checked="checked"{/if}
                     onclick="javascript: $('.lyra_oney{$suffix|escape:'html':'UTF-8'}_review').hide(); $('#lyra_oney{$suffix|escape:'html':'UTF-8'}_review_{$key|escape:'html':'UTF-8'}').show();">
            {/if}

            <label for="lyra_oney{$suffix|escape:'html':'UTF-8'}_option_{$key|escape:'html':'UTF-8'}" style="display: inline;">
              <span style="vertical-align: middle;">{$option.localized_label|escape:'html':'UTF-8'}</span>
            </label>

            <table class="lyra_oney{$suffix|escape:'html':'UTF-8'}_review lyra_review" id="lyra_oney{$suffix|escape:'html':'UTF-8'}_review_{$key|escape:'html':'UTF-8'}" {if $first != true} style="display: none;"{/if}>
              <thead>
                <tr>
                  <th>{l s='Your order total :' mod='lyra'} {$option.order_total|escape:'html':'UTF-8'}</th>
                  <th>{l s='Debit dates' mod='lyra'}</th>
                </tr>
              </thead>

              <tbody>
                <tr>
                  <td>{l s='Contribution :' mod='lyra'} {$option.first_payment|escape:'html':'UTF-8'}</td>
                  <td><strong>{$smarty.now|date_format:'%d/%m/%Y'|escape:'html':'UTF-8'}</strong></td>
                </tr>
                <tr>
                  <td>{{l s='Followed by %s payments' mod='lyra'}|sprintf:{$option.funding_count|escape:'html':'UTF-8'}}</td>
                  <td></td>
                </tr>
                {section name=row start=1 loop=$option.count step=1}
                  {assign var=i value={$smarty.section.row.index|intval}}
                  <tr>
                    <td>&nbsp;- {{l s='Payment %s :' mod='lyra'}|sprintf:$i} {$option.monthly_payment|escape:'html':'UTF-8'}</td>
                    <td><strong>{"+{$i|escape:'html':'UTF-8'} months"|date_format:'%d/%m/%Y'}</strong></td>
                  </tr>
                {/section}
                <tr>
                  <td colspan="2">{l s='Total cost of credit :' mod='lyra'} {$option.funding_fees|escape:'html':'UTF-8'}</td>
                </tr>
                <!-- <tr>
                  <td colspan="2" class="small">{{l s='Funding of %s with a fixed APR of %s %%.' mod='lyra'}|sprintf:{$option.funding_total|escape:'html':'UTF-8'}:{$option.taeg|escape:'html':'UTF-8'}}</td>
                </tr> -->
              </tbody>
          </table>

          {assign var=first value=false}
          </div>
        {/foreach}

        {if version_compare($smarty.const._PS_VERSION_, '1.6', '<')}
          <input type="submit" name="submit" value="{l s='Pay' mod='lyra'}" class="button" />
        {else}
          <button type="submit" name="submit" class="button btn btn-default standard-checkout button-medium" >
            <span>{l s='Pay' mod='lyra'}</span>
          </button>
        {/if}
      </form>
    </a>
  </div>
{/if}

{if version_compare($smarty.const._PS_VERSION_, '1.6', '>=')}
  </div></div>
{/if}