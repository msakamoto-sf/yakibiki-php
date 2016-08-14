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

require_once('Log.php');

/**
 * YakiBiki PEAR::Log's Simple Log
 *
 * @author msakamoto-sf <msakamoto-sf@users.sourceforge.net>
 * @version $Id: Log.php 507 2009-01-06 05:59:54Z msakamoto-sf $
 */
class yb_Log
{
    // {{{ _initialize_pear_logger()

    function &_initialize_pear_logger()
    {
        $configs = array();
        $_keys = array('append', 'locking', 'mode', 'dirmode', 'eol', 
            'lineFormat', 'timeFormat');
        foreach ($_keys as $_k) {
            $_ck = 'log.' . $_k;
            $_v = _YB($_ck);
            if (is_null($_v)) {
                continue;
            }
            $_v = trim($_v);
            switch ($_k) {
            case 'mode':
            case 'dirmode':
                $configs[$_k] = sscanf($_v, "%o");
                break;
            case 'eol':
                $_eol = strtoupper($_v);
                if ($_eol == "CR") {
                    $__eol = "\x0d";
                } else if ($_eol == "CRLF") {
                    $__eol = "\x0d\x0a";
                } else if ($_eol == "LF") {
                    $__eol = "\x0a";
                } else {
                    $__eol = PHP_EOL;
                }
                $configs[$_k] = $__eol;
                break;
            default:
                $configs[$_k] = $_v;

            }
        }
        $outfile = _YB('log.out');
        $_level = constant(_YB('log.level'));
        $level = is_numeric($_level) ? $_level : constant($_level);
        $uc = yb_Session::user_context();
        $ident = "'" . yb_Var::server('REMOTE_ADDR') . "' - " 
            . session_id() . '(' . $uc['name'] . ')';
        $logger =& Log::singleton('file', $outfile, $ident, $configs, $level);
        return $logger;
    }

    // }}}
    // {{{ get_logger()

    /**
     * @static
     * @access public
     */
    function &get_logger()
    {
        static $logger = null;
        if (is_null($logger)) {
            $logger = yb_Log::_initialize_pear_logger();
        }
        return $logger;
    }

    // }}}
    // {{{ dlog()

    /**
     * @static
     * @access public
     */
    function dlog()
    {
        $args = func_get_args();
        $backtrace = debug_backtrace();
        $laststack = array_shift($backtrace);
        $file = $laststack['file'];
        $line = $laststack['line'];

        $_els = array();
        foreach ($args as $a) {
            if (is_scalar($a)) {
                $_els[] = (string)$a;
            } else {
                $_els[] = var_export($a, true);
            }
        }
        $log = "[[ " . implode("\r\n", $_els) . " ]]($file : $line)";

        trigger_error($log, E_USER_NOTICE);

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
