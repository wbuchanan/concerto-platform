<?php
if (!isset($ini))
{
    require_once'Ini.php';
    $ini = new Ini();
}
?>

<html>
    <head>
    </head>

    <body>

        <?php
        if (!array_key_exists("submit", $_POST)) $_POST['submit'] = false;
        else $_POST['submit'] = true;
        ?>
        <form action="socket_connect.php" method="post">
            Enter some text:<br>
            <input type="Text" name="message" size="15"><input type="submit" name="submit" value="Send">
        </form>
        <?php
        if ($_POST['submit'])
        {
            if (!TestServer::is_running()) TestServer::start_process();
            $result = TestServer::send($_POST['message']);
            ?>
            Server said: <b><? echo $result; ?></b>
            <?php
        }
        ?>

    </body>
</html>
