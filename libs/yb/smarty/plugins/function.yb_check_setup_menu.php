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
 * YakiBiki Smarty Plugin : Check setup-scripts are enabled or not.
 *
 * @author msakamoto-sf <msakamoto-sf@users.sourceforge.net>
 * @version $Id$
 */

/**
 * @param array Parameters specified in Smarty Template.
 * @return string Output Contents
 */
function smarty_function_yb_check_setup_menu($params, &$smarty)
{
    if (_YB('setup.menu')) {
        $msg = t("Setup Scripts are enabled! Turn off _YB('setup.menu'), to 0 in your configuration!!");
        $icon = _YB('url.themes') . '/icons/warn.png';
        $str = '<ul class="session-get-flash-info"><li>';
        $str .= '<img src="' . $icon . '" alt="warn icon" title="warn"/>&nbsp;';
        $str .= '<em style="color: red;">' . $msg . '</em></li></ul>';
        return $str;
    }
    return "";
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
