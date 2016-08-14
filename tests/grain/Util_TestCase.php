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
 *   limitations under the License.*
 */

require_once('grain/Util.php');

class grain_Util_TestCase extends UnitTestCase
{
    // {{{ test_boxchunk_split()

    function test_boxchunk_split()
    {
        // {{{ split size = 'ABC'
        $this->assertEqual(grain_Util::boxchunk_split(1, 'ABC'), 1);
        $this->assertEqual(grain_Util::boxchunk_split(2, 'ABC'), 2);
        $this->assertEqual(grain_Util::boxchunk_split(3, 'ABC'), 3);
        // }}}
        // {{{ split size = null
        $this->assertEqual(grain_Util::boxchunk_split(1, null), 1);
        $this->assertEqual(grain_Util::boxchunk_split(2, null), 2);
        $this->assertEqual(grain_Util::boxchunk_split(3, null), 3);
        // }}}
        // {{{ split size = -1
        $this->assertEqual(grain_Util::boxchunk_split(1, -1), 1);
        $this->assertEqual(grain_Util::boxchunk_split(2, -1), 2);
        $this->assertEqual(grain_Util::boxchunk_split(3, -1), 3);
        // }}}
        // {{{ split size = 0
        $this->assertEqual(grain_Util::boxchunk_split(1, 0), 1);
        $this->assertEqual(grain_Util::boxchunk_split(2, 0), 2);
        $this->assertEqual(grain_Util::boxchunk_split(3, 0), 3);
        // }}}
        // {{{ split size = 1
        $this->assertEqual(grain_Util::boxchunk_split(1, 1), 1);
        $this->assertEqual(grain_Util::boxchunk_split(2, 1), 2);
        $this->assertEqual(grain_Util::boxchunk_split(3, 1), 3);
        // }}}
        // {{{ split size = 2
        $this->assertEqual(grain_Util::boxchunk_split(1, 2), 2);
        $this->assertEqual(grain_Util::boxchunk_split(2, 2), 2);
        $this->assertEqual(grain_Util::boxchunk_split(3, 2), 4);
        $this->assertEqual(grain_Util::boxchunk_split(4, 2), 4);
        $this->assertEqual(grain_Util::boxchunk_split(5, 2), 6);
        $this->assertEqual(grain_Util::boxchunk_split(6, 2), 6);
        // }}}
        // {{{ split size = 3
        $this->assertEqual(grain_Util::boxchunk_split(1, 3), 3);
        $this->assertEqual(grain_Util::boxchunk_split(2, 3), 3);
        $this->assertEqual(grain_Util::boxchunk_split(3, 3), 3);
        $this->assertEqual(grain_Util::boxchunk_split(4, 3), 6);
        $this->assertEqual(grain_Util::boxchunk_split(5, 3), 6);
        $this->assertEqual(grain_Util::boxchunk_split(6, 3), 6);
        $this->assertEqual(grain_Util::boxchunk_split(7, 3), 9);
        $this->assertEqual(grain_Util::boxchunk_split(8, 3), 9);
        $this->assertEqual(grain_Util::boxchunk_split(9, 3), 9);
        // }}}
        // {{{ split size = 4
        $this->assertEqual(grain_Util::boxchunk_split(1, 4), 4);
        $this->assertEqual(grain_Util::boxchunk_split(2, 4), 4);
        $this->assertEqual(grain_Util::boxchunk_split(3, 4), 4);
        $this->assertEqual(grain_Util::boxchunk_split(4, 4), 4);
        $this->assertEqual(grain_Util::boxchunk_split(5, 4), 8);
        $this->assertEqual(grain_Util::boxchunk_split(6, 4), 8);
        $this->assertEqual(grain_Util::boxchunk_split(7, 4), 8);
        $this->assertEqual(grain_Util::boxchunk_split(8, 4), 8);
        $this->assertEqual(grain_Util::boxchunk_split(9, 4), 12);
        $this->assertEqual(grain_Util::boxchunk_split(10, 4), 12);
        $this->assertEqual(grain_Util::boxchunk_split(11, 4), 12);
        $this->assertEqual(grain_Util::boxchunk_split(12, 4), 12);
        // }}}
        // {{{ split size = 10
        $this->assertEqual(grain_Util::boxchunk_split(1, 10), 10);
        $this->assertEqual(grain_Util::boxchunk_split(9, 10), 10);
        $this->assertEqual(grain_Util::boxchunk_split(10, 10), 10);
        $this->assertEqual(grain_Util::boxchunk_split(11, 10), 20);
        $this->assertEqual(grain_Util::boxchunk_split(19, 10), 20);
        $this->assertEqual(grain_Util::boxchunk_split(20, 10), 20);
        $this->assertEqual(grain_Util::boxchunk_split(21, 10), 30);
        $this->assertEqual(grain_Util::boxchunk_split(29, 10), 30);
        $this->assertEqual(grain_Util::boxchunk_split(30, 10), 30);
        $this->assertEqual(grain_Util::boxchunk_split(99, 10), 100);
        $this->assertEqual(grain_Util::boxchunk_split(100, 10), 100);
        $this->assertEqual(grain_Util::boxchunk_split(101, 10), 110);
        // }}}
        // {{{ split size = 100
        $s = 100;
        $this->assertEqual(grain_Util::boxchunk_split(1, $s), 100);
        $this->assertEqual(grain_Util::boxchunk_split(99, $s), 100);
        $this->assertEqual(grain_Util::boxchunk_split(100, $s), 100);
        $this->assertEqual(grain_Util::boxchunk_split(101, $s), 200);
        $this->assertEqual(grain_Util::boxchunk_split(199, $s), 200);
        $this->assertEqual(grain_Util::boxchunk_split(200, $s), 200);
        $this->assertEqual(grain_Util::boxchunk_split(201, $s), 300);
        $this->assertEqual(grain_Util::boxchunk_split(299, $s), 300);
        $this->assertEqual(grain_Util::boxchunk_split(300, $s), 300);
        $this->assertEqual(grain_Util::boxchunk_split(301, $s), 400);
        $this->assertEqual(grain_Util::boxchunk_split(999, $s), 1000);
        $this->assertEqual(grain_Util::boxchunk_split(1000, $s), 1000);
        $this->assertEqual(grain_Util::boxchunk_split(1001, $s), 1100);
        // }}}
        // {{{ split size = 1000
        $s = 1000;
        $this->assertEqual(grain_Util::boxchunk_split(1, $s), 1000);
        $this->assertEqual(grain_Util::boxchunk_split(99, $s), 1000);
        $this->assertEqual(grain_Util::boxchunk_split(999, $s), 1000);
        $this->assertEqual(grain_Util::boxchunk_split(1000, $s), 1000);
        $this->assertEqual(grain_Util::boxchunk_split(1001, $s), 2000);
        $this->assertEqual(grain_Util::boxchunk_split(1999, $s), 2000);
        $this->assertEqual(grain_Util::boxchunk_split(2000, $s), 2000);
        $this->assertEqual(grain_Util::boxchunk_split(2001, $s), 3000);
        $this->assertEqual(grain_Util::boxchunk_split(2999, $s), 3000);
        $this->assertEqual(grain_Util::boxchunk_split(3000, $s), 3000);
        $this->assertEqual(grain_Util::boxchunk_split(3001, $s), 4000);
        $this->assertEqual(grain_Util::boxchunk_split(9999, $s), 10000);
        $this->assertEqual(grain_Util::boxchunk_split(10000, $s), 10000);
        $this->assertEqual(grain_Util::boxchunk_split(10001, $s), 11000);
        $this->assertEqual(grain_Util::boxchunk_split(10999, $s), 11000);
        $this->assertEqual(grain_Util::boxchunk_split(11000, $s), 11000);
        $this->assertEqual(grain_Util::boxchunk_split(11001, $s), 12000);
        // }}}
    }

    // }}}
    // {{{ test_strip()

    function test_strip()
    {
        $this->assertEqual("abc", grain_Util::strip("a\x0Ab\x0Dc"));
        $this->assertEqual("abc", grain_Util::strip(
            "a" . GRAIN_DATA_RS . "b" . GRAIN_DATA_FS . "c"));

        $s = 'a' . GRAIN_DATA_GS . 'b' . GRAIN_DATA_FS 
            . "c\x0A\x0D" . GRAIN_DATA_RS;
        $this->assertEqual('a' . GRAIN_DATA_GS . 'bc', grain_Util::strip($s));
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
