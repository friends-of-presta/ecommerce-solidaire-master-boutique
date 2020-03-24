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
        <div>
            <img src="../modules/storecommander/views/img/logo.png"/><br/><br/>
            <p>{l s='Store Commnander is installed. You can launch the application from the Modules > Store Commander menu.' mod='storecommander'}</p><br/>
            <a href="index.php?controller=AdminStoreCommander&token={$token|escape:'htmlall':'UTF-8'}" class="sc_bouton">{l s='Go to Store Commander' mod='storecommander'}</a>
        </div>
    </section>
</div>
<div style="clear: both;"></div>