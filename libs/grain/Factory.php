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

require_once('grain/Config.php');
require_once('grain/Sequence.php');
require_once('grain/Grain.php');
require_once('grain/Raw.php');
require_once('grain/Index/Datetime.php');
require_once('grain/Index/Match.php');
require_once('grain/Index/Pair.php');

/**
 * Grain Data Storage Library : Factory
 *
 * @author msakamoto-sf <msakamoto-sf@users.sourceforge.net>
 * @version $Id: Factory.php 410 2008-11-02 15:43:54Z msakamoto-sf $
 */
class grain_Factory
{
    // {{{ sequence()

    /**
     * Sequence Factory interface
     *
     * @static
     * @access public
     * @param string sequence name
     * @return object reference
     */
    function &sequence($name)
    {
        $ret = null;
        $ret = grain_Sequence::factory($name);
        return $ret;
    }

    // }}}
    // {{{ grain()

    /**
     * Grain Factory interface
     *
     * @static
     * @access public
     * @param string grain name
     * @return object reference
     */
    function &grain($name)
    {
        $_fz = $GLOBALS[FACTORY_ZONE];
        static $grains = array();
        if (isset($grains[$_fz][$name])) {
            return $grains[$_fz][$name];
        }

        $ret = null;
        $dir_root = grain_Config::get('grain.dir.grain');
        if (empty($dir_root)) {
            return $ret;
        }

        $gdir = $dir_root . '/' . $name;

        $chunksize = grain_Config::get('grain.chunksize.' . $name);
        if (empty($chunksize)) {
            $chunksize = grain_Config::get('grain.chunksize.default');
        }

        $grains[$_fz][$name] = new grain_Grain($gdir, $chunksize);

        return $grains[$_fz][$name];
    }

    // }}}
    // {{{ raw()

    /**
     * Raw Factory interface
     *
     * @static
     * @access public
     * @param string raw data name
     * @return object reference
     */
    function &raw($name)
    {
        $_fz = $GLOBALS[FACTORY_ZONE];
        static $raws= array();
        if (isset($raws[$_fz][$name])) {
            return $raws[$_fz][$name];
        }

        $ret = null;
        $dir_root = grain_Config::get('grain.dir.raw');
        if (empty($dir_root)) {
            return $ret;
        }

        $rdir = $dir_root . '/' . $name;

        $chunksize = grain_Config::get('grain.chunksize.' . $name);
        if (empty($chunksize)) {
            $chunksize = grain_Config::get('grain.chunksize.default');
        }

        $raws[$_fz][$name] = new grain_Raw($rdir, $chunksize);

        return $raws[$_fz][$name];
    }

    // }}}
    // {{{ index()

    /**
     * Index Factory interface
     *
     * @static
     * @access public
     * @param string type ('match', 'pair', 'datetime')
     * @param string name
     * @return object reference
     */
    function &index($type, $name)
    {
        $_fz = $GLOBALS[FACTORY_ZONE];
        $type = strtolower($type);
        static $indice = array();
        if (isset($indice[$_fz][$name])) {
            $r =& $indice[$_fz][$name];
            if ($type == 'datetime') {
                $r->reset();
            }
            return $r;
        }

        $ret = null;

        $dir_root = grain_Config::get('grain.dir.index');
        if (empty($dir_root)) {
            return $ret;
        }

        switch ($type) {
        case 'pair':
            $f = $dir_root . '/' . $name . '.idx';
            $indice[$_fz][$name] = new grain_Index_Pair($f);
            break;
        case 'match':
            $f = $dir_root . '/' . $name . '.idx';
            $indice[$_fz][$name] = new grain_Index_Match($f);
            break;
        case 'datetime':
            $d = $dir_root . '/' . $name;
            $indice[$_fz][$name] = new grain_Index_Datetime($d);
            break;
        default:
            return $ret;
        }

        return $indice[$_fz][$name];
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
