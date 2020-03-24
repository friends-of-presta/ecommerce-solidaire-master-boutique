<script type="text/javascript">
    $(document).ready(function() {
        $(".loading").click(function() {
            $.loader({
                className:"blue-with-image-2",
                content:""
            });
        });
    });
</script>
<div id="content" class="bootstrap" style="margin: 0px; padding: 0px; width: 700px;">
    <section class="panel widget allow_push">
        <header class="panel-heading">{l s='Ready for StoreCommander installation!' mod='storecommander'}</header>
        <div>
            <img src="../modules/storecommander/views/img/logo.png"/><br/><br/>
            <p>{l s='Save unvaluable time working with Store Commander.' mod='storecommander'}</p><br/>
            <p>{l s='Click on the button below: Store Commander\'s application will be downloaded in a new directory /modules/storecommander/' mod='storecommander'}</p><br/>
            <p>{l s='If you need assistance, please contact us at' mod='storecommander'} <a href="mailto:support@storecommander.com">support@storecommander.com</a></p><br/>
            <a href="{$currentUrl|escape:'UTF-8'}{$baseParams|escape:'UTF-8'}&sc_step=2" class="sc_bouton loading">{l s='Start downloading and installing SC' mod='storecommander'}</a>
        </div>
    </section>
</div>
<div style="clear: both;"></div>