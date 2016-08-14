<?php
/*
 *   Copyright (c) 2008 msakamoto-sf <msakamoto-sf@users.sourceforge.net>
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
 * Grain Data Storage Library : Configuration
 *
 * @author msakamoto-sf <msakamoto-sf@users.sourceforge.net>
 * @version $Id: Config.php 295 2008-06-22 04:45:58Z msakamoto-sf $
 */
class grain_Config
{
    var $config;

    // {{{ grain_Config()

    /**
     * @private
     */
    function grain_Config()
    {
        $this->config = array();
    }

    // }}}
    // {{{ singleton()

    /**
     * @public
     */
    function &singleton()
    {
        static $instance = null;
        if (is_null($instance)) {
            $instance = new grain_Config();
        }
        return $instance;
    }

    // }}}
    // {{{ get()

    /**
     * @static
     * @public
     */
    function get($key)
    {
        $c =& grain_Config::singleton();
        $ret = (isset($c->config[$key])) ? $c->config[$key] : null;
        return $ret;
    }

    // }}}
    // {{{ set()

    /**
     * @static
     * @public
     */
    function set($key, $value)
    {
        $c =& grain_Config::singleton();
        $old = (isset($c->config[$key])) ? $c->config[$key] : null;
        $c->config[$key] = $value;
        return $old;
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
