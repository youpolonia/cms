<?php
// Simple write test
file_put_contents("/tmp/apache_test.txt", date("Y-m-d H:i:s") . " - Apache test\n", FILE_APPEND);
echo "Written to /tmp/apache_test.txt";
