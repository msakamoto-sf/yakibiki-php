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
 * YakiBiki: post new comment module
 *
 * @author msakamoto-sf <msakamoto-sf@users.sourceforge.net>
 * @version $Id: dispatcher.php 566 2009-07-23 13:57:45Z msakamoto-sf $
 */

yb_Session::start();
if (!yb_Session::isAuthenticated()) {
    yb_Util::forward_error_die(
        t('Please login to post new comment.'), 
        null, 403);
}
$uc = yb_Session::user_context();

yb_Util::ticket_is_valid_or_error_die(YB_UTIL_TICKET_NS4COMMENT);

// retrieve data id and version number.
$did = yb_Var::post('id');
$display_id = yb_Var::post('display_id');
$err = array();
$data = yb_Finder::find_by_id($uc, $did, $err, YB_ACL_PERM_READ);

if (is_null($data)) {
    yb_Util::forward_error_die(
        t($err['msg'], $err['args']), 
        null, $err['status']);
}

$comment_body = (string)yb_Var::post('comment_body');
$comment_body = trim($comment_body);

if (empty($comment_body)) {
    yb_Util::forward_error_die(
        t('%label is required.', array('label' => t('comment body'))), 
        null, 403);
}

// add comment data
$dao_comment =& yb_dao_Factory::get('comment');
$comment = array(
    'data_id' => $did,
    'owner' => $uc['id'],
    'text' => yb_Util::encode_ctrl_char($comment_body),
    'approved' => !$data['is_comments_moderated'], 
);
$comment_id = $dao_comment->create($comment);
$r = $dao_comment->find_by_id($comment_id);

// add did to comment ids index
$idx =& grain_Factory::index('pair', 'data_to_comment');
$idx->add($comment_id, $did);

// add newely posted comment index
$idx =& grain_Factory::index('datetime', 'comment_by_updated');
$idx->append($comment_id, $r[0]['updated_at']);

$log =& yb_Log::get_Logger();
$log->info(sprintf('comment_add success: data_id=%d, comment_id=%d', 
    $did, $comment_id));

HTTP_Header::redirect(yb_Util::redirect_url(
        array('mdl' => 'view', 'id' => $display_id)));

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
