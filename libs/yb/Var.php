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
 *
 */

/**
 * YakiBiki GET/POST/Uploaded Files/COOKIE access wrapper
 *
 * @author msakamoto-sf <msakamoto-sf@users.sourceforge.net>
 * @version $Id: Var.php 402 2008-10-28 14:29:06Z msakamoto-sf $
 */
class yb_Var
{
    // {{{ request()

    /**
     * $_REQUEST warpper
     *
     * @static
     * @access public
     * @param string key (If omitted, all $_REQUEST is returned.)
     * @return mixed
     */
    function request($key = null)
    {
        if (is_null($key)) {
            return $_REQUEST;
        }
        return @$_REQUEST[$key];
    }

    // }}}
    // {{{ get()

    /**
     * $_GET warpper
     *
     * @static
     * @access public
     * @param string key (If omitted, all $_GET is returned.)
     * @return mixed
     */
    function get($key = null)
    {
        if (is_null($key)) {
            return $_GET;
        }
        return @$_GET[$key];
    }

    // }}}
    // {{{ post()

    /**
     * $_POST warpper
     *
     * @static
     * @access public
     * @param string key (If omitted, all $_POST is returned.)
     * @return mixed
     */
    function post($key = null)
    {
        if (is_null($key)) {
            return $_POST;
        }
        return @$_POST[$key];
    }

    // }}}
    // {{{ files()

    /**
     * $_FILES warpper
     *
     * @see http://jp.php.net/manual/ja/features.file-upload.php
     * @static
     * @access public
     * @param string key (If omitted, all $_FILES is returned.)
     * @return mixed
     */
    function files($key = null)
    {
        if (is_null($key)) {
            return $_FILES;
        }
        return @$_FILES[$key];
    }

    // }}}
    // {{{ env()

    /**
     * $_SERVER, $_EVN, getenv() warpper
     *
     * @static
     * @access public
     * @param string key
     * @return mixed
     */
    function env($key)
    {
        if ($key == 'HTTPS') {
            if (isset($_SERVER) && !empty($_SERVER)) {
                return (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on');
            } else {
                return (strpos(env('SCRIPT_URI'), 'https://') === 0);
            }
        }

        if (isset($_SERVER[$key])) {
            return $_SERVER[$key];
        } elseif (isset($_ENV[$key])) {
            return $_ENV[$key];
        } elseif (getenv($key) !== false) {
            return getenv($key);
        }

        if ($key == 'SCRIPT_FILENAME' && 
            defined('SERVER_IIS') && 
            SERVER_IIS === true) {
            return str_replace('\\\\', '\\', env('PATH_TRANSLATED') );
        }

        if ($key == 'DOCUMENT_ROOT') {
            $offset = 0;
            if (!strpos(env('SCRIPT_NAME'), '.php')) {
                $offset = 4;
            }
            return substr(
                env('SCRIPT_FILENAME'), 
                0, 
                strlen(env('SCRIPT_FILENAME')) 
                    - (strlen(env('SCRIPT_NAME')) + $offset)
                );
        }
        if ($key == 'PHP_SELF') {
            return str_replace(
                env('DOCUMENT_ROOT'), 
                '', 
                env('SCRIPT_FILENAME'));
        }
        return null;
    }

    // }}}
    // {{{ server()

    /**
     * $_SERVER warpper
     *
     * @see http://jp.php.net/manual/ja/reserved.variables.php#reserved.variables.server
     * @static
     * @access public
     * @param string key (required)
     * @return mixed
     */
    function server($key = null)
    {
        if ($key == 'REMOTE_ADDR') {
            if (yb_Var::env('HTTP_X_FORWARDED_FOR') != null) {
                $ipaddr = preg_replace('/,.*/', '', 
                    yb_Var::env('HTTP_X_FORWARDED_FOR'));
            } else {
                if (yb_Var::env('HTTP_CLIENT_IP') != null) {
                    $ipaddr = yb_Var::env('HTTP_CLIENT_IP');
                } else {
                    $ipaddr = yb_Var::env('REMOTE_ADDR');
                }
            }

            if (yb_Var::env('HTTP_CLIENTADDRESS') != null) {
                $tmpipaddr = yb_Var::env('HTTP_CLIENTADDRESS');

                if (!empty($tmpipaddr)) {
                    $ipaddr = preg_replace('/,.*/', '', $tmpipaddr);
                }
            }
            return trim($ipaddr);
        }
        if (is_null($key)) {
            return $_SERVER;
        }

        return @$_SERVER[$key];
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
