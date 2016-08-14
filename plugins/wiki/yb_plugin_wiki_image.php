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
 * &image() { ... } wiki inline plugin
 *
 * usage:
 * <code>
 * &image(<data id>) { "alt" attribute text }
 * &image(<data id>, width=400)
 * &image(<data id>, width=400, height=500)
 * &image(http://.../xxyy.jpg, with=xxxx)
 * &image(yb://relative/path/image.jpg)
 * </code>
 *
 * @author msakamoto-sf <msakamoto-sf@users.sourceforge.net>
 * @version $Id: yb_plugin_wiki_image.php 547 2009-07-14 05:29:59Z msakamoto-sf $
 * @param string plugin parameter
 * @param string inline text
 * @param wiki_Context reference
 * @return string
 */
function yb_plugin_wiki_image_invoke_inline($param1, $param2, &$ctx)
{
    $param1 = trim($param1);
    $param2 = trim($param2);

    $els = explode(',', $param1);
    $els = array_map('trim', $els);

    $attrs_acceptable = array(
        'height', 'width', 'border', 'hspace', 'vspace', 'float');
    $data_spec = array_shift($els);
    $attrs = array();
    foreach ($els as $el) {
        $_els = explode('=', $el, 2);
        $_els = array_map('trim', $_els);
        $_k = h(strtolower($_els[0]));
        if (!in_array($_k, $attrs_acceptable)) {
            continue;
        }

        if (!isset($_els[1])) { continue; }
        $_v = strtolower($_els[1]);

        switch ($_k) {
        case 'float':
            switch ($_v) {
            case 'left':
            case 'right':
            case 'none':
                break;
            default:
                $_v = 'none';
            }
            $attrs[] = 'style="margin: 0.5ex; float: '.h($_v).'; "';
            break;
        default:
            $_v = intval($_v);
            $attrs[] = $_k.'="'.$_v.'"';

        }
    }

    $uc = yb_Session::user_context();
    $err = array();
    if (preg_match('/^\d+$/', $data_spec)) {
        $did = $data_spec;
        $data = yb_Finder::find_by_id($uc, $did, $err, YB_ACL_PERM_READ);
        if (is_null($data)) {
            $alt = '';
        } else {
            $alt = $data['title'];
        }
        $_url = yb_Util::redirect_url(array('mdl' => 'view', 'id' => $did));
        $_src = yb_Util::redirect_url(array('mdl' => 'raw', 'id' => $did));
    } else if (preg_match('/^https?:\/\//', $data_spec)) {
        $alt = '';
        $_url = $data_spec;
        $_src = $_url;
    } else if (preg_match('/yb:\/\//mi', $data_spec)) {
        $alt = '';
        $_path = str_replace('yb://', '', $data_spec);
        $_url = _YB('url') . $_path;
        $_src = $_url;
    } else {
        return h('&image(' . $param1 . '){' . $param2 . '}');
    }

    if (!empty($param2)) { $alt = $param2; }

    return sprintf(
        '<a href="%s" title="%s" target="_blank">' 
        . '<img src="%s" alt="%s" title="%s" %s /></a>',
        h($_url), h($alt), 
        h($_src), h($alt), h($alt), implode(' ', $attrs)
    );
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
