<?php
if (!isset($ini))
{
    require_once'../../Ini.php';
    $ini = new Ini();
}
$logged_user = User::get_logged_user();
if ($logged_user == null)
{
    echo "<script>location.reload();</script>";
    die(Language::string(278));
}
?>

<script>
    $(function(){
        $("#tnd_mainMenu").tabs({
            show:function(event,ui){
                if(ui.index==0){
                    Test.uiRefreshCodeMirrors();
                }
                if(ui.index==4){
                    try{
                        $("#divUsersAccordion").accordion("resize");
                    }
                    catch(err){
                    }
                }
            }
        });
        $(".tooltipTabs").tooltip({
            position:{ my: "left top", at: "left bottom", offset: "15 0" },
            tooltipClass:"tooltipWindow"
        });
        
        Methods.currentVersion = "<?= Ini::$version ?>";
        Methods.checkLatestVersion(function(isNewerVersion,version){
            var div = $("#divVersionCheck");
            var newer = isNewerVersion==1;
            if(newer)
            {
                div.addClass("ui-state-error");
                div.html("<?= Language::string(262) ?> <a href='http://code.google.com/p/concerto-platform'><?= Language::string(263) ?> v"+version+"</a>");
            }
            else
            {
                div.html("<?= Language::string(264) ?>");
            }
        });
        $("#divUsersAccordion").accordion({
            collapsible:true,
            active:false,
            change:function(){
                $(this).accordion("resize");
            }
        });
    });
</script>
<div class="ui-widget-content ui-corner-all margin" align="center"><?php include Ini::$path_internal . 'cms/view/includes/header.inc.php'; ?></div>
<div align="center" class="margin" >
    <div id="tnd_mainMenu">
        <ul>
            <?php
            if ($logged_user->is_module_accesible("Test"))
            {
                ?>
                <li><a href="#tnd_mainMenu-tests" class="tooltipTabs" title="<?= Language::string(193) ?>"><?= Language::string(88) ?></a></li>
            <?php } ?>

            <?php
            if ($logged_user->is_module_accesible("CustomSection"))
            {
                ?>
                <li><a href="#tnd_mainMenu-customSections" class="tooltipTabs" title="<?= Language::string(194) ?>"><?= Language::string(84) ?></a></li>
            <?php } ?>

            <?php
            if ($logged_user->is_module_accesible("Template"))
            {
                ?>
                <li><a href="#tnd_mainMenu-templates" class="tooltipTabs" title="<?= Language::string(195) ?>"><?= Language::string(167) ?></a></li>
            <?php } ?>

            <?php
            if ($logged_user->is_module_accesible("Table"))
            {
                ?>
                <li><a href="#tnd_mainMenu-tables" class="tooltipTabs" title="<?= Language::string(196) ?>"><?= Language::string(85) ?></a></li>
            <?php } ?>

            <?php
            if ($logged_user->is_module_accesible("User") || $logged_user->is_module_accesible("UserType") || $logged_user->is_module_accesible("UserGroup"))
            {
                ?>
                <li><a href="#tnd_mainMenu-users" class="tooltipTabs" title="<?= Language::string(197) ?>"><?= Language::string(198) ?></a></li>
            <?php } ?>
        </ul>

        <?php
        if ($logged_user->is_module_accesible("Test"))
        {
            ?>
            <div id="tnd_mainMenu-tests">
                <?php include Ini::$path_internal . 'cms/view/includes/tab_tests.inc.php'; ?>
            </div>
        <?php } ?>

        <?php
        if ($logged_user->is_module_accesible("CustomSection"))
        {
            ?>
            <div id="tnd_mainMenu-customSections">
                <?php include Ini::$path_internal . 'cms/view/includes/tab_custom_sections.inc.php'; ?>
            </div>
        <?php } ?>

        <?php
        if ($logged_user->is_module_accesible("Template"))
        {
            ?>
            <div id="tnd_mainMenu-templates">
                <?php include Ini::$path_internal . 'cms/view/includes/tab_templates.inc.php'; ?>
            </div>
        <?php } ?>

        <?php
        if ($logged_user->is_module_accesible("Table"))
        {
            ?>
            <div id="tnd_mainMenu-tables">
                <?php include Ini::$path_internal . 'cms/view/includes/tab_tables.inc.php'; ?>
            </div>
        <?php } ?>

        <?php
        if ($logged_user->is_module_accesible("User") || $logged_user->is_module_accesible("UserType") || $logged_user->is_module_accesible("UserGroup"))
        {
            ?>
            <div id="tnd_mainMenu-users">
                <?php include Ini::$path_internal . 'cms/view/includes/tab_users.inc.php'; ?>
            </div>
        <?php } ?>

    </div>
</div>

<div class="margin ui-widget-content ui-corner-all" align="center"><?php include Ini::$path_internal . 'cms/view/includes/footer.inc.php'; ?></div>