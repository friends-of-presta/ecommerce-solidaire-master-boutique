<script type="text/javascript">

    var deliveries = {$deliveries|json_encode nofilter};
    var delivery_text_between = "{l s='By choosing [name], your order will be delivered between the [start] and [end]' mod='deliverydate' d='Modules.DeliveryDate.Shop'}";
    var delivery_text = "{l s='By choosing [name], your order will be delivered the [start]' mod='deliverydate' d='Modules.DeliveryDate.Shop'}";
    {if !isset($ajax) || !$ajax}
        document.addEventListener('DOMContentLoaded', function() {
    {/if}

        {if isset($position) && $position == 'bottom'}
            moveDates();
            {if isset($reason) && $reason && $reason != ''}
                moveReason();
            {/if}
        {/if}

        changeDeliveryDate();

    {if !isset($ajax) || !$ajax}
        });
    {/if}


</script>
