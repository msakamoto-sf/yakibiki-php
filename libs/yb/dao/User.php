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
 * YakiBiki Dao User
 *
 * @author msakamoto-sf <msakamoto-sf@users.sourceforge.net>
 * @version $Id: User.php 346 2008-09-15 08:32:09Z msakamoto-sf $
 */

require_once(dirname(__FILE__) . '/Base.php');

/**
 * YakiBiki Dao User
 *
 * @author msakamoto-sf <msakamoto-sf@users.sourceforge.net>
 */
class yb_dao_User extends yb_dao_Base
{
    // {{{ constructor

    /**
     * Constructor
     *
     * @access private
     */
    function yb_dao_User()
    {
        $this->_cache_name = __CLASS__;

        $this->_grain_name = 'user';

        $this->_updatable_fields = array(
            'mail', 'name', 'status', 'password', 'role');

        $this->_sortable = array('id', 'mail', 'name', 'status', 
            'created_at', 'updated_at');
    }

    // }}}
    // {{{ flesh2grain()

    function flesh2grain($flesh)
    {
        $_role = explode(GRAIN_DATA_GS, $flesh['role']);
        $__role = array();
        foreach ($_role as $_r) {
            if (trim($_r) != "") {
                $__role[] = $_r;
            }
        }
        $flesh['role'] = $__role;
        return $flesh;
    }

    // }}}
    // {{{ grain2flesh()

    function grain2flesh($grain)
    {
        if (is_array($grain['role'])) {
            $grain['role'] = implode(GRAIN_DATA_GS, $grain['role']);
        }
        return $grain;
    }

    // }}}
    // {{{ find_by_id()

    function find_by_id($ids, $sort_by = "id", $order_by = ORDER_BY_ASC)
    {
        return $this->find_by('id', $ids, $sort_by, $order_by);
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
