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
 * #help_outline|(max heading level)| wiki block plugin
 *
 * usage:
 * <code>
 * #help_outline||
 *
 * or
 *
 * #help_outline|3|
 * </code>
 *
 * @author msakamoto-sf <msakamoto-sf@users.sourceforge.net>
 * @version $Id: yb_plugin_wiki_help_outline.php 547 2009-07-14 05:29:59Z msakamoto-sf $
 * @param string plugin parameter
 * @param string inline text
 * @param wiki_Context reference
 * @return string
 */
function yb_plugin_wiki_help_outline_invoke_block($param1, $param2, &$ctx)
{
    $nest = intval(trim($param1));
    $help_page = $ctx->pagename;
    $locale = _YB('resource.locale');
    $help_file = _YB('dir.helpdocs') . "/{$locale}/{$help_page}.txt";
    $src = @file_get_contents($help_file);

    $body =& wiki_Parser::parse_block($src, $ctx->did, $ctx->pagename);
    $list = array();
    foreach ($body->getelements() as $e) {
        if (strtolower(get_class($e)) != 'wiki_t_heading') {
            continue;
        }
        $els = $e->getelem();
        $str = $els->getsource();
        $fragment = 'id' . substr(md5(
            $ctx->did . $e->getlevel() . $e->getsource()), 0, 6);
        if (empty($nest) || $e->getlevel() <= $nest) {
            $list[] = str_repeat('-', $e->getlevel()) 
                . ' &anchor(' . $fragment . '){' . $str . '}';
        }
    }

    return wiki_Parser::convert_block(join("\n", $list), 
        $ctx->did, $ctx->pagename);
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
