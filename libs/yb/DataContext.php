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
 * requires
 */
//require_once('Cache/Lite/Function.php');

/**
 * YakiBiki data context
 *
 * @author msakamoto-sf <msakamoto-sf@users.sourceforge.net>
 * @version $Id: DataContext.php 110 2007-12-16 06:33:29Z msakamoto-sf $
 */
class yb_DataContext
{
    /**
     * @access protected
     * @type array
     */
    var $_data = array();

    // {{{ yb_DataContext()

    function yb_DataContext($data)
    {
        $this->_data = $data;
    }

    // }}}
    // {{{ get()

    /**
     * @access public
     */
    function get($key)
    {
        return (isset($this->_data[$key])) ? $this->_data[$key] : null;
    }

    // }}}
    // {{{ set($key)

    /**
     * @access public
     */
    function set($key, $val)
    {
        $old = @$this->_data[$key];
        $this->_data[$key] = $val;
        return $old;
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
