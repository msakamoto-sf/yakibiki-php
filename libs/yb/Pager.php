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
 *
 */

require_once("Pager.php");

/**
 * YakiBiki Page Slider
 *
 * @author msakamoto-sf <msakamoto-sf@users.sourceforge.net>
 * @version $Id: Pager.php 220 2008-03-24 00:37:58Z msakamoto-sf $
 */
class yb_Pager
{
    // {{{ properties

    var $_urlVar = 'b';

    var $_pager_path = '';

    var $_pager_extraVars = array();

    var $_itemData;

    var $_limit_name = "l";

    var $_sort_by_name = "sb";

    var $_order_by_name = "ob";

    var $_limit;

    var $_sort_by;

    var $_order_by;

    var $_limit_list = array(10 => 10, 30 => 30, 50 => 50, 100 => 100);

    var $_sort_by_list = array();

    var $_order_by_list = array(
        ORDER_BY_ASC => 'asc', 
        ORDER_BY_DESC => 'desc'
        );

    var $_name_space = null;

    // }}}
    // {{{ setup()

    /**
     * Setup parameters.
     *
     * @access public
     * @return array array of sort_by, sort_order, limit
     */
    function setup()
    {
        $this->_order_by_list[ORDER_BY_ASC] = t('asc');
        $this->_order_by_list[ORDER_BY_DESC] = t('desc');

        $_sess_l = yb_Session::get('limit', null, $this->_name_space);
        $_sess_sb = yb_Session::get('sort_by', null, $this->_name_space);
        $_sess_ob = yb_Session::get('order_by', null, $this->_name_space);
        $_l = yb_Var::get($this->_limit_name);
        if (is_null($_l)) {
            $_l = $_sess_l;
        }
        $_sb = yb_Var::get($this->_sort_by_name);
        if (is_null($_sb)) {
            $_sb = $_sess_sb;
        }
        $_ob = yb_Var::get($this->_order_by_name);
        if (is_null($_ob)) {
            $_ob = $_sess_ob;
        }

        // validate limit
        $keys = array_keys($this->_limit_list);
        $this->_limit = $keys[0]; // initial value
        foreach ($this->_limit_list as $k => $v) {
            if ($k == $_l) {
                $this->_limit = $_l;
            }
        }

        // validate sort_by
        $keys = array_keys($this->_sort_by_list);
        $this->_sort_by = $keys[0]; // initial value
        foreach ($this->_sort_by_list as $k => $v) {
            if ($k == $_sb) {
                $this->_sort_by = $_sb;
            }
        }

        // validate order_by
        $keys = array_keys($this->_order_by_list);
        $this->_order_by = $keys[0]; // initial value
        foreach ($this->_order_by_list as $k => $v) {
            if ($k == $_ob) {
                $this->_order_by = $_ob;
            }
        }

        yb_Session::set('limit', $this->_limit, $this->_name_space);
        yb_Session::set('sort_by', $this->_sort_by, $this->_name_space);
        yb_Session::set('order_by', $this->_order_by, $this->_name_space);

        return array(
            $this->_limit_name => $this->_limit,
            $this->_sort_by_name => $this->_sort_by,
            $this->_order_by_name => $this->_order_by,
        );
    }

    // }}}
    // {{{ itemData()

    /**
     * Set/Get itemDatas.
     *
     * @access public
     * @param array itemData
     * @return array current(=old) itemData
     */
    function itemData($v = null)
    {
        $curr = $this->_itemData;
        if (!is_null($v)) {
            $this->_itemData = $v;
        }
        return $curr;
    }

    // }}}
    // {{{ _html_select()

    function _html_select($selectlist, $current, $name)
    {
        $_opts = array();
        foreach ($selectlist as $k => $v) {
            $selected = ($k == $current) ? ' selected="selected" ' : '';
            $_opts[] = sprintf('<option value="%s"%s>%s</option>', 
                $k, $selected, $v);
        }
        return sprintf("<select name=\"%s\">\n%s\n</select>\n",
            $name, implode("\n", $_opts));
    }

    // }}}
    // {{{ build()

    function build()
    {
        // Detect Xhwlay are loaded or not, and BCID is generated or not.
        $bcid = null;
        if (class_exists('Xhwlay_Var')) {
            $bcid = Xhwlay_Var::get(XHWLAY_VAR_KEY_BCID);
            if (!is_null($bcid)) {
                $this->_pager_extraVars['_bcid_'] = $bcid;
            }
        }
        $params = array(
            'itemData' => $this->_itemData,
            //'totalItems' => $this->_totalItems,
            'perPage' => $this->_limit,
            'mode' => 'Jumping',
            'delta' => 10,
            'expanded' => true,
            'importQuery' => false,
            'urlVar' => $this->_urlVar,
            'path' => $this->_pager_path,
            'append' => true,
            'fileName' => "",
            'fixFileName' => false,
            'extraVars' => $this->_pager_extraVars,
            'firstPagePre' => '',
            'firstPageText' => '[|&lt;]',
            'firstPagePost' => '',
            'lastPagePre' => '',
            'lastPageText' => '[&gt;|]',
            'lastPagePost' => '',
            'prevImg' => '[&lt;]',
            'nextImg' => '[&gt;]',
            'linkClass' => '',
            'curPageLinkClassName' => '',
            'separator'  => '',
            'spacesBeforeSeparator' => 1,
            'spacesAfterSeparator'  => 1,
        );
        $pager =& Pager::factory($params);

        $count = count($this->_itemData);
        $limit = $this->_limit;
        $curr = $pager->getCurrentPageID();
        $offset = ($curr - 1) * $limit;
        $from = $offset + 1;
        if(($offset + $limit) > $count) {
            $to = $count;
        } else {
            $to = $offset + $limit;
        }

        $select_limit = $this->_html_select(
            $this->_limit_list, $this->_limit, $this->_limit_name);
        $select_sort_by= $this->_html_select(
            $this->_sort_by_list, $this->_sort_by, $this->_sort_by_name);
        $select_order_by= $this->_html_select(
            $this->_order_by_list, $this->_order_by, $this->_order_by_name);

        $queries = explode('&', yb_Var::server('QUERY_STRING'));
        $hiddens = array();
        foreach ($queries as $q) {
            if (strpos($q, '=') === false) {
                continue;
            }
            $_q = explode('=', $q);
            if ($_q[0] == $this->_urlVar) {
                // skip page position
                continue;
            }
            $hiddens[] = sprintf(
                '<input type="hidden" name="%s" value="%s">',
                $_q[0], rawurldecode($_q[1]));
        }

        return array(
            'max' => $count,
            'offset' => $offset,
            'from' => $from,
            'to' => $to,
            'html_hidden_queries' => implode("\n", $hiddens),
            'html_select_lists' => array(
                'limit' => $select_limit,
                'sort_by' => $select_sort_by,
                'order_by' => $select_order_by,
            ),
            'pager' => &$pager,
        );
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

