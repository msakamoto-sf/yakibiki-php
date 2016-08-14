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
 * YakiBiki Smarty Plugin : acl permission select menu list
 *
 * @author msakamoto-sf <msakamoto-sf@users.sourceforge.net>
 * @version $Id: function.yb_acl_perm_selectmenu.php 225 2008-03-25 08:07:28Z msakamoto-sf $
 */

/**
 *
 * @param array Parameters specified in Smarty Template.
 *              "default" : selected value
 *              "name" : select tag's name attribute
 *              "class" : select tag's class attribute
 * @return string Output Contents
 */
function smarty_function_yb_acl_perm_selectmenu($params, &$smarty)
{
    $default = @$params['default'];
    $css_class = '';
    if (isset($params['class'])) {
        $css_class = ' class="' . $params['class'] . '" ';
    }
    $html = '<select name="' . @$params['name'] . $css_class . '">';

    $_options = array(
        YB_ACL_PERM_NONE => t('(none:invisible)'),
        YB_ACL_PERM_READ => t('Read Only'),
        YB_ACL_PERM_READWRITE => t('Read and Edit'),
    );
    foreach ($_options as $v => $l) {
        $_selected = ($default == $v) ? ' selected ' : '';
        $html .= '<option value="' . $v . '" ' . $_selected . '>' . $l . '</option>';
    }
    $html .= '</select>';
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
