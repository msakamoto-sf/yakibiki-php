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
 * YakiBiki Dao Data 
 *
 * @author msakamoto-sf <msakamoto-sf@users.sourceforge.net>
 * @version $Id: Data.php 494 2009-01-04 02:23:39Z msakamoto-sf $
 */

require_once(dirname(__FILE__) . '/Base.php');

/**
 * YakiBiki Dao Data
 *
 * @author msakamoto-sf <msakamoto-sf@users.sourceforge.net>
 */
class yb_dao_Data extends yb_dao_Base
{
    // {{{ constructor

    /**
     * Constructor
     *
     * @access private
     */
    function yb_dao_Data()
    {
        $this->_cache_name = __CLASS__;

        $this->_grain_name = 'data';

        $this->_sortable = array('id', 'created_at', 'updated_at');
    }

    // }}}
    // {{{ flesh2grain()

    function flesh2grain($flesh)
    {
        $ars = array(
            'categories',
            'versions',
            'comments',
        );
        foreach ($ars as $_k) {
            if (!isset($flesh[$_k])) {
                continue;
            }
            $_v = array_map('trim', explode(GRAIN_DATA_GS, $flesh[$_k]));
            $__v = array();
            foreach ($_v as $_e) {
                if (strlen($_e) != 0) {
                    $__v[] = $_e;
                }
            }
            $flesh[$_k] = $__v;
        }

        $moderates = array(
            'is_versions_moderated', 
            'is_comments_moderated', 
        );
        foreach ($moderates as $_m) {
            if (!isset($flesh[$_m])) {
                continue;
            }
            $v = $flesh[$_m];
            $flesh[$_m] = (0 == $v + 0) ? false : true;
        }

        return $flesh;
    }

    // }}}
    // {{{ grain2flesh()

    function grain2flesh($grain)
    {
        $ars = array(
            'categories',
            'versions',
            'comments',
        );
        foreach ($ars as $_k) {
            if (!isset($grain[$_k])) {
                continue;
            }
            $_v = $grain[$_k];
            if (!is_array($_v)) {
                $_v = array($_v);
            }
            $grain[$_k] = implode(GRAIN_DATA_GS, $_v);
        }

        $moderates = array(
            'is_versions_moderated', 
            'is_comments_moderated', 
        );
        foreach ($moderates as $_m) {
            if (!isset($grain[$_m])) {
                continue;
            }
            $v = $grain[$_m];
            $grain[$_m] = (0 == $v + 0) ? 0 : 1;
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
    // {{{ update()

    function update($id, $newgrain)
    {
        $keys = array_keys($newgrain);

        $this->_updatable_fields = array();
        foreach ($keys as $_k) {
            switch ($_k) {
            case 'id':
            case 'owner':
            case 'type':
                break;
            default:
                $this->_updatable_fields[] = $_k;
            }
        }

        return parent::update($id, $newgrain);
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
