<?php
define('YB_CONFIG_PHP', realpath(dirname(__FILE__) . '/../libs/config.php'));
define('YB_CONFIG_PHP_APPEND', realpath(dirname(__FILE__) . '/yb_append.php'));
require_once(dirname(__FILE__) . '/../ybprepare.php');

function find_files($dir, &$list, $callback)
{
    $d = opendir($dir);
    if (!$d) {
        die();
    }
    while (false !== ($e = readdir($d))) {
        if ($e == '.' || $e == '..') {
            continue;
        }
        if ($e[0] == '.') {
            // hidden file
            continue;
        }
        $_e = realpath($dir . DIRECTORY_SEPARATOR . $e);
        if (is_dir($_e)) {
            find_files($_e, $list, $callback);
        }
        if (is_callable($callback)) {
            $r = $callback($_e);
        } else {
            $r = $_e;
        }
        $list[] = $r;
    }
    closedir($d);
}

function okng($r)
{
    $_ok = '<span style="font-weight: bold; color: green;">OK</span>';
    $_ng = '<span style="font-weight: bold; color: red;">NG</span>';
    if (strtoupper($r) === 'OK') {
        return $_ok;
    } else if (strtoupper($r) === 'NG') {
        return $_ng;
    } else {
        $_r = (boolean)$r;
        if ($_r) {
            return $_ok;
        } else {
            return $_ng;
        }
    }
}

