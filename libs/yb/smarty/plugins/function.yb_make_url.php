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
 * YakiBiki Smarty Plugin : echo back-to-url
 *
 * @author msakamoto-sf <msakamoto-sf@users.sourceforge.net>
 * @version $Id: function.yb_make_url.php 525 2009-06-16 17:59:38Z msakamoto-sf $
 */

/**
 * yb_Util::make_url()'s wrapper.
 *
 * '__ENCURL__' is converted to make_url()'s 2nd args, if specified.
 *
 * @see yb_Util::make_url().
 */
function smarty_function_yb_make_url($params, &$smarty)
{
    $encurl = true;
    if (isset($params['__ENCURL__'])) {
        $encurl = $params['__ENCURL__'];
        unset($params['__ENCURL__']);
    }
    $ticket_id = '';
    if (isset($params['__TICKET_ID__'])) {
        $ticket_id = $params['__TICKET_ID__'];
        unset($params['__TICKET_ID__']);
    }
    $ticket_name = '';
    if (isset($params['__TICKET_NAME__'])) {
        $ticket_name = $params['__TICKET_NAME__'];
        unset($params['__TICKET_NAME__']);
    }
    if (!empty($ticket_id) && !empty($ticket_name)) {
        $params[$ticket_name] = $ticket_id;
    }
    return yb_Util::make_url($params, $encurl);
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
