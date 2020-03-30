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

<p>{l s='Thanks for installing this module on your website.' mod='medmatomolite'}</p>
<p>{$description|escape:'htmlall':'UTF-8'}</p>

{if isset($addons_id) && $addons_id}

<p>{l s='Developped by' mod='medmatomolite'} <strong>Mediacom87</strong>, {l s='which helps you to grow your business.' mod='medmatomolite'}</p>
<p>{l s='If you need support on this module:' mod='medmatomolite'} <a href="https://addons.prestashop.com/contact-community.php?id_product={$addons_id|escape:'htmlall':'UTF-8'}" class="btn btn-info" target="_blank"><i class="{if $ps_version >= 1.6}icon-envelope-alt{else}fa fa-envelope-o{/if}"></i> Support</a></p>

{else}

<p>{l s='Developped by' mod='medmatomolite'} <a class="redLink" href="https://www.mediacom87.fr/?utm_source=module&utm_medium=cpc&utm_campaign={$name|escape:'htmlall':'UTF-8'}" target="_blank"><strong>Mediacom87</strong></a>, {l s='which helps you to grow your business.' mod='medmatomolite'}</p>
<p>{l s='If you need support on this module:' mod='medmatomolite'} <a href="mailto:support@mediacom87.net?subject={l s='Need help on this module:' mod='medmatomolite'} {$name|escape:'htmlall':'UTF-8'} V.{$version|escape:'htmlall':'UTF-8'} - {if $tb_version}TB.{$tb_version|escape:'htmlall':'UTF-8'}{else}PS.{$ps_version|escape:'htmlall':'UTF-8'}{/if}" class="btn btn-info"><i class="{if $ps_version >= 1.6}icon-envelope-alt{else}fa fa-envelope-o{/if}"></i> Support</a></p>

{/if}

<p><b>Module:</b> {$version|escape:'htmlall':'UTF-8'}</p>
{if $tb_version}
<p><b>thirty bees:</b> {$tb_version|escape:'htmlall':'UTF-8'}</p>
{else}
<p><b>PrestaShop:</b> {$ps_version|escape:'htmlall':'UTF-8'}</p>
{/if}
<p><b>PHP :</b> {$php_version|escape:'htmlall':'UTF-8'}</p>
