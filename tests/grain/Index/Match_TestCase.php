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

require_once('grain/Index/Match.php');

class grain_Index_Match_TestCase extends UnitTestCase
{
    // {{{ test_search()

    function test_search()
    {
        $idx =& new grain_Index_Match(
            dirname(__FILE__) . '/Match_TestIndex1.idx');

        $this->assertFalse($idx->case_sensitive());
        $this->assertFalse($idx->case_sensitive(true));
        $this->assertTrue($idx->case_sensitive());

        $idx->reset();
        $this->assertFalse($idx->case_sensitive());

        $result = $idx->search("Linux");
        $this->assertEqual(count($result), 4);
        $this->assertEqual($result[0], 300);
        $this->assertEqual($result[1], 900);
        $this->assertEqual($result[2], 1000);
        $this->assertEqual($result[3], 1100);

        $result = $idx->search("windows");
        $this->assertEqual(count($result), 4);
        $this->assertEqual($result[0], 100);
        $this->assertEqual($result[1], 600);
        $this->assertEqual($result[2], 700);
        $this->assertEqual($result[3], 800);

        $idx->case_sensitive(true);
        $result = $idx->search("Linux");
        $this->assertEqual(count($result), 2);
        $this->assertEqual($result[0], 300);
        $this->assertEqual($result[1], 1000);

        $result = $idx->search("Windows");
        $this->assertEqual(count($result), 3);
        $this->assertEqual($result[0], 100);
        $this->assertEqual($result[1], 600);
        $this->assertEqual($result[2], 700);

        $result = $idx->search("FooBar");
        $this->assertEqual(count($result), 0);

    }

    // }}}
    // {{{ test_fullmatch()

    function test_fullmatch()
    {
        $idx =& new grain_Index_Match(
            dirname(__FILE__) . '/Match_TestIndex1.idx');

        $result = $idx->fullmatch("Free BSD 1.0");
        $this->assertEqual(count($result), 3);
        $this->assertEqual($result[0], 1200);
        $this->assertEqual($result[1], 1300);
        $this->assertEqual($result[2], 1400);

        $idx->case_sensitive(true);
        $result = $idx->fullmatch("Free BSD 1.0");
        $this->assertEqual(count($result), 2);
        $this->assertEqual($result[0], 1200);
        $this->assertEqual($result[1], 1300);
    }

    // }}}
    // {{{ test_listmatch()

    function test_listmatch()
    {
        $idx =& new grain_Index_Match(
            dirname(__FILE__) . '/Match_TestIndex2.idx');

        $result = $idx->listmatch('Level1');
        $this->assertEqual(count($result), 8);
        $this->assertEqual($result[100], 'Level1');
        $this->assertEqual($result[300], 'Level1/Level1_1');
        $this->assertEqual($result[400], 'Level1/Level1_1/Level1_1_1');
        $this->assertEqual($result[200], 'Level1/Level1_1/Level1_1_2');
        $this->assertEqual($result[700], 'Level1/Level1_2');
        $this->assertEqual($result[500], 'Level1/Level1_2/Level1_2_1');
        $this->assertEqual($result[600], 'Level1/Level1_3');
        $this->assertEqual($result[900], 'Level1A/Level1_3 (1)');

        $r = $idx->listmatch('Level1/');
        $this->assertEqual(count($r), 6);
        $this->assertEqual($r[300], 'Level1/Level1_1');
        $this->assertEqual($r[400], 'Level1/Level1_1/Level1_1_1');
        $this->assertEqual($r[200], 'Level1/Level1_1/Level1_1_2');
        $this->assertEqual($r[700], 'Level1/Level1_2');
        $this->assertEqual($r[500], 'Level1/Level1_2/Level1_2_1');
        $this->assertEqual($r[600], 'Level1/Level1_3');

        $result = $idx->listmatch('Level1/Level1_1');
        $this->assertEqual(count($result), 3);
        $this->assertEqual($result[300], 'Level1/Level1_1');
        $this->assertEqual($result[400], 'Level1/Level1_1/Level1_1_1');
        $this->assertEqual($result[200], 'Level1/Level1_1/Level1_1_2');

        $result = $idx->listmatch('Level1/Level1_1/Level1_1_1');
        $this->assertEqual(count($result), 1);
        $this->assertEqual($result[400], 'Level1/Level1_1/Level1_1_1');

        $result = $idx->listmatch('Level1/Level1_2');
        $this->assertEqual(count($result), 2);
        $this->assertEqual($result[700], 'Level1/Level1_2');
        $this->assertEqual($result[500], 'Level1/Level1_2/Level1_2_1');

        $result = $idx->listmatch(' Level1/Level1_2 ');
        $this->assertEqual(count($result), 0);

        $result = $idx->listmatch('Level2');
        $this->assertEqual(count($result), 0);

        $result = $idx->listmatch('');
        $this->assertEqual(count($result), 0);

        $result = $idx->listmatch(' ');
        $this->assertEqual(count($result), 0);
    }

    // }}}
    // {{{ test_cud()

    function test_cud()
    {
        $index_file = dirname(__FILE__) . '/tmp/Match_TestIndex.idx';
        $idx =& new grain_Index_Match($index_file);

        $this->assertFalse(is_writable($index_file));

        // {{{ register()

        $this->assertTrue($idx->register(10, "Foo Bar"));
        $this->assertTrue($idx->register(20, "nfoo bohe"));
        $this->assertTrue($idx->register(30, "nFoo Bohe"));
        $this->assertTrue($idx->register(40, "Hoge hoge"));

        $result = $idx->search("foo");
        $this->assertEqual(count($result), 3);
        $this->assertEqual($result[0], 10);
        $this->assertEqual($result[1], 20);
        $this->assertEqual($result[2], 30);

        $idx->case_sensitive(true);
        $this->assertTrue($idx->register(40, "Moga Bar"));

        $result = $idx->search("Bar");
        $this->assertEqual(count($result), 2);
        $this->assertEqual($result[0], 10);
        $this->assertEqual($result[1], 40);

        // }}}
        /* now, index file contents should be:
         * 10, Foo Bar
         * 20, nfoo bohe
         * 30, nFoo Bohe
         * 40, Moga Bar
         */
        // {{{ unregister()

        $this->assertTrue($idx->unregister(10));
        $this->assertTrue($idx->unregister(100)); // NOT registered yet.

        $result = $idx->search("Bar");
        $this->assertEqual(count($result), 1);
        $this->assertEqual($result[0], 40);

        // }}}
        /* now, index file contents should be:
         * 20, nfoo bohe
         * 30, nFoo Bohe
         * 40, Moga Bar
         */

        $idx2 =& new grain_Index_Match($index_file);
        // {{{ new instance $idx2 create, file load, edit, check indice.
        $this->assertTrue($idx2->unregister(20));

        $result = $idx2->search("foo");
        $this->assertEqual(count($result), 1);
        $this->assertEqual($result[0], 30);

        $idx->case_sensitive(true);
        $result = $idx2->search("Bar");
        $this->assertEqual(count($result), 1);
        $this->assertEqual($result[0], 40);

        // }}}

        // now, add text which include special controll characters 
        // to $idx2.
        $this->assertTrue($idx2->register(10, 
            "A\x0AB\x0DC\r\nD" . GRAIN_DATA_FS . "E" 
            . GRAIN_DATA_RS . "F" . GRAIN_DATA_GS . "G"));
        $result = $idx2->search("ABC");
        $this->assertEqual(count($result), 1);
        $this->assertEqual($result[0], 10);

        // old $idx doesn't have new index added above to $idx2.
        $result = $idx->search("ABC");
        $this->assertEqual(count($result), 0);

        // clean up temporary index directory.
        @unlink($index_file);
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
