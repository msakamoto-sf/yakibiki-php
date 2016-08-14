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
 * YakiBiki Default URL routing hook.
 *
 * @author msakamoto-sf <msakamoto-sf@users.sourceforge.net>
 * @version $Id: yb_hook_make_url.php 405 2008-11-02 00:59:58Z msakamoto-sf $
 * @param array assoc array of url queries (key => val)
 * @param boolean automatically add Xhwlay's BCID or not. (default: false)
 * @param boolean apply urlrawencode() internally or not. (default: true)
 * @return string absolute url (use _YB('url')).
 */
function yb_hook_make_url($query_params, $for_xhwlay = false, $urlencs = true)
{
    $base = _YB('url'); // -> http://hogehoge/
    $_index = _YB('index_file');
    if (!empty($_index)) {
        $base .= $_index;
    }

    $module = '';
    $action = '';
    $id = '';
    if (isset($query_params['mdl'])) {
        $module = $query_params['mdl'];
        // if module is default module, then remove.
        if ($module == _YB('default.module')) {
            unset($query_params['mdl']);
        }
    }

    $_params = array();
    foreach ($query_params as $k => $v) {
        if ($urlencs) {
            $v = rawurlencode($v);
        }
        $_params[] = $k . '=' . h($v);
    }
    if ($for_xhwlay) {
        $_params[] = '_bcid_=' . Xhwlay_Var::get(XHWLAY_VAR_KEY_BCID);
    }
    $_query = '?' . implode('&', $_params);
    if (count($_params) > 0) {
        $base .= $_query;
    }

    return $base;
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
