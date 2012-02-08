<?php
if (!isset($ini)) {
    require_once'Ini.php';
    $ini = new Ini();
}
?>

<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8" />
        <meta name="description" content="" />
        <meta name="author" content="Przemyslaw Lis" />
        <title>Concerto</title>

        <script type="text/javascript" src="cms/js/lib/jquery-1.7.1.min.js"></script>
        <script type="text/javascript" src="cms/js/lib/jquery.json-2.3.min.js"></script>

        <script type="text/javascript" src="js/ConcertoMethods.js"></script>
        <script type="text/javascript" src="js/Concerto.js"></script>
        <script>
            $(function(){
<?php if (array_key_exists("sid", $_GET) || array_key_exists("tid", $_GET)) { ?>
            var test = new Concerto("#divTestContainer",<?= array_key_exists("sid", $_GET) ? $_GET['sid'] : "null" ?>,<?= array_key_exists("tid", $_GET) ? $_GET['tid'] : "null" ?>);
            test.run();
<?php } ?>
    })
        </script>
    </head>

    <body>

        <div style="width:100%;" id="divTestContainer"></div>
    </body>
</html>