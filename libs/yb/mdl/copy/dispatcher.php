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
 * YakiBiki: copy data module dispatcher.
 *
 * @author msakamoto-sf <msakamoto-sf@users.sourceforge.net>
 * @version $Id: dispatcher.php 525 2009-06-16 17:59:38Z msakamoto-sf $
 */

require_once('yb/datatype/Utils.php');
require_once('yb/tx/data/New.php');

yb_Session::start();
$uc = yb_Session::user_context();
if (!yb_datatype_Utils::user_can_create_data($uc['id'], $uc['role'])) {
    yb_datatype_Utils::redirect_to_login_and_exit();
}

// {{{ retrieve original data id
$did = 0;
$version = null;
$id = yb_Var::get('id');
$matches = array();
if (preg_match('/^(\d+)$/mi', $id, $matches)) {
    $did = $matches[1];
} else if (preg_match('/^(\d+)_(\d+)$/mi', $id, $matches)) {
    $did = $matches[1];
    $version = $matches[2];
}

$err = array();
$base = yb_Finder::find_by_id(
    $uc, $did, $err, YB_ACL_PERM_READ, false, $version);
if (is_null($base)) {
    yb_Util::forward_error_die(
        t($err['msg'], $err['args']), 
        null, $err['status']);
}

// }}}
// {{{ copy data attributes

$copies = array('type', 'acl', 'categories', 'is_versions_moderated', 
    'is_comments_moderated');
$virtual = array();
foreach ($copies as $_f) {
    $virtual[$_f] = $base[$_f];
}

$virtual['title'] = $base['display_title'] . '(copy)';
$virtual['owner'] = $uc['id'];
$t =& new yb_Time();
$virtual['published_at'] = $t->getGMT(YB_TIME_FMT_INTERNAL_RAW);

// }}}

$dtplugin =& yb_Util::factoryDataType($base['type']);
$dtplugin->format_copy_data($virtual, $base);

$validate_errors = array();
if (yb_Var::server('REQUEST_METHOD') == 'POST' && 
    yb_datatype_Utils::validate_copy_data($virtual, $validate_errors)) {

    yb_Util::ticket_is_valid_or_error_die();

    $id = $dtplugin->copy_data($virtual, $base);

    $log =& yb_Log::get_Logger();
    $log->info(sprintf(
        'copy data: base_id=%d, new_id=%d, acl=%d, type=%s, title=%s', 
        $base['id'], $id, $virtual['acl'], $base['type'], $virtual['title']));

    HTTP_Header::redirect(yb_Util::redirect_url(
        array('mdl' => 'view', 'id' => $id)));
    exit();
}

$renderer =& new yb_smarty_Renderer();

list($ticket_form, $ticket_id) = yb_Util::issue_ticket();
$renderer->set('ticket_form', $ticket_form);
$renderer->set('ticket_id', $ticket_id);

$renderer->setTitle($dtplugin->title_copy($base));
$renderer->set('title_img', $dtplugin->title_copy_img());
$renderer->set('user_context', $uc);
$renderer->set('virtual', $virtual);
$renderer->set('dtform', 
    $dtplugin->build_specific_form_on_copy($virtual, $renderer->smarty));
$renderer->set('validate_errors', $validate_errors);

$renderer->setViewName("theme:modules/new_common/copy_tpl.html");
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
