{*
* Project : everpsorderoptions
* @author Team EVER
* @copyright Team EVER
* @license   Tous droits réservés / Le droit d'auteur s'applique (All rights reserved / French copyright law applies)
* @link https://www.team-ever.com
*}

{extends file='checkout/_partials/steps/checkout-step.tpl'}

{block name='step_content'}
    <div class="custom-checkout-step">
        {if isset($evermessage) && $evermessage}
        {$evermessage nofilter}
        {/if}
        <form
                method="POST"
                action="{$urls.pages.order}"
                data-refresh-url="{url entity='order' params=['ajax' => 1, 'action' => 'customStep']}"
        >
            {foreach from=$fields item=field}
            {foreach from=$everdata item=item key=key}
            {if $field.id_everpsorderoptions_field == $key|substr:19:1 && $key|substr:0:19 eq "everpsorderoptions_"}
                {assign var='defaultKey' value=$key|substr:19:1}
                {assign var='defaultValue' value=$item}
            {/if}
            {/foreach}
            <section class="form-fields">
                <div class="form-group row">
                    {if $field.type == 'text' || $field.type == 'number' || $field.type == 'password' || $field.type == 'url' || $field.type == 'email' || $field.type == 'date' || $field.type == 'time' || $field.type == 'datetime'}
                    {* Fields are type default text *}
                    <label class="col-md-4 form-control-label required">{$field.field_title} {if isset($field.is_required) && $field.is_required}{l s='(required)' mod='everpsorderoptions'}{/if}</label>
                    <div class="col-md-6">
                        <input type="{$field.type}" name="everpsorderoptions_{$field.id_everpsorderoptions_field}" {if isset($field.is_required) && $field.is_required}required{/if} {if isset($defaultValue)}value="{$defaultValue}"{/if}>
                    </div>
                    {if isset($field.field_description) && $field.field_description}
                    <div class="col-md-12 text-center">
                        <em>{$field.field_description nofilter}</em>
                    </div>
                    {/if}
                </div>
                {elseif $field.type == 'textarea'}
                {* Fields are type textarea *}
                <div class="form-group">
                    <label for="{$field.id_everpsorderoptions_field|escape:'htmlall':'UTF-8'}">{$field.field_title|escape:'htmlall':'UTF-8'}</label>
                    <textarea class="form-control" rows="5" name="everpsorderoptions_{$field.id_everpsorderoptions_field|escape:'htmlall':'UTF-8'}" id="{$field.id_everpsorderoptions_field|escape:'htmlall':'UTF-8'}" {if isset($field.is_required) && $field.is_required} required{/if}>
                        {if isset($defaultValue)}{$defaultValue|escape:'htmlall':'UTF-8'}{/if}
                    </textarea>
                    {if isset($field.field_description) && $field.field_description}
                    <em>{$field.field_description nofilter}</em>
                    {/if}
                </div>
                {* Fields are type radio or checkboxes *}
                {elseif $field.type == 'radio' || $field.type == 'checkbox'}
                {foreach from=$options item=option}
                {if $option.id_field == $field.id_everpsorderoptions_field}
                <div class="{$field.type|escape:'htmlall':'UTF-8'}">
                  <label><input type="{$field.type|escape:'htmlall':'UTF-8'}" name="everpsorderoptions_{$option.id_everpsorderoptions_option|escape:'htmlall':'UTF-8'}" value="{$option.id_everpsorderoptions_option|escape:'htmlall':'UTF-8'}" {if isset($defaultValue) && $defaultValue == $option.id_everpsorderoptions_option}checked{/if}>{$option.option_title|escape:'htmlall':'UTF-8'}</label>
                </div>
                {/if}
                {/foreach}
                {* Fields are type select *}
                {elseif $field.type == 'select'}
                <div class="form-group">
                    <label for="{$field.id_everpsorderoptions_field|escape:'htmlall':'UTF-8'}">{$field.field_title|escape:'htmlall':'UTF-8'}</label>
                    <select class="form-control" id="{$field.id_everpsorderoptions_field|escape:'htmlall':'UTF-8'}" name="everpsorderoptions_{$field.id_everpsorderoptions_field|escape:'htmlall':'UTF-8'}">
                    {foreach from=$options item=option}
                    {if $option.id_field == $field.id_everpsorderoptions_field}
                        <option value="{$option.id_everpsorderoptions_option|escape:'htmlall':'UTF-8'}" {if isset($defaultValue) && $defaultValue == $option.id_everpsorderoptions_option}selected{/if}>{$option.option_title|escape:'htmlall':'UTF-8'}</option>
                    {/if}
                    {/foreach}
                </div>
                </select>
                {/if}
            </section>
            {/foreach}
            <!-- Les Champs spécifiques de la step avec assignation de la variable si elle existe -->
            <footer class="clearfix">
                <input type="submit" name="submitCustomStep" value="{l s='Submit' mod='everpsorderoptions'}"
                       class="btn btn-primary continue float-xs-right"/>
            </footer>
        </form>
    </div>
{/block}
