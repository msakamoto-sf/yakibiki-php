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
 * &help() wiki inline plugin
 *
 * usage:
 * <code>
 * &help(WikiFormat)
 * </code>
 *
 * if parameter is omitted, displays 'HelpTop' help page link.
 *
 * @author msakamoto-sf <msakamoto-sf@users.sourceforge.net>
 * @version $Id: yb_plugin_wiki_help.php 468 2008-11-22 05:51:46Z msakamoto-sf $
 * @param string plugin parameter
 * @param string inline text
 * @param wiki_Context reference
 * @return string
 */
function yb_plugin_wiki_help_invoke_inline($param1, $param2, &$ctx)
{
    $helpname = h(trim($param1));
    if (empty($helpname)) {
        $helpname = 'HelpTop';
    }
    $_url = yb_Util::make_url(array('mdl' => 'help', 'h' => $helpname));
    $trans_helpname = t($helpname, null, 'help');
    return sprintf('<a href="%s">%s</a>', $_url, $trans_helpname);
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
