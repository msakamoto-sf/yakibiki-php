<?php
/*
 *   Copyright (c) 2008 msakamoto-sf <msakamoto-sf@users.sourceforge.net>
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

if (!defined('GRAIN_DATA_FS')) {
    define('GRAIN_DATA_FS', "\x1C"); // Field Separator (^\)
}
if (!defined('GRAIN_DATA_GS')) {
    define('GRAIN_DATA_GS', "\x1D"); // Group Separator (^])
}
if (!defined('GRAIN_DATA_RS')) {
    define('GRAIN_DATA_RS', "\x0A"); // Record Separator (LF)
}

/**
 * Grain Data Storage Library : Utilities
 *
 * @author msakamoto-sf <msakamoto-sf@users.sourceforge.net>
 * @version $Id: Util.php 302 2008-07-22 14:08:03Z msakamoto-sf $
 */
class grain_Util
{
    // {{{ boxchunk_split()

    /**
     * Get splitted boxnumber chunk position
     *
     * @static
     * @access public
     * @param integer box number
     * @param integer chunk split limit
     * @return integer box chunk number
     */
    function boxchunk_split($bno, $limit)
    {
        $limit = (integer)$limit;
        if ($limit < 1) { $limit = 1; }

        $_x1 = floor($bno / $limit);
        $_x2 = $bno % $limit;
        if ($_x2 == 0) {
            return $_x1 * $limit;
        } else {
            return ($_x1 + 1) * $limit;
        }
    }

    // }}}
    // {{{ strip

    /**
     * Strip grain special controll characters (CR, LF, GRAIN_DATA_FS, 
     * GRAIN_DATA_RS)
     *
     * @static
     * @access public
     * @param string
     * @return string
     */
    function strip($data)
    {
        return str_replace(
            array("\x0D", "\x0A", GRAIN_DATA_FS, GRAIN_DATA_RS),
            array('',     '',     '',            ''),
            $data);
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
