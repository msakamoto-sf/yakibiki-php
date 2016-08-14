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
 * YakiBiki Smarty Plugin : Data/Template category check boxes
 *
 * @author msakamoto-sf <msakamoto-sf@users.sourceforge.net>
 * @version $Id: function.yb_dt_categories_checkboxes.php 499 2009-01-05 07:01:03Z msakamoto-sf $
 */

/**
 *
 * @param array Parameters specified in Smarty Template.
 * @return string Output Contents
 */
function smarty_function_yb_dt_categories_checkboxes($params, &$smarty)
{
    $dao =& yb_dao_Factory::get('category');
    $categories = $dao->find_all('name', ORDER_BY_ASC);

    $en = 'categories';
    if (isset($params['name'])) {
        $en = $params['name'];
    }

    if (isset($params['default']) && is_array($params['default'])) {
        $_selected = $params['default'];
    } else {
        $_selected = array();
    }

    $_category_list = array();
    foreach ($categories as $_c) {
        $_cid = h($_c['id']);
        $_checked = (in_array($_cid, $_selected))
            ? ' checked="checked" ' : '';

        $_category_list[] = sprintf('<input name="%s[%d]" type="checkbox" value="1" id="c_%d" %s /><label for="c_%d">%s</label>',
            $en, $_cid, $_cid, $_checked, $_cid, h($_c['name']));
    }

    return implode("\n", $_category_list);
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
