{*
* Project : everpsorderoptions
* @author Team EVER
* @copyright Team EVER
* @license   Tous droits réservés / Le droit d'auteur s'applique (All rights reserved / French copyright law applies)
* @link https://www.team-ever.com
*}
<div class="panel everheader">
    <div class="panel-heading">
        <i class="icon icon-smile"></i> {l s='Ordered Options' mod='everpsorderoptions'}
    </div>
    <div class="panel-body">
        <div class="col-md-2">
            <img id="everlogo" src="{$everimg_dir|escape:'htmlall':'UTF-8'}/logo.png" style="max-width: 120px;">
        </div>
        <div class="col-md-10">
            <div class="table-responsive">
                <table id="everpsorderoptions" class="display responsive nowrap dataTable no-footer dtr-inline collapsed table">
                    <thead>
                        <tr>
                            <th>{l s='Field' mod='everpsprocatalog'}</th>
                            <th>{l s='Value' mod='everpsprocatalog'}</th>
                        </tr>
                    </thead>
                    <tbody>
                    {foreach from=$everoptions item=option}
                    <tr>
                        <td class="option_name">
                            {$option->name|escape:'htmlall':'UTF-8'}
                        </td>
                        <td class="option_value">
                            {$option->value|escape:'htmlall':'UTF-8'}
                        </td>
                    </tr>
                    {/foreach}
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
