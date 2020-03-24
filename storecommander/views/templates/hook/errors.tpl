{if !empty($errors) && is_array($errors)}
    <div class="sc_error">
        {foreach from=$errors item=error name=errors}
            <p><img src="../modules/storecommander/views/img/error2.png" />&nbsp;{$error|escape:'htmlall':'UTF-8'}</p>
        {/foreach}
    </div>
{/if}