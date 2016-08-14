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

//require_once('yb/tx/Search.php');

/*
 * <yb_navi> html plugin
 *
 * usage:
 * <code>
 *
 * <yb_navi /> : based on current page name.
 *
 * <yb_navi (virtual directory) /> : based on given virtual directory.
 *
 * <yb_navi (virtual directroy)>header</yb_navi> 
 * : with header hr tags and simple display.
 *
 * <yb_navi (virtual directroy)>footer</yb_navi> 
 * : with footer hr tags and detailed display.
 *
 * </code>
 *
 * @author msakamoto-sf <msakamoto-sf@users.sourceforge.net>
 * @version $Id: yb_plugin_html_navi.php 551 2009-07-15 05:36:47Z msakamoto-sf $
 * @param mixed tag attribute
 * @param mixed internal element
 * @param yb_DataContext
 * @return string
 */
function yb_plugin_html_navi_invoke($param1, $param2, &$ctx)
{
    static $_match_cache = array(); // internal index match cache
    static $_link_cache = array(); // internal page-navi-link cache

    $param1 = yb_hex2bin($param1);
    $param2 = yb_hex2bin($param2);

    $base = trim($param1);
    if (empty($base)) {
        // if base point was not give, current page name will be base point.
        $base = $ctx->get('title');
    }
    $current = $ctx->get('title');
    $is_base = ($base == $current);

    $display_type = trim($param2);
    switch ($display_type) {
    case 'header':
    case 'footer':
        break;
    default:
        $display_type = 'normal';
    }

    // {{{ List up child pages.
    if (isset($_match_cache[$base])) {
        // use cache.
        $results = $_match_cache[$base];
    } else {

        $uc = yb_Session::user_context();
        $finder =& new yb_Finder();
        $finder->textmatch = $base;
        $finder->use_listmatch = true;
        $ids = $finder->search($uc);

        $dao =& yb_dao_Factory::get('data');
        $_datas = $dao->find_by_id($ids);
        $results = array();
        foreach ($_datas as $d) {
            $_did = $d['id'];
            $results[$_did] = $d['title'];
        }
        natsort($results);

        $_match_cache[$base] = $results;
    }
    if (count($results) == 0) {
        return "(Specified name : [".h($base)."] was not found.)";
    }
    if (count($results) == 1 && 
        array_search($base, $results) != false) {
        // If only one page found and it is same to given base name.

        if (!$is_base) {
            // If current is not base, simply child data was not found.
            return "(Specified data (name : [".h($base)."]) "
                ."does not have any child datas like : [".h($base)."/Foo])";
        } else {
            // If current is same to base, simply, this plugin has NO EFFECTS.
            return '';
        }
    }
    // }}}

    if (!isset($_link_cache[$base][$current])) {
        $_link_cache[$base][$current] = 
            _yb_plugin_html_navi_extract_page_links(
                $base, $current, $is_base, $ctx, $results);
    }
    $_links = $_link_cache[$base][$current];

    return _yb_plugin_html_navi_make_html($_links, $display_type);
}

// {{{ _yb_plugin_html_navi_extract_page_links()

function _yb_plugin_html_navi_extract_page_links(
    $base, $current, $is_base, $ctx, $results)
{
    $pos = strrpos($current, '/');
    $up = '';
    if ($pos > 0) {
        $up = substr($current, 0, $pos);
    }

    $pages = array(
        'prev_id' => 0,
        'prev_name' => '',
        'base_id' => 0,
        'base_name' => $base,
        'up_id' => 0,
        'up_name' => $up,
        'next_id' => 0,
        'next_name' => '',
    );
    if ($is_base) {
        $pages['base_id'] = $ctx->get('id');
    }

    if (array_search($current, $results) == false) {
        // If current page is not in listmatch()'s result, then, 
        // current page is OUTER page.
        // So, navis we should display are only two of "base" and "next".

        reset($results);
        $pages['base_id'] = key($results);
        next($results);
        $pages['next_id'] = key($results);
        $pages['next_name'] = current($results);

    } else {
        // Current page is in listmatch()'s result.

        reset($results);
        foreach ($results as $_id => $_name) {
            if (!$is_base && $_name == $base) {
                $pages['base_id'] = $_id;
            }
            if ($_name == $up) {
                $pages['up_id'] = $_id;
            }
            if ($_name == $current) {
                break;
            }
            $pages['prev_id'] = $_id;
            $pages['prev_name'] = $_name;
        }
        $pages['next_id'] = key($results);
        $pages['next_name'] = current($results);
    }

    // "**1" => Alphabetical short format.
    // "**2" => Full format displaying title/name.
    $_links = array(
        'prev1' => '',
        'prev2' => '',
        'base1' => '',
        'base2' => '',
        'up1' => '',
        'up2' => '',
        'next1' => '',
        'next2' => '',
    );
    if (!empty($pages['prev_id'])) {
        list($_f, $_s) = _yb_plugin_html_navi_make_links(
            $pages['prev_id'], $pages['prev_name'], 'Prev');
        $_links['prev1'] = $_s;
        $_links['prev2'] = $_f;
    }
    if (!empty($pages['next_id'])) {
        list($_f, $_s) = _yb_plugin_html_navi_make_links(
            $pages['next_id'], $pages['next_name'], 'Next');
        $_links['next1'] = $_s;
        $_links['next2'] = $_f;
    }
    if (!empty($pages['base_id'])) {
        list($_f, $_s) = _yb_plugin_html_navi_make_links(
            $pages['base_id'], $pages['base_name'], 'Home');
        $_links['base1'] = $_s;
        $_links['base2'] = $_f;
    }
    if (!empty($pages['up_id'])) {
        list($_f, $_s) = _yb_plugin_html_navi_make_links(
            $pages['up_id'], $pages['up_name'], 'Up');
        $_links['up1'] = $_s;
        $_links['up2'] = $_f;
    }

    return $_links;
}

// }}}
// {{{ _yb_plugin_html_navi_make_links()

function _yb_plugin_html_navi_make_links($id, $name, $short_name)
{
    static $_fmt = '<a href="%s" title="%s">%s</a>';
    $_url = yb_Util::make_url(array('mdl' => 'view', 'id' => $id));
    $_full = sprintf($_fmt, $_url, h($name), h($name));
    $_short = sprintf($_fmt, $_url, h($name), h($short_name));

    return array($_full, $_short);
}

// }}}
// {{{ _yb_plugin_html_navi_make_html()

function _yb_plugin_html_navi_make_html($links, $display_type)
{
    $_html = array();
    switch ($display_type) {
    case 'header':
        $_html[] = '<ul class="plugin_navi">';
        if (!empty($links['prev1'])) {
            $_html[] = '<li>[&nbsp;' . $links['prev1'] . '&nbsp;]</li>';
        }
        if (!empty($links['next1'])) {
            $_html[] = '<li>[&nbsp;' . $links['next1'] . '&nbsp;]</li>';
        }
        if (!empty($links['base2'])) {
            $_html[] = '<li>[&nbsp;' . $links['base2'] . '&nbsp;]</li>';
        }
        $_html[] = '</ul>';
        $_html[] = '<hr class="full_hr" />';
        break;
    case 'footer':
        $_html[] = '<hr class="full_hr" />';
    default:
        $_html[] = '<ul class="plugin_navi">';
        if (!empty($links['prev1'])) {
            $_html[] = '<li>[&nbsp;' . $links['prev1'] . '&nbsp;]</li>';
        }
        if (!empty($links['next1'])) {
            $_html[] = '<li>[&nbsp;' . $links['next1'] . '&nbsp;]</li>';
        }
        if (!empty($links['up1'])) {
            $_html[] = '<li>[&nbsp;' . $links['up1'] . '&nbsp;]</li>';
        }
        if (!empty($links['base2'])) {
            $_html[] = '<li>[&nbsp;' . $links['base2'] . '&nbsp;]</li>';
        }
        $_html[] = '</ul>';
    }

    return implode("\n", $_html);
}

// }}}

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
