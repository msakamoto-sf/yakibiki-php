<?php
function yb_hook_test($v1, $v2, &$v3)
{
    $v3 = $v1 + $v2;
    return $v1 * $v2;
}
