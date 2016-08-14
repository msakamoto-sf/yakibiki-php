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
 * Grain Data Storage Library : Grain Data Storage
 *
 * @author msakamoto-sf <msakamoto-sf@users.sourceforge.net>
 * @version $Id: Grain.php 323 2008-09-08 14:57:49Z msakamoto-sf $
 */
class grain_Grain
{
    // {{{ properties

    /**
     * grain data directory
     *
     * @var string
     * @access protected
     */
    var $_dirname = "";

    /**
     * file chunk size
     *
     * @var integer
     * @access protected
     */
    var $_chunksize = 0;

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
     * @param string grain data directory
     * @param integer file chunk size
     */
    function grain_Grain($dirname, $chunksize)
    {
        $this->_dirname = $dirname;
        $this->_chunksize = $chunksize;

        if (!is_dir($this->_dirname) && !mkdir($this->_dirname)) {
            yb_Error::raise("mkdir(" . $this->_dirname . ") failed!");
        }
    }

    // }}}
    // {{{ find_data_files()

    /**
     * Get data file names.
     *
     * @access protected
     * @return array array of data file names.
     */
    function find_data_files()
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
            if (!preg_match('/^\d+\.dat$/m', $entry)) {
                continue;
            }
            $filenames[] = realpath($this->_dirname . '/' . $entry);
        }
        sort($filenames);

        return $filenames;
    }

    // }}}
    // {{{ data_filename()

    /**
     * Get corresponding data file name for given box number.
     *
     * @access protected
     * @param integer grain's box number
     * @return string data file name
     */
    function data_filename($bno)
    {
        $chunk = grain_Util::boxchunk_split($bno, $this->_chunksize);
        return $this->_dirname . '/' . $chunk . '.dat';
    }

    // }}}
    // {{{ _load_lines()

    /**
     * Load from given file pointer, and return parsed grain records.
     *
     * @access protected
     * @param resource file pointer
     * @return array array of grain datas(box unit).
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
            $_els = explode(GRAIN_DATA_FS, $line, 3);
            if (count($_els) != 3) {
                continue;
            }
            list($bno, $gname, $v) = $_els;
            $results[$bno][$gname] = $v;
        }

        return $results;
    }

    // }}}
    // {{{ _make_data()

    /**
     * Make grain data
     *
     * @access protected
     * @param array array's key is box number, value is grain assoc-array.
     * @return string data text for fwrite()
     */
    function _make_data($records)
    {
        ksort($records);
        $lines = array();
        foreach ($records as $_bno => $_grains) {
            foreach ($_grains as $_gname => $_v) {
                $lines[] = $_bno . GRAIN_DATA_FS 
                    . $_gname . GRAIN_DATA_FS . $_v;
            }
        }
        return implode(GRAIN_DATA_RS, $lines) . GRAIN_DATA_RS;

   }

    // }}}
    // {{{ find()

    /**
     * Find and return specified box
     *
     * @access public
     * @param integer box number (if omitted, all datas are returned)
     * @return array assoc-array of bno => grain box.
     *          empty array returned if not found.
     *          if file errors, return null.
     */
    function find($bno = null)
    {
        if (is_null($bno)) {
            $files = $this->find_data_files();
            foreach ($files as $_f) {
                $fp = yb_Util::file_open_lock($_f, 'rb', LOCK_SH);
                if (!$fp) {
                    continue;
                }
                $results = $this->_load_lines($fp);
                foreach ($results as $_bno => $_grains) {
                    foreach ($_grains as $_gname => $_v) {
                        $this->_cache[$_bno][$_gname] = $_v;
                    }
                }
                fclose($fp);
            }
            return $this->_cache;
        }

        if (isset($this->_cache[$bno])) {
            return array($bno => $this->_cache[$bno]);
        }

        $filename = $this->data_filename($bno);
        if (!is_readable($filename)) {
            return array();
        }
        $fp = yb_Util::file_open_lock($filename, 'rb', LOCK_SH);
        if ($fp) {
            $results = $this->_load_lines($fp);
            foreach ($results as $_bno => $_grains) {
                foreach ($_grains as $_gname => $_v) {
                    $this->_cache[$_bno][$_gname] = $_v;
                }
            }
            fclose($fp);
        }

        return (isset($this->_cache[$bno])) 
            ? array($bno => $this->_cache[$bno]) : array();
    }

    // }}}
    // {{{ save()

    /**
     * Save grain box
     *
     * @access public
     * @param integer box number
     * @param array assoc-array of "grain name" => value
     * @return boolean If success, TRUE. Any error occurs, FALSE.
     */
    function save($bno, $grains)
    {
        $filename = $this->data_filename($bno);
        $fp = yb_Util::file_open_lock($filename, 'a+b', LOCK_EX);
        if (!$fp) {
            return false;
        }

        $records = $this->_load_lines($fp);
        if (is_null($records)) {
            return false;
        }

        // add grain box
        foreach ($grains as $gname => $v) {
            $gname = grain_Util::strip($gname);
            $v = grain_Util::strip($v);
            $records[$bno][$gname] = $v;
            $this->_cache[$bno][$gname] = $v;
        }
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
     * Delete grain box
     *
     * @access public
     * @param integer box number
     * @return boolean If success, TRUE.
     *                 If given box number is not found, or, any 
     *                 error ocuurs, return FALSE.
     */
    function delete($bno)
    {
        $filename = $this->data_filename($bno);
        $fp = yb_Util::file_open_lock($filename, 'a+b', LOCK_EX);
        if (!$fp) {
            return false;
        }

        $records = $this->_load_lines($fp);
        if (is_null($records)) {
            return false;
        }

        $ret = true;
        unset($this->_cache[$bno]);
        if (isset($records[$bno])) {
            unset($records[$bno]);
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
    // {{{ destroy()

    /**
     * Delete all physical chunk data files.
     *
     * @access public
     * @return boolean If success, TRUE.
     *                 If any error ocuurs, return FALSE.
     */
    function destroy()
    {
        $ret = true;
        $files = $this->find_data_files();
        foreach ($files as $_f) {
            if (!unlink($_f)) {
                yb_Error::raise('unlink(' . $_f . ') failed!');
                $ret = false;
            }
        }
        return $ret;

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
