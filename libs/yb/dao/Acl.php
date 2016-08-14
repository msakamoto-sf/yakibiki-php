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
 * YakiBiki Acl
 *
 * @author msakamoto-sf <msakamoto-sf@users.sourceforge.net>
 * @version $Id: Acl.php 331 2008-09-11 03:51:05Z msakamoto-sf $
 */

require_once(dirname(__FILE__) . '/Base.php');

// {{{ constants

/*
 * Field index number in 'perms' column
 */
define('YB_ACL_PERMS_SEPARATOR', '|');
define('YB_ACL_PERMS_COLUMN_TYPE', 0);
define('YB_ACL_PERMS_COLUMN_ID', 1);
define('YB_ACL_PERMS_COLUMN_PERM', 2);

// }}}

/**
 * YakiBiki Dao Acl
 *
 * @author msakamoto-sf <msakamoto-sf@users.sourceforge.net>
 */
class yb_dao_Acl extends yb_dao_Base
{
    // {{{ constructor

    /**
     * Constructor
     *
     * @access private
     */
    function yb_dao_Acl()
    {
        $this->_cache_name = __CLASS__;

        $this->_grain_name = 'acl';

        $this->_updatable_fields = array(
            'owner', 'name', 'policy', 'perms');

        $this->_sortable = array(
            'id', 'owner', 'name', 'created_at', 'updated_at');
    }

    // }}}
    // {{{ flesh2grain()

    function flesh2grain($flesh)
    {
        $_perms = array_map('trim', explode(GRAIN_DATA_GS, $flesh['perms']));
        $__perms = array();
        foreach ($_perms as $_p) {
            if (strlen($_p) == 0) {
                continue;
            }
            $_p_els = explode(YB_ACL_PERMS_SEPARATOR, $_p);
            $__perms[] = array(
                'type' => $_p_els[YB_ACL_PERMS_COLUMN_TYPE],
                'id' => intval($_p_els[YB_ACL_PERMS_COLUMN_ID]),
                'perm' => intval($_p_els[YB_ACL_PERMS_COLUMN_PERM]),
            );
        }

        $flesh['perms'] = $__perms;
        return $flesh;
    }

    // }}}
    // {{{ grain2flesh()

    function grain2flesh($grain)
    {
        $lines = array();

        // edit 'perms' fields
        if (!isset($grain['perms'])) {
            // if not specified, set default empty array
            $grain['perms'] = array();
        }
        if (is_array($grain['perms'])) {
            $perms = $grain['perms'];
            $p_tmp = array();
            foreach ($perms as $p) {
                $p_tmp[] = $p['type'] . YB_ACL_PERMS_SEPARATOR .
                    $p['id'] . YB_ACL_PERMS_SEPARATOR .
                    $p['perm'];
            }
            $grain['perms'] = implode(GRAIN_DATA_GS, $p_tmp);
        }

        return $grain;
    }

    // }}}
    // {{{ find_by_id()

    function find_by_id($id, $sort_by = 'id', $order_by = ORDER_BY_ASC)
    {
        return $this->find_by('id', $id, $sort_by, $order_by);
    }

    // }}}
    // {{{ find_by_owner()

    function find_by_owner($id, $sort_by = 'id', $order_by = ORDER_BY_ASC)
    {
        return $this->find_by('owner', $id, $sort_by, $order_by);
    }

    // }}}
    // {{{ create()

    function create($newgrain)
    {
        yb_AclCache::clean();
        return parent::create($newgrain);
    }

    // }}}
    // {{{ update()

    function update($id, $newgrain)
    {
        yb_AclCache::clean();
        return parent::update($id, $newgrain);
    }

    // }}}
    // {{{ delete()

    function delete($id)
    {
        yb_AclCache::clean();
        return parent::delete($id);
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
