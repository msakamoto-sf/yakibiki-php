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
 * YakiBiki Original Error Stack
 *
 * @author msakamoto-sf <msakamoto-sf@users.sourceforge.net>
 * @version $Id: Error.php 242 2008-03-29 04:56:07Z msakamoto-sf $
 */
class yb_Error
{
    var $_errors = array();

    var $_raise_callback = null;

    // {{{ _singleton()

    /**
     * @static
     * @access protected
     */
    function &_singleton()
    {
        static $instance = null;
        if (is_null($instance)) {
            $instance = new yb_Error();
        }
        return $instance;
    }

    // }}}
    // {{{ set_raise_callback()

    /**
     * @static
     * @access public
     * @param callable raise() callback handler
     * @return callable old callback handler
     */
    function set_raise_callback($handler)
    {
        $ye =& yb_Error::_singleton();
        $cur = $ye->_raise_callback;
        $ye->_raise_callback = $handler;
        return $cur;
    }

    // }}}
    // {{{ get()

    /**
     * @static
     * @access public
     * @param boolean clear or not (optional, default : true (clea))
     * @return array raised errors : array of array(
     *              'msg', 'code', 'stacktrace'(debug_backtrace() result)).
     */
    function get($flush = true)
    {
        $ye =& yb_Error::_singleton();
        $ret = $ye->_errors;
        if ($flush) {
            $ye->_errors = array();
        }
        return $ret;
    }

    // }}}
    // {{{ raise()

    /**
     * @static
     * @access public
     * @param string error message
     * @param integer error code (optional)
     */
    function raise($msg, $code = null)
    {
        $ye =& yb_Error::_singleton();
        $st = array();
        if (function_exists('debug_backtrace')) {
           $st = debug_backtrace();
        }
        $err = array(
            'msg' => $msg,
            'code' => $code,
            'stacktrace' => $st,
        );
        if (is_callable($ye->_raise_callback)) {
            call_user_func_array($ye->_raise_callback, array($err));
        }
        $ye->_errors[] = $err;
    }

    // }}}
    // {{{ count()

    /**
     * @static
     * @access public
     * @return integer
     */
    function count()
    {
        $ye =& yb_Error::_singleton();
        return count($ye->_errors);
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
