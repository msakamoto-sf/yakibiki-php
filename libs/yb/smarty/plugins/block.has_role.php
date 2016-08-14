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
 *
 */

/**
 * YakiBiki Smarty Plugin : 'has_role' block plugin
 *
 * @author msakamoto-sf <msakamoto-sf@users.sourceforge.net>
 * @version $Id: block.has_role.php 355 2008-09-16 12:53:48Z msakamoto-sf $
 */

/**
 * Smarty 'has_role' block plugin
 *
 * @param mixed block arguments
 * @param string compiled source text
 * @param object smarty object reference
 * @param boolean if true, first call. if false, end-tag call.
 * @return string
 */
function smarty_block_has_role($params, $content, &$smarty, &$repeat)
{
    // output if end tag.
    if (!$repeat && isset($content)) {
        $uc = yb_Session::user_context();
        $_roles = $uc['role'];
        $_r = @$params['role'];
        if (in_array('sys', $_roles) || in_array($_r, $_roles)) {
            return $content;
        } else {
            return '';
        }
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
