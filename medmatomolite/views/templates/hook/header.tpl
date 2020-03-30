{*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade this module to newer
* versions in the future. If you wish to customize this module for your
* needs please refer to http://doc.prestashop.com/display/PS15/Overriding+default+behaviors
* #Overridingdefaultbehaviors-Overridingamodule%27sbehavior for more information.
*
* @author Mediacom87 <support@mediacom87.net>
* @copyright  Mediacom87
* @license    commercial license see tab in the module
*}

{if isset($config.matomo_url) && $config.matomo_url
    && isset($config.matomo_siteId) && $config.matomo_siteId}
<!-- Matomo -->
<script type="text/javascript">
  var _paq = window._paq || [];
  var u="{$config.matomo_url|escape:'html':'UTF-8'}";
  var siteId = '{$config.matomo_siteId|escape:'html':'UTF-8'}'
  {literal}
  /* tracker methods like "setCustomDimension" should be called before "trackPageView" */
  _paq.push(["setDocumentTitle", document.domain + "/" + document.title]);
  _paq.push(['trackPageView']);
  _paq.push(['enableLinkTracking']);
  (function() {
    _paq.push(['setTrackerUrl', u+'matomo.php']);
    _paq.push(['setSiteId', siteId]);
    var d=document, g=d.createElement('script'), s=d.getElementsByTagName('script')[0];
    g.type='text/javascript'; g.async=true; g.defer=true; g.src=u+'matomo.js'; s.parentNode.insertBefore(g,s);
  })();
  {/literal}
</script>
<noscript><p><img src="{$config.matomo_url|escape:'html':'UTF-8'}matomo.php?idsite={$config.matomo_siteId|escape:'html':'UTF-8'}&amp;rec=1" style="border:0;" alt="" /></p></noscript>
<!-- End Matomo Code -->
{/if}