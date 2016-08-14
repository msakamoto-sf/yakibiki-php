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
 * YakiBiki: search module dispatcher.
 *
 * @author msakamoto-sf <msakamoto-sf@users.sourceforge.net>
 * @version $Id: dispatcher.php 384 2008-10-18 02:54:48Z msakamoto-sf $
 */

require_once('yb/mdl/search/Pager.php');

yb_Session::start();
$uc = yb_Session::user_context();
$finder =& new yb_Finder();
$_hidden_tags = array();

// {{{ set categories/textmatch conditions.

$v = array(
    'categories' => yb_Var::get('c'),
    'textmatch' => (string)yb_Var::get('s'),
    'is_fullmatch' => (boolean)yb_Var::get('ism'),
    'case_sensitive' => (boolean)yb_Var::get('cs'),
    'andor_c_t' => (integer)yb_Var::get('ao'),
);

if (is_array($v['categories']) && count($v['categories']) > 0) {
    $finder->categories = $v['categories'];
    foreach ($v['categories'] as $_k => $_v) {
        $_hidden_tags["c[{$_k}]"] = $_v;
    }
}

if ($v['textmatch'] != '') {
    $finder->textmatch = $v['textmatch'];
    $finder->is_fullmatch = $v['is_fullmatch'];
    $finder->case_sensitive = $v['case_sensitive'];
    $_hidden_tags['s'] = $v['textmatch'];
    $_hidden_tags['ism'] = $v['is_fullmatch'];
    $_hidden_tags['cs'] = $v['case_sensitive'];
}

$finder->andor_c_t = $v['andor_c_t'];
$_hidden_tags['ao'] = $v['andor_c_t'];

// }}}
// {{{ build page navigator, set order_by, sort_by, and search.

$navi =& new yb_mdl_search_Pager();
$params = $navi->setup();

// order by
$order_by = $params['ob'];
$order_by = ($order_by == ORDER_BY_ASC) 
    ? ORDER_BY_ASC : ORDER_BY_DESC;
$finder->order_by = $order_by;

// sort by
$sort_by = $params['sb'];
$sort_by = ($sort_by == yb_Finder::SORT_BY_CREATED_AT())
    ? yb_Finder::SORT_BY_CREATED_AT()
    : yb_Finder::SORT_BY_UPDATED_AT();
$finder->sort_by = $sort_by;

// get id list.
$ids = $finder->search($uc);

$navi->itemData($ids);
$navi_datas= $navi->build();
$pager = $navi_datas['pager'];
$navi_datas['links'] = $pager->getLinks();
$page_dids = $pager->getPageData();

// }}}

$renderer =& new yb_smarty_Renderer();
$renderer->setTitle(t('Search'));
$renderer->set('user_context', $uc);
$renderer->set('hidden_tags', $_hidden_tags);
if (count($page_dids) == 0) {
    $renderer->setViewName("theme:modules/search/no_hit_tpl.html");
} else {
    // get data contents.
    $datas = array();
    $err = array(); // dummy
    $_count = 1;
    foreach ($page_dids as $did) {

        $_d = yb_Finder::find_by_id(
            $uc, $did, $err, YB_ACL_PERM_READ, true);
        $ctx =& new yb_DataContext($_d);
        $type = $_d['type'];
        $dtplugin =& yb_Util::factoryDataType($type);
        if (is_null($dtplugin)) {
            yb_Util::forward_error_die(
                t('Illegal data type : [%type]', 
                array('type' => $type)),
                null, 404);
        }
        $_d['contents'] = $dtplugin->view(
            $ctx, 
            $_d['_raw_filepath'], 
            yb_Html::LIST_MODE(), 
            $_d['display_id'], $_d['display_title']);
        $_d['fragment'] = "af_" . $_count;
        $_count++;
        $datas[] = $_d;
    }
    $renderer->set('navi', $navi_datas);
    $renderer->set('datas', $datas);
    $renderer->setViewName("theme:modules/search/index_tpl.html");
}

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
