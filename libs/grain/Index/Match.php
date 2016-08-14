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
 * Grain Data Storage Library : Match(Regexp Match) Index
 *
 * @author msakamoto-sf <msakamoto-sf@users.sourceforge.net>
 * @version $Id: Match.php 452 2008-11-16 15:26:38Z msakamoto-sf $
 */
class grain_Index_Match
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

    /**
     * Case sensitivity
     *
     * @var boolean
     * @access protected
     */
    var $_case_sensitive = false;

    // {{{ constructor

    /**
     * Constructor
     *
     * @access public
     * @param string data file fullpath
     */
    function grain_Index_Match($index_file)
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
    // {{{ case_sensitive()

    /**
     * Get/Set case sensitivity
     *
     * @access public
     * @param boolean set case sensitive on(true) or off(false)
     * @return boolean current(=old) case sensitive status
     */
    function case_sensitive($new = null)
    {
        $cur = $this->_case_sensitive;
        if (!is_null($new)) {
            $this->_case_sensitive = $new;
        }
        return $cur;
    }

    // }}}
    // {{{ reset()

    /**
     * Reset properties
     *
     * @access public
     */
    function reset()
    {
        $this->_case_sensitive = false;
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
            $_els = explode(GRAIN_DATA_FS, $line, 2);
            if (count($_els) != 2) {
                continue;
            }

            // $_els[0] : data id
            // $_els[1] : value
            $results[$_els[0]] = $_els[1];
        }
        // sort by key (data id)
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
        foreach ($this->_cache as $did => $text) {
            $lines[] = $did . GRAIN_DATA_FS . $text;
        }
        return implode(GRAIN_DATA_RS, $lines) . GRAIN_DATA_RS;
    }
    // }}}
    // {{{ search()

    /**
     * Get data ids by searching given keyword.
     *
     * @access public
     * @param string keyword
     * @return array data ids.
     */
    function search($needle)
    {
        $ret = array();
        $func = ($this->_case_sensitive) ? "strpos" : "stristr";
        foreach ($this->_cache as $did => $haystack) {
            if ($func($haystack, $needle) !== false) {
                $ret[] = $did;
            }
        }
        sort($ret);
        return $ret;
    }

    // }}}
    // {{{ fullmatch()

    /**
     * Get data ids by searching FULL-MATCH with given keyword.
     *
     * @access public
     * @param string keyword
     * @return array data ids.
     */
    function fullmatch($needle)
    {
        if (!$this->_case_sensitive) {
            // If case sensitive is NOT used, round strings to upper case.
            $needle = strtoupper($needle);
        }

        $ret = array();
        foreach ($this->_cache as $did => $haystack) {
            if (!$this->_case_sensitive && 
                (strtoupper($haystack) == $needle)) {
                // if case sensitive is NOT used and upper-rounded text hit.
                $ret[] = $did;
                continue;
            }
            if ($haystack == $needle) {
                $ret[] = $did;
                continue;
            }
        }
        sort($ret);
        return $ret;
    }

    // }}}
    // {{{ listmatch()

    /**
     * Get data id and its title pairs by head-match with given keyword.
     * (case sensitive enforced.)
     *
     * @access public
     * @param string keyword
     * @return array assoc-array of data id => its titles.
     */
    function listmatch($keyword)
    {
        $ret = array();
        if ('' === trim($keyword)) {
            return $ret;
        }
        $regexp = '/^' . preg_quote($keyword, '/') . '/mi';
        foreach ($this->_cache as $did => $haystack) {
            if (preg_match($regexp, $haystack)) {
                $ret[$did] = $haystack;
            }
        }

        natsort($ret);
        return $ret;
    }

    // }}}
    // {{{ register()

    /**
     * Register data id and its search text data.
     *
     * Already added id is simply overwrited.
     *
     * @access public
     * @param integer data id
     * @param string text data 
     *               (CR, LF, and other some controll codes are removed.)
     * @return boolean If success, TRUE. Any error occurs, FALSE.
     */
    function register($id, $text)
    {
        $fp = yb_Util::file_open_lock($this->_index_file, 'a+b', LOCK_EX);
        if (!$fp) {
            return false;
        }

        $this->_cache = $this->_load_lines($fp);

        // add or overwrite id and text.
        $this->_cache[$id] = grain_Util::strip($text);
        $data = $this->_make_data_from_cache();

        if (false === yb_Util::file_write($fp, $data, strlen($data), true)) {
            return false;
        }

        fclose($fp);

        return true;
    }

    // }}}
    // {{{ unregister()

    /**
     * Un-Register data id.
     *
     * @access public
     * @param integer data id
     * @param string text data
     * @return boolean If success, TRUE. Any error occurs, FALSE.
     */
    function unregister($id)
    {
        $fp = yb_Util::file_open_lock($this->_index_file, 'a+b', LOCK_EX);
        if (!$fp) {
            return false;
        }

        $this->_cache = $this->_load_lines($fp);

        // remove specified data id
        unset($this->_cache[$id]);
        $data = $this->_make_data_from_cache();

        if (false === yb_Util::file_write($fp, $data, strlen($data), true)) {
            return false;
        }

        fclose($fp);

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
