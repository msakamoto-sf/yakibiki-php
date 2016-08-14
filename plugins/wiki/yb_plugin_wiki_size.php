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

/*
 * &size()/#size|| wiki inline/block plugin
 *
 * usage:
 * <code>
 * &size(12px) { ... }
 *
 * or
 *
 * #size|xx-large|>
 * ...
 * ||<
 * </code>
 *
 * if parameter is omitted, "medium" size is used.
 *
 * @author msakamoto-sf <msakamoto-sf@users.sourceforge.net>
 * @version $Id: yb_plugin_wiki_size.php 548 2009-07-14 06:24:42Z msakamoto-sf $
 * @param string plugin parameter
 * @param string inline text
 * @param wiki_Context reference
 * @return string
 */
function yb_plugin_wiki_size_invoke_inline($param1, $param2, &$ctx)
{
    $size = yb_Util::check_css_size($param1);
    if (empty($size)) {
        $size = 'medium';
    }
    $text = wiki_Parser::convert_inline($param2, $ctx->did, $ctx->pagename);
    return '<span style="font-size: ' . $size . '">' . $text . '</span>';
}

function yb_plugin_wiki_size_invoke_block($param1, $param2, &$ctx)
{
    $size = yb_Util::check_css_size($param1);
    if (empty($size)) {
        $size = 'medium';
    }
    $text = wiki_Parser::convert_block($param2, $ctx->did, $ctx->pagename);
    return '<div style="font-size: ' . $size . '">' . $text . '</div>';
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
