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

require_once('grain/Util.php');
require_once('yb/Util.php');

/**
 * Grain Data Storage Library : Pair(1:N, M:N ralational) Index
 *
 * @author msakamoto-sf <msakamoto-sf@users.sourceforge.net>
 * @version $Id: Pair.php 297 2008-06-25 15:11:43Z msakamoto-sf $
 */
class grain_Index_Pair
{
    /**
     * index data file
     *
     * @var string
     * @access protected
     */
    var $_index_file = "";

    /**
     * cache
     *
     * @var mixed
     * @access protected
     */
    var $_cache = null;

    // {{{ constructor

    /**
     * Constructor
     *
     * @access public
     * @param string data file fullpath
     */
    function grain_Index_Pair($index_file)
    {
        $this->_index_file = $index_file;

        $this->_cache = array();

        if (is_readable($this->_index_file)) {
            $fp = yb_Util::file_open_lock($index_file, 'rb', LOCK_SH);
            if ($fp) {
                $this->_cache = $this->_load_lines($fp);
                fclose($fp);
            }
        }
    }

    // }}}
    // {{{ _load_lines()

    /**
     * Load from given file pointer, and return parsed index records.
     *
     * @access protected
     * @param resource file pointer
     * @return array array of index records.
     */
    function _load_lines($fp)
    {
        $results = array();
        $data = "";
        while (!feof($fp)) {
            $_buf = fread($fp, 8192);
            if ($_buf === false) {
                yb_Error::raise("fread() failed!");
                return $results;
            }
            $data .= $_buf;
        }

        $lines = array_map('trim', explode(GRAIN_DATA_RS, $data));

        foreach ($lines as $line) {
            if ($line == '') {
                continue;
            }
            $els = explode(GRAIN_DATA_FS, $line, 2);
            $kid = $els[0];
            if (isset($els[1])) {
                $_dids = yb_Util::array_remove_empty_string(
                    array_map('trim', explode(GRAIN_DATA_GS, $els[1])));
                sort($_dids);
                $results[$kid] = $_dids;
            } else {
                $results[$kid] = array();
            }
        }
        ksort($results);
        return $results;
    }

    // }}}
    // {{{ _make_data_from_cache()

    /**
     * Make index file data from internal cache
     *
     * @access protected
     * @return string data text for fwrite()
     */
    function _make_data_from_cache()
    {
        ksort($this->_cache);
        $lines = array();
        foreach ($this->_cache as $kid => $dids) {
            $lines[] = trim($kid . GRAIN_DATA_FS 
                . implode(GRAIN_DATA_GS, $dids));
        }
        return trim(implode(GRAIN_DATA_RS, $lines)) . GRAIN_DATA_RS;

    }
    // }}}
    // {{{ get_from()

    /**
     * Get ids related with specified key id(s).
     *
     * Unexisted key id is ignored.
     *
     * @access public
     * @param mixed key id (integer: single id, array of id: multiple id)
     * @return array assoc-array which key is key id(integer), value
     *               is array of ids.
     */
    function get_from($kid)
    {
        $kids = $kid;
        if (!is_array($kid)) {
            $kids = array($kid);
        }

        $result = array();
        foreach ($kids as $kid) {
            if (isset($this->_cache[$kid])) {
                $result[$kid] = $this->_cache[$kid];
            }
        }
        return $result;
    }

    // }}}
    // {{{ count_for()

    /**
     * Return number of data ids in specified key id.
     *
     * @access public
     * @param integer key id
     * @return integer If given id is not existed, return 0.
     */
    function count_for($kid)
    {
        if (!isset($this->_cache[$kid])) {
            return 0;
        }
        return count($this->_cache[$kid]);
    }

    // }}}
    // {{{ add()

    /**
     * Add data id to specified key id's entries.
     *
     * Unexisted key id is registered automatically.
     * Already added key id is ignored.
     *
     * @access public
     * @param integer related data id
     * @param mixed key id (integer: single id, array of id: multiple id)
     * @return integer count of affected entries. (or error happen, return 0.)
     */
    function add($did, $kids)
    {
        $fp = yb_Util::file_open_lock($this->_index_file, 'a+b', LOCK_EX);
        if (!$fp) {
            return 0;
        }

        $this->_cache = $this->_load_lines($fp);

        if (!is_array($kids)) {
            $kids = array($kids);
        }

        $cnt = 0;
        foreach ($kids as $kid) {
            if (!isset($this->_cache[$kid])) {
                $this->_cache[$kid] = array();
            }
            $_dids = $this->_cache[$kid];
            if (!in_array($did, $_dids)) {
                $_dids[] = $did;
                sort($_dids);
                $this->_cache[$kid] = $_dids;
                $cnt++;
            }
        }

        $data = $this->_make_data_from_cache();
        if (false === yb_Util::file_write($fp, $data, strlen($data), true)) {
            return 0;
        }

        fclose($fp);

        return $cnt;
    }

    // }}}
    // {{{ remove()

    /**
     * Remove data id from specified key id's entries.
     *
     * Unexisted key id is ignored.
     * Already removed key id is ignored.
     *
     * @access public
     * @param integer related data id
     * @param mixed key id (integer: single id, array of id: multiple id)
     * @return integer count of affected entries. (or error happen, return 0.)
     */
    function remove($did, $kids)
    {
        $fp = yb_Util::file_open_lock($this->_index_file, 'a+b', LOCK_EX);
        if (!$fp) {
            return 0;
        }

        $this->_cache = $this->_load_lines($fp);

        if (!is_array($kids)) {
            $kids = array($kids);
        }

        $cnt = 0;
        foreach ($kids as $kid) {
            if (!isset($this->_cache[$kid])) {
                continue;
            }
            $_dids = $this->_cache[$kid];
            $_new = array();
            foreach ($_dids as $_v) {
                if (intval($_v) === intval($did)) {
                    $cnt++;
                    continue;
                }
                $_new[] = $_v;
            }
            sort($_new);
            $this->_cache[$kid] = $_new;
        }

        $data = $this->_make_data_from_cache();
        if (false === yb_Util::file_write($fp, $data, strlen($data), true)) {
            return 0;
        }

        fclose($fp);

        return $cnt;
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
