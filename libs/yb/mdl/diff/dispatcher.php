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

/**
 * YakiBiki: edit data info module dispatcher.
 *
 * @author msakamoto-sf <msakamoto-sf@users.sourceforge.net>
 * @version $Id: dispatcher.php 560 2009-07-21 08:07:22Z msakamoto-sf $
 */

yb_Session::start();
$uc = yb_Session::user_context();

$did = (integer)yb_Var::request('id');
$err = array();
$virtual = yb_Finder::find_by_id($uc, $did, $err, YB_ACL_PERM_READ);
if (is_null($virtual)) {
    yb_Util::forward_error_die(
        t($err['msg'], $err['args']), 
        null, $err['status']);
}

$allow_update = (in_array('sys', $uc['role'])) || $virtual['owner'] == $uc['id'];

$dao_version =& yb_dao_Factory::get('version');
$_versions = $dao_version->find_by_id($virtual['versions'], 'id', ORDER_BY_DESC);
if ($allow_update) {
    $versions = $_versions;
} else {
    $versions = array();
    foreach ($_versions as $_v) {
        if ($_v['approved']) {
            $versions[] = $_v;
        }
    }
}

$errors = array();
$diff_contents = '';
$old_v = yb_Var::request('old_v');
$new_v = yb_Var::request('new_v');
if (!empty($old_v) && !empty($new_v)) {

    // retrive old version's filepath
    $old_data = yb_Finder::find_by_id(
        $uc, $did, $err, YB_ACL_PERM_READ, false, $old_v);
    if (empty($old_data)) {
        yb_Util::forward_error_die(t($err['msg'], $err['args']), 
        null, $err['status']);
    }
    $old_filename = $old_data['_raw_filepath'];

    // retrive new version's filepath
    $new_data = yb_Finder::find_by_id(
        $uc, $did, $err, YB_ACL_PERM_READ, false, $new_v);
    if (empty($new_data)) {
        yb_Util::forward_error_die(t($err['msg'], $err['args']), 
        null, $err['status']);
    }
    $new_filename = $new_data['_raw_filepath'];

    // invoke datatype's diff()
    $type = $virtual['type'];
    $dtplugin =& yb_Util::factoryDataType($type);
    if (is_null($dtplugin)) {
        yb_Util::forward_error_die(
            t('Illegal data type : [%type]', array('type' => $type)),
            null, 404);
    }
    $diff_contents = $dtplugin->diff(
        $did, 
        $old_v, $old_data['display_title'], $old_filename,
        $new_v, $new_data['display_title'], $new_filename);
} else {
    if (empty($new_v)) {
        $new_v = $versions[0]['version'];
    }
    if (empty($old_v)) {
        if (1 == count($versions)) {
            $old_v = $versions[0]['version'];
        } else {
            $old_v = $versions[1]['version'];
        }
    }
}

$renderer =& new yb_smarty_Renderer();
$renderer->setTitle($virtual['title'] . ' : ' . t('Diff View'));
$renderer->set('user_context', $uc);
$renderer->set('data', $virtual);
$renderer->set('versions', $versions);
$renderer->set('errors', $errors);
$renderer->set('old_v', $old_v);
$renderer->set('new_v', $new_v);
$renderer->set('diff_contents', $diff_contents);
$renderer->set('text_diff', yb_Var::request('text_diff'));

$renderer->setViewName('theme:modules/diff/diff_tpl.html');
$output = $renderer->render();

echo $output;

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
