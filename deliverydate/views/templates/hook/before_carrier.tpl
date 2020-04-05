<div class="delivery_date"></div>

{if isset($reason) && $reason && $reason != ''}
	<div class="delivery_reason">{$reason|escape:'htmlall':'UTF-8'}</div>
{/if}
