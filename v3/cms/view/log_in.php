<?php
if (!isset($ini))
{
    require_once'../../Ini.php';
    $ini = new Ini();
}
?>

<script>
    $(function(){
        $("#dd_login").dialog({
            modal:true,
            title:"<?=Language::string(211)?>",
            resizeable:false,
            closeOnEscape:false,
            dialogClass:"no-close",
            open:function(){
                Methods.iniTooltips();
            },
            buttons:{
                "login":function(){ User.uiLogIn(); }
            }
        });
    });
</script>
<div id="dd_login">
    <span><?=Language::string(212)?></span>
    <div class="padding ui-widget-content ui-corner-all margin">
        <table>
            <tr>
                <td class="noWrap horizontalPadding ui-widget-header"><?=Language::string(173)?>:</td>
                <td><span class="tooltip spanIcon ui-icon ui-icon-help" title="<?= Language::string(260) ?>"></span></td>
                <td class="fullWidth">
                    <div class="horizontalMargin">
                        <input type="text" id="dd_login_inp_login" class="fullWidth margin ui-widget-content ui-corner-all" />
                    </div>
                </td>
            </tr>
            <tr>
                <td class="noWrap horizontalPadding ui-widget-header"><?=Language::string(179)?>:</td>
                <td><span class="tooltip spanIcon ui-icon ui-icon-help" title="<?= Language::string(261) ?>"></span></td>
                <td class="fullWidth">
                    <div class="horizontalMargin">
                        <input type="password" id="dd_login_inp_password" class="fullWidth margin ui-widget-content ui-corner-all" />
                    </div>
                </td>
            </tr>
        </table>
    </div>	 
</div>