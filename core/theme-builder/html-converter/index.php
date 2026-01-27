<?php
/**
 * HTML to TB Converter - Security Block
 * 
 * This directory contains the HTML to Theme Builder JSON converter.
 * Direct access is not allowed.
 *
 * @package ThemeBuilder
 * @subpackage HtmlConverter
 * @version 4.0
 */

http_response_code(403);
exit('Access denied');
