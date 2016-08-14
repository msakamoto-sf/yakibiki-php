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

require_once('grain/Util.php');
require_once('yb/Util.php');

/**
 * Grain Data Storage Library : Datetime Index
 *
 * @author msakamoto-sf <msakamoto-sf@users.sourceforge.net>
 * @version $Id: Datetime.php 298 2008-06-29 06:23:11Z msakamoto-sf $
 */
class grain_Index_Datetime
{
    // {{{ properties

    /**
     * index data directory
     *
     * @var string
     * @access protected
     */
    var $_dirname = "";

    /**
     * default property values
     *
     * @var array
     * @access protected
     */
    var $_defaults = array(
        '_order' => ORDER_BY_DESC,
        '_filters' => null,
    );

    /**
     * Sort order (ORDER_BY_DESC:default or ORDER_BY_ASC)
     *
     * @var integer
     * @access protected
     */
    var $_order;

    /**
     * Filtering IDs
     *
     * @var array Data ID array
     * @access protected
     */
    var $_filters;

    /**
     * cache
     *
     * @var mixed
     * @access protected
     */
    var $_cache = array();

    // }}}
    // {{{ constructor

    /**
     * Constructor
     *
     * @access public
     * @param string index data directory
     */
    function grain_Index_Datetime($dirname)
    {
        $this->_dirname = $dirname;

        $this->reset();

        $files = $this->find_index_files();

        // correct index datas from each targeted index files.
        foreach ($files as $_f) {
            $fp = yb_Util::file_open_lock($_f, 'rb', LOCK_SH);
            if ($fp) {
                $indice = $this->_load_lines($fp);
                foreach ($indice as $_v => $_k) {
                    $this->_cache[$_v] = $_k;
                }
                fclose($fp);
            }
        }
        // sort by array's value (= index key = datetime)
        asort($this->_cache);
    }

    // }}}
    // {{{ _load_lines()

    /**
     * Load from given file pointer, and return parsed index records.
     *
     * @access protected
     * @param resource file pointer
     * @return array array of index records.
     *         If read error, return null.
     */
    function _load_lines($fp)
    {
        $data = "";
        while (!feof($fp)) {
            $_buf = fread($fp, 8192);
            if ($_buf === false) {
                yb_Error::raise("fread() failed!");
                return null;
            }
            $data .= $_buf;
        }

        $lines = array_map('trim', explode(GRAIN_DATA_RS, $data));

        $results = array();
        foreach ($lines as $line) {
            if ($line  == '') {
                continue;
            }
            $_els = explode(GRAIN_DATA_FS, $line, 2);
            if (count($_els) != 2) {
                continue;
            }

            // $_els[0] : datetime = index key (sort by value)
            // $_els[1] : id
            $results[$_els[1]] = $_els[0];
        }
        // sort by array's value (= index key = datetime)
        asort($results);

        return $results;
    }

    // }}}
    // {{{ order()

    /**
     * Get/Set 'order' property value
     *
     * @access public
     * @param integer new value for 'order'(optional)
     * @return integer current(=old) 'order' value
     */
    function order($new = null)
    {
        $old = $this->_order;
        if (!is_null($new)) {
            $this->_order = $new;
        }
        return $old;
    }

    // }}}
    // {{{ filters()

    /**
     * Get/Set 'filters' property value
     *
     * @access public
     * @param array new data ID array for 'filters'(optional)
     * @return array current(=old) 'filters' value
     */
    function filters($new = null)
    {
        $old = $this->_filters;
        if (!is_null($new)) {
            if (!is_array($new)) {
                $new = array($new);
            }
            $this->_filters = $new;
        }
        return $old;
    }

    // }}}
    // {{{ reset()

    /**
     * Reset property values to default.
     *
     * @access public
     */
    function reset()
    {
        foreach ($this->_defaults as $_p => $_v) {
            $this->{$_p} = $_v;
        }
    }

    // }}}
    // {{{ index_filename()

    /**
     * Get corresponding index file name for given key id.
     *
     * @access protected
     * @param string GMT datetime formatted by YB_TIME_FMT_INTERNAL_RAW
     * @return string index file name
     */
    function index_filename($gmt_raw)
    {
        $t =& yb_Time::singleton();
        $els = $t->splitInternalRaw($gmt_raw);
        $key = sprintf("%04d%02d", $els['year'], $els['month']);
        return $this->_dirname . '/' . $key . '.idx';
    }

    // }}}
    // {{{ find_index_files()

    /**
     * Get file names for specified ranges.
     *
     * @access protected
     * @return array array of targeted index file names.
     */
    function find_index_files()
    {
        $filenames = array();
        if (!is_dir($this->_dirname)) {
            if (!mkdir($this->_dirname)) {
                yb_Error::raise("mkdir(" . $this->_dirname . ") failed!");
                return $filenames;
            }
        }
        $d = dir($this->_dirname);
        while (false !== ($entry = $d->read())) {
            if (!preg_match('/^\d{6}\.idx$/m', $entry)) {
                continue;
            }
            $filenames[] = realpath($this->_dirname . '/' . $entry);
        }
        sort($filenames);

        return $filenames;
    }

    // }}}
    // {{{ gets()

    /**
     * Return datas
     *
     * @access public
     * @return array array of data ID
     *         if file errors, return null.
     */
    function gets()
    {
        $_tmp = array();
        if (is_array($this->_filters)) {
            foreach ($this->_cache as $_v => $_k) {
                if (!in_array($_v, $this->_filters)) {
                    continue;
                }
                $_tmp[$_v] = $_k;
            }
        } else {
            foreach ($this->_cache as $_v => $_k) {
                $_tmp[$_v] = $_k;
            }
        }

        $sort_f = ($this->_order == ORDER_BY_ASC) ? "asort" : "arsort";
        $sort_f($_tmp);
        return array_keys($_tmp);
    }

    // }}}
    // {{{ _make_data()

    /**
     * Make index data from "array[$v] = $k" for file writing
     *
     * @access protected
     * @param array array's key is data id, value is key id.
     * @return string data text for fwrite()
     */
    function _make_data($indice)
    {
        asort($indice);
        $lines = array();
        foreach ($indice as $_v => $_k) {
            $lines[] = $_k . GRAIN_DATA_FS . $_v;
        }
        return implode(GRAIN_DATA_RS, $lines) . GRAIN_DATA_RS;

   }

    // }}}
    // {{{ append()

    /**
     * Append entry
     *
     * @access public
     * @param integer data id
     * @param string GMT datetime formatted by YB_TIME_FMT_INTERNAL_RAW
     * @return boolean If success, TRUE. Any error occurs, FALSE.
     */
    function append($did, $gmt_raw)
    {
        $filename = $this->index_filename($gmt_raw);
        $fp = yb_Util::file_open_lock($filename, 'a+b', LOCK_EX);
        if (!$fp) {
            return false;
        }

        $records = $this->_load_lines($fp);
        if (is_null($records)) {
            return false;
        }

        // add entry
        $records[$did] = $gmt_raw;
        $this->_cache[$did] = $gmt_raw;
        $writes = $this->_make_data($records);

        $ret = yb_Util::file_write($fp, $writes, strlen($writes), true);
        if ($ret === false) {
            return false;
        }

        fclose($fp);

        return true;
    }

    // }}}
    // {{{ delete()

    /**
     * Delete entry
     *
     * @access public
     * @param integer data id
     * @param string GMT datetime formatted by YB_TIME_FMT_INTERNAL_RAW
     * @return boolean If success, TRUE.
     *                 If given data id or key id is not found, or, any 
     *                 error ocuurs, return FALSE.
     */
    function delete($did, $gmt_raw)
    {
        $filename = $this->index_filename($gmt_raw);
        $fp = yb_Util::file_open_lock($filename, 'a+b', LOCK_EX);
        if (!$fp) {
            return false;
        }

        $records = $this->_load_lines($fp);
        if (is_null($records)) {
            return false;
        }

        $ret = true;
        if (isset($this->_cache[$did]) && $this->_cache[$did] == $gmt_raw) {
            unset($this->_cache[$did]);
        }
        if (isset($records[$did]) && $records[$did] == $gmt_raw) {
            // delete entry
            unset($records[$did]);
            $writes = $this->_make_data($records);

            $ret = yb_Util::file_write($fp, $writes, strlen($writes), true);
        } else {
            $ret = false;
        }

        fclose($fp);

        if ($ret === false) {
            return false;
        }

        return true;
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
