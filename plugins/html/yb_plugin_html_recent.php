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
 * <yb_recent> html plugin
 *
 * usage:
 * <code>
 * <yb_recent /> : list up recent udpated datas (default 10 datas).
 * <yb_recent 99 /> : list up recent updated datas max 99 datas.
 * <yb_recent (99)>(ignored)</yb_recent> : (same)
 * </code>
 *
 * @author msakamoto-sf <msakamoto-sf@users.sourceforge.net>
 * @version $Id: yb_plugin_html_recent.php 409 2008-11-02 15:31:31Z msakamoto-sf $
 * @param mixed tag attribute
 * @param mixed internal element
 * @param yb_DataContext
 * @return string
 */
function yb_plugin_html_recent_invoke($param1, $param2, &$ctx)
{
    $limit = 10;
    $_p1 = (integer)trim($param1);
    if (!empty($_p1)) {
        $limit = $_p1;
    }

    $uc = yb_Session::user_context();
    $finder =& new yb_Finder();
    $ids = $finder->search($uc);
    if (count($ids) == 0) {
        return '';
    }

    // count limit
    $_limited_ids = array();
    $_count = 1;
    foreach ($ids as $_id) {
        $_limited_ids[] = $_id;
        $_count++;
        if ($_count > $limit) {
            break;
        }
    }

    // retrieve datas and sorting.
    $dao =& yb_dao_Factory::get('data');
    $datas = $dao->find_by_id($_limited_ids, 'updated_at', ORDER_BY_DESC);
    $_links = array();
    foreach ($datas as $_d) {
        $_time =& new yb_Time();
        $_time->setInternalRaw($_d['updated_at']);
        $_t1 = $_time->get("%Y%m%d");
        $_t2 = sprintf("%s-%s-%s", substr($_t1, 0, 4), 
            substr($_t1, 4, 2), substr($_t1, 6, 2));
        $__url = yb_Util::make_url(array('mdl' => 'view', 'id' => $_d['id']));
        $__link = '<a href="' . $__url . '">' . h($_d['title']). '</a>';
        $_links[$_t2][] = $__link;
    }
    if (count($_links) == 0) {
        return '';
    }

    $html = '<div class="plugin_recent">' . "\n";
    foreach ($_links as $_date => $_ls) {
        $html .= '<span class="plugin_recent_date">' . $_date . "</span>\n";
        $html .= '<div class="plugin_recent_page">' . "\n";
        foreach ($_ls as $_l) {
            $html .= $_l . "<br />\n";
        }
        $html .= "</div>\n";
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
