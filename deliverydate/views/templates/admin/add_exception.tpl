<ps-panel header="{l s='Add exception' mod='deliverydate'}">

    <form class="form-horizontal" action="{$current_url|escape:'quotes':'UTF-8'}" method="post">

        <ps-form-group label="{l s='Carrier' mod='deliverydate'}">
            <select name="carrier" class="fixed-width-xxl">
                <option value="0" {if isset($exception) && $exception.id_carrier == 0}selected="selected"{/if}>{l s='All' mod='deliverydate'}</option>
                {foreach from=$carriers item='carrier'}
                    <option value="{$carrier.id_carrier|intval}" {if isset($exception) && $exception.id_carrier == $carrier.id_carrier}selected="selected"{/if}>{$carrier.name|escape:'htmlall':'UTF-8'}</option>
                {/foreach}
            </select>
        </ps-form-group>

        <ps-form-group label="{l s='Zone' mod='deliverydate'}">
            <select name="zone" class="fixed-width-xxl">
                <option value="0" {if isset($exception) && $exception.id_zone == 0}selected="selected"{/if}>{l s='All ' mod='deliverydate'}</option>
                {foreach from=$zones item='zone'}
                    <option value="{$zone.id_zone|intval}" {if isset($exception) && $exception.id_zone == $zone.id_zone}selected="selected"{/if}>{$zone.name|escape:'htmlall':'UTF-8'}</option>
                {/foreach}
            </select>
        </ps-form-group>

        <ps-date-picker name="date" label="{l s='Date' mod='deliverydate'}" value="{if isset($exception)}{$exception.date|escape:'quotes':'UTF-8'}{/if}" size="10" fixed-width="lg"></ps-date-picker>

        <ps-switch name="preparation" label="{l s='Preparation' mod='deliverydate'}" yes="{l s='Yes' mod='deliverydate'}" no="{l s='No' mod='deliverydate'}" active="{if isset($exception) && $exception.preparation}true{else}false{/if}"></ps-switch>

        <ps-switch name="delivery" label="{l s='Delivery' mod='deliverydate'}" yes="{l s='Yes' mod='deliverydate'}" no="{l s='No' mod='deliverydate'}" active="{if isset($exception) && $exception.delivery}true{else}false{/if}"></ps-switch>

        <ps-panel-footer>
            <ps-panel-footer-link title="{l s='Cancel' mod='deliverydate'}" icon="process-icon-back" href="{$cancel_url|escape:'quotes':'UTF-8'}" direction="left"  img="{$module_dir|escape:'quotes':'UTF-8'}views/img/process-icon-back.png"></ps-panel-footer-link>
            <ps-panel-footer-submit title="{l s='Save' mod='deliverydate'}" icon="process-icon-save" direction="right" img="{$module_dir|escape:'quotes':'UTF-8'}views/img/process-icon-save.png" name="addException"></ps-panel-footer-submit>
        </ps-panel-footer>

    </form>

</ps-panel>
