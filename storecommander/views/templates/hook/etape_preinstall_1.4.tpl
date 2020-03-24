<link type="text/css" rel="stylesheet" href="../modules/storecommander/views/css/admin.css" />
<script type="text/javascript" src="../modules/storecommander/views/js/loader/jquery.loader-min.js"></script>
<script type="text/javascript">
{literal}
    $(document).ready(function() {
        $(".loading").click(function() {
            $.loader({
                className:"blue-with-image-2",
                content:""
            });
        });
    });
{/literal}
</script>
<fieldset>
    <div class="conf"><img src="../modules/storecommander/views/img/ok2.png" alt="" /> {l s='Ready for StoreCommander installation!' mod='storecommander'}</div>
    <br/>
    <img src="../modules/storecommander/views/img/logo.png"/><br/><br/>
    <p>{l s='Save unvaluable time working with Store Commander.' mod='storecommander'}</p><br/>
    <p>{l s='Click on the button below: Store Commander\'s application will be downloaded in a new directory /modules/storecommander/' mod='storecommander'}</p><br/>
    <p>{l s='If you need assistance, please contact us at' mod='storecommander'} <a href="mailto:support@storecommander.com">support@storecommander.com</a></p><br/>
    <a href="{$currentUrl|escape:'UTF-8'}{$baseParams|escape:'UTF-8'}&sc_step=2" class="sc_bouton loading">{l s='Start downloading and installing SC' mod='storecommander'}</a>
</fieldset>