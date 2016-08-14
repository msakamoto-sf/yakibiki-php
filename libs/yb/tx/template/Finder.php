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
 * YakiBiki Template Transactions : Finder
 *
 * @author msakamoto-sf <msakamoto-sf@users.sourceforge.net>
 * @version $Id: Finder.php 379 2008-10-05 14:30:35Z msakamoto-sf $
 */
class yb_tx_template_Finder
{
    /**
     * Find by ID
     *
     * @static
     * @access public
     * @param mixed template id (integer: single id, array of id: multiple id)
     * @param string sort field (default: id)
     * @param integer ORDER_BY_DESC or ORDER_BY_ASC(default)
     * @return array array of template data array.
     *               if templates not found, then return empty array.
     */
    function by_id($ids, $sort_by = 'id', $order_by = ORDER_BY_ASC)
    {
        $dao_template =& yb_dao_Factory::get('template');
        $dao_user =& yb_dao_Factory::get('user');
        $dao_cat =& yb_dao_Factory::get('category');
        $dao_acl =& yb_dao_Factory::get('acl');

        $results = $dao_template->find_by_id($ids, $sort_by, $order_by);

        $ret = array();
        foreach ($results as $r) {

            $_users = $dao_user->find_by_id($r['owner']);
            $r['owner'] = (count($_users) == 1) ? $_users[0] : false;

            $_acls = $dao_acl->find_by_id($r['acl']);
            $r['acl'] = (count($_acls) == 1) ? $_acls[0] : false;

            $cs = $r['categories'];
            if (is_array($cs) && count($cs) > 0) {
                $_cats = $dao_cat->find_by_id($r['categories']);
                $r['categories'] = (count($_cats) > 0) ? $_cats : array();
            }

            $ret[] = $r;
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
     * @return array array of template data array.
     *               if templates not found, then return empty array.
     */
    function all($sort_by = 'id', $order_by = ORDER_BY_ASC)
    {
        $dao_template =& yb_dao_Factory::get('template');
        $dao_user =& yb_dao_Factory::get('user');
        $dao_cat =& yb_dao_Factory::get('category');
        $dao_acl =& yb_dao_Factory::get('acl');

        $results = $dao_template->find_all($sort_by, $order_by);

        $ret = array();
        foreach ($results as $r) {

            $_users = $dao_user->find_by_id($r['owner']);
            $r['owner'] = (count($_users) == 1) ? $_users[0] : false;

            $_acls = $dao_acl->find_by_id($r['acl']);
            $r['acl'] = (count($_acls) == 1) ? $_acls[0] : false;

            $cs = $r['categories'];
            if (is_array($cs) && count($cs) > 0) {
                $_cats = $dao_cat->find_by_id($r['categories']);
                $r['categories'] = (count($_cats) > 0) ? $_cats : array();
            }

            $ret[] = $r;
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
