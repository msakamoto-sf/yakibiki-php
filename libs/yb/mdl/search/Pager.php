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

require_once("yb/Pager.php");

/**
 * YakiBiki: search module Pager
 *
 * @author msakamoto-sf <msakamoto-sf@users.sourceforge.net>
 * @version $Id: Pager.php 384 2008-10-18 02:54:48Z msakamoto-sf $
 */
class yb_mdl_search_Pager extends yb_Pager
{

    var $_order_by_list = array(
        ORDER_BY_DESC => 'desc', 
        ORDER_BY_ASC => 'asc', 
        );

    function yb_mdl_search_Pager ()
    {
        $this->_name_space = __CLASS__;
        $this->_pager_extraVars['mdl'] = 'search';

        $v = yb_Var::request('ao');
        if (!empty($v)) { $this->_pager_extraVars['ao'] = $v; }
        $v = yb_Var::request('s');
        if (!empty($v)) { $this->_pager_extraVars['s'] = $v; }
        $v = yb_Var::request('ism');
        if (!empty($v)) { $this->_pager_extraVars['ism'] = $v; }
        $v = yb_Var::request('cs');
        if (!empty($v)) { $this->_pager_extraVars['cs'] = $v; }
        $v = yb_Var::request('c');
        if (!empty($v)) {
            if (!is_array($v)) {
                $v = array($v);
            }
            $this->_pager_extraVars['c'] = $v;
        }

        $this->_sort_by_list = array(
            'u' => t('updated at'),
            'c' => t('created at'),
            );
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

