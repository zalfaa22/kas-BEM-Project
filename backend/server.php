<?php
$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
$public = dirname(__DIR__).'/frontend/public';
if ($uri !== '/' && file_exists($public.$uri)) {
    return false;
}
require $public.'/index.php';
