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
 * YakiBiki Smarty Plugin : Data/Template ACL select menu list
 *
 * @author msakamoto-sf <msakamoto-sf@users.sourceforge.net>
 * @version $Id: function.yb_dt_acl_selectmenu.php 358 2008-09-19 10:40:06Z msakamoto-sf $
 */

/**
 *
 * @param array Parameters specified in Smarty Template.
 * @return string Output Contents
 */
function smarty_function_yb_dt_acl_selectmenu($params, &$smarty)
{
    $dao =& yb_dao_Factory::get('acl');
    $acls = $dao->find_all();

    $en = 'acl';
    if (isset($params['name'])) {
        $en = $params['name'];
    }

    $default_acl_id = $params['default'];

    $_acl_list = array();
    $selected = '';
    foreach ($acls as $_a) {
        $selected = ($_a['id'] == $default_acl_id)
            ? ' selected ' : '';
        $_acl_list[] = sprintf(
            '<option value="%d" %s>%s</option>', 
            h($_a['id']), $selected, h($_a['name']));
    }
    return '<select name="' . $en . '">' . "\n"
        . implode("\n", $_acl_list) . "\n" . '</select>';
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
