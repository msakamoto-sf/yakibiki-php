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
 * #newcomments|| wiki block plugin (wrapper of <yb_newcomments> html plugin)
 *
 * usage:
 * <code>
 * #newcomments|| : list up recent posted comments (default 10 comments).
 * #newcomments|99| : list up recent comments max 99 comments.
 * #newcomments|99|>
 * (ignored)
 * ||< : (same)
 * </code>
 *
 * @author msakamoto-sf <msakamoto-sf@users.sourceforge.net>
 * @version $Id: yb_plugin_wiki_newcomments.php 551 2009-07-15 05:36:47Z msakamoto-sf $
 * @param string plugin parameter
 * @param string inline text
 * @param wiki_Context reference
 * @return string
 */
function yb_plugin_wiki_newcomments_invoke_block($param1, $param2, &$ctx)
{
    $p = intval(trim($param1));
    return '<yb_newcomments ' . $p . ' />';
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