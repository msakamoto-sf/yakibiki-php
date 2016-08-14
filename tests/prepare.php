<?php
/*
 *   Copyright (c) 2007 msakamoto-sf <msakamoto-sf@users.sourceforge.net>
 *
 *   Licensed under the Apache License, Version 2.0 (the "License");
 *   you may not use this file except in compliance with the License.
 *   You may obtain a copy of the License at
 *
 *       http://www.apache.org/licenses/LICENSE-2.0
 *
 *   Unless required by applicable law or agreed to in writing, software
 *   distributed under the License is distributed on an "AS IS" BASIS,
 *   WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *   See the License for the specific language governing permissions and
 *   limitations under the License.
 *
 */

/*
 * @author msakamoto-sf <msakamoto-sf@users.sourceforge.net>
 * $Id: prepare.php 479 2008-12-03 15:59:11Z msakamoto-sf $
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once('simpletest/unit_tester.php');
require_once('simpletest/mock_objects.php');

require_once(dirname(__FILE__) . '/../ybprepare.php');
require_once('System.php');

$__base_dir = dirname(__FILE__);
set_include_path($__base_dir . PATH_SEPARATOR . get_include_path());

function yb_test_error_raise_callback($error)
{
    dlog($error['msg']);
    $st = $error['stacktrace'];
    array_pop($st);
    //dlog($st);
}

function yb_test_echo($s)
{
    echo $s . PHP_EOL;
}

function yb_test_sleep($s, $msg = null)
{
    if ($msg) {
        echo $msg;
    }
    echo " Please wait for {$s} seconds..." . PHP_EOL;
    sleep($s);
}

/**
 * Local Variables:
 * mode: php
 * coding: iso-8859-1
 * tab-width: 4
 * c-basic-offset: 4
 * c-hanging-comment-ender-p: nil
 * indent-tabs-mode: nil
 * End:
 * vim: set expandtab tabstop=4 shiftwidth=4:
 */
