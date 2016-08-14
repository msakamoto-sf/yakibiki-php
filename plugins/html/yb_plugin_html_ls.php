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
 * <yb_ls> html plugin
 *
 * usage:
 * <code>
 * <yb_ls /> : list up based on current page name.
 * <yb_ls (virtual directory) /> : list up based on virtual directory.
 * <yb_ls (virtual directory) >(ignored)</yb_ls> : (same).
 * </code>
 * NOTE: "(virtual directory)" is needed to encoded by yb_bin2hex().
 *
 * @author msakamoto-sf <msakamoto-sf@users.sourceforge.net>
 * @version $Id: yb_plugin_html_ls.php 551 2009-07-15 05:36:47Z msakamoto-sf $
 * @param mixed tag attribute
 * @param mixed internal element
 * @param yb_DataContext
 * @return string
 */
function yb_plugin_html_ls_invoke($param1, $param2, &$ctx)
{
    $param1 = yb_hex2bin($param1);
    $prefix = trim($param1);
    if (empty($prefix)) {
        $prefix = $ctx->get('title');
    }
    $prefix .= '/';

    $uc = yb_Session::user_context();

    $finder =& new yb_Finder();
    $finder->textmatch = $prefix;
    $finder->use_listmatch = true;
    $ids = $finder->search($uc);
    if (count($ids) == 0) {
        return '';
    }
    $datas = array();
    $dao =& yb_dao_Factory::get('data');
    $_datas = $dao->find_by_id($ids);
    foreach ($_datas as $data) {
        $_did = $data['id'];
        $_acl = $data['acl'];
        $_title = $data['title'];
        $datas[$_did] = $_title;
    }
    if (count($datas) == 0) {
        return '';
    }
    natsort($datas);

    $_links = array();
    foreach ($datas as $_did => $_title) {
        $__url = yb_Util::make_url(array('mdl' => 'view', 'id' => $_did));
        $_links[] = '<li><a href="' . $__url . '">' . h($_title). '</a></li>';
    }
    return "<ul>\n" . implode("\n", $_links) . "\n</ul>\n";
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
