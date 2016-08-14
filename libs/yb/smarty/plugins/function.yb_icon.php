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
 * YakiBiki Smarty Plugin : display icon images under {theme}/icons/ directory
 *
 * @author msakamoto-sf <msakamoto-sf@users.sourceforge.net>
 * @version $Id: function.yb_icon.php 218 2008-03-23 22:46:27Z msakamoto-sf $
 */

/**
 * Display <img> tag for icons under {theme}/icons/*.png
 * 
 * <code>
 * Usage:
 *
 * {yb_icon icon=login} : <img src=".../icons/login.png" />
 *
 * {yb_icon icon=login alt='Alt Attributes'}
 * : <img src="..." alt="Alt Attributes" />
 *
 * (other optional attributes: title, width, height, class, style, border)
 * </code>
 *
 * @param array Parameters specified in Smarty Template.
 * @return string Output Contents
 */
function smarty_function_yb_icon($params, &$smarty)
{
    if (!isset($params['icon'])) {
        return "";
    }
    if (isset($params['title'])) {
        $params['title'] = t($params['title']);
    }
    $src = _YB('url.themes') . '/icons/' . $params['icon'] . '.png';
    unset($params['icon']);
    $attrs = array();
    foreach ($params as $attr => $val) {
        $attrs[] = $attr . '="' . h($val) .'"';
    }
    return '<img src="' . $src . '" ' . implode(' ', $attrs) . ' />';
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
