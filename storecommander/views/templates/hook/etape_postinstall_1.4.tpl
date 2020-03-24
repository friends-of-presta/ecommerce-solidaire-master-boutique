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
    <img src="../modules/storecommander/views/img/logo.png"/><br/><br/>
    <p>{l s='Store Commnander is installed. You can launch the application from the Modules > Store Commander menu.' mod='storecommander'}</p><br/>
    <a href="index.php?tab=AdminStoreCommander&token={$token|escape:'htmlall':'UTF-8'}" class="sc_bouton">{l s='Go to Store Commander' mod='storecommander'}</a>
</fieldset>