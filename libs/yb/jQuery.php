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

/**
 * YakiBiki Simple jQuery manager
 *
 * @author msakamoto-sf <msakamoto-sf@users.sourceforge.net>
 * @version $Id: jQuery.php 432 2008-11-09 04:39:15Z msakamoto-sf $
 */
class yb_jQuery
{
    var $_onload_events;

    // {{{ singleton()

    /**
     * @static
     * @access private
     */
    function &singleton()
    {
        static $i = null;
        if (is_null($i)) {
            $i = new yb_jQuery();
            $i->_onload_events = array();
        }
        return $i;
    }

    // }}}
    // {{{ add_onload_event()

    /**
     * Add onload event : $(function(){ ... })
     *
     * @static
     * @access public
     * @param string internal id
     * @param string javascript code.
     */
    function add_onload_event($id, $js)
    {
        $i =& yb_jQuery::singleton();
        $i->_onload_events[$id] = $js;
    }

    // }}}
    // {{{ get_onload_event()

    /**
     * Retrieve registered onload events
     *
     * @static
     * @access public
     * @param string internal id (optional)
     * @return assoc-array of id => javascript code.
     */
    function get_onload_event($id = null)
    {
        $i =& yb_jQuery::singleton();
        if (is_null($id)) {
            return $i->_onload_events;
        }
        if (isset($i->_onload_events[$id])) {
            return array($id => $i->_onload_events[$id]);
        } else {
            return null;
        }
    }

    // }}}
    // {{{ clean_onload_event()

    /**
     * Cleanup registered onload events
     *
     * @static
     * @access public
     */
    function clean_onload_event()
    {
        $i =& yb_jQuery::singleton();
        $i->_onload_events = array();
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

