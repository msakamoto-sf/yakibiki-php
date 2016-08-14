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
 * YakiBiki: create new data (from template) module dispatcher.
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

$t =& new yb_Time();

$template_id = (integer)yb_Var::request('template');
$dao_template =& yb_dao_Factory::get('template');
$templates = $dao_template->find_by_id($template_id);
if (count($templates) != 1) {
    yb_Util::forward_error_die(t('Invalid Template ID.'));
}
$template = $templates[0];
// template title's special keywords replacement.
$replaces = array(
    '{$now_ymd}' => $t->get('%Y%m%d'),
    '{$now_y/m/d}' => $t->get('%Y/%m/%d'),
    '{$now_y-m-d}' => $t->get('%Y-%m-%d'),
    '{$now_year}' => $t->get('%Y'),
    '{$now_month}' => $t->get('%m'),
    '{$now_day}' => $t->get('%d'),
    '{$now_hour}' => $t->get('%H'),
    '{$now_min}' => $t->get('%M'),
    '{$now_sec}' => $t->get('%S'),
    '{$now_hhmm}' => $t->get('%H%M'),
    '{$now_hh:mm}' => $t->get('%H:%M'),
    '{$now_hhmmss}' => $t->get('%H%M%S'),
    '{$now_hh:mm:ss}' => $t->get('%H:%M:%S'),
);
$template['title'] = str_replace(
    array_keys($replaces), 
    array_values($replaces), 
    $template['title']);

$dt = $template['type'];
$dtplugin =& yb_Util::factoryDataType($dt);
$virtual = array(
    'owner' => $uc['id'],
    'title' => $template['title'],
    'acl' => $template['acl'],
    'categories' => $template['categories'],
    'is_versions_moderated' => $template['is_versions_moderated'],
    'is_comments_moderated' => $template['is_comments_moderated'],
    'published_at' => $t->getGMT(YB_TIME_FMT_INTERNAL_RAW),
    'type' => $dt,
);
$dtplugin->format_new_data($virtual, $template);

$_title = trim((string)yb_Var::get('title'));
if (!empty($_title)) {
    $virtual['title'] = $_title;
}
$_url = trim((string)yb_Var::get('url'));
if (!empty($_url)) {
    $virtual['url'] = $_url;
}

$validate_errors = array();
if (yb_Var::server('REQUEST_METHOD') == 'POST' && 
    yb_datatype_Utils::validate_new_data($virtual, $validate_errors)) {

    yb_Util::ticket_is_valid_or_error_die();

    $id = $dtplugin->create_data($virtual);

    $log =& yb_Log::get_Logger();
    $log->info(sprintf(
        'new data from template(%d): id=%d, acl=%d, type=%s, title=%s', 
        $template_id, $id, $virtual['acl'], $dt, $virtual['title']));

    HTTP_Header::redirect(yb_Util::redirect_url(
        array('mdl' => 'view', 'id' => $id)));
    exit();
}

$renderer =& new yb_smarty_Renderer();

list($ticket_form, $ticket_id) = yb_Util::issue_ticket();
$renderer->set('ticket_form', $ticket_form);
$renderer->set('ticket_id', $ticket_id);

$renderer->setTitle($dtplugin->title_create());
$renderer->set('title_img', $dtplugin->title_create_img());
$renderer->set('user_context', $uc);
$renderer->set('virtual', $virtual);
$renderer->set('dtform', 
    $dtplugin->build_specific_form_on_new($virtual, $renderer->smarty));
$renderer->set('validate_errors', $validate_errors);

$renderer->setViewName("theme:modules/new_common/input_tpl.html");
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
