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
 * YakiBiki Dao Group
 *
 * @author msakamoto-sf <msakamoto-sf@users.sourceforge.net>
 * @version $Id: Group.php 305 2008-07-27 21:03:28Z msakamoto-sf $
 */

require_once(dirname(__FILE__) . '/Base.php');

/**
 * YakiBiki Dao Group
 *
 * @author msakamoto-sf <msakamoto-sf@users.sourceforge.net>
 */
class yb_dao_Group extends yb_dao_Base
{
    // {{{ constructor

    /**
     * Constructor
     *
     * @access private
     */
    function yb_dao_Group()
    {
        $this->_cache_name = __CLASS__;

        $this->_grain_name = 'group';

        $this->_updatable_fields = array('owner', 'name', 'mates');

        $this->_sortable = array('id', 'owner', 'name', 
            'created_at', 'updated_at');
    }

    // }}}
    // {{{ flesh2grain()

    function flesh2grain($flesh)
    {
        $_mates = array_map('trim', explode(GRAIN_DATA_GS, $flesh['mates']));
        $__mates = array();
        foreach ($_mates as $_m) {
            if (strlen($_m) != 0) {
                $__mates[] = $_m;
            }
        }
        $flesh['mates'] = $__mates;
        return $flesh;
    }

    // }}}
    // {{{ grain2flesh()

    function grain2flesh($grain)
    {
        // edit "mates" fields
        if (!isset($grain['mates'])) {
            // if not specified, set default empty array
            $grain['mates'] = array();
        }
        if (is_array($grain['mates'])) {
            $grain['mates'] = implode(GRAIN_DATA_GS, $grain['mates']);
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
    // {{{ find_by_owner()

    function find_by_owner($ids, $sort_by = "id", $order_by = ORDER_BY_ASC)
    {
        return $this->find_by('owner', $ids, $sort_by, $order_by);
    }

    // }}}
    // {{{ groupMates()

    /**
     * Get user ids who belongs to given group id
     *
     * @access public
     * @param integer group id
     * @return array array of user id
     *  (If given gid is not registered, return empty array.)
     */
    function groupMates($gid)
    {
        $records = $this->find_by_id($gid);
        if (count($records) == 0) {
            return array();
        }
        return $records[0]['mates'];
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
