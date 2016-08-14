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
 * YakiBiki: edit data body module dispatcher.
 *
 * @author msakamoto-sf <msakamoto-sf@users.sourceforge.net>
 * @version $Id: dispatcher.php 525 2009-06-16 17:59:38Z msakamoto-sf $
 */

require_once('yb/datatype/Utils.php');

yb_Session::start();
$uc = yb_Session::user_context();
$did = (integer)yb_Var::request('id');
$err = array();
$data = yb_Finder::find_by_id($uc, $did, $err, YB_ACL_PERM_READWRITE, true);
if (is_null($data)) {
    yb_Util::forward_error_die(
        t($err['msg'], $err['args']), 
        null, $err['status']);
}

$changelog = '';
$version_up = true;
if (yb_Var::server('REQUEST_METHOD') == 'POST') {
    $changelog = yb_Var::post('changelog');
    $version_up = (boolean)yb_Var::post('version_up');
}

$vid = $data['current_version_id'];
$versions = $data['_versions'];
$sha1 = '';
foreach ($versions as $vinfo) {
    if ($vid == $vinfo['id']) {
        $sha1 = $vinfo['sha1'];
        break;
    }
}
if (yb_Var::server('REQUEST_METHOD') == 'GET') {
    yb_Session::set('prev_sha1_checksum', $sha1, __FILE__);
}

$dtplugin =& yb_Util::factoryDataType($data['type']);
$dtplugin->format_edit_data($data);

$validate_errors = array();

if (yb_Var::server('REQUEST_METHOD') == 'POST' && 
    $dtplugin->validate_edit_data($validate_errors, $data)) {

    $old_sha1 = yb_Session::get('prev_sha1_checksum', null, __FILE__);
    if ($sha1 == $old_sha1) {

        yb_Util::ticket_is_valid_or_error_die();

        yb_Session::clear('prev_sha1_checksum', __FILE__);
        $dtplugin->update_data(
            $did, $data, $version_up, $changelog, $uc['id']);

        yb_Session::set_flash('info', 
            t('Data "%title"(ID: %id) was updated.', 
            array('title' => $data['title'], 'id' => $did)));

        $log =& yb_Log::get_Logger();
        $log->info(sprintf('data edit success: id=%d, title=%s', 
            $did, $data['title']));

        $mailto = $data['owner']['mail'];
        $subject = _YB('mail_notify.subject_prefix') 
            . t('data edit success : "%title"(ID=%id)', array(
                'title' => $data['title'], 'id' => $did), 'mail');
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
        HTTP_Header::redirect(yb_Util::redirect_url(
            array('mdl' => 'view', 'id' => $did)));
        exit();
    }
    $validate_errors[] = t('Check-Sum is different : Anyone updated the same data before you post.');
}

$renderer =& new yb_smarty_Renderer();

list($ticket_form, $ticket_id) = yb_Util::issue_ticket();
$renderer->set('ticket_form', $ticket_form);
$renderer->set('ticket_id', $ticket_id);

$renderer->set('dtform', 
    $dtplugin->build_specific_form_on_edit($data, $renderer->smarty));
$renderer->setTitle($data['title'] . ' : ' . t('Edit Data'));
$renderer->set('user_context', $uc);
$renderer->set('data', $data);
$renderer->set('changelog', $changelog);
$renderer->set('version_up', $version_up);
$renderer->set('validate_errors', $validate_errors);
$renderer->setViewName('theme:modules/edit/edit_tpl.html');
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
