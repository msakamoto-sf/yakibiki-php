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
 * YakiBiki Group Transactions : Finder
 *
 * @author msakamoto-sf <msakamoto-sf@users.sourceforge.net>
 * @version $Id: Finder.php 350 2008-09-16 06:28:04Z msakamoto-sf $
 */
class yb_tx_group_Finder
{
    /**
     * Find by ID
     *
     * @static
     * @access public
     * @param mixed group id (integer: single id, array of id: multiple id)
     * @param string sort field (default: id)
     * @param integer ORDER_BY_DESC or ORDER_BY_ASC(default)
     * @return array array of group data array.
     *               if groups not found, then return empty array.
     */
    function by_id($ids, $sort_by = 'id', $order_by = ORDER_BY_ASC)
    {
        $dao_user =& yb_dao_Factory::get('user');
        $dao_group =& yb_dao_Factory::get('group');
        $results = $dao_group->find_by_id($ids, $sort_by, $order_by);

        $ret = array();
        foreach ($results as $g) {
            $_g = $g;
            $owner = $_g['owner'];
            $_users = $dao_user->find_by_id($owner);
            if (count($_users) == 1) {
                $_g['owner'] = $_users[0];
            } else {
                $_g['owner'] = false;
            }
            $ret[] = $_g;
        }

        return $ret;
    }

    /**
     * Find All records
     *
     * @static
     * @access public
     * @param string sort field(optional). default : sort by id.
     * @param integer ORDER_BY_ASC or ORDER_BY_DESC (optional)
     *                default: ORDER_BY_ASC
     * @return array array of group data array.
     *               if groups not found, then return empty array.
     */
    function all($sort_by = 'id', $order_by = ORDER_BY_ASC)
    {
        $dao_user =& yb_dao_Factory::get('user');
        $dao_group =& yb_dao_Factory::get('group');
        $results = $dao_group->find_all($sort_by, $order_by);

        $ret = array();
        foreach ($results as $g) {
            $_g = $g;
            $owner = $_g['owner'];
            $_users = $dao_user->find_by_id($owner);
            if (count($_users) == 1) {
                $_g['owner'] = $_users[0];
            } else {
                $_g['owner'] = false;
            }
            $ret[] = $_g;
        }

        return $ret;
    }
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
