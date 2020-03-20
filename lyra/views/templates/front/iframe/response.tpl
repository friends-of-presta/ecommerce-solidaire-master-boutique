{**
 * Copyright © Lyra Network.
 * This file is part of Lyra Collect plugin for PrestaShop. See COPYING.md for license details.
 *
 * @author    Lyra Network (https://www.lyra-network.com/)
 * @copyright Lyra Network
 * @license   https://opensource.org/licenses/afl-3.0.php Academic Free License (AFL 3.0)
 *}

{if version_compare($smarty.const._PS_VERSION_, '1.7', '>=')}
  {include file="module:lyra/views/templates/front/iframe/loader.tpl"}
{else}
  {include file="./loader.tpl"}
{/if}

<script type="text/javascript">
  var url = decodeURIComponent("{$lyra_url|escape:'url':'UTF-8'}");

  if (window.top) {
    window.top.location = url;
  } else {
    window.location = url;
  }
</script>
