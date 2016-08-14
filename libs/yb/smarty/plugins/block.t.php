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
 * YakiBiki Smarty Plugin : 't' yb_Trans::t() wrapper block plugin
 *
 * @author msakamoto-sf <msakamoto-sf@users.sourceforge.net>
 * @version $Id: block.t.php 218 2008-03-23 22:46:27Z msakamoto-sf $
 */

/**
 * Smarty 't' block plugin
 *
 * @param mixed block arguments
 * @param string compiled source text
 * @param object smarty object reference
 * @param boolean if true, first call. if false, end-tag call.
 * @return string
 */
function smarty_block_t($params, $content, &$smarty, &$repeat)
{
    // output if end tag.
    if (!$repeat && isset($content)) {

        $args = array();
        $domain = null;
        foreach ($params as $_k => $_v) {
            if ($_k == 'domain') {
                $domain = $_v;
                continue;
            }
            $args[$_k] = h($_v);
        }
        return t($content, $args, $domain);
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
