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
 * YakiBiki HTML Data special filter
 *
 * @author msakamoto-sf <msakamoto-sf@users.sourceforge.net>
 * @version $Id: Html.php 408 2008-11-02 14:31:19Z msakamoto-sf $
 */
class yb_Html
{
    /**#@+
     * @static
     * @access public
     */

    function LIST_MODE() { return 1; }
    function DETAIL_MODE() { return 2; }

    /**#@-*/

    // {{{ convert()

    /**
     * @static
     * @access public
     * @param string source text data
     * @param yb_DataContext reference
     * @param integer yb_Html::LIST_MODE() or yb_Html::DETAIL_MODE()
     * @return string parsed html text data.
     */
    function convert($source, &$ctx, $mode)
    {
        $dest = '';
        $regexp1 = '!^<yb_[a-zA-Z0-9_]+(?:[\t ]+.*?)?[\t ]*?/?>!mi';

        $source = preg_replace('/\r?\n/', "\n", $source);
        $src = $source;
        while ($src != '') {

            // pre-match and cut strings by using "stristr", 
            // which is not terminated huge size string.
            if (($prematched = stristr($src, '<yb_')) === false) {
                $dest .= $src;
                break;
            }
            $backtrack = _substr($src, 0, 
                strlen($src) - strlen($prematched));

            // exact tag matching using regexp.
            if (preg_match($regexp1, $prematched, $m)) {
                $dest .= $backtrack;
                $src = $prematched;
                if (!yb_Html::parse($src, $dest, $ctx, $mode)) {
                    // <yb_more />
                    $src = '';
                    break;
                }
            } else {
                $dest .= $src;
                break;
            }
        }
        if ($ctx->get('show_readmore')) {
            $_url = yb_Util::make_url(array(
                'mdl' => 'view', 'id' => $ctx->get('id')));
            $dest .= sprintf(
                '<a href="%s" class="readmore_link">%s</a>', 
                $_url, t('(show all text)'));
        }
        return $dest;
    }

    // }}}
    // {{{ parse()

    /**
     * parse html document and invoke yb html plugins.
     *
     * @static
     * @access public
     * @param string reference to source text data.
     * @param string reference to destination text buffer.
     * @param yb_DataContext reference to yb data context object.
     * @param integer yb_Html::LIST_MODE() || yb_Html::DETAIL_MODE()
     * @return boolean if continue, return true.
     *                 if parse break, return false.
     */
    function parse(&$str, &$dest, &$ctx, $mode)
    {
        $ctx->set('show_readmore', false);
        $regexp1 = '!^<yb_([a-zA-Z0-9_]+)(?:[\t ]+(.*?))?[\t ]*(\/?)>!i';
        if (!preg_match($regexp1, $str, $m)) {
            return true;
        }

        $tag = $m[0];
        $pluginname = 'yb_' . strtolower($m[1]);
        $param1 = $m[2];

        if ($pluginname == 'yb_more' && $mode == yb_Html::LIST_MODE()) {
            $ctx->set('show_readmore', true);
            return false;
        }

        if ($m[3] == '/') {
            $dest .= yb_Html::invoke_html_plugin(
                $pluginname, $param1, '', $ctx);
            $str = _substr($str, strlen($tag));
            return true;
        }

        if (!preg_match("!</$pluginname>!i", $str)) {
            // Short-Cut if there's not closing-tag.
            $str = _substr($str, strlen($tag));
            return true;
        }
        $s1 = _substr($str, strlen($tag));
        $param2 = '';
        //if (preg_match("!^(.*?)(?:</$pluginname>|\\n)!m", $s1, $m)) {
        if (preg_match("!^((?:.|\n)*?)</$pluginname>!mi", $s1, $m)) {
            $param2 = $m[1];
            $s1 = _substr($s1, strlen($param2));
        }
        if (!preg_match("!^</$pluginname>!i", $s1, $m)) {
            return false;
        }
        $close_tag = $m[0];
        $cut_pos = strlen($tag) + strlen($param2) + strlen($close_tag);


        $dest .= yb_Html::invoke_html_plugin(
            $pluginname, $param1, $param2, $ctx);
        $str = _substr($str, $cut_pos);

        return true;
    }

    // }}}
    // {{{ invoke_html_plugin()

    /**
     * @static
     * @access public
     * @param string plugin name ("yb_****")
     * @param string plugin's tag attribute
     * @param string plugin's tag internal element data.
     * @param yb_DataContext reference
     * @return string
     */
    function invoke_html_plugin($pluginname, $param1, $param2, &$ctx)
    {
        if ($pluginname == 'yb_more') {
            return '';
        }
        $_dir = _YB('dir.plugin.html');

        $_plugin_basename = str_replace('yb_', '', $pluginname);

        $_pfile = realpath($_dir . '/yb_plugin_html_' 
            . $_plugin_basename . '.php');

        $ret = include_once $_pfile;
        if (!$ret) {
            // TODO Error Handlings.
            return '';
        }

        $_fname = 'yb_plugin_html_' . $_plugin_basename . '_invoke';
        if (!function_exists($_fname)) {
            // TODO Error Handlings.
            return '';
        }
        return $_fname($param1, $param2, $ctx);
    }

    // }}}
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
