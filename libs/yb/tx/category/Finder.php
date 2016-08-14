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
 * YakiBiki Category Transactions : Finder
 *
 * @author msakamoto-sf <msakamoto-sf@users.sourceforge.net>
 * @version $Id: Finder.php 354 2008-09-16 08:22:32Z msakamoto-sf $
 */
class yb_tx_category_Finder
{
    /**
     * Find by ID
     *
     * @access public
     * @param mixed category id (integer: single id, array of id: multiple id)
     * @param string sort field (default: name)
     * @param integer ORDER_BY_DESC or ORDER_BY_ASC(default)
     * @return array array of category data array.
     */
    function by_id($ids, $sort_by = 'name', $order_by = ORDER_BY_ASC)
    {
        $dao_user =& yb_dao_Factory::get('user');
        $dao_cat =& yb_dao_Factory::get('category');
        $c2d =& grain_Factory::index('pair', 'category_to_data');
        $results = $dao_cat->find_by_id($ids, $sort_by, $order_by);

        // cache user datas locally
        $_all_users = $dao_user->find_all();
        $all_users = array();
        foreach ($_all_users as $u) {
            $k = $u['id'];
            $all_users[$k] = $u;
        }

        $return = array();
        foreach ($results as $c) {
            $_c = $c;
            $cid = $c['id'];
            $uid = $c['owner'];
            $_c['owner'] = $all_users[$uid];
            $_c['count'] = $c2d->count_for($cid);
            $return[] = $_c;
        }

        return $return;
    }

    /**
     * Find All records
     *
     * @access public
     * @param string sort field(optional). default : sort by name.
     * @param integer ORDER_BY_ASC or ORDER_BY_DESC (optional)
     *                default: ORDER_BY_ASC
     * @return array array of category data array.
     */
    function all($sort_by = 'name', $order = ORDER_BY_ASC)
    {
        $dao_user =& yb_dao_Factory::get('user');
        $dao_cat =& yb_dao_Factory::get('category');
        $c2d =& grain_Factory::index('pair', 'category_to_data');
        $results = $dao_cat->find_all($sort_by, $order);

        // cache user datas locally
        $_all_users = $dao_user->find_all();
        $all_users = array();
        foreach ($_all_users as $u) {
            $k = $u['id'];
            $all_users[$k] = $u;
        }

        $return = array();
        foreach ($results as $c) {
            $_c = $c;
            $cid = $c['id'];
            $uid = $c['owner'];
            $_c['owner'] = $all_users[$uid];
            $_c['count'] = $c2d->count_for($cid);
            $return[] = $_c;
        }

        return $return;
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
