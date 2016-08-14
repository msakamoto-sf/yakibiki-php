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
 * @version $Id: dispatcher.php 525 2009-06-16 17:59:38Z msakamoto-sf $
 */

require_once('yb/datatype/Utils.php');
require_once('yb/tx/data/DataInfo.php');

yb_Session::start();
$uc = yb_Session::user_context();

$did = (integer)yb_Var::request('id');
$dao_data =& yb_dao_Factory::get('data');
$datas = $dao_data->find_by_id($did);
if (count($datas) != 1) {
    yb_Util::forward_error_die(
        t('data (ID=%id) is not found.', array('id' => $did)), 
        null, 404);
}
$virtual = $datas[0];

$allowed_acls = yb_AclCache::evaluate($uc['id'], YB_ACL_PERM_READ);
$allow_readable = in_array($virtual['acl'], $allowed_acls);
$allow_update = (in_array('sys', $uc['role'])) || $virtual['owner'] == $uc['id'];

if (!$allow_update && !$allow_readable) {
    yb_Util::forward_error_die(
        t("You don't have any permission to access specified data (ID=%id).", 
        array('id' => $did)), 
        null, 403);
}

$validate_errors = array();

if (yb_Var::server('REQUEST_METHOD') == 'POST' && 
    $allow_update && 
    yb_datatype_Utils::validate_datainfo_update($virtual, $validate_errors)) {

    yb_Util::ticket_is_valid_or_error_die();

    yb_tx_data_DataInfo::go($did, $virtual);

    yb_Session::set_flash('info', 
        t('Data Attributes "%title"(ID: %id) was updated.', 
        array('title' => $virtual['title'], 'id' => $did)));

    $log =& yb_Log::get_Logger();
    $log->info(sprintf('datainfo update success: id=%d, acl=%d, title=%s', 
        $did, $virtual['acl'], $virtual['title']));

    $dao_user =& yb_dao_Factory::get('user');
    $user = $dao_user->find_by_id($virtual['owner']);
    $mailto = $user[0]['mail'];
    $subject = _YB('mail_notify.subject_prefix') 
        . t('data attribute update success : "%title"(ID=%id)', array(
            'title' => $virtual['title'], 'id' => $did), 'mail');
    $body = t('URL : %url', array(
        'url' => yb_Util::redirect_url(array('id' => $did))), 
    'mail');
    $attaches = array();
    $r = yb_Util::hook('mail_notifier', 
        array($mailto, $subject, $body, $attaches));
    list($mail_success, $errmsg) = $r;
    if (!$mail_success) {
        $log->err($errmsg);
    }

    // refresh
    $datas = $dao_data->find_by_id($did);
    $virtual = $datas[0];
}

$renderer =& new yb_smarty_Renderer();
$renderer->setTitle($virtual['title'] . ' : ' . t('Attributes Information'));
$renderer->set('user_context', $uc);
$renderer->set('virtual', $virtual);
$renderer->set('validate_errors', $validate_errors);

list($ticket_form, $ticket_id) = yb_Util::issue_ticket();
$renderer->set('ticket_form', $ticket_form);
$renderer->set('ticket_id', $ticket_id);

$renderer->setViewName(
    ($allow_update) 
    ?  'theme:modules/datainfo/edit_tpl.html'
    :  'theme:modules/datainfo/show_tpl.html'
);
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
