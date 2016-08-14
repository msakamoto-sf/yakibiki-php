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

require_once('grain/Factory.php');

/**
 * Typical Dao Base class.
 *
 * for usage, see test case.
 *
 * @author msakamoto-sf <msakamoto-sf@users.sourceforge.net>
 * @version $Id: Base.php 323 2008-09-08 14:57:49Z msakamoto-sf $
 */
class yb_dao_Base
{
    // {{{ properties

    /**
     * Grain Name
     *
     * @var string
     * @access protected
     */
    var $_grain_name = array();

    /**
     * Sortable field names
     *
     * @var array of string
     * @access protected
     */
    var $_sortable = array();

    /**
     * Updatable field data
     *
     * @var array of string
     * @access protected
     */
    var $_updatable_fields = array();

    /**
     * yb_Cache's group name
     *
     * @var string
     * @access protected
     */
    var $_cache_name = '';

    // }}}
    // {{{ constructor

    /**
     */
    function yb_dao_Base()
    {
    }

    // }}}
    // {{{ flesh2grain()

    /**
     * Convert internal data(grain-world) to external data(model-world)
     *
     * @param array flesh (Internal Data assoc-array)
     * @return array grain (External Data assoc-array)
     */
    function flesh2grain($flesh)
    {
        return $flesh;
    }

    // }}}
    // {{{ grain2flesh()

    /**
     * Convert external data(model-world) to internal data(grain-world)
     *
     * @param array grain (External Data assoc-array)
     * @return array flesh (Internal Data assoc-array)
     */
    function grain2flesh($grain)
    {
        return $grain;
    }

    // }}}
    // {{{ finder()

    /**
     * grain, filter-like sortable finder (non-cached)
     *
     * @access public
     * @param string sort field(optional). default : sort by id.
     * @param integer ORDER_BY_ASC or ORDER_BY_DESC (optional)
     *                default: ORDER_BY_ASC
     * @param callback filter callback (default: return array of data array)
     * @param mixed appendix arguments of filter callback
     * @return mixed return value of callback.
     *               (If error raised, null return.)
     */
    function finder($sort_by, $order, 
        $filter_callback = null, $filter_args = null)
    {
        $g =& grain_Factory::grain($this->_grain_name);
        $fleshes = $g->find();
        $records = array();
        foreach ($fleshes as $bno => $flesh) {
            $flesh['id'] = $bno;
            $records[] = $this->flesh2grain($flesh);
        }

        // create user-compare-function by given sort_by, order_by param.
        $_sort_by = (in_array($sort_by, $this->_sortable)) 
            ? $sort_by : "id";
        $_order_by = ($order == ORDER_BY_ASC) ? "<" : ">";
        $_cmp_func_impl = sprintf(
            'if (@$a["%s"] == @$b["%s"]) return 0; ' .
            ' return (@$a["%s"] %s @$b["%s"]) ? -1 : 1;', 
            $_sort_by, $_sort_by, 
            $_sort_by, $_order_by, $_sort_by);

        // sort by user-compare-function (lambda function)
        usort($records, create_function('$a, $b', $_cmp_func_impl));

        /*
         * Apply user defined callback filter
         */
        if ($filter_callback && is_callable($filter_callback)) {
            if (!is_array($filter_args)) {
                $filter_args = array($filter_args);
            }
            array_unshift($filter_args, $records);
            $records = call_user_func_array($filter_callback, $filter_args);
        }

        return $records;
    }

    // }}}
    // {{{ find_all()

    /**
     * Find All records(Cached)
     *
     * @access public
     * @param string sort field(optional). default : sort by id.
     * @param integer ORDER_BY_ASC or ORDER_BY_DESC (optional)
     *                default: ORDER_BY_ASC
     * @return array array of data array.
     */
    function find_all($sort_by = "id", $order = ORDER_BY_ASC)
    {
        $cache =& yb_Cache::factory($this->_cache_name);
        return $cache->call(array(&$this, 'finder'), $sort_by, $order);
    }

    // }}}
    // {{{ find_by()

    /**
     * Find by Any Field (Cached)
     *
     * @access public
     * @param string find-by-field name
     * @param mixed find-by-value(s)
     * @param string sort field(optional). default : sort by id.
     * @param integer ORDER_BY_ASC or ORDER_BY_DESC (optional)
     *                default: ORDER_BY_ASC
     * @return array array of data array.
     */
    function find_by($by_key, $by_values, 
        $sort_by = "id", $order_by = ORDER_BY_ASC)
    {
        $cache =& yb_Cache::factory($this->_cache_name);

        $by_key = strtolower($by_key);
        if (!is_array($by_values)) {
            $by_values = array($by_values);
        }
        $filter_callback = array(&$this, 'default_filter');
        $filter_args = array($by_key, $by_values);

        return $cache->call(
            array(&$this, 'finder'), 
            $sort_by, $order_by, $filter_callback, $filter_args);
    }

    // }}}
    // {{{ default_filter()

    /**
     * Default Filter for finder()
     *
     * @access public
     * @param array array or records (= grains, not equal fleshes!)
     * @param string filter-by-field name
     * @param mixed filter-by-value(s)
     * @return array array of filtered record.
     */
    function default_filter($records, $by_key, $by_values)
    {
        $results = array();
        foreach ($records as $record) {
            $v = $record[$by_key];
            if (in_array($v, $by_values)) {
                $results[] = $record;
            }
        }
        return $results;
    }

    // }}}
    // {{{ create()

    /**
     * Create new record.
     *
     * Fill 'created_at', 'updated_at' field by current time automatically.
     *
     * @access public
     * @param mixed new data
     * @return integer created id.
     *               (If error raised, null return.)
     */
    function create($newgrain)
    {
        $cache =& yb_Cache::factory($this->_cache_name);
        $cache->clean();

        $seq =& grain_Factory::sequence($this->_grain_name);
        $bno = $seq->next();

        // create data array
        $t =& new yb_Time();
        $date_at = $t->getGMT(YB_TIME_FMT_INTERNAL_RAW);
        $newgrain['created_at'] = $date_at;
        $newgrain['updated_at'] = $date_at;

        $flesh = $this->grain2flesh($newgrain);

        $g =& grain_Factory::grain($this->_grain_name);
        return ($g->save($bno, $flesh)) ? $bno : null;
    }

    // }}}
    // {{{ update()
    /**
     * Update record specified by given id.
     *
     * Fill 'updated_at' field by current time automatically.
     *
     * NOTICE: It only update fileds which are specified in 
     * '_updatable_fields' array.
     *
     * @access public
     * @param integer target record id
     * @param mixed new data
     * @return integer affected row count (1 or 0).
     *               (If error raised, null return.)
     */
    function update($id, $newgrain)
    {
        $cache =& yb_Cache::factory($this->_cache_name);
        $cache->clean();

        $g =& grain_Factory::grain($this->_grain_name);
        $fleshes = $g->find($id);
        if (0 == count($fleshes)) {
            return 0;
        }
        $flesh = $fleshes[$id];
        $current_grain = $this->flesh2grain($flesh);

        // filter only updatable fields
        foreach ($this->_updatable_fields as $_f) {
            if (isset($newgrain[$_f])) {
                $current_grain[$_f] = $newgrain[$_f];
            }
        }
        $t =& new yb_Time();
        // auto filling "updated_at" field
        $current_grain["updated_at"] = $t->getGMT(YB_TIME_FMT_INTERNAL_RAW);

        $newflesh = $this->grain2flesh($current_grain);

        return ($g->save($id, $newflesh)) ? 1 : 0;
    }

    // }}}
    // {{{ delete()

    /**
     * Delete record specified by given id.
     *
     * @access public
     * @param integer target record id
     * @return integer affected row count (1 or 0).
     *               (If error raised, null return.)
     */
    function delete($id)
    {
        $cache =& yb_Cache::factory($this->_cache_name);
        $cache->clean();

        $g =& grain_Factory::grain($this->_grain_name);
        return ($g->delete((integer)$id)) ? 1 : 0;
    }

    // }}}
    // {{{ destroy()

    /**
     * Delete all grain physical data files.
     *
     * @access public
     * @return integer If success 1, error raised 0.
     */
    function destroy()
    {
        $cache =& yb_Cache::factory($this->_cache_name);
        $cache->clean();

        $g =& grain_Factory::grain($this->_grain_name);
        return ($g->destroy()) ? 1 : 0;
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
