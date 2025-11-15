<?php

$db = new SQLite3('database/database.sqlite');

$tables = $db->query("SELECT name FROM sqlite_master WHERE type='table' AND name='users'");
if (!$tables->fetchArray()) {
    die("Users table does not exist\n");
}

$result = $db->query("PRAGMA table_info(users)");
while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
    print_r($row);
}

$db->close();
