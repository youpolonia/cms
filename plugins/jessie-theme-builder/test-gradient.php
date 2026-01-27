<?php
define("CMS_ROOT", "/var/www/cms");
require_once __DIR__ . "/includes/parser/class-jtb-style-extractor.php";
require_once __DIR__ . "/includes/parser/class-jtb-attribute-converter.php";

$extractor = new JessieThemeBuilder\JTB_Style_Extractor();
$converter = new JessieThemeBuilder\JTB_Attribute_Converter();

$style = "background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 80px 40px;";
$result = $extractor->parseStyleString($style);
echo "Parsed:\n";
print_r($result);

echo "\nExpanded:\n";
$expanded = $extractor->expandAllShorthands($result);
print_r($expanded);

echo "\nConverted to JTB attrs:\n";
$attrs = $converter->convert($result);
print_r($attrs);
