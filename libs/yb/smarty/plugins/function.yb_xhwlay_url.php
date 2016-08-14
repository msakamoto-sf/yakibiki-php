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
 * YakiBiki Smarty Plugin : echo xhwlau_url
 *
 * @author msakamoto-sf <msakamoto-sf@users.sourceforge.net>
 * @version $Id$
 */

/**
 * yb_Util::xhwlay_url()'s wrapper.
 *
 * '__ENCURL__' is converted to make_url()'s 2nd args, if specified.
 *
 * @see yb_Util::xhwlay_url().
 */
function smarty_function_yb_xhwlay_url($params, &$smarty)
{
    $encurl = true;
    if (isset($params['__ENCURL__'])) {
        $encurl = $params['__ENCURL__'];
        unset($params['__ENCURL__']);
    }
    return yb_Util::xhwlay_url($params, $encurl);
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
