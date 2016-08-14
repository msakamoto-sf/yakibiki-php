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
require_once('Cache/Lite/Function.php');

/**
 * YakiBiki Cache_Lite_Function wrapper
 *
 * @author msakamoto-sf <msakamoto-sf@users.sourceforge.net>
 * @version $Id: Cache.php 243 2008-03-29 11:42:01Z msakamoto-sf $
 */
class yb_Cache extends Cache_Lite_Function
{
    // {{{ yb_Cache()

    function yb_Cache($group, $options = null)
    {
        if (is_null($options)) {
            $options = _YB('cache.options');
        }
        $options['defaultGroup'] = $group;
        $options['dontCacheWhenTheResultIsFalse'] = true;
        $options['dontCacheWhenTheResultIsNull'] = true;

        parent::Cache_Lite_Function($options);
    }

    // }}}
    // {{{ clean()

    function clean()
    {
        parent::clean($this->_defaultGroup);
    }

    // }}}
    // {{{ factory()

    function &factory($group, $options = null)
    {
        static $insts = array();
        if (!isset($insts[$group])) {
            $insts[$group] = new yb_Cache($group, $options);
        }
        return $insts[$group];
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
