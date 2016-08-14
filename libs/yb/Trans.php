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

require_once('Log.php');

/**
 * YakiBiki Translation utility
 *
 * @author msakamoto-sf <msakamoto-sf@users.sourceforge.net>
 * @version $Id: Trans.php 218 2008-03-23 22:46:27Z msakamoto-sf $
 */
class yb_Trans
{
    var $_locale;

    var $_locale_dir;

    var $_mes;

    // {{{ constructor

    function yb_Trans($locale, $locale_dir)
    {
        $this->_locale = $locale;
        $this->_locale_dir = $locale_dir;

        $this->_mes = array();
        $this->_load();
    }

    // }}}
    // {{{ factory()

    function &factory($locale)
    {
        static $insts = array();
        if (isset($insts[$locale])) {
            return $insts[$locale];
        }
        $default = null;
        $availables = yb_Trans::availableLocales();
        if (!in_array($locale, $availables)) {
            return $default;
        }
        $_dir = realpath(_YB('dir.locale') . '/' . $locale);
        $insts[$locale] =& new yb_Trans($locale, $_dir);

        return $insts[$locale];
    }

    // }}}
    // {{{ availableLocales()

    /**
     * @static
     * @access public
     */
    function availableLocales()
    {
        $_dir = _YB('dir.locale');
        $_pattern = $_dir . '/*';
        $_list = glob($_pattern, GLOB_ONLYDIR);
        $return = array();
        foreach ($_list as $l) {
            $return[] = basename($l);
        }

        return $return;
    }

    // }}}
    // {{{ locale()

    /**
     * @access public
     */
    function locale()
    {
        return $this->_locale;
    }

    // }}}
    // {{{ _load()

    /**
     * @access protected
     */
    function _load()
    {
        $_glob_pat = $this->_locale_dir . '/*.m';
        $_list = glob($_glob_pat);
        foreach ($_list as $mes_file) {
            $_domain = basename($mes_file, '.m');
            $_lines = file($mes_file);
            $this->_mes[$_domain] = $this->_parse($_lines);
        }
    }

    // }}}
    // {{{ _parse()

    /**
     * @access protected
     */
    function _parse($lines)
    {
        $status = 0;
        $_datas = array();
        $_buf_source = '';
        $_buf_trans = '';
        $_curr_key = '';
        foreach ($lines as $line) {
            if (preg_match('/^;/', $line)) {
                // comment
                continue;
            }
            if (preg_match('/^====/', $line)) {
                $_old_status = $status;
                $status = 1; // source data.

                if ($_old_status == 2) {
                    // if translated data is exist, set.
                    $_datas[$_curr_key] = trim($_buf_trans);
                }
                $_buf_trans = '';
                continue;
            }
            if (preg_match('/^----/', $line)) {
                $status = 2; // translated data.
                // source data is stored as key.
                $_curr_key = trim($_buf_source);
                $_buf_source = '';
                continue;
            }

            // All trailing new line is converted to CRLF.
            $line = trim($line) . "\r\n";

            switch ($status) {
            case 1:
                // source data.
                $_buf_source .= $line;
                break;
            case 2:
                // translated data.
                $_buf_trans .= $line;
            default:
                // ignored.
            }
        }
        // process EOF
        if ($status == 2) {
            // translated data.
            $_datas[$_curr_key] = trim($_buf_trans);
        }

        return $_datas;
    }

    // }}}
    // {{{ t()

    function t($src, $args = null, $domain = null)
    {
        if (is_null($args)) {
            $args = array();
        }

        // convert src's internal LF to CRLF
        $src = str_replace("\r", '', $src);
        $src = str_replace("\n", "\r\n", $src);
        $src = trim($src);

        // retrive translated data.
        $translated = null;
        if (is_null($domain)) {
            foreach ($this->_mes as $_domain => $catalog) {
                if (isset($catalog[$src])) {
                    $translated = $catalog[$src];
                    break;
                }
            }
        } else {
            $translated = @$this->_mes[$domain][$src];
        }

        if (empty($translated)) {
            $translated = $src;
        }

        // convert replace arguments.
        foreach ($args as $_k => $_v) {
            $_search = '%' . $_k;
            $translated = str_replace($_search, $_v, $translated);
        }

        return $translated;
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
