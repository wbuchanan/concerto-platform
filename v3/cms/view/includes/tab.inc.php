<?php
if ($readable)
{
    ?>

    <script>
        $(function(){
            $( "#div<?= $class_name ?>RadioMenu" ).buttonset(); 
        });
    </script>

    <div align="center" id="div<?= $class_name ?>RadioMenu">
        <input type="radio" id="radio<?= $class_name ?>List" name="radio<?= $class_name ?>" checked="checked" onclick="<?= $class_name ?>.uiShowList();" />
        <label for="radio<?= $class_name ?>List"><?=Language::string(337)?></label>
        <?php
        if ($writeable)
        {
            ?>
            <input type="radio" id="radio<?= $class_name ?>Form" name="radio<?= $class_name ?>" disabled="disabled" onclick="<?= $class_name ?>.uiShowForm();" />
            <label for="radio<?= $class_name ?>Form"><?=Language::string(338)?> <?=Language::string(73)?></label>
            <?php
        }
        ?>
    </div>

    <div align="center" id="div<?= $class_name ?>List" class="table">
        <?php include Ini::$path_internal . 'cms/view/list.php'; ?>
    </div>

    <div align="center" id="div<?= $class_name ?>Form" class="table" style="display:none;">
        <?php include Ini::$path_internal . 'cms/view/' . $class_name . '_form.php'; ?>
    </div>
    <?php
}
?>