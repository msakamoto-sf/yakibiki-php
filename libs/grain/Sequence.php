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

/**
 * Grain Data Storage Library : Sequence
 *
 * @author msakamoto-sf <msakamoto-sf@users.sourceforge.net>
 * @version $Id: Sequence.php 378 2008-10-05 13:52:39Z msakamoto-sf $
 */
class grain_Sequence
{
    var $_filename;

    // {{{ constructor()

    /**
     * @access protected
     */
    function grain_Sequence($filename)
    {
        $this->_filename = $filename;
    }

    // }}}
    // {{{ factory()

    /**
     * Factory interface
     *
     * @static
     * @access public
     * @param string sequence name
     * @return object reference
     */
    function &factory($name)
    {
        $_fz = $GLOBALS[FACTORY_ZONE];
        static $instances = array();
        if (!isset($instances[$_fz][$name])) {
            $_seq_file = grain_Config::get('grain.dir.sequence') 
                . '/' . $name . '.seq';
            $instances[$_fz][$name] = new grain_Sequence($_seq_file);
        }
        return $instances[$_fz][$name];
    }

    // }}}
    // {{{ _open()

    /**
     * open and lock, load sequence file.
     *
     * @access private
     * @param integer reference which current sequence data is stored.
     * @return resource file pointer if success, 
     *                  null if any error has occurred.
     */
    function _open(&$data)
    {
        $fp = fopen($this->_filename, 'a+b');
        if (!$fp) {
            yb_Error::raise("fopen('" . $this->_filename . "') failed!");
            return null;
        }
        if (!flock($fp, LOCK_EX)) {
            yb_Error::raise("flock('" . $this->_filename . "', LOCK_EX) failed!");
            return null;
        }

        if (fseek($fp, 0) === false) {
            yb_Error::raise("fseek('" . $this->_filename . "', 0) failed!");
            return null;
        }
        $_data = "";
        while (!feof($fp)) {
            $_buf = fread($fp, 8192);
            if ($_buf === false) {
                yb_Error::raise("fread('" . $this->_filename . "') failed!");
                return null;
            }
            $_data .= $_buf;
        }
        $_data = trim($_data);
        $data = (integer)$_data;

        return $fp;
    }

    // }}}
    // {{{ _write()

    /**
     * write sequence number to data file
     *
     * @access private
     * @param resource file pointer
     * @param integer sequence number
     * @return boolean true if success, 
     *                  false if any error has occurred.
     */
    function _write($fp, $data)
    {
        if (!ftruncate($fp, 0)) {
            yb_Error::raise("ftruncate('" . $this->_filename . "', 0) failed!");
            return false;
        }

        if (fseek($fp, 0) === false) {
            yb_Error::raise("fseek('" . $this->_filename . "', 0) failed!");
            return false;
        }
        if (fwrite($fp, (string)$data) === false) {
            yb_Error::raise("fwrite('" . $this->_filename . "', ...) failed!");
            return false;
        }

        return true;
    }

    // }}}
    // {{{ current()

    /**
     * return current sequnce number (not count up)
     *
     * @access public
     * @return integer if error occurrs, return -1.
     */
    function current()
    {
        $data = 0;
        $fp = $this->_open($data);
        if (is_null($fp)) {
            return -1;
        }
        @fclose($fp);

        return $data;
    }

    // }}}
    // {{{ set()

    /**
     * update sequence number
     *
     * @access public
     * @param integer new sequence number
     * @return boolean true if success, false if any error has occurred.
     */
    function set($new)
    {
        $data = 0;
        $fp = $this->_open($data);
        if (is_null($fp)) {
            return false;
        }

        $data = $new;

        if (!$this->_write($fp, $data)) {
            return false;
        }
        @fclose($fp);

        return true;
    }

    // }}}
    // {{{ next()

    /**
     * return atomic count upped sequence number.
     *
     * @access public
     * @return integer if error occurrs, return -1.
     */
    function next()
    {
        $data = 0;
        $fp = $this->_open($data);
        if (is_null($fp)) {
            return -1;
        }

        $data = $data + 1;

        if (!$this->_write($fp, $data)) {
            return -1;
        }
        @fclose($fp);

        return $data;
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
