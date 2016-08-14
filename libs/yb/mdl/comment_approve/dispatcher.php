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
 * YakiBiki: comment approve/disapprove module
 *
 * @author msakamoto-sf <msakamoto-sf@users.sourceforge.net>
 * @version $Id: dispatcher.php 566 2009-07-23 13:57:45Z msakamoto-sf $
 */

yb_Session::start();
$uc = yb_Session::user_context();

yb_Util::ticket_is_valid_or_error_die(YB_UTIL_TICKET_NS4COMMENT);

// retrieve data id and version number.
$did = 0;
$display_id = yb_Var::get('id');
$matches = array();
if (preg_match('/^(\d+)$/mi', $display_id, $matches)) {
    $did = $matches[1];
} else if (preg_match('/^(\d+)_(\d+)$/mi', $display_id, $matches)) {
    $did = $matches[1];
}
$err = array();
$data = yb_Finder::find_by_id($uc, $did, $err, YB_ACL_PERM_READ);
if (is_null($data)) {
    yb_Util::forward_error_die(
        t($err['msg'], $err['args']), 
        null, $err['status']);
}

$c_id = yb_Var::get('c');
$dao_comment =& yb_dao_Factory::get('comment');
$r = $dao_comment->find_by_id($c_id);
if (count($r) != 1) {
    yb_Util::forward_error_die(t('Illegal operation.'), null, 500);
}
$comment = $r[0];

// only (sys role | data's owner) can approve/disapprove comment post.
$approvable = (in_array('sys', $uc['role']) || $data['owner'] == $uc['id']);

if (!$approvable) {
    yb_Util::forward_error_die(t('Illegal operation.'), null, 500);
}

// flip approved status.
$comment['approved'] = !$comment['approved'];

$dao_comment->update($c_id, $comment);

$log =& yb_Log::get_Logger();
$log->info(sprintf(
    'comment_approve success: data_id=%d, comment_id=%d, approved=%d', 
    $did, $c_id, $comment['approved']));

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
