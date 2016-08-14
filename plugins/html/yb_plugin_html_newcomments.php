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

/*
 * <yb_newcomments> html plugin
 *
 * usage:
 * <code>
 * <yb_newcomments /> : list up recent posted comments (default 10 datas).
 * <yb_newcomments 99 /> : list up recent comments max 99 datas.
 * <yb_newcomments (99)>(ignored)</yb_newcomments> : (same)
 * </code>
 *
 * @author msakamoto-sf <msakamoto-sf@users.sourceforge.net>
 * @version $Id: yb_plugin_html_newcomments.php 413 2008-11-03 05:06:36Z msakamoto-sf $
 * @param mixed tag attribute
 * @param mixed internal element
 * @param yb_DataContext
 * @return string
 */
function yb_plugin_html_newcomments_invoke($param1, $param2, &$ctx)
{
    $uc = yb_Session::user_context();

    $dao_comment =& yb_dao_Factory::get('comment');

    $idx =& grain_Factory::index('datetime', 'comment_by_updated');
    $idx->order(ORDER_BY_DESC);
    $c_ids = $idx->gets();

    $limit = 10;
    $_p1 = (integer)trim($param1);
    if (!empty($_p1)) {
        $limit = $_p1;
    }

    $cnt = 1;
    $comments = array();
    foreach ($c_ids as $_c_id) {
        $r = $dao_comment->find_by_id($_c_id);
        if (count($r) != 1) {
            continue;
        }
        $c = $r[0];
        $did = $c['data_id'];
        $err = array();
        $data = yb_Finder::find_by_id($uc, $did, $err, YB_ACL_PERM_READ);
        if (is_null($data)) {
            continue;
        }

        $c['owner'] = yb_Util::get_user_info_ex($c['owner']);

        $comments[] = $c;
        $cnt++;
        if ($cnt > $limit) {
            break;
        }
    }

    $html = '<div class="plugin_newcomments">' . "\n";
    $html .= '<h6>' 
        . t('recent %cnt comments', array('cnt' => h($limit))) 
        . '</h6>' . "\r\n";

    foreach ($comments as $c) {
        $owner = @$c['owner']['name'];
        $data_url = yb_Util::make_url(
            array('mdl' => 'view', 'id' => $c['data_id']));
        $data_url .= '#cf_' . $c['id'];
        $t =& yb_Time::singleton();
        $t->setInternalRaw($c['updated_at']);
        $display_dt = $t->get("%Y-%m-%d");
        $html .= sprintf('<a href="%s">%s</a>&nbsp;by&nbsp;%s<br />', 
            $data_url, $display_dt, h($owner));
        $html .= "\r\n";
    }

    $html .= "</div>\n";

    return $html;
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
