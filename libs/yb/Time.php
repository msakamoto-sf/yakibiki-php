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
 *
 */

define('YB_TIME_FMT_INTERNAL_RAW', '%Y%m%d%H%M%S');
define('YB_TIME_REGEXP_INTERNAL_RAW', 
    '/^(\d{4})(\d{2})(\d{2})(\d{2})(\d{2})(\d{2})$/mi');

/**
 * YakiBiki Internal Date-Time operator
 *
 * set(), get() use TimeZoned data.
 * unixtime(), getGMT() returns GMT data.
 *
 * NOTICE & WARN : WE >> GIVE-UP << TO TREAT CORRECTLY NEAR 
 * 1970-01-01 00:00:00 TIME WITH VARIOUS TIMEZONE.
 * -> If timezone offset is larger than 0, and, user input is 1970-01-01
 * 00:00:00, then, REAL gmt will be minus value (0 - offset(plus)).
 * IN THIS CASE, WE ROUND {OFF|UP} TO "0 for GMT"!!!
 *
 * So, ... YakiBiki CAN'T treat near time '1970-01-01 00:00:00' in 
 * created/updated timestamp. Don't set it.
 *
 * @author msakamoto-sf <msakamoto-sf@users.sourceforge.net>
 * @version $Id: Time.php 188 2008-01-12 05:13:30Z msakamoto-sf $
 */
class yb_Time
{
    // {{{ properties

    /**
     * UNIX TIME as GMT
     *
     * @access protected
     * @type integer
     */
    var $_unixtime = 0;

    /**
     * Time Zone information
     * (Defined in timezone.php, $GLOBALS['YB_TIMEZONES']
     *
     * @access protected
     * @type array
     */
    var $_timezone = null;

    // }}}
    // {{{ constructor

    /**
     * Constructor
     *
     * @param string Time Zone Name (option)
     * @access protected
     */
    function yb_Time($tzname = null)
    {
        $_yb_default_tz = _YB('default.timezone');
        $_tzname = 'UTC';
        if (!is_null($tzname) && yb_Time::isValidTimeZone($tzname)) {
            $_tzname = $tzname;
        } else {
            if (yb_Time::isValidTimeZone($_yb_default_tz)) {
                $_tzname = $_yb_default_tz;
            }
        }
        $_tzinfo = $GLOBALS['YB_TIMEZONES'][$_tzname];
        $_tzinfo['name'] = $_tzname;

        $this->_timezone = $_tzinfo;
        $this->_unixtime = time(); // time() returns GMT unix time.
    }

    // }}}
    // {{{ singleton()

    /**
     * Singleton interface for simply yb_Time's value formatting.
     *
     * @access public
     * @param string Time Zone Name (option)
     * @return object reference to yb_Time singleton instance.
     */
    function &singleton($tzname = null)
    {
        static $instance = null;
        if (is_null($instance)) {
            $instance = new yb_Time($tzname);
        } else if (!empty($tzname)) {
            $instance->timezone($tzname);
        }
        return $instance;
    }

    // }}}
    // {{{ timezone()

    /**
     * Get/Set timezone
     *
     * @access public
     * @param string new time zone name defined in timezones.php (optional)
     * @return string current (old) time zone name
     */
    function timezone($tzname = null)
    {
        $ret = $this->_timezone;
        if (!is_null($tzname) && yb_Time::isValidTimeZone($tzname)) {
            $_tzinfo = $GLOBALS['YB_TIMEZONES'][$tzname];
            $_tzinfo['name'] = $tzname;
            $this->_timezone = $_tzinfo;
        }
        return $ret;
    }

    // }}}
    // {{{ unixtime()

    /**
     * Get/Set UNIX TIME as GMT
     *
     * @access public
     * @param integer new unix time as GMT (optional)
     * @return integer current (old) unix time as GMT
     */
    function unixtime($gmt_ut = null)
    {
        $ret = $this->_unixtime;
        if (!is_null($gmt_ut)) {
            $this->_unixtime = $gmt_ut;
        }
        return $ret;
    }

    // }}}
    // {{{ isValidTimeZone()

    /**
     * Return given time zone name is defined in $GLOBALS['YB_TIMEZONES']
     *
     * @static
     * @access public
     * @return boolean
     */
    function isValidTimeZone($tz)
    {
        return isset($GLOBALS['YB_TIMEZONES'][$tz]);
    }

    // }}}
    // {{{ set()

    /**
     * Set new time which represented by current time zone (may be not in GMT)
     *
     * @access public
     * @param integer year
     * @param integer month
     * @param integer day
     * @param integer hour
     * @param integer minute
     * @param integer second
     */
    function set($year, $month, $day, $hour = 0, $min = 0, $sec = 0)
    {
        // get as GMT representation.
        $_ut_as_gmt = @gmmktime($hour, $min, $sec, $month, $day, $year);
        if ($_ut_as_gmt < 0) {
            $this->_unixtime = 0;
            return;
        }

        $this->_unixtime = $_ut_as_gmt;

        // 2nd, we fix time zone offset manually.
        $offset = $this->_timezone['offset'] / 1000;

        $_ut_real_gmt = $_ut_as_gmt - $offset;

        if ($_ut_real_gmt < 0) {
            $_ut_real_gmt = 0;
        }

        $this->_unixtime = $_ut_real_gmt;
    }

    // }}}
    // {{{ setInternalRaw()

    /**
     * Set YB_TIME_FMT_INTERNAL_RAW format time.
     *
     * @access public
     * @param string time
     */
    function setInternalRaw($s)
    {
        $year = @substr($s, 0, 4);
        $month = @substr($s, 4, 2);
        $day = @substr($s, 6, 2);
        $hour = @substr($s, 8, 2);
        $min = @substr($s, 10, 2);
        $sec = @substr($s, 12, 2);
        $this->set($year, $month, $day, $hour, $min, $sec);

        // get as GMT representation.
        $_ut_as_gmt = @gmmktime($hour, $min, $sec, $month, $day, $year);

        if ($_ut_as_gmt < 0) {
            $this->_unixtime = 0;
            return;
        }
        $this->_unixtime = $_ut_as_gmt;
    }

    // }}}
    // {{{ get()

    /**
     * Returns timezoned strftime() data.
     *
     * @access public
     * @param string sftftime()'s format string
     * @return string
     */
    function get($format)
    {
        $offset = $this->_timezone['offset'] / 1000;
        // fix zone time offset manually.
        $_ut = $this->_unixtime + $offset;

        return gmstrftime($format, $_ut);
    }

    // }}}
    // {{{ getGMT()

    /**
     * Returns GMT strftime() data.
     *
     * @access public
     * @param string sftftime()'s format string
     * @return string
     */
    function getGMT($format)
    {
        return gmstrftime($format, $this->_unixtime);
    }

    // }}}
    // {{{ splitInternalRaw()

    /**
     * Split to year, month, day, hour, minute, second elements from
     * Internal Raw format.
     *
     * @static
     * @access public
     * @param string internal raw format.
     * @return array
     */
    function splitInternalRaw($data)
    {
        $ret = array(
            'year' => '',
            'month' => '',
            'day' => '',
            'hour' => '',
            'min' => '',
            'sec' => '',
        );
        $ret = array('', '', '', '', '', '');
        if (preg_match(YB_TIME_REGEXP_INTERNAL_RAW, $data, $m)) {
            $ret['year'] = $m[1];
            $ret['month'] = $m[2];
            $ret['day'] = $m[3];
            $ret['hour'] = $m[4];
            $ret['min'] = $m[5];
            $ret['sec'] = $m[6];
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
