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

<span></span>
<div class="padding ui-widget-content ui-corner-all margin">
    <div class="padding ui-widget-content ui-corner-all margin">
        <table class="fullWidth">
            <thead>
                <tr>
                    <th class="noWrap horizontalPadding ui-widget-header"><?=Language::string(149)?></th>
                    <th class="noWrap horizontalPadding ui-widget-header"><?=Language::string(150)?></th>
                    <th class="noWrap horizontalPadding ui-widget-header"><?=Language::string(151)?></th>
                </tr>
            </thead>
            <tbody>
                <?php
                foreach ($_POST['vars'] as $var)
                {
                    $var = json_decode($var);
                    ?>
                    <tr>
                        <td class="noWrap horizontalPadding ui-widget-header"><?= $var->name ?></td>
                        <td class="horizontalPadding ui-widget-content">
                            <ul>
                                <?php
                                foreach ($var->section as $section)
                                {
                                    echo "<li>" . $section->counter . ": " . $section->name . "</li>";
                                }
                                ?>
                            </ul>
                        </td>
                        <td class="horizontalPadding ui-widget-content">
                            <ul>
                                <?php
                                $i = 0;
                                foreach ($var->type as $type)
                                {
                                    echo "<li>".$type."</li>";
                                }
                                ?>
                            </ul>
                        </td>
                    </tr>
                    <?php
                }
                ?>
            </tbody>
        </table>
    </div>
</div>