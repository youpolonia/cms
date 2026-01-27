<?php
// HelloWorld plugin main file

function hello_world_init() {
    echo "<!-- HelloWorld plugin initialized -->";
}

function hello_world_content_filter($content) {
    return str_replace('Hello', 'Greetings', $content);
}
