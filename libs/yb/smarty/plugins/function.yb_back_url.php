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
 * @version $Id: function.yb_back_url.php 151 2007-12-24 19:07:36Z msakamoto-sf $
 */

/**
 * _YB('current_url') wrapper.
 *
 * @param array Parameters specified in Smarty Template.
 *              If 'encode' is false, then, do not raw_url_encode().
 * @return string Output Contents
 */
function smarty_function_yb_back_url($params, &$smarty)
{
    $url = yb_Util::current_url();
    if (isset($params['encode']) && $params['encode'] == false) {
        return $url;
    } else {
        return rawurlencode($url);
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
