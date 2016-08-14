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
 * YakiBiki Smarty Plugin : yb search box plugin
 *
 * @author msakamoto-sf <msakamoto-sf@users.sourceforge.net>
 * @version $Id: function.yb_search_box.php 499 2009-01-05 07:01:03Z msakamoto-sf $
 */

/**
 *
 * @param array Parameters specified in Smarty Template.
 * @return string Output Contents
 */
function smarty_function_yb_search_box($params, &$smarty)
{
    $accepts = array(
        'categories' => yb_Var::get('c'),
        'textmatch' => (string)yb_Var::get('s'),
        'is_fullmatch' => (boolean)yb_Var::get('ism'),
        'case_sensitive' => (boolean)yb_Var::get('cs'),
        'andor_c_t' => (integer)yb_Var::get('ao'),
    );

    $condition = array();

    // categories and text search AND/OR radio box (default: or)
    $ao = $accepts['andor_c_t'];
    switch ($ao) {
    case YB_AND:
        $condition['ao_and'] = true;
        $condition['ao_or'] = false;
        break;
    case YB_OR:
    default:
        $condition['ao_and'] = false;
        $condition['ao_or'] = true;
    }

    // textmatch/fullmatch checkbox/case sensitive checkbox
    $_text = (empty($accepts['textmatch'])) ? '' : $accepts['textmatch'];
    $condition['textmatch'] = $_text;
    $condition['ism'] = $accepts['is_fullmatch'];
    $condition['cs'] = $accepts['case_sensitive'];

    // categories checkbox
    $dao_category =& yb_dao_Factory::get('category');
    $categories = $dao_category->find_all('name', ORDER_BY_ASC);
    $condition['categories'] = array();
    foreach ($categories as $c) {
        $_id = $c['id'];
        $_name = $c['name'];
        $_checked = is_array($accepts['categories']) && 
            in_array($_id, $accepts['categories']);
        $condition['categories'][] = array(
            'id' => $_id,
            'name' => $_name,
            'checked' => $_checked,
            );
    }

    $smarty->assign('condition', $condition);
    $output = $smarty->fetch("theme:plugins/yb_search_box_tpl.html");

    return $output;
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
