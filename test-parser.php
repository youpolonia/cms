<?php
define("CMS_ROOT", "/var/www/cms");
require_once CMS_ROOT . "/plugins/jessie-theme-builder/includes/parser/class-jtb-html-parser.php";

$html = (string)file_get_contents("/var/www/cms/test-html.html");

$parser = new \JessieThemeBuilder\JTB_HTML_Parser();
$result = $parser->parse($html);

echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
