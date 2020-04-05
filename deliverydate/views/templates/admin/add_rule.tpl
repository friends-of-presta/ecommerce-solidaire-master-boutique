<ps-panel header="{l s='Add rule' mod='deliverydate'}">

	<form class="form-horizontal" action="{$current_url|escape:'quotes':'UTF-8'}" method="post">

		<ps-form-group label="{l s='Carrier' mod='deliverydate'}">
			<select name="carrier" class="fixed-width-xxl">
				{foreach from=$carriers item='carrier'}
					<option value="{$carrier.id_carrier|intval}" {if isset($rule) && $rule.id_carrier == $carrier.id_carrier}selected="selected"{/if}>{$carrier.name|escape:'htmlall':'UTF-8'}</option>
				{/foreach}
			</select>
		</ps-form-group>

		<ps-form-group label="{l s='Zone' mod='deliverydate'}">
			<select name="zone" class="fixed-width-xxl">
				{foreach from=$zones item='zone'}
					<option value="{$zone.id_zone|intval}" {if isset($rule) && $rule.id_zone == $zone.id_zone}selected="selected"{/if}>{$zone.name|escape:'htmlall':'UTF-8'}</option>
				{/foreach}
			</select>
		</ps-form-group>

		<ps-checkboxes label="{l s='Delivery days' mod='deliverydate'}" help="{l s='Which days this carrier delivers in this zone?' mod='deliverydate'}">
			<ps-checkbox name="monday" value="1" {if isset($days.0) && $days.0 == 1}checked="true"{/if}>{l s='Monday' mod='deliverydate'}</ps-checkbox>
			<ps-checkbox name="tuesday" value="1" {if isset($days.1) && $days.1 == 1}checked="true"{/if}>{l s='Tuesday' mod='deliverydate'}</ps-checkbox>
			<ps-checkbox name="wednesday" value="1" {if isset($days.2) && $days.2 == 1}checked="true"{/if}>{l s='Wednesday' mod='deliverydate'}</ps-checkbox>
			<ps-checkbox name="thursday" value="1" {if isset($days.3) && $days.3 == 1}checked="true"{/if}>{l s='Thursday' mod='deliverydate'}</ps-checkbox>
			<ps-checkbox name="friday" value="1" {if isset($days.4) && $days.4 == 1}checked="true"{/if}>{l s='Friday' mod='deliverydate'}</ps-checkbox>
			<ps-checkbox name="saturday" value="1" {if isset($days.5) && $days.5 == 1}checked="true"{/if}>{l s='Saturday' mod='deliverydate'}</ps-checkbox>
			<ps-checkbox name="sunday" value="1" {if isset($days.6) && $days.6 == 1}checked="true"{/if}>{l s='Sunday' mod='deliverydate'}</ps-checkbox>
		</ps-checkboxes>

		<ps-input-text name="min" label="{l s='Min days' mod='deliverydate'}" size="10" value="{if isset($rule)}{$rule.min|intval}{/if}" required-input="true" suffix="{l s='day(s)' mod='deliverydate'}" fixed-width="xs"></ps-input-text>

		<ps-input-text name="max" label="{l s='Max days' mod='deliverydate'}" size="10" value="{if isset($rule)}{$rule.max|intval}{/if}" required-input="true" suffix="{l s='day(s)' mod='deliverydate'}" fixed-width="xs"></ps-input-text>

		<ps-form-group label="{l s='No expedition after' mod='deliverydate'}">
			<select name="hours">
				{for $i=0 to 23}
					<option value="{$i|intval}" {if isset($rule) && $rule.hours == $i}selected="selected"{/if}>{$i|intval}h</option>
				{/for}
			</select>
			<select name="minutes">
				{for $i=0 to 59}
					<option value="{$i|intval}" {if isset($rule) && $rule.minutes == $i}selected="selected"{/if}>{$i|intval}min</option>
				{/for}
			</select>
		</ps-form-group>

		<ps-panel-footer>
			<ps-panel-footer-link title="{l s='Cancel' mod='deliverydate'}" icon="process-icon-back" href="{$cancel_url|escape:'quotes':'UTF-8'}" direction="left" img="{$module_dir|escape:'quotes':'UTF-8'}views/img/process-icon-back.png"></ps-panel-footer-link>
			<ps-panel-footer-submit title="{l s='Save' mod='deliverydate'}" icon="process-icon-save" direction="right" img="{$module_dir|escape:'quotes':'UTF-8'}views/img/process-icon-save.png" name="addRule"></ps-panel-footer-submit>
		</ps-panel-footer>

	</form>

</ps-panel>
