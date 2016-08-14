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
 * YakiBiki: ld module
 *
 * @author msakamoto-sf <msakamoto-sf@users.sourceforge.net>
 * @version $Id: dispatcher.php 528 2009-06-20 14:34:20Z msakamoto-sf $
 */

function __yb_mdl_ls_search($title, $user_context)
{
    $finder =& new yb_Finder();
    $finder->textmatch = $title;
    $finder->use_listmatch = true;
    $ids = $finder->search($user_context);
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
        $_links[] = '<li><a href="' . $__url . '">' 
            . h($_title). '</a></li>';
    }
    return "<ul>\n" . implode("\n", $_links) . "\n</ul>\n";
}

yb_Session::start();
$uc = yb_Session::user_context();

$title = yb_Var::get('t');
$_t_param = array('title' => '"' . $title . '"');
$page_title = t('list of %title pages', $_t_param);
$not_found = t('No pages found which name start %title.', $_t_param);

if (empty($title)) {
    $html = $not_found;
} else {
    $html = __yb_mdl_ls_search($title, $uc);
    if (empty($html)) {
        $html = $not_found;
    }
}

$renderer =& new yb_smarty_Renderer();
$renderer->setTitle($page_title);
$renderer->set('user_context', $uc);
$renderer->set('ls', $html);
$renderer->setViewName("theme:modules/ls/ls_tpl.html");
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
