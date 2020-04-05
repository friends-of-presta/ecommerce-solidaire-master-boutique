<div class="dd_available {if !$product.quantity}hidden{/if}">
    <b><i class="material-icons">local_shipping</i> {$available_text|escape:'quotes':'UTF-8'}</b>
</div>
{if isset($oot_text)}
    <div class="dd_oot {if $product.quantity || !$product.allow_oosp}hidden{/if}">
        <b><i class="material-icons">local_shipping</i> {$oot_text|escape:'quotes':'UTF-8'}</b>
    </div>
{/if}
