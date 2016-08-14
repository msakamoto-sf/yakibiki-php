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
 * YakiBiki Smarty Plugin : default HTML escape modifier wrapper
 *
 * Special Thanks to shimooka!!
 * @see http://d.hatena.ne.jp/shimooka/20080714/1216021170
 *
 * @author msakamoto-sf <msakamoto-sf@users.sourceforge.net>
 * @version $Id: modifier.yb_escape.php 324 2008-09-10 04:10:10Z msakamoto-sf $
 */

require_once(_YB('dir.libs') . '/smarty/plugins/modifier.escape.php');

/**
 * YakiBiki Smarty default HTML escape modifier wrapper
 *
 * @param string yb_Time internal raw value (ex. created_at, updated_at)
 * @param string strftime()'s format string
 * @return string
 */
function smarty_modifier_yb_escape(
    $string, $esc_type = 'html', $char_set = 'UTF-8')
{
    switch (gettype($string)) {
    case 'string':
        return smarty_modifier_escape($string, $esc_type, $char_set);
    default:
        return $string;
    }
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
