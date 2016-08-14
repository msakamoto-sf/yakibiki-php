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
 * &gmap() wiki inline plugin (wrapper of <yb_gmap> html plugin)
 *
 * usage:
 * <code>
 * &gmap(id=map1,lat=32.11,lng=135.21,w=300,h=300,zoom=12,control=small+maptype+where,fixed,marker=true) { pinmarker message }
 * </code>
 *
 * @author msakamoto-sf <msakamoto-sf@users.sourceforge.net>
 * @version $Id: yb_plugin_wiki_gmap.php 552 2009-07-18 03:20:39Z msakamoto-sf $
 * @param string plugin parameter
 * @param string inline text
 * @param wiki_Context reference
 * @return string
 */
function yb_plugin_wiki_gmap_invoke_inline($param1, $param2, &$ctx)
{
    $param1 = yb_bin2hex(trim($param1));
    $param2 = yb_bin2hex(trim($param2));
    return '<yb_gmap ' . $param1 . ' >' . $param2 . '</yb_gmap>';
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
