<ps-tabs position="left">

    <ps-tab label="{l s='Settings' mod='deliverydate'}" active="{if !$dd_tab || !in_array($dd_tab, array('rules', 'exceptions'))}true{else}false{/if}" id="dd-settings" icon="icon-AdminTools" img="../img/t/AdminTools.gif">

        <form class="form-horizontal" action="{$current_url|escape:'quotes':'UTF-8'}" method="post">

            <input type="hidden" value="save_settings" name="action">

            <ps-input-text name="prep_time" label="{l s='Preparation time' mod='deliverydate'}" help="{l s='How many days do you need to prepare an order?' mod='deliverydate'}" size="10" value="{if isset($config.DELIVERYDATE_PREP_TIME)}{$config.DELIVERYDATE_PREP_TIME|intval}{/if}" required-input="true" suffix="{l s='day(s)' mod='deliverydate'}" fixed-width="xs"></ps-input-text>

            <ps-input-text name="outofstock" label="{l s='Out of stock' mod='deliverydate'}" help="{l s='How many days to add if a product is out of stock? You can also set a value per supplier. Go in Catalog > Suppliers and edit the wanted supplier(s).' mod='deliverydate'}" size="10" value="{if isset($config.DELIVERYDATE_OUT_OF_STOCK)}{$config.DELIVERYDATE_OUT_OF_STOCK|intval}{/if}" required-input="true" suffix="{l s='day(s)' mod='deliverydate'}" fixed-width="xs"></ps-input-text>

            <ps-switch name="max_date" label="{l s='Date max only' mod='deliverydate'}" yes="{l s='Yes' mod='deliverydate'}" no="{l s='No' mod='deliverydate'}" active="{if isset($config.DELIVERYDATE_DATE_MAX) && $config.DELIVERYDATE_DATE_MAX}true{else}false{/if}" help="{l s='If you only want to add the out of stock delay on the max date and not the min date.' mod='deliverydate'}"></ps-switch>

            <ps-checkboxes label="{l s='Preparation days' mod='deliverydate'}" help="{l s='Which days do you prepare your orders?' mod='deliverydate'}">
                <ps-checkbox name="monday" value="1" {if isset($days.0) && $days.0 == 1}checked="true"{/if}>{l s='Monday' mod='deliverydate'}</ps-checkbox>
                <ps-checkbox name="tuesday" value="1" {if isset($days.1) && $days.1 == 1}checked="true"{/if}>{l s='Tuesday' mod='deliverydate'}</ps-checkbox>
                <ps-checkbox name="wednesday" value="1" {if isset($days.2) && $days.2 == 1}checked="true"{/if}>{l s='Wednesday' mod='deliverydate'}</ps-checkbox>
                <ps-checkbox name="thursday" value="1" {if isset($days.3) && $days.3 == 1}checked="true"{/if}>{l s='Thursday' mod='deliverydate'}</ps-checkbox>
                <ps-checkbox name="friday" value="1" {if isset($days.4) && $days.4 == 1}checked="true"{/if}>{l s='Friday' mod='deliverydate'}</ps-checkbox>
                <ps-checkbox name="saturday" value="1" {if isset($days.5) && $days.5 == 1}checked="true"{/if}>{l s='Saturday' mod='deliverydate'}</ps-checkbox>
                <ps-checkbox name="sunday" value="1" {if isset($days.6) && $days.6 == 1}checked="true"{/if}>{l s='Sunday' mod='deliverydate'}</ps-checkbox>
            </ps-checkboxes>

            <ps-input-text-lang name="reason" label="{l s='Message' mod='deliverydate'}" help="{l s='If you need to add a special message with the delivery date in the checkout process' mod='deliverydate'}" size="60" active-lang="{$active_lang|intval}" col-lg="5" >
                {foreach from=$module_languages item="lang"}
                    <div data-is="ps-input-text-lang-value" iso-lang="{$lang.iso_code|escape:'htmlall':'UTF-8'}" id-lang="{$lang.id_lang|intval}" lang-name="{$lang.name|escape:'htmlall':'UTF-8'}" value="{if is_array($config.DELIVERYDATE_REASON)}{$config.DELIVERYDATE_REASON[$lang.id_lang]|escape:'htmlall':'UTF-8'}{/if}"></div>
                {/foreach}
            </ps-input-text-lang>

            <ps-switch name="product" label="{l s='Product page' mod='deliverydate'}" yes="{l s='Yes' mod='deliverydate'}" no="{l s='No' mod='deliverydate'}" active="{if isset($config.DELIVERYDATE_PRODUCT) && $config.DELIVERYDATE_PRODUCT}true{else}false{/if}" help="{l s='If you want to display delivery dates on product pages' mod='deliverydate'}"></ps-switch>

            {if !$ps17}
                <ps-switch name="tabs" label="{l s='Tabs' mod='deliverydate'}" yes="{l s='Yes' mod='deliverydate'}" no="{l s='No' mod='deliverydate'}" active="{if isset($config.DELIVERYDATE_TABS) && $config.DELIVERYDATE_TABS}true{else}false{/if}" help="{l s='If your theme uses tabs on product sheets, activate this option.' mod='deliverydate'}"></ps-switch>
            {/if}

            <ps-form-group label="{l s='Cart position' mod='deliverydate'}">
                <select name="position" class="fixed-width-xxl">
                    <option value="bottom" {if isset($config.DELIVERYDATE_POSITION) && $config.DELIVERYDATE_POSITION != 'top'}selected="selected"{/if}>{l s='After carriers list' mod='deliverydate'}</option>
                    <option value="top" {if isset($config.DELIVERYDATE_POSITION) && $config.DELIVERYDATE_POSITION == 'top'}selected="selected"{/if}>{l s='Before carriers list' mod='deliverydate'}</option>
                </select>
            </ps-form-group>

            <ps-panel-footer>
                <ps-panel-footer-submit title="{l s='Save' mod='deliverydate'}" icon="process-icon-save" direction="right" name="saveSettings" img="{$module_dir|escape:'quotes':'UTF-8'}views/img/process-icon-save.png"></ps-panel-footer-submit>
            </ps-panel-footer>

        </form>

    </ps-tab>

    <ps-tab label="{l s='Rules' mod='deliverydate'}" id="dd-dates" icon="icon-list" img="../img/admin/date.png" panel="false" {if $dd_tab && $dd_tab == 'rules'}active="true"{/if}>
        <ps-table header="{l s='Rules' mod='deliverydate'}" icon="icon-list" content="{$data_rules|replace:'{':'\{'|replace:'}':'\}'|escape:'htmlall':'UTF-8'}" no-items-text="{l s='No rules found' mod='deliverydate'}"></ps-table>
    </ps-tab>

    <ps-tab label="{l s='Exceptions' mod='deliverydate'}" id="dd-exceptions" icon="icon-calendar-o" panel="false" {if $dd_tab && $dd_tab == 'exceptions'}active="true"{/if}  img="../img/t/AdminAttributesGroups.gif">
        <ps-table header="{l s='Exceptions' mod='deliverydate'}" icon="icon-calendar-o" content="{$data_exceptions|replace:'{':'\{'|replace:'}':'\}'|escape:'htmlall':'UTF-8'}" no-items-text="{l s='No exceptions found' mod='deliverydate'}"></ps-table>
    </ps-tab>

    {if $modules}
        <ps-tab id="dd-modules" panel="false" label="{l s='Modules' mod='deliverydate'}" icon="icon-AdminParentModules">

            <ps-panel header="{l s='Modules' mod='deliverydate'}" icon="icon-AdminParentModules">
                <table id="module-list" class="table row">
                    <tbody>
                        {foreach from=$modules item="module"}
                            {if $module->product_type != 'email'}
                                <tr>
                                    <td class="module_inactive text-center"></td>
                                    <td class="fixed-width-xs">
                                        <img width="57" src="{$module->img|escape:'quotes':'UTF-8'}" />
                                    </td>
                                    <td>
                                        <div class="text-muted">{$module->categoryName|escape:'htmlall':'UTF-8'}</div>
                                        <div class="module_name">
                                            {$module->displayName|escape:'htmlall':'UTF-8'}
                                            <small class="text-muted">v{$module->version|escape:'htmlall':'UTF-8'}</small>
                                        </div>
                                        <p class="module_description">{$module->fullDescription|truncate:140|escape:'quotes':'UTF-8'}</p>
                                    </td>
                                    <td class="actions">
                                        <div class="btn-group-action">
                                            <div class="btn-group pull-right">
                                                <a class="btn btn-default _blank" href="{$module->url|escape:'quotes':'UTF-8'}">
                                                    <i class="icon-chevron-right"></i> {l s='Discover' mod='deliverydate'}
                                                </a>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            {/if}
                        {/foreach}
                    </tbody>
                </table>
            </ps-panel>
            <ps-panel header="{l s='Email templates' mod='deliverydate'}" icon="icon-envelope">
                <ps-alert-hint>{l s='Customize all your automatic e-mails with your store’s design! These emails templates are responsive to ensure an optimal open rate on all devices, and their colors and links are fully customizable.' mod='deliverydate'}</ps-alert-hint>
                {foreach from=$modules item="module"}
                    {if $module->product_type == 'email'}
                        <div class="col-lg-6 col-md-6 col-sm-12">
                            <div class="panel">
                                <a href="{$module->url|escape:'quotes':'UTF-8'}" target="_blank">
                                    <img width="100%" src="{$module->cover->big|escape:'quotes':'UTF-8'}" />
                                </a>
                                <br/>
                                <br/>
                                <a href="{$module->url|escape:'quotes':'UTF-8'}" class="btn btn-primary btn-lg btn-block" target="_blank" >{l s='Discover' mod='deliverydate'}</a>
                            </div>
                        </div>
                    {/if}
                {/foreach}
                <div class="clearfix"></div>
            </ps-panel>
            <div class="clearfix"></div>
        </ps-tab>
    {/if}

    <ps-tab label="{l s='Documentation' mod='deliverydate'}" id="dd-documentation" icon="icon-AdminCatalog" {if $dd_tab && $dd_tab == 'documentation'}active="true"{/if}  img="../img/admin/AdminCatalog.gif">
        <iframe src="../modules/deliverydate/docs/readme_{$doc_iso|escape:'htmlall':'UTF-8'}.pdf#page=1&zoom=120"></iframe>
    </ps-tab>

</ps-tabs>
