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
 * YakiBiki Smarty Plugin : display yb_Session::get_flash('info')
 *
 * @author msakamoto-sf <msakamoto-sf@users.sourceforge.net>
 * @version $Id: function.yb_session_get_flash_info.php 531 2009-06-21 03:11:35Z msakamoto-sf $
 */

/**
 * Display yb_Session::get_flash('info') contents.
 */
function smarty_function_yb_session_get_flash_info($params, &$smarty)
{
    if (!yb_Session::has_flash('info')) {
        return '';
    }
    $info = yb_Session::get_flash('info');
    $icon = _YB('url.themes') . '/icons/info.png';
    $str = '<ul class="session-get-flash-info"><li>';
    $str .= '<img src="' . $icon . '" alt="info icon" title="info"/>&nbsp;';
    $str .= '<em>' . h($info) . '</em></li></ul>';

    return $str;
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
