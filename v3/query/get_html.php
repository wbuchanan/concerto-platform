<?php

if (!isset($ini)) {
    require_once'../Ini.php';
    $ini = new Ini();
}

$template = Template::from_mysql_id($_POST['template_id']);
$vals = TestSection::from_property(array("counter" => $_POST['values']["LOAD_HTML_SECTION_INDEX"], "Test_id" => $_POST['values']["TEST_ID"]), false)->get_values();
$html = $template->get_html_with_return_properties($vals);

foreach ($template->get_inserts() as $k) {
    $var_value = "";
    $reference = $template->get_insert_reference($k, $vals);
    if (array_key_exists($reference, $_POST['values']))
        $var_value = $_POST['values'][$reference];
    $html = str_replace("{{" . $k . "}}", $var_value, $html);
}

echo json_encode(array("html" => $html));
?>