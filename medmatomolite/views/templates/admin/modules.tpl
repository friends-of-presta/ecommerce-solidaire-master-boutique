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


<ps-alert-hint class="medAddonsMarket">

    <p>{l s='Do you like this module?' mod='medmatomolite'}</p>
    <p>{l s='Get other ones directly on' mod='medmatomolite'}</p>
    <p>{if isset($addons_id) && $addons_id}<a href="https://addons.prestashop.com/{$iso_code|escape:'htmlall':'UTF-8'}/2_community-developer?contributor=322" target="_blank" title="Prestashop Addons Market Place"><img src="{$img_path}prestashop-addons-logo.png" alt="Prestashop Addons Market Place" class="img-responsive" /></a>{else}<a href="https://www.prestatoolbox.{$iso_domain|escape:'htmlall':'UTF-8'}/1_mediacom87?utm_source=module&utm_medium=cpc&utm_campaign={$name|escape:'htmlall':'UTF-8'}" target="_blank" title="PrestaToolBox Market Place"><img src="{$img_path}prestatoolbox.png" alt="PrestaToolBox Market Place" class="img-responsive" /></a>{/if}</p>

</ps-alert-hint>


{if isset($json_modules) && $json_modules}
    {foreach from=$json_modules.products item=module name=foo}

    {if $smarty.foreach.foo.index % 3 == 0}<div class="row addons-products clearfix">{/if}

        <a href="{$module.url|escape:'html':'UTF-8'}" target="_blank" class="addons-link{if $ps_version < 1.6} ps15{/if}">
        <div class="col-lg-4{if $smarty.foreach.foo.index % 3 == 2} last-item{elseif $smarty.foreach.foo.index % 3 == 0} first-item{else} item{/if}">

            <img src="{$module.img|escape:'html':'UTF-8'}" class="pull-left" style="padding:0 5px 5px 0" />
            <h5>{$module.categoryName|escape:'html':'UTF-8'}</h5>
            <h4>{$module.displayName|escape:'html':'UTF-8'|stripslashes} <span class="price pull-right">{$module.price['EUR']|escape:'html':'UTF-8'} €</span></h4>

            <p>{$module.fullDescription|strip_tags|escape:'html':'UTF-8'|stripslashes}</p>

        </div>
        </a>

    {if $smarty.foreach.foo.index % 3 == 2 || $smarty.foreach.foo.last}</div>{/if}

    {/foreach}

{/if}
