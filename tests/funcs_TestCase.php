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

class funcs_TestCase extends UnitTestCase
{
    // {{{ test__YB()

    function test__YB()
    {
        $curr = _YB("a.b.c");
        $this->assertNull($curr);
        $old = _YB("a.b.c", "new_value");
        $this->assertNull($old);
        $curr = _YB("a.b.c");
        $this->assertEqual($curr, "new_value");

        _YB("d.e.f", array(1, 2, 3, 4, 5));
        $curr = _YB("d.e.f");
        $this->assertEqual(count($curr), 5);
    }

    // }}}
    // {{{ test__substr()

    function test__substr()
    {
        $s = "ABCDEFG"; // 7 chars
        $this->assertEqual(_substr($s, 5), substr($s, 5));
        $this->assertEqual(_substr($s, 6), substr($s, 6));
        $this->assertEqual(_substr($s, 7), '');
        $this->assertEqual(_substr($s, 8), '');
        $this->assertEqual(_substr($s, 5, 1), substr($s, 5, 1));
    }

    // }}}
    // {{{ test_yb_bin2hex()

    function test_yb_bin2hex()
    {
        $s = "ABCDEFG"; // 7 chars
        $this->assertEqual(yb_bin2hex($s), bin2hex($s));
    }

    // }}}
    // {{{ test_yb_hex2bin()

    function test_yb_hex2bin()
    {
        $s = "ABCDEFG"; // 7 chars
        $this->assertEqual(yb_hex2bin(yb_bin2hex($s)), $s);
    }

    // }}}
    // {{{ test__y_()

    function test__y_()
    {
        $s = "<'\"&>";
        $this->assertEqual($s, _y_(h($s)));
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
