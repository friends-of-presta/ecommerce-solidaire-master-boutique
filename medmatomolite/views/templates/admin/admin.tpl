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

<div id="chargement">
    <i class="{if $ps_version >= 1.6}process-icon-refresh icon-spin icon-pulse{else}fa fa-refresh fa-spin fa-pulse clear{/if}"></i> {l s='Loading...' mod='medmatomolite'}<span id="chargement-infos"></span>
</div>

<ps-panel>

    <center>

        <p>{l s='Tracking module and service generously offered by' mod='medmatomolite'} <a href="https://www.mediacom87.fr/?utm_source=module&utm_medium=cpc&utm_campaign={$name|escape:'htmlall':'UTF-8'}" target="_blank"><strong>Mediacom87</strong></a> {l s='as part of the #EcommerceSolidaire action.' mod='medmatomolite'}</p>

        <p>{l s='Register your site to take advantage of an offer to track the time of use of your shop' mod='medmatomolite'}</p>

        <p><a href="https://analytics.mediacom87.eu/" target="_blank"><img src="https://analytics.mediacom87.eu/misc/user/logo-header.png?matomo" height="50" class="img-responsive" /></a></p>

    </center>

</ps-panel>

<form class="form-horizontal" method="post" action="{$form_url|escape:'quotes':'UTF-8'}" id="medConfForm" enctype='multipart/form-data'>

    <ps-tabs position="left">

        <ps-tab label="{l s='Configuration' mod='medmatomolite'}" active="true" id="tab10" icon="icon-cogs" fa="cogs" panel="false">

            {include file="$tpl_path/views/templates/admin/configuration.tpl"}

        </ps-tab>

        <ps-tab label="{l s='Informations and support' mod='medmatomolite'}" id="tab20" icon="icon-info" fa="info" gap="true">

            {include file="$tpl_path/views/templates/admin/about.tpl"}

        </ps-tab>

        <ps-tab label="{l s='More Modules' mod='medmatomolite'}" id="tab25" icon="icon-cubes" fa="cubes">

            {include file="$tpl_path/views/templates/admin/modules.tpl"}

        </ps-tab>

        {if isset($addons_id) && $addons_id}

        <ps-tab label="{l s='Opinion' mod='medmatomolite'}" id="tab26" icon="icon-thumbs-up" fa="thumbs-up">

            {include file="$tpl_path/views/templates/admin/rate.tpl"}

        </ps-tab>

        {/if}

        <ps-tab label="{l s='License' mod='medmatomolite'}" id="tab30" icon="icon-legal" fa="legal" panel="false">

            {include file="$tpl_path/views/templates/admin/licence.tpl"}

        </ps-tab>

        <ps-tab label="Changelog" id="tab40" icon="icon-code" fa="code" panel="false">

            {include file="$tpl_path/views/templates/admin/changelog.tpl"}

        </ps-tab>

    </ps-tabs>

</form>
