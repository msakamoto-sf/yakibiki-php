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

/**
 * yb_dao_XXXX Singleton Factory
 *
 * @author msakamoto-sf <msakamoto-sf@users.sourceforge.net>
 * @version $Id: Factory.php 378 2008-10-05 13:52:39Z msakamoto-sf $
 */
class yb_dao_Factory
{
    // {{{ get()

    /**
     * get singleton cached instance
     *
     * @static
     * @access public
     * @param string dao name
     * @return object reference
     */
    function &get($name)
    {
        $_fz = $GLOBALS[FACTORY_ZONE];
        $ret = null;
        $_n = ucwords(strtolower($name));
        static $daos = array();
        if (isset($daos[$_fz][$_n])) {
            return $daos[$_fz][$_n];
        }
        $_klass = 'yb_dao_' . $_n;
        if (!class_exists($_klass)) {
            $_file = dirname(__FILE__) . '/' . $_n . '.php';
            if (!is_readable($_file)) {
                return $ret;
            }
            $r = include_once $_file;
            if (!$r) {
                return $ret;
            }
        }
        if (!class_exists($_klass)) {
            return $ret;
        }

        $daos[$_fz][$_n] = new $_klass();

        return $daos[$_fz][$_n];
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
