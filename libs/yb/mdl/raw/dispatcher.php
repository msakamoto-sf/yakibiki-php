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
 */

/**
 * requires
 */
require_once('HTTP/Download.php');

/**
 * YakiBiki raw module
 *
 * @author msakamoto-sf <msakamoto-sf@users.sourceforge.net>
 * @version $Id: dispatcher.php 363 2008-09-21 13:25:09Z msakamoto-sf $
 */

yb_Session::start();
$user_context = yb_Session::user_context();

// retrieve data id and version number.
$did = 0;
$version = null;
$title = null;
$id = yb_Var::request('id');
$matches = array();
if (preg_match('/^(\d+)$/mi', $id, $matches)) {
    $did = $matches[1];
} else if (preg_match('/^(\d+)_(\d+)$/mi', $id, $matches)) {
    $did = $matches[1];
    $version = $matches[2];
}

$err = array();
$data = yb_Finder::find_by_id(
    $user_context, 
    $did, 
    $err, 
    YB_ACL_PERM_READ, 
    true, // expand owner, updated_by, categories data
    $version // specify version
);

if (is_null($data)) {
    yb_Util::forward_error_die(
        t($err['msg'], $err['args']), 
        null, $err['status']);
}

$contents = "";
$type = $data['type'];
$ctx =& new yb_DataContext($data);

$dtplugin =& yb_Util::factoryDataType($type);
if (is_null($dtplugin)) {
    yb_Util::forward_error_die(
        t('Illegal data type : [%type]', array('type' => $type)),
        null, 404);
}
$dtplugin->raw($ctx, $data['_raw_filepath']);

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
