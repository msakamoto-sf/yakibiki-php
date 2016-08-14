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

require_once('yb/Time.php');

class yb_Time_TestCase extends UnitTestCase
{
    // {{{ test_isValidTimeZone()

    function test_isValidTimeZone()
    {
        $this->assertTrue(yb_Time::isValidTimeZone('UTC'));
        $this->assertTrue(yb_Time::isValidTimeZone('Asia/Tokyo'));
        $this->assertFalse(yb_Time::isValidTimeZone('Asia/Toky'));
    }

    // }}}
    // {{{ test_constructor()

    function test_constructor()
    {
        // check yb_Time's default time zone.
        $_old_tz = _YB('default.timezone', null);
        $t =& new yb_Time();
        $gmt_now = time(); // GMT UNIX Epoch
        $ut = $t->unixtime();

        // {{{ check unixtime() behaviour

        // update, return old timezone.
        $this->assertEqual($t->unixtime(100), $ut);
        // check updated.
        $this->assertEqual($t->unixtime(), 100);

        // }}}
        // {{{ check timezone() behaviour

        $tz = $t->timezone();
        $this->assertEqual($tz['name'], 'UTC');
        $this->assertEqual($tz['offset'], 0);
        // update, return old timezone.
        $tz = $t->timezone('Asia/Tokyo');
        $this->assertEqual($tz['name'], 'UTC');
        // check updated.
        $tz = $t->timezone();
        $this->assertEqual($tz['name'], 'Asia/Tokyo');
        // update undefined time zone. -> not updated.
        $tz = $t->timezone('Foo/Bar');
        $this->assertEqual($tz['name'], 'Asia/Tokyo');
        $tz = $t->timezone();
        $this->assertEqual($tz['name'], 'Asia/Tokyo');

        // }}}

        // check _YB('default.timezone') is not null
        _YB('default.timezone', $_old_tz);
        $t =& new yb_Time();
        $tz = $t->timezone();
        $this->assertEqual($tz['name'], $_old_tz);
    }

    // }}}
    // {{{ test_set()

    function test_set()
    {
        $t =& new yb_Time('UTC');

        $t->set(1969, 12, 31, 23, 59, 59);
        $this->assertEqual($t->unixtime(), 0);

        $t->set(1970, 1, 1);
        $this->assertEqual($t->unixtime(), 0);

        $t->set(1970, 1, 1, 0, 0, 0);
        $this->assertEqual($t->unixtime(), 0);

        $t->set(2001, 12, 31, 23, 59, 59); // IN GMT
        $this->assertEqual($t->unixtime(), 1009843199); // equals gmmktime()

        $t =& new yb_Time('Asia/Tokyo');

        $t->set(1970, 1, 1, 0, 0, 0); // In Asia/Tokyo Time
        $this->assertEqual($t->unixtime(), 0); // NOTICE: ROUNDED GMT Time

        // NOTICE : ... WE GIVE UP !!
        $t->set(1970, 1, 1, 9, 0, 0);
        $this->assertEqual($t->unixtime(), 0);

        $t->set(2001, 12, 31, 23, 59, 59); // IN Asia/Tokyo
        $this->assertEqual($t->unixtime(), 1009810799); // GMT - 0900
        // At 2001-12-31, 23:59:59 In Asia/Tokyo, GMT is -09:00.

        $t =& new yb_Time('US/Hawaii');

        $t->set(1969, 12, 31, 13, 59, 59);
        $this->assertEqual($t->unixtime(), 0);

        $t->set(1969, 12, 31, 14, 0, 0);
        $this->assertEqual($t->unixtime(), 0);

        // NOTICE : ... WE GIVE UP !!
        $t->set(1969, 12, 31, 14, 0, 1);
        $this->assertEqual($t->unixtime(), 0);

        $t->set(2001, 12, 31, 23, 59, 59); // IN US/Hawaii
        $this->assertEqual($t->unixtime(), 1009879199); // GMT - 0900
        // At 2001-12-31, 23:59:59 In Asia/Tokyo, GMT is -09:00.
    }

    // }}}
    // {{{ test_setInternalRaw()

    function test_setInternalRaw()
    {
        $t =& new yb_Time('UTC');
        $t->setInternalRaw('20011231235959');
        $this->assertEqual($t->unixtime(), 1009843199);

        $t =& new yb_Time('Asia/Tokyo');
        $t->setInternalRaw('20011231235959');
        $this->assertEqual($t->unixtime(), 1009843199);

        $t =& new yb_Time('US/Hawaii');
        $t->setInternalRaw('20011231235959');
        $this->assertEqual($t->unixtime(), 1009843199);
    }

    // }}}
    // {{{ test_get()

    function test_get()
    {
        $t =& new yb_Time('UTC');
        $t->unixtime(981140759);

        $s = $t->get(YB_TIME_FMT_INTERNAL_RAW);
        $this->assertEqual($s, '20010202190559');

        $t =& new yb_Time('Asia/Tokyo');
        $t->unixtime(981140759);

        $s = $t->get(YB_TIME_FMT_INTERNAL_RAW);
        $this->assertEqual($s, '20010203040559');

        $t =& new yb_Time('US/Hawaii');
        $t->unixtime(981140759);

        $s = $t->get(YB_TIME_FMT_INTERNAL_RAW);
        $this->assertEqual($s, '20010202090559');

    }

    // }}}
    // {{{ test_getGMT()

    function test_getGMT()
    {
        $t =& new yb_Time('UTC');
        $t->unixtime(981140759);

        $s = $t->getGMT(YB_TIME_FMT_INTERNAL_RAW);
        $this->assertEqual($s, '20010202190559');

        $t =& new yb_Time('Asia/Tokyo');
        $t->unixtime(981140759);

        $s = $t->getGMT(YB_TIME_FMT_INTERNAL_RAW);
        $this->assertEqual($s, '20010202190559');

        $t =& new yb_Time('US/Hawaii');
        $t->unixtime(981140759);

        $s = $t->getGMT(YB_TIME_FMT_INTERNAL_RAW);
        $this->assertEqual($s, '20010202190559');
    }

    // }}}
    // {{{ test_splitInternalRaw()

    function test_splitInternalRaw()
    {
        $ret = yb_Time::splitInternalRaw('20010102030405');
        $this->assertEqual($ret['year'], '2001');
        $this->assertEqual($ret['month'], '01');
        $this->assertEqual($ret['day'], '02');
        $this->assertEqual($ret['hour'], '03');
        $this->assertEqual($ret['min'], '04');
        $this->assertEqual($ret['sec'], '05');
    }

    // }}}
    // {{{ test_complex()

    function test_complex()
    {
        $t =& new yb_Time('UTC');
        $_ir = '20010102030405';

        $t->setInternalRaw($_ir);
        $_ut1 = $t->unixtime();
        $t->set(2001, 1, 2, 3, 4, 5);
        $_ut2 = $t->unixtime();
        $this->assertEqual($_ut1, $_ut2);

        $_s1 = $t->getGMT(YB_TIME_FMT_INTERNAL_RAW);
        $this->assertEqual($_s1, $_ir);
        $_s2 = $t->get(YB_TIME_FMT_INTERNAL_RAW);
        $this->assertEqual($_s2, '20010102030405');

        $t =& new yb_Time('Asia/Tokyo');
        $t->setInternalRaw('20010102030405');
        $_ut1 = $t->unixtime();
        $t->set(2001, 1, 2, 12, 4, 5);
        $_ut2 = $t->unixtime();
        $this->assertEqual($_ut1, $_ut2);

        $_s1 = $t->getGMT(YB_TIME_FMT_INTERNAL_RAW);
        $this->assertEqual($_s1, $_ir);
        $_s2 = $t->get(YB_TIME_FMT_INTERNAL_RAW);
        $this->assertEqual($_s2, '20010102120405');

        $t =& new yb_Time('US/Hawaii');
        $t->setInternalRaw('20010102030405');
        $_ut1 = $t->unixtime();
        $t->set(2001, 1, 1, 17, 4, 5);
        $_ut2 = $t->unixtime();
        $this->assertEqual($_ut1, $_ut2);

        $_s1 = $t->getGMT(YB_TIME_FMT_INTERNAL_RAW);
        $this->assertEqual($_s1, $_ir);
        $_s2 = $t->get(YB_TIME_FMT_INTERNAL_RAW);
        $this->assertEqual($_s2, '20010101170405');

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
