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
 * YakiBiki Category
 *
 * @author msakamoto-sf <msakamoto-sf@users.sourceforge.net>
 * @version $Id: Category.php 306 2008-08-03 11:18:45Z msakamoto-sf $
 */

require_once(dirname(__FILE__) . '/Base.php');

/**
 * YakiBiki Dao Category
 *
 * @author msakamoto-sf <msakamoto-sf@users.sourceforge.net>
 */
class yb_dao_Category extends yb_dao_Base
{
    // {{{ constructor

    /**
     * Constructor
     *
     * @access private
     */
    function yb_dao_Category()
    {
        $this->_cache_name = __CLASS__;

        $this->_grain_name = 'category';

        $this->_updatable_fields = array('name', 'owner');

        $this->_sortable = array('id', 'name', 'owner', 
            'created_at', 'updated_at');
    }

    // }}}
    // {{{ find_by_id()

    function find_by_id($ids, $sort_by = 'name', $order_by = ORDER_BY_ASC)
    {
        return $this->find_by('id', $ids, $sort_by, $order_by);
    }

    // }}}
    // {{{ find_by_owner()

    function find_by_owner($ids, $sort_by = 'name', $order_by = ORDER_BY_ASC)
    {
        return $this->find_by('owner', $ids, $sort_by, $order_by);
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
