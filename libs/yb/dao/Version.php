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
 * YakiBiki Dao Version
 *
 * @author msakamoto-sf <msakamoto-sf@users.sourceforge.net>
 * @version $Id: Version.php 368 2008-09-25 01:30:05Z msakamoto-sf $
 */

require_once(dirname(__FILE__) . '/Base.php');

/**
 * YakiBiki Dao Version
 *
 * @author msakamoto-sf <msakamoto-sf@users.sourceforge.net>
 */
class yb_dao_Version extends yb_dao_Base
{
    // {{{ constructor

    /**
     * Constructor
     *
     * @access private
     */
    function yb_dao_Version()
    {
        $this->_cache_name = __CLASS__;

        $this->_grain_name = 'version';

        $this->_updatable_fields = array('owner', 'raw_id', 'version', 
            'approved', 'changelog', 'md5', 'sha1');

        $this->_sortable = array('id', 'data_id', 'created_at', 'updated_at');
    }

    // }}}
    // {{{ flesh2grain()

    function flesh2grain($flesh)
    {
        $v = $flesh['approved'];
        $flesh['approved'] = (0 == $v + 0) ? false : true;
        $changelog = $flesh['changelog'];
        $flesh['changelog'] = yb_Util::decode_ctrl_char($changelog);
        return $flesh;
    }

    // }}}
    // {{{ grain2flesh()

    function grain2flesh($grain)
    {
        $v = $grain['approved'];
        $grain['approved'] = (0 == $v + 0) ? 0 : 1;
        $changelog = $grain['changelog'];
        $grain['changelog'] = yb_Util::encode_ctrl_char($changelog);
        return $grain;
    }

    // }}}
    // {{{ find_by_id()

    function find_by_id($ids, $sort_by = "id", $order_by = ORDER_BY_ASC)
    {
        return $this->find_by('id', $ids, $sort_by, $order_by);
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
