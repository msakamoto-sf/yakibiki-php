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

define('YB_SESSION_KEY_PARAMETER', "\x00__parameter__area");
define('YB_SESSION_KEY_FLASH', "\x00__flash__area");

/**
 * YakiBiki Session manager
 *
 * @author msakamoto-sf <msakamoto-sf@users.sourceforge.net>
 * @version $Id: Session.php 447 2008-11-15 16:40:48Z msakamoto-sf $
 */
class yb_Session
{
    // {{{ start()

    /**
     * Start session
     *
     * @static
     * @access public
     */
    function start()
    {
        session_save_path(_YB('session.save.path'));

        session_set_cookie_params(
            _YB('session.lifetime'),
            _YB('session.path'),
            _YB('session.domain'),
            _YB('session.secure'));

        session_cache_limiter(_YB('session.cache.limiter'));

        if (!is_null(_YB('session.name'))) {
            session_name(_YB('session.name'));
        }
        session_start();

        $cur_ut = time();
        if (yb_Session::has('yb_core_last_access_ut')) {
            $la_ut = yb_Session::get('yb_core_last_access_ut');
            if (_YB('session.id_regenerate_interval') < ($cur_ut - $la_ut)) {
                yb_Session::regenerate_id();
            }
        }
        yb_Session::set('yb_core_last_access_ut', $cur_ut);

        $_user = yb_Session::user_context();
        if (is_null($_user)) {
            // setup default(anonymous) user context
            yb_Session::user_context(yb_Session::anonymous_user_context());
        }
    }

    // }}}
    // {{{ user_context()

    /**
     * Get/Set user context data
     *
     * @static
     * @access public
     * @param array new user data (optional)
     * @return array (current) user data
     */
    function user_context($new = null)
    {
        $old = yb_Session::get('user_context');
        if (!is_null($new)) {
            unset($new['password']);
            yb_Session::set('user_context', $new);
        }
        return $old;
    }

    // }}}
    // {{{ isAuthenticated()

    /**
     * Retrieve authenticated status
     *
     * @static
     * @access public
     * @return boolean
     */
    function isAuthenticated()
    {
        $uc = yb_Session::user_context();
        return ($uc['id'] != YB_GUEST_UID);
    }

    // }}}
    // {{{ hasRole()

    /**
     * Retrieve specified role is set or not.
     *
     * @static
     * @access public
     * @param string role
     * @return boolean
     */
    function hasRole($_role)
    {
        $uc = yb_Session::user_context();
        $user_roles = @$uc['role'];
        if (!is_array($user_roles)) {
            return false;
        }
        if (!in_array($_role, $user_roles)) {
            return false;
        }
        return true;
    }

    // }}}
    // {{{ get()

    /**
     * Get session vars
     *
     * @static
     * @access public
     * @param string key (if omitted or null, all vars)
     * @param mixed default value if not set
     * @param string namespece (if omitted, global namespace)
     * @return mixed
     */
    function get($key, $default = null, $ns = '__global__')
    {
        $_k = YB_SESSION_KEY_PARAMETER;
        if (is_null($key)) {
            return @$_SESSION[$_k][$ns];
        } else {
            return isset($_SESSION[$_k][$ns][$key]) 
                ? $_SESSION[$_k][$ns][$key] : $default;
        }
    }

    // }}}
    // {{{ has()

    /**
     * Retrieve specified key is set or not.
     *
     * @static
     * @access public
     * @param string key
     * @param string namespece (if omitted, global namespace)
     * @return boolean
     */
    function has($key, $ns = '__global__')
    {
        $_k = YB_SESSION_KEY_PARAMETER;
        return isset($_SESSION[$_k][$ns][$key]);
    }

    // }}}
    // {{{ set()

    /**
     * Set session vars
     *
     * @static
     * @access public
     * @param string key
     * @param mixed value
     * @param string namespece (if omitted, global namespace)
     * @return mixed (old value)
     */
    function set($key, $value, $ns = '__global__')
    {
        $_k = YB_SESSION_KEY_PARAMETER;
        $old = @$_SESSION[$_k][$ns][$key];
        $_SESSION[$_k][$ns][$key] = $value;
        return $old;
    }

    // }}}
    // {{{ clear()

    /**
     * Clear session vars.
     *
     * @static
     * @access public
     * @param string key (if omitted, all vars are cleared)
     * @param string namespece (if omitted, global namespace vars are cleared)
     */
    function clear($key, $ns = '__global__')
    {
        $_k = YB_SESSION_KEY_PARAMETER;
        if (is_null($key)) {
            unset($_SESSION[$_k][$ns]);
        } else if (isset($_SESSION[$_k][$ns][$key])){
            unset($_SESSION[$_k][$ns][$key]);
        }
    }

    // }}}
    // {{{ get_flash()

    /**
     * Get and clean flash data area.
     *
     * @static
     * @access public
     * @param string key
     * @return mixed
     */
    function get_flash($key)
    {
        $_fk = YB_SESSION_KEY_FLASH;
        $ret = null;
        if (isset($_SESSION[$_fk][$key])) {
            $ret = $_SESSION[$_fk][$key];
            unset($_SESSION[$_fk][$key]);
        }
        return $ret;
    }

    // }}}
    // {{{ has_flash()

    /**
     * Return specified flash key is exist or not.
     *
     * @static
     * @access public
     * @param string key
     * @return boolean
     */
    function has_flash($key)
    {
        $_fk = YB_SESSION_KEY_FLASH;
        return isset($_SESSION[$_fk][$key]);
    }

    // }}}
    // {{{ set_flash()

    /**
     * Set flash data area.
     *
     * @static
     * @access public
     * @param string key
     * @param mixed value
     * @return mixed old value (If new data, return null.)
     */
    function set_flash($key, $value)
    {
        $_fk = YB_SESSION_KEY_FLASH;
        $old = null;
        if (isset($_SESSION[$_fk][$key])) {
            $old = $_SESSION[$_fk][$key];
        }
        $_SESSION[$_fk][$key] = $value;
        return $old;
    }

    // }}}
    // {{{ regenerate_id()

    /**
     * Regenerate Session Id
     *
     * Against session fixation attacks : generate new session id and 
     * destroy old one.
     *
     * see the article posted by "Nicolas dot Chachereau at Infomaniak dot ch"
     * at 03-Jun-2005 03:40 in following url:
     * @see http://jp.php.net/manual/ja/function.session-regenerate-id.php
     * @static
     * @access public
     */
    function regenerate_id()
    {
        $_backup = $_SESSION;
        $sid_old = session_id(); // save old sid.
        session_regenerate_id(); // generate new sid.
        $sid_new = session_id(); // save it.
        session_id($sid_old);    // now, set current as saved old sid.
        session_destroy();       // destroy current (equals old sid).
        session_id($sid_new);    // re-set current as new sid.
        session_start();         // re-start session.
        $_SESSION = $_backup;
    }

    // }}}
    // {{{ anonymous_user_context()

    /**
     * Return anonymous(guest) user context data
     *
     * @static
     * @access public
     * @return array anonymous user data
     */
    function anonymous_user_context()
    {
        return array(
            'id' => YB_GUEST_UID,
            'name' => _YB('guest.user.name'),
            'role' => array(),
            // TODO Are there any other else items ?
        );
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
