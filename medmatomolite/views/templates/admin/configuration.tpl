{*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade this module to newer
* versions in the future. If you wish to customize this module for your
* needs please refer to http://doc.prestashop.com/display/PS15/Overriding+default+behaviors
* #Overridingdefaultbehaviors-Overridingamodule%27sbehavior for more information.
*
*   @author Mediacom87 <support@mediacom87.net>
*   @copyright  Mediacom87
*   @license    commercial license see tab in the module
*}


<ps-panel header="{l s='Tracking code' mod='medmatomolite'}">

    <ps-input-text name="matomo_url" label="{l s='Tracking Url' mod='medmatomolite'}" help="{l s='You must enter the Matomo installation url for the follow-up of your shop, this url is presented in the script transmitted by Matomo as in the example below. This url must have http(s) at the beginning and a / at the end.' mod='medmatomolite'}" size="70" value="{if isset($config.matomo_url) && $config.matomo_url}{$config.matomo_url|escape:'html':'UTF-8'}{/if}"></ps-input-text>

    <ps-input-text name="matomo_siteId" label="{l s='Site ID' mod='medmatomolite'}" help="{l s='The identifier was also communicated to you in the Matomo script as in the example below.' mod='medmatomolite'}" size="70" value="{if isset($config.matomo_siteId) && $config.matomo_siteId}{$config.matomo_siteId|escape:'html':'UTF-8'}{/if}"></ps-input-text>

    <h3>{l s='Matomo code example' mod='medmatomolite'}</h3>
    <img src="{$img_path}matomo-example.png" alt="Matomo" class="img-responsive" />

    <ps-panel-footer>

        <ps-panel-footer-submit title="{l s='Save' mod='medmatomolite'}" icon="process-icon-save" fa="floppy-o" direction="left" name="saveconf"></ps-panel-footer-submit>

    </ps-panel-footer>

</ps-panel>
