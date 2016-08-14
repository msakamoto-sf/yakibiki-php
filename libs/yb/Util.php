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
 *   limitations under the License.*
 */

require_once('HTTP/Header.php');

/**
 * YakiBiki Utility static functions
 *
 * @author msakamoto-sf <msakamoto-sf@users.sourceforge.net>
 * @version $Id: Util.php 566 2009-07-23 13:57:45Z msakamoto-sf $
 */
class yb_Util
{
    // {{{ hook()

    /**
     * YakiBiki Hook caller.
     *
     * <code>
     * yb_Util::hook('hogehoge', array($ar1, &$ar2, $ar3...));
     * // -> search _YB('dir.plugin.hook') directory and 
     *       'yb_hook_hogehoge.php' file, load it, and call
     *       yb_hook_hogehoge() function.
     * </code>
     * Arguments are passed by call_user_func_array().
     * TODO If hook file not found, then error log output.
     *
     * And, you can replace your own hooks without change yb's original 
     * hook php file by defining _YB('hook.convert.(hook name)').
     * <code>
     * // in config.php
     * _YB('hook.convert.sample', 'my_sample');
     * ...
     * // in call point.
     * yb_Util::hook('sample', array(...));
     * </code>
     * Above sample, 'yb_hook_my_sample.php' is acutually included and called.
     * NOT yb_hook_sample.php.
     *
     * @static
     * @access public
     * @param string hook name
     * @param array 2nd arg of call_user_func_array()
     */
    function hook($hn, $args)
    {
        $_hn = _YB('hook.convert.' . $hn);
        if (!empty($_hn)) {
            // replace hook name.
            $hn = $_hn;
        }

        $_hookfile = _YB('dir.plugin.hook') . '/yb_hook_' . $hn . '.php';
        $ret = include_once $_hookfile;
        if (!$ret) {
            // TODO Error Handlings.
            return;
        }

        $f = 'yb_hook_' . $hn;
        return call_user_func_array($f, $args);
    }

    // }}}
    // {{{ factoryDataType()

    /**
     * YakiBiki Data-Type instance factory
     *
     * @static
     * @access public
     * @param string data-type
     * @return object yb_datatype_XXXX instance.
     *                if data type is not found, then null.
     */
    function &factoryDataType($type)
    {
        $ret = null;
        static $dts = array();
        if (isset($dts[$type])) {
            return $dts[$type];
        }

        $datatypes = _YB('datatypes');
        if (!isset($datatypes[$type]['class'])) {
            return $ret;
        }
        $_klass = $datatypes[$type]['class'];
        if (!class_exists($_klass)) {
            $_file = str_replace('_', '/', $_klass) . '.php';
            $r = include_once $_file;
            if (!$r) {
                return $ret;
            }
        }
        if (!class_exists($_klass)) {
            return $ret;
        }

        $dts[$type] = new $_klass();

        return $dts[$type];
    }

    // }}}
    // {{{ make_url()

    /**
     * YakiBiki make_url hook wrapper.
     *
     * @static
     * @access public
     * @param array assoc array of url queries (key => val)
     * @param boolean apply urlrawencode() internally or not.
     * @return string absolute url (use _YB('url')).
     */
    function make_url($query_params, $urlencs = true)
    {
        return h(yb_Util::hook('make_url', 
            array($query_params, false, $urlencs)));
    }

    // }}}
    // {{{ xhwlay_url()

    /**
     * YakiBiki Xhwlay's BCID embedded make_url hook wrapper.
     *
     * @static
     * @access public
     * @param array assoc array of url queries (key => val)
     * @param boolean apply urlrawencode() internally or not.
     * @return string absolute url (use _YB('url')).
     */
    function xhwlay_url($query_params, $urlencs = true)
    {
        return h(yb_Util::hook('make_url', 
            array($query_params, true, $urlencs)));
    }

    // }}}
    // {{{ redirect_url()

    /**
     * return url for "Location" header : not encode "&" to "&amp;"
     *
     * @static
     * @access public
     * @param array assoc array of url queries (key => val)
     * @param boolean apply urlrawencode() internally or not.
     * @return string absolute url (use _YB('url')).
     */
    function redirect_url($query_params, $urlencs = true)
    {
        return yb_Util::hook('make_url', 
            array($query_params, false, $urlencs));
    }

    // }}}
    // {{{ array_remove_empty_string()

    /**
     * Remove empty string ("", '') from given simple array.
     *
     * @static
     * @access public
     * @param array NOTICE: not assoc array.
     * @return array
     */
    function array_remove_empty_string($_arr)
    {
        $result = array();
        foreach ($_arr as $v) {
            if ($v !== "") {
                $result[] = $v;
            }
        }
        return $result;
    }

    // }}}
    // {{{ array_or()

    /**
     * Array's OR operator
     *
     * @static
     * @access public
     * @param array
     * @return array
     */
    function array_or()
    {
        $args = func_get_args();
        switch(func_num_args()) {
        case 0:
            return array();
        case 1:
            return $args[0];
        }
        $a = call_user_func_array('array_merge', $args);
        return array_unique($a);
    }

    // }}}
    // {{{ array_and()

    /**
     * Array's AND operator
     *
     * @static
     * @access public
     * @param array
     * @return array
     */
    function array_and()
    {
        $args = func_get_args();
        switch(func_num_args()) {
        case 0:
            return array();
        case 1:
            return $args[0];
        }
        return call_user_func_array('array_intersect', $args);
    }

    // }}}
    // {{{ encode_ctrl_char()

    /**
     * Encode \t, \r, \n characters to "\t", "\r", "\n".
     *
     * @static
     * @access public
     * @param string
     * @return array
     */
    function encode_ctrl_char($str)
    {
        return str_replace(
            array("\r", "\n", "\t"),
            array('\r', '\n', '\t'),
            $str);
    }

    // }}}
    // {{{ decode_ctrl_char()

    /**
     * Decode "\t", "\r", "\n" characters to \t, \r, \n.
     *
     * @static
     * @access public
     * @param string
     * @return array
     */
    function decode_ctrl_char($str)
    {
        return str_replace(
            array('\r', '\n', '\t'),
            array("\r", "\n", "\t"),
            $str);
    }

    // }}}
    // {{{ url_parse()

    /**
     * parse_url()'s wrapper.
     *
     *
     * @see http://jp.php.net/manual/ja/function.parse-url.php
     * @static
     * @access public
     * @param string url
     * @return array assoc-array
     */
    function url_parse($url)
    {
        $returns = array(
            'scheme' => '',
            'host' => '',
            'port' => '',
            'user' => '',
            'pass' => '',
            'path' => '',
            'query' => '',
            'fragment' => '',
        );

        $results = parse_url($url);
        if ($results === false) {
            return $returns;
        }
        foreach ($results as $k => $v) {
            $returns[$k] = $v;
        }

        if (empty($returns['port'])) {
            if ($returns['scheme'] == 'http') {
                $returns['port'] = 80;
            } else if ($returns['scheme'] == 'https') {
                $returns['port'] = 443;
            }
        }
        return $returns;
    }

    // }}}
    // {{{ current_url()

    /**
     * Get current url
     *
     * @static
     * @access public
     * @return string absolute url
     */
    function current_url()
    {
        $_els = yb_Util::url_parse(_YB('url'));
        $_port = ":" . $_els['port'];
        if (($_els['scheme'] == 'https' && $_els['port'] == 443) || 
            ($_els['scheme'] == 'http' && $_els['port'] == 80)) {
            $_port = '';
        }
        $_url = $_els['scheme'] . '://' . $_els['host'] . $_port 
            . yb_Var::server('REQUEST_URI');

        return $_url;
    }

    // }}}
    // {{{ hash_password()

    /**
     * hash password
     *
     * @static
     * @access public
     * @param string password
     * @return string hashed password
     */
    function hash_password($password)
    {
        $func = _YB('password.hash.func');
        $salt = _YB('password.salt');
        if (!is_callable($func)) {
            return $password;
        } else {
            return $func(strrev($salt) . $password . $salt);
        }
    }

    // }}}
    // {{{ upload_max_filesize()

    /**
     * Return php.ini's "upload_max_filesize" value as bytes.
     *
     * @static
     * @access public
     * @return integer
     */
    function upload_max_filesize()
    {
        $val = ini_get('upload_max_filesize');
        if (empty($val)) {
            return 0;
        }
        $val = trim($val);
        $last = strtolower($val{strlen($val)-1});
        switch($last) {
            // 'G' is available from PHP 5.1.0
            case 'g':
                $val *= 1024;
            case 'm':
                $val *= 1024;
            case 'k':
                $val *= 1024;
        }
        return $val;
    }

    // }}}
    // {{{ resolvepath()

    /**
     * Resolve pagename's path.
     *
     * @static
     * @access public
     * @param string page name
     * @param string base path for relative pagename like '../../xyz/../..'
     * @return string full path
     */
    function resolvepath($pagename, $basepath = '')
    {
        $pagename = trim($pagename);
        if (preg_match('/^\.\.?\//', $pagename)) {
            $path = trim($basepath) . '/./' . $pagename;
        } else {
            $path = $pagename;
        }

        $path = explode('/', $path);
        $ret = array();
        foreach ($path as $p) {
            if ($p == '' || $p == '.') {
                continue;
            }
            if ($p == '..') {
                array_pop($ret);
            } else {
                array_push($ret, $p);
            }
        }
        return implode('/', $ret);
    }

    // }}}
    // {{{ protectmail_url

    /**
     * Apply simple protection to mail address for a tag.
     *
     * @static
     * @access public
     * @param string e-mail address
     * @return string protected string
     */
    function protectmail_url($address)
    {
        $encoded = chunk_split(bin2hex($address), 2, '%');
        return '%' . _substr($encoded, 0, strlen($encoded) - 1);
    }

    // }}}
    // {{{ protectmail_html

    /**
     * Apply simple protection to mail address for html raw string.
     *
     * @static
     * @access public
     * @param string e-mail address
     * @return string protected string
     */
    function protectmail_html($address)
    {
        $encoded = chunk_split(bin2hex($address), 2, ';&#x');
        return '&#x' . _substr($encoded, 0, strlen($encoded) - 3);
    }

    // }}}
    // {{{ extract_dataname_with_re

    /**
     * Extract base name from "Re: ... (x)" modified name.
     *
     * ex):
     * "Re: abc" -> "abc"
     * "Re: \t FooBar (20)" -> "FooBar"
     *
     * @static
     * @access public
     * @param string original string
     * @return string extracted name
     */
    function extract_dataname_with_re($s)
    {
        if (preg_match('/^re:[\t ]*(.+?)[\t ]*$/im', $s, $m1)) {
            if (preg_match('/^re:[\t ]*(.+?)[\t ]*\(\d+\)$/im', $s, $m2)) {
                return $m2[1];
            }
            return $m1[1];
        }
        return $s;
    }

    // }}}
    // {{{ forward_error_die()

    /**
     * Forward and display error html and die.
     *
     * <code>
     * // error message only.
     * yb_Util::forward_error_die("Error Message");
     * // error message and yb error code
     * yb_Util::forward_error_die("Error Message", 1000);
     * // error message, yb error code, and http status code.
     * yb_Util::forward_error_die("Error Message", 1000, 404);
     * // error message and http status code. (don't specify yb error code)
     * yb_Util::forward_error_die("Error Message", null, 404);
     * </code>
     *
     * @static
     * @access public
     * @param string error message
     * @param integer error code
     * @param integer HTTP Status Code
     */
    function forward_error_die($msg, $code = null, $status = null)
    {
        $user_context = yb_Session::user_context();
        if (!is_null($status)) {
            $header =& new HTTP_Header();
            $header->sendStatusCode($status);
        }

        $renderer =& new yb_smarty_Renderer();
        $renderer->setTitle(t('Error'));
        $renderer->set('user_context', $user_context);
        $renderer->set('msg', $msg);
        $renderer->set('code', $code);
        $renderer->setViewName("theme:forward_error_die_tpl.html");
        $output = $renderer->render();
        echo $output;

        die();
    }

    // }}}
    // {{{ checkdatetime()

    /**
     * PHP's checkdate() wrapper.
     *
     * @static
     * @access public
     * @param integer year
     * @param integer month
     * @param integer day
     * @param integer hour (omittable)
     * @param integer minute (omittable)
     * @param integer second (omittable)
     * @return boolean checkdate() result
     */
    function checkdatetime($y, $m, $d, $h = 0, $m2 = 0, $s = 0)
    {
        $y = (integer)$y;
        $m = (integer)$m;
        $d = (integer)$d;
        $h = (integer)$h;
        $m2 = (integer)$m2;
        $s = (integer)$s;
        return checkdate($m, $d, $y) && 
            ( (0 <= $h) && ($h <= 23) ) &&
            ( (0 <= $m2) && ($m2 <= 59) ) &&
            ( (0 <= $s) && ($s <= 59) );
    }

    // }}}
    // {{{ get_user_info_ex()

    /**
     * Get user data (including YB_GUEST_UID)
     *
     * @static
     * @access public
     * @param integer user id
     * @return array user data (result of yb_dao_User::find_by_id())
     */
    function get_user_info_ex($uid)
    {
        if ($uid == YB_GUEST_UID) {
            return yb_Session::anonymous_user_context();
        }
        $dao =& yb_dao_Factory::get('user');
        $results = $dao->find_by_id($uid);
        if (count($results) != 1) {
            return false;
        }
        return $results[0];
    }

    // }}}
    // {{{ check_email_address()

    /**
     * Check E-Mail format and address validation
     *
     * @static
     * @access public
     * @param string e-mail address
     * @param boolean use strict regexp or not (default : true, use)
     * @param boolean use checkdnsrr() or not (default : false, not)
     * @return boolean
     */
    function check_email_address(
        $email, $use_strict = true, $use_checkdnsrr = false)
    {
        if ($use_strict == true) {
            $re = '/^([^@\s]+)@((?:[-a-z0-9]+\.)+[a-z]{2,})$/i';
        } else {
            /* Cal Henderson: http://iamcal.com/publish/articles/php/parsing_email/pdf/
             * The long regular expression below is made by the following code
             * fragment:
             *
             *   $qtext = '[^\\x0d\\x22\\x5c\\x80-\\xff]';
             *   $dtext = '[^\\x0d\\x5b-\\x5d\\x80-\\xff]';
             *   $atom = '[^\\x00-\\x20\\x22\\x28\\x29\\x2c\\x2e\\x3a-\\x3c'
             *         . '\\x3e\\x40\\x5b-\\x5d\\x7f-\\xff]+';
             *   $quoted_pair = '\\x5c\\x00-\\x7f';
             *   $domain_literal = "\\x5b($dtext|$quoted_pair)*\\x5d";
             *   $quoted_string = "\\x22($qtext|$quoted_pair)*\\x22";
             *   $domain_ref = $atom;
             *   $sub_domain = "($domain_ref|$domain_literal)";
             *   $word = "($atom|$quoted_string)";
             *   $domain = "$sub_domain(\\x2e$sub_domain)*";
             *   $local_part = "$word(\\x2e$word)*";
             *   $addr_spec = "$local_part\\x40$domain";
             */

            $re = '/^([^\\x00-\\x20\\x22\\x28\\x29\\x2c\\x2e\\x3a-\\x3c\\x3e\\x40\\x5b-'
                .'\\x5d\\x7f-\\xff]+|\\x22([^\\x0d\\x22\\x5c\\x80-\\xff]|\\x5c\\x00-'
                .'\\x7f)*\\x22)(\\x2e([^\\x00-\\x20\\x22\\x28\\x29\\x2c\\x2e\\x3a-'
                .'\\x3c\\x3e\\x40\\x5b-\\x5d\\x7f-\\xff]+|\\x22([^\\x0d\\x22\\x5c\\x80'
                .'-\\xff]|\\x5c\\x00-\\x7f)*\\x22))*\\x40([^\\x00-\\x20\\x22\\x28\\x29'
                .'\\x2c\\x2e\\x3a-\\x3c\\x3e\\x40\\x5b-\\x5d\\x7f-\\xff]+|\\x5b([^'
                .'\\x0d\\x5b-\\x5d\\x80-\\xff]|\\x5c\\x00-\\x7f)*\\x5d)(\\x2e([^\\x00-'
                .'\\x20\\x22\\x28\\x29\\x2c\\x2e\\x3a-\\x3c\\x3e\\x40\\x5b-\\x5d\\x7f-'
                .'\\xff]+|\\x5b([^\\x0d\\x5b-\\x5d\\x80-\\xff]|\\x5c\\x00-\\x7f)*'
                .'\\x5d))*$/'
                ;
        }

        if (!preg_match($re, $email)) {
            return false;
        }

        if ($use_checkdnsrr && function_exists('checkdnsrr')) {
            $tokens = explode('@', $email);
            if (!checkdnsrr($tokens[1], 'MX') && 
                !checkdnsrr($tokens[1], 'A')) {
                return false;
            }
        }

        return true;
    }

    // }}}
    // {{{ file_open_lock()

    /**
     * Generic file open and lock function.
     *
     * @static
     * @access public
     * @param string file name
     * @param string fopen()'s mode (ex: 'r+', 'rb')
     * @param integer LOCK_EX or LOCK_SH
     * @return fp return file pointer (If error raised, null return.)
     */
    function file_open_lock($filename, $fopen_flag, $lock_mode)
    {
        $fp = fopen($filename, $fopen_flag);
        if (!$fp) {
            yb_Error::raise("fopen('" . $filename . "') failed!");
            return null;
        }
        if (!flock($fp, $lock_mode)) {
            yb_Error::raise("flock('" . $filename . "', " . $lock_mode . ") failed!");
            return null;
        }

        if (fseek($fp, 0) === false) {
            yb_Error::raise("fseek('" . $filename . "', 0) failed!");
            return null;
        }

        return $fp;
    }

    // }}}
    // {{{ file_write()

    /**
     * Generic file write function
     *
     * @static
     * @access public
     * @param resource file pointer
     * @param string write data
     * @param integer data length
     * @param boolean call ftruncate() or not
     * @param string filename for error message(optional)
     * @return integer written data length, if error, return false.
     */
    function file_write($fp, $data, $length, $call_ftruncate, $filename = '')
    {
        if ($call_ftruncate) {
            if (!ftruncate($fp, 0)) {
                yb_Error::raise("ftruncate('{$filename}') failed!");
                return false;
            }
        }

        if (fseek($fp, 0, SEEK_END) === false) {
            yb_Error::raise("fseek('{$filename}') failed!");
            return false;
        }
        $length = (integer)$length;
        $write_len = fwrite($fp, $data, $length);
        if ($write_len !== $length) {
            yb_Error::raise("fwrite('{$filename}') failed!");
            return false;
        }

        return $write_len;
    }

    // }}}
    // {{{ user_roles_displaynames()

    /**
     * Get user role's display names
     *
     * @static
     * @access public
     * @return array assoc-array of role to display name.
     */
    function user_roles_displaynames()
    {
        return array(
            'sys' => t('System role'),
            'group' => t('Group role'),
            'acl' => t('ACL role'),
            'category' => t('Category role'),
            'template' => t('Template role'),
            'new' => t('New Document role'),
        );
    }

    // }}}
    // {{{ explode_for_autols()

    /**
     * original explode() for yb_auto_ls smarty plugin.
     *
     * @static
     * @access public
     * @param string title for auto-ls.
     * @return array assoc-array of exploded parts.
     */
    function explode_for_autols($title)
    {
        if (strlen($title) === 0) {
            return array();
        }
        if (strlen($title) == 1 && $title == '/') {
            return array(array('/', '/'));
        }
        if (strpos($title, '/') === false) {
            return array(array($title, $title));
        }
        $src = $title;
        $_progress = '';

        $ret = array();
        while (($p = strpos($src, '/')) !== false) {
            if ($p == 0) { // matches !^/.*!
                $_progress .= '/';
                $ret[] = array('/', $_progress);
                $src = _substr($src, 1);
                continue;
            }
            $_s = _substr($src, 0, $p);
            $_progress .= $_s;
            $ret[] = array($_s, $_progress);
            $_progress .= '/';
            $ret[] = array('/', $_progress);

            $src = _substr($src, $p + 1);
        }
        if (strlen($src) != 0) {
            $_progress .= $src;
            $ret[] = array($src, $_progress);
        }
        return $ret;
    }

    // }}}
    // {{{ issue_ticket()

    /**
     * issue form ticket name and id, store to session.
     *
     * @static
     * @access public
     * @param string namespace (optional)
     * @return array array(name, id)
     */
    function issue_ticket($ns = '')
    {
        $ticket_form = md5(uniqid(rand(), true));
        $ticket_id = md5(uniqid(rand(), true));

        if (empty($ns)) {
            $ns = YB_UTIL_TICKET_NAMESPACE;
        } else {
            $ns = YB_UTIL_TICKET_NAMESPACE.'.'.$ns;
        }

        yb_Session::set('ticket_form', $ticket_form, $ns);
        yb_Session::set('ticket_id', $ticket_id, $ns);
        return array($ticket_form, $ticket_id);
    }

    // }}}
    // {{{ ticket_is_valid_or_error_die()

    /**
     * check ticket id, if not valid, yb_Util::forward_error_die().
     *
     * @static
     * @access public
     * @param string namespace (optional)
     * @return boolean true if valid, or false.
     */
    function ticket_is_valid_or_error_die($ns = '')
    {
        if (empty($ns)) {
            $ns = YB_UTIL_TICKET_NAMESPACE;
        } else {
            $ns = YB_UTIL_TICKET_NAMESPACE.'.'.$ns;
        }

        $ticket_form = yb_Session::get('ticket_form', null, $ns);
        $ticket_id_s = yb_Session::get('ticket_id', null, $ns);
        $ticket_id_f = yb_Var::request($ticket_form);
        yb_Session::clear('ticket_form', $ns);
        yb_Session::clear('ticket_id', $ns);
        if (empty($ticket_id_s) || empty($ticket_id_f)) {
            yb_Util::forward_error_die(t('input errors'), null, 403);
            return false;
        }
        if ($ticket_id_s !== $ticket_id_f) {
            yb_Util::forward_error_die(t('input errors'), null, 403);
            return false;
        }
        return true;
    }

    // }}}
    // {{{ check_css_color()

    /**
     * check valid css color value or not.
     *
     * @static
     * @access public
     * @param string
     * @return string valid color value or empty('') string
     */
    function check_css_color($v)
    {
        $v = trim($v);
        $v = str_replace(array(' ', "\r", "\n"), array('', '', ''), $v);

        if (preg_match('/^[a-zA-Z]+$/mi', $v)) {
            // "red", "WindowText"
            return $v;
        }

        if (preg_match('/^#[0-9a-fA-F]{6}$/mi', $v)) {
            return $v;
        }

        if (preg_match('/^#[0-9a-fA-F]{3}$/mi', $v)) {
            return $v;
        }

        if (preg_match('/^rgb\(\d{1,3},\d{1,3},\d{1,3}\)$/mi', $v)) {
            return strtolower($v);
        }

        if (preg_match('/^rgb\(\d{1,3}%,\d{1,3}%,\d{1,3}%\)$/mi', $v)) {
            return strtolower($v);
        }

        return '';
    }

    // }}}
    // {{{ check_css_size()

    /**
     * check valid css size value or not.
     *
     * @static
     * @access public
     * @param string
     * @return string valid size value or empty('') string
     */
    function check_css_size($v)
    {
        $v = trim($v);
        $v = str_replace(array(' ', "\r", "\n"), array('', '', ''), $v);
        $v = strtolower($v);

        $keywords = array(
            'xx-large',
            'x-large',
            'large',
            'medium',
            'small',
            'x-small',
            'xx-small',
            'larger',
            'smaller',
        );
        if (in_array($v, $keywords)) {
            return $v;
        }

        if (preg_match('/^[0-9\.]+(em|ex|px|pt|pc|mm|cm|in|%)$/mi', $v)) {
            return $v;
        }

        return '';
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
