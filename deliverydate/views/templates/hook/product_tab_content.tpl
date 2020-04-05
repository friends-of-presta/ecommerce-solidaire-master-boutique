{* $only_content is only for 1.7 *}
{if isset($tab) && $tab == true && !$only_content}
    <div id="DeliveryDate" class="product_accordion rte block_hidden_only_for_screen hidden">
{else if !$only_content}
    <section class="page-product-box">
        <h3 class="page-product-heading">{l s='Delivery' mod='deliverydate'}</h3>
{/if}
        <table
            class="table-data-sheet"
            id="dd_carriers_list"
            data-qties="{$qties|json_encode}"
            data-allow-oosp="{$allow_oosp|intval}"
            data-id-product-attribute="{$id_product_attribute|intval}"
        >
            <tbody>
                {foreach from=$carriers item=c key=id_carrier}
                    <tr class="odd hidden dd_available">
                        <td align="center"><img src="{$c.logo|escape:'quotes':'UTF-8'}" style="max-height:50px;{if isset($tab) && $tab}margin-right:10px;{/if}" /></td>
                        <td><b>{$c.name|escape:'htmlall':'UTF-8'}</b> : {$c.date nofilter}</td>
                    </tr>
                    {if isset($c.oot_date)}
                        <tr class="odd hidden dd_oot">
                            <td align="center"><img src="{$c.logo|escape:'quotes':'UTF-8'}" style="max-height:50px;{if isset($tab) && $tab}margin-right:10px;{/if}" /></td>
                            <td><b>{$c.name|escape:'htmlall':'UTF-8'}</b> : {$c.oot_date nofilter}</td>
                        </tr>
                    {/if}
                {/foreach}
            </tbody>
        </table>
{if isset($tab) && $tab == true && !$only_content}
    </div>
{else if !$only_content}
    </section>
{/if}
