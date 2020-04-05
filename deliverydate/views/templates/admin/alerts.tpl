{foreach from=$errors item='error'}
	<ps-alert-error>{$error}{* HTML *}</ps-alert-error>
{/foreach}

{foreach from=$warnings item='warning'}
	<ps-alert-warn>{$warning}{* HTML *}</ps-alert-warn>
{/foreach}

{foreach from=$confirmations item='confirmation'}
	<ps-alert-success>{$confirmation}{* HTML *}</ps-alert-success>
{/foreach}
