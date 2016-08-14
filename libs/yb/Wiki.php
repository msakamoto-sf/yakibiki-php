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

require_once('wiki/Parser.php');

define('YB_WIKI_CACHE_GROUP', 'yb_Wiki_Cache_Group');

/**
 * YakiBiki Wiki Data special filter
 *
 * @author msakamoto-sf <msakamoto-sf@users.sourceforge.net>
 * @version $Id: Wiki.php 417 2008-11-05 23:39:28Z msakamoto-sf $
 */
class yb_Wiki
{
    // {{{ convert()

    /**
     * @static
     * @access public
     * @param string source text data
     * @param yb_DataContext reference
     * @param integer yb_Html::LIST_MODE() or yb_Html::DETAIL_MODE()
     * @param boolean use cache or not (if omitted, default true)
     * @return string parsed html text data.
     */
    function convert($did, $pagename, $src, &$ctx, $mode, $use_cache = true)
    {
        $cache =& new yb_Cache(YB_WIKI_CACHE_GROUP);

        if ($use_cache) {
            if ($wiki = $cache->get($did, YB_WIKI_CACHE_GROUP)) {
                return yb_Html::convert($wiki, $ctx, $mode);
            }
        }

        yb_Wiki::displayed_source($src);
        $wiki = wiki_Parser::convert_block($src, $did, $pagename);
        $ft =& wiki_Footnote::singleton();
        $wiki .= $ft->getnote($did);
        if ($use_cache) {
            $cache->save($wiki, $did, YB_WIKI_CACHE_GROUP);
        }

        return yb_Html::convert($wiki, $ctx, $mode);
    }

    // }}}
    // {{{ displayed_source()

    /**
     * @static
     * @access public
     * @param source displayed version source (optional)
     * @return string displayed version source
     */
    function displayed_source($src = null)
    {
        static $_src = '';
        if (!is_null($src)) {
            $_src = $src;
        }
        return $_src;
    }

    // }}}
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
