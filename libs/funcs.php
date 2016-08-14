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

/*
 * YakiBiki Short-Cut functions
 *
 * @author msakamoto-sf <msakamoto-sf@users.sourceforge.net>
 * @version $Id: funcs.php 561 2009-07-22 06:07:59Z msakamoto-sf $
 */

// {{{ _YB()

/**
 * YakiBiki Global Configuration value singleton interface
 *
 * If value is not specified, returns current value.
 * If value is specified, returns old value, and update by specified value.
 *
 * @param string key configuration key
 * @param mixed value (optional)
 * @return mixed current(old) value
 */
function _YB()
{
    static $_vars = array();
    $numargs = func_num_args();

    if ($numargs == 1) {
        // If new value is not specified, return current value.

        $k = func_get_arg(0);
        return @$_vars[$k];

    } else if ($numargs > 1) {
        // If new value is specified, return current value, and update.

        $k = func_get_arg(0);
        $v = func_get_arg(1);
        $old = @$_vars[$k];
        $_vars[$k] = $v;
        return $old;

    }

    // $numargs == 0
    return $_vars;
}

// }}}
// {{{ h()

/**
 * YakiBiki 'htmlspecialchars'
 *
 * @param string
 * @return string
 */
function h($s)
{
    return htmlspecialchars($s, ENT_QUOTES, _YB('internal.encoding'));
}

// }}}
// {{{ _y_()

/**
 * YakiBiki 'htmlspecialchars_decode'
 * (reverse of h())
 *
 * @param string
 * @return string
 */
function _y_($s)
{
    return str_replace(
        array('&lt;', '&gt;', '&amp;', '&quot;', '&#039;'),
        array('<', '>', '&', '"', "'"),
        $s);
}

// }}}
// {{{ t()

/**
 * YakiBiki yb_Trans::t() wrapper
 *
 * @param string
 * @param array replacement arguments (optional, default null)
 * @param string domain (optional)
 * @return string
 */
function t($src, $args = null, $domain = null)
{
    $ybt =& yb_Trans::factory(_YB('resource.locale'));
    return $ybt->t($src, $args, $domain);
}

// }}}
// {{{ dlog()

/**
 * YakiBiki Debug Log (alias of trigger_error())
 *
 * @param mixed
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
// {{{ _substr()

/**
 * Wiki feature's special substr()
 *
 * If start position is larger than strlen(source text), then return ''.
 *
 * @param string source text
 * @param integer cut start position
 * @param integer cut length
 * @return string
 */
function _substr($string, $start, $length = null)
{
    if ($start >= strlen($string)) {
        return '';
    }

    return (is_null($length)) 
        ? substr($string, $start) 
        : substr($string, $start, $length);
}


// }}}
// {{{ yb_bin2hex()

/**
 * wrapper for bin2hex()
 *
 * @param string source text
 * @return string
 */
function yb_bin2hex($s)
{
    return bin2hex($s);
}

// }}}
// {{{ yb_hex2bin()

/**
 * reverse for yb_bin2hex()
 *
 * @param string hex string
 * @return string
 */
function yb_hex2bin($s)
{
    return pack("H*", $s);
}

// }}}
// {{{ yb_error_webhandler

/**
 * yb_Error's web handler
 *
 * Log error, and show error page using yb_Util::forward_error_die().
 *
 * @param mixed errorinfo assoc-array
 */
function yb_error_webhandler($err)
{
    $log =& yb_Log::get_logger();
    $log->err(sprintf('message=[%s], code=[%s]', @$err['msg'], @$err['code']));
    $log->err(var_export(@$err['stacktrace'], true));

    yb_Util::forward_error_die(
        t('System error occurred. See log files.'), 
        null, 500);
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
