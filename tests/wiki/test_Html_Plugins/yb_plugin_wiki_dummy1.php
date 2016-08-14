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
 * wiki dummy1 plugin
 *
 * @author msakamoto-sf <msakamoto-sf@users.sourceforge.net>
 * @version $Id: yb_plugin_wiki_dummy1.php 278 2008-05-11 07:22:26Z msakamoto-sf $
 */

/**
 * @param string
 * @param string
 * @param wiki_Context reference
 */
function yb_plugin_wiki_dummy1_invoke_block($param1, $param2, &$ctx)
{
    return sprintf("!dummy1_block|%s|%s|%s|%s!",
        $param1, $param2, $ctx->did, $ctx->pagename);
}

/**
 * @param string
 * @param string
 * @param wiki_Context reference
 */
function yb_plugin_wiki_dummy1_invoke_inline($param1, $param2, &$ctx)
{
    return sprintf("!dummy1_inline|%s|%s|%s|%s!",
        $param1, $param2, $ctx->did, $ctx->pagename);
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
