<?php

$descriptorspec = array(
    0 => array("pipe", "r"),
    1 => array("pipe", "w"),
    2 => array("pipe", "w")
);

$pipes = null;

$r = proc_open("su concerto_1", $descriptorspec, $pipes);
if (is_resource($r)) {
    fwrite($pipes[0], "c55228f5dca8b0b2efcc9280477f6318\n");
    fclose($pipes[0]);

    echo "1: " . stream_get_contents($pipes[1]) . "\n";
    fclose($pipes[1]);

    echo "2: " . stream_get_contents($pipes[2]) . "\n";
    fclose($pipes[2]);

    proc_close($r);
}
?>
