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
 * YakiBiki: view module
 *
 * @author msakamoto-sf <msakamoto-sf@users.sourceforge.net>
 * @version $Id: dispatcher.php 566 2009-07-23 13:57:45Z msakamoto-sf $
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
} else {
    $title = _YB('default.pagename');
}

$err = array();
if ($did === 0) {
    $data = yb_Finder::find_by_title(
        $user_context, 
        $title, 
        $err, 
        YB_ACL_PERM_READ, 
        true // expand owner, updated_by, categories data
    );
} else {
    $data = yb_Finder::find_by_id(
        $user_context, 
        $did, 
        $err, 
        YB_ACL_PERM_READ, 
        true, // expand owner, updated_by, categories data
        $version // specify version
    );
}

if (is_null($data)) {
    yb_Util::forward_error_die(
        t($err['msg'], $err['args']), 
        null, $err['status']);
}

$contents = "";
$display_id = $data['display_id'];
$display_title= $data['display_title'];
$type = $data['type'];
$ctx =& new yb_DataContext($data);

$dtplugin =& yb_Util::factoryDataType($type);
if (is_null($dtplugin)) {
    yb_Util::forward_error_die(
        t('Illegal data type : [%type]', array('type' => $type)),
        null, 404);
}
$contents = $dtplugin->view(
    $ctx, 
    $data['_raw_filepath'], 
    yb_Html::DETAIL_MODE(), 
    $data['display_id'], $data['display_title']);

$renderer =& new yb_smarty_Renderer();
$renderer->setTitle($data['display_title']);
$renderer->set('user_context', $user_context);
$renderer->set('data', $data);
$renderer->set('contents', $contents);

// title auto ls effect
if (_YB('use.title_auto_ls')) {
    $_links = array();
    $_parts = yb_Util::explode_for_autols($data['title']);
    foreach ($_parts as $_p) {
        list($display, $lst) = $_p;
        $_url = yb_Util::make_url(array('mdl' => 'ls', 't' => $lst));
        $_links[] = sprintf('<a href="%s">%s</a>', $_url, h($display));
    }
    $_buf = implode('&nbsp;', $_links);
    $_t_display = $display_title;
    $_t_origin = $data['title'];
    $_v_part = str_replace($_t_origin, '', $_t_display);
    $_buf .= $_v_part;
    $renderer->set('title_auto_ls', $_buf);
} else {
    $renderer->set('title_auto_ls', false);
}

// comment list
$did = $data['id'];
$edit_comment_id = yb_Var::get('ce');
$idx =& grain_Factory::index('pair', 'data_to_comment');
$r = $idx->get_from($did);
$comments = array();
if (isset($r[$did])) {
    $dao_comment =& yb_dao_Factory::get('comment');
    $_comments = $dao_comment->find_by_id(array_values($r[$did]), 
        'created_at', ORDER_BY_ASC);
    foreach ($_comments as $c) {

        // get expanded comment owner information
        $owner_id = $c['owner'];
        $c['owner'] = yb_Util::get_user_info_ex($owner_id);

        $c['text'] = yb_Util::decode_ctrl_char($c['text']);

        // set html <a> tag's name attribute fragment
        $c['fragment'] = 'cf_' . $c['id'];

        // sys role or data's owner only can approve/disapprove comment post.
        $c['approvable'] = (in_array('sys', $user_context['role']) || 
            $data['owner'] == $user_context['id']);

        // only (sys role | data's owner | comment's owner) can 
        // edit/delete comment post.
        $c['updatable'] = (
            in_array('sys', $user_context['role']) || 
            $data['owner'] == $user_context['id'] || 
            $owner_id == $user_context['id']
        );

        // decide comment body should be displayed or not.
        if (!$data['is_comments_moderated']) {
            $c['body_visible'] = true;
        } else {
            $c['body_visible'] = $c['updatable'] || $c['approved'];
        }

        $comments[] = $c;
    }
}
$renderer->set('comment_list', $comments);

// comment ticket id
list($ticket_form, $ticket_id) = 
    yb_Util::issue_ticket(YB_UTIL_TICKET_NS4COMMENT);
$renderer->set('ticket_form', $ticket_form);
$renderer->set('ticket_id', $ticket_id);

$renderer->setViewName("theme:modules/view/view_tpl.html");
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
