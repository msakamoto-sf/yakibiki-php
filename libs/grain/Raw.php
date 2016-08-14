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
 * Grain Data Storage Library : Raw Data Storage
 *
 * @author msakamoto-sf <msakamoto-sf@users.sourceforge.net>
 * @version $Id: Raw.php 309 2008-08-09 17:09:16Z msakamoto-sf $
 */
class grain_Raw
{
    // {{{ properties

    /**
     * raw data directory
     *
     * @var string
     * @access protected
     */
    var $_dirname = "";

    /**
     * chunk size
     *
     * @var integer
     * @access protected
     */
    var $_chunksize = 0;

    // }}}
    // {{{ constructor

    /**
     * Constructor
     *
     * @access public
     * @param string raw data directory
     * @param integer file chunk size
     */
    function grain_Raw($dirname, $chunksize)
    {
        $this->_dirname = $dirname;
        $this->_chunksize = $chunksize;

        if (!is_dir($this->_dirname) && !mkdir($this->_dirname)) {
            yb_Error::raise("mkdir(" . $this->_dirname . ") failed!");
        }
    }

    // }}}
    // {{{ filename()

    /**
     * Find and return specified raw data file name.
     *
     * @access public
     * @param integer raw data id
     * @return string realpath file name (if not found, null).
     */
    function filename($id)
    {
        $chunk = grain_Util::boxchunk_split($id, $this->_chunksize);
        $_filename = realpath($this->_dirname . '/' . $chunk . '/' . $id);

        return is_readable($_filename) ? $_filename : null;
    }

    // }}}
    // {{{ save()

    /**
     * Save raw data
     *
     * @access public
     * @param integer raw data id
     * @param string raw binary data
     * @return boolean If success, TRUE. Any error occurs, FALSE.
     */
    function save($id, $data)
    {
        $chunk = grain_Util::boxchunk_split($id, $this->_chunksize);
        $_dir = $this->_dirname . '/' . $chunk;
        if (!is_dir($_dir) && !mkdir($_dir)) {
            yb_Error::raise('mkdir(' . $_dir . ') failed!');
            return false;
        }
        $_fname = $_dir . '/' . $id;

        $fp = yb_Util::file_open_lock($_fname, 'w+b', LOCK_EX);
        if (!$fp) {
            return false;
        }

        $ret = yb_Util::file_write($fp, $data, strlen($data), true, $_fname);
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
     * @param integer raw data id
     * @return boolean If success, TRUE.
     *                 If given id is not found, or, any 
     *                 error ocuurs, return FALSE.
     */
    function delete($id)
    {
        $_fname = $this->filename($id);
        if (!$_fname) {
            return false;
        }
        return @unlink($_fname);
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
