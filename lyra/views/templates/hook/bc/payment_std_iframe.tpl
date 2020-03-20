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
{if $lyra_saved_identifier}
  <a class="unclickable lyra-standard-link" title="{l s='Choose pay with registred means of payment or enter payment information and click « Pay » button' mod='lyra'}">
    <img class="logo" src="{$lyra_logo|escape:'html':'UTF-8'}" alt="Lyra Collect" />{$lyra_title|escape:'html':'UTF-8'}
{else}
  <a href="javascript: void(0);" title="{l s='Click here to pay by credit card' mod='lyra'}" id="lyra_standard_link" class="lyra-standard-link">
    <img class="logo" src="{$lyra_logo|escape:'html':'UTF-8'}" alt="Lyra Collect" />{$lyra_title|escape:'html':'UTF-8'}
    <br />
{/if}

    {if $lyra_saved_identifier}
      {include file="./payment_std_oneclick.tpl"}
      <input id="lyra_payment_by_identifier" type="hidden" name="lyra_payment_by_identifier" value="1" />
    {/if}

    <iframe class="lyra-iframe" id="lyra_iframe" src="{$link->getModuleLink('lyra', 'iframe', ['content_only' => 1], true)|escape:'html':'UTF-8'}" style="display: none;">
    </iframe>

    {if $lyra_can_cancel_iframe}
        <button class="lyra-iframe" id="lyra_cancel_iframe" style="display: none;">{l s='< Cancel and return to payment choice' mod='lyra'}</button>
    {/if}
  </a>

  <script type="text/javascript">
    var done = false;
    function lyraShowIframe() {
      if (done) {
        return;
      }

      done = true;

      {if !$lyra_saved_identifier}
        $('#lyra_iframe').parent().addClass('unclickable');
      {/if}

      $('.lyra-iframe').show();
      $('#lyra_oneclick_payment_description').hide();

      var url = "{$link->getModuleLink('lyra', 'redirect', ['content_only' => 1], true)|escape:'url':'UTF-8'}";
      {if $lyra_saved_identifier}
            url = url + '&lyra_payment_by_identifier=' + $('#lyra_payment_by_identifier').val();
      {/if}

      $('#lyra_iframe').attr('src', decodeURIComponent(url) + '&' + Date.now());
    }

    function lyraHideIframe() {
      if (!done) {
        return;
      }

      done = false;

      {if !$lyra_saved_identifier}
        $('#lyra_iframe').parent().removeClass('unclickable');
      {/if}

      $('.lyra-iframe').hide();
      $('#lyra_oneclick_payment_description').show();

      var url = "{$link->getModuleLink('lyra', 'iframe', ['content_only' => 1], true)|escape:'url':'UTF-8'}";
      $('#lyra_iframe').attr('src', decodeURIComponent(url) + '&' + Date.now());
    }

    $(function() {
      $('#lyra_standard_link').click(lyraShowIframe);
      $('#lyra_cancel_iframe').click(function() {
        lyraHideIframe();
        return false;
      });

      $('.payment_module a:not(.lyra-standard-link)').click(lyraHideIframe);
    });
  </script>
</div>

{if version_compare($smarty.const._PS_VERSION_, '1.6', '>=')}
</div></div>
{/if}