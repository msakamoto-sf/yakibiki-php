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
 * <yb_link> html plugin
 *
 * usage:
 * <code>
 * <yb_link 40 /> : by id
 * <yb_link title /> : by title/name
 * <yb_link id/title>string</yb_link> : generate <a href="...">string</a>
 * <yb_link yb://relative/path</yb_link> : _YB('url') . '/relative/path' link
 * </code>
 *
 * @author msakamoto-sf <msakamoto-sf@users.sourceforge.net>
 * @version $Id: yb_plugin_html_link.php 465 2008-11-21 16:38:59Z msakamoto-sf $
 * @param mixed tag attribute
 * @param mixed internal element
 * @param yb_DataContext
 * @return string
 */
function yb_plugin_html_link_invoke($param1, $param2, &$ctx)
{
    static $_buffers = array();

    $user_context = yb_Session::user_context();

    $param1 = trim($param1);
    // split by fragment.
    $_els = explode('#', $param1, 2);
    $_p1 = $_els[0];
    $_fragment = @$_els[1];

    $_p2 = trim($param2);
    if (empty($_p1)) {
        return '';
    }

    if (empty($_p2)) {
        if (isset($_buffers[$param1]["\x00dummy"])) {
            return $_buffers[$param1]["\x00dummy"];
        }
    } else {
        if (isset($_buffers[$param1][$_p2])) {
            return $_buffers[$param1][$_p2];
        }
    }

    $is_external = false;
    $err = array();
    if (preg_match('/^\d+$/mi', $_p1)) {
        // ID is specified

        $data = yb_Finder::find_by_id(
            $user_context, 
            $_p1, 
            $err, 
            YB_ACL_PERM_READ, 
            false // don't expand owner, updated_by, categories data
        );
        if (is_null($data)) {
            return t($err['msg'], $err['args']);
        }

        $did = $_p1;
        $_name = $data['title'];
        $_url = yb_Util::make_url(array('mdl' => 'view', 'id' => $did));

    } else if (preg_match('/yb:\/\//mi', $_p1)) {
        // relative to _YB('url') path

        $is_external = true;
        $_path = str_replace('yb://', '', $_p1);
        $_url = _YB('url') . h($_path);
        $_name = _YB('url') . $_path;

    } else {
        // Title is specified

        $data = yb_Finder::find_by_title(
            $user_context, 
            $_p1, 
            $err, 
            YB_ACL_PERM_READ, 
            false // don't expand owner, updated_by, categories data
        );
        if (is_null($data)) {
            // not found in index -> make 'new' url
            $_name = $_p1;
            $_url = yb_Util::make_url(
                array('mdl' => 'new', 'title' => $_p1));
        } else {
            // 1 hit -> make direct url.
            $did = $data['id'];
            $_name = $data['title'];
            $_url = yb_Util::make_url(
                array('mdl' => 'view', 'id' => $did));
        }
    }

    $_title = (empty($_p2)) ? $_name : $_p2;

    if (!empty($_fragment)) {
        $_url .= '#' . $_fragment;
    }

    $_target = ($is_external) ? 'target="_blank" ' : '';
    $_link = sprintf('<a href="%s" %s>%s</a>', $_url, $_target, h($_title));
    if (empty($_p2)) {
        $_buffers[$param1]["\x00dummy"] = $_link;
    } else {
        $_buffers[$param1][$_p2] = $_link;
    }

    return $_link;
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
