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

require_once('grain/Index/Pair.php');

class grain_Index_Pair_TestCase extends UnitTestCase
{
    // {{{ test_get_from()

    function test_get_from()
    {
        // Not Existed Data File Load: result should be empty.
        $idx =& new grain_Index_Pair(
            dirname(__FILE__) . '/NotExistedData.idx');
        $result = $idx->get_from(1);
        $this->assertEqual(count($result), 0);

        // Load Existed Test Index Data.
        $idx =& new grain_Index_Pair(
            dirname(__FILE__) . '/Pair_TestIndex1.idx');

        /*
         * from key #1
         */
        // get ids which related with key id #1.
        $result = $idx->get_from(1);
        // result must be array of array and has 1 item which key is '1'.
        $this->assertEqual(count($result), 1);
        $this->assertTrue(isset($result[1]));
        // #1, we now must get three data ids.
        $ids = $result[1];
        $this->assertEqual(count($ids), 3);
        $this->assertTrue(in_array(1, $ids));
        $this->assertTrue(in_array(3, $ids));
        $this->assertTrue(in_array(6, $ids));

        /*
         * from key#2 (0 records)
         */
        // get ids which related with key id #2 (NOT EXISTS).
        $result = $idx->get_from(2);
        // result must be empty array.
        $this->assertEqual(count($result), 0);

        /*
         * from multiple key#1, #2, #3, #5
         */
        // get ids which related with key id #1, #2, #3, #5
        $result = $idx->get_from(array(1, 2, 3, 5));
        // NOTICE: #2 is NOT EXISTS key id.
        // result must be array of array and has 3 item which key is 1, 3, 5.
        $this->assertEqual(count($result), 3);
        $this->assertTrue(isset($result[1]));
        $this->assertTrue(isset($result[3]));
        $this->assertTrue(isset($result[5]));
        // #1, we now must get three data ids.
        $ids = $result[1];
        $this->assertEqual(count($ids), 3);
        $this->assertTrue(in_array(1, $ids));
        $this->assertTrue(in_array(3, $ids));
        $this->assertTrue(in_array(6, $ids));
        // #3, we now must get 1 data ids.
        $ids = $result[3];
        $this->assertEqual(count($ids), 1);
        $this->assertTrue(in_array(10, $ids));
        // #5, we now must get 0 data ids.
        $ids = $result[5];
        $this->assertTrue(is_array($ids));
        $this->assertEqual(count($ids), 0);
    }

    // }}}
    // {{{ test_cud()

    function test_cud()
    {
        $index_file = dirname(__FILE__) . '/tmp/Pair_TestIndex.idx';
        $idx =& new grain_Index_Pair($index_file);

        $this->assertFalse(is_writable($index_file));

        // {{{ add did#100 to key ids#1, #2, #3.

        // add did #100 to key id #1, #2, #3.
        $this->assertEqual($idx->add(100, array(1, 2, 3)), 3);

        // check affected entry : key id #1.
        $result = $idx->get_from(1);
        $this->assertEqual(count($result), 1);
        $this->assertTrue(isset($result[1]));
        $this->assertTrue(in_array(100, $result[1]));

        // check affected entry : key id #2.
        $result = $idx->get_from(2);
        $this->assertEqual(count($result), 1);
        $this->assertTrue(isset($result[2]));
        $this->assertTrue(in_array(100, $result[2]));

        // check affected entry : key id #3.
        $result = $idx->get_from(3);
        $this->assertEqual(count($result), 1);
        $this->assertTrue(isset($result[3]));
        $this->assertTrue(in_array(100, $result[3]));

        // }}}

        // okay, and add some datas for following test cases.
        $idx->add(200, array(5, 10, 4));
        $idx->add(300, array(3, 5, 1));
        // {{{ ouch! I forgot to adding did to single key id.
        $this->assertEqual($idx->add(100, 10), 1);
        $result = $idx->get_from(10);
        $this->assertEqual(count($result), 1);
        $this->assertTrue(isset($result[10]));
        $this->assertTrue(in_array(100, $result[10]));
        $this->assertTrue(in_array(200, $result[10]));
        // }}}
        $idx->add(400, array(1));
        // Internal Index Structures At This Point:
        // c#1 : d#100, d#300, d#400
        // c#2 : d#100
        // c#3 : d#100, d#300
        // c#4 : d#200
        // c#5 : d#200, d#300
        // c#10 : d#100, d#200

        // {{{ count_for() checks.
        $this->assertEqual($idx->count_for(1), 3); // c#1
        $this->assertEqual($idx->count_for(2), 1); // c#2
        $this->assertEqual($idx->count_for(3), 2); // c#3
        $this->assertEqual($idx->count_for(4), 1); // c#4
        $this->assertEqual($idx->count_for(5), 2); // c#5
        $this->assertEqual($idx->count_for(6), 0); // NOT EXISTS
        $this->assertEqual($idx->count_for(10), 2); // c#10
        // }}}

        // {{{ remove() operations variety patterns.

        // remove did#100 from c#2. affected rows(return value) must be 1.
        $this->assertEqual($idx->remove(100, 2), 1);
        // check affected entry.
        $this->assertEqual($idx->count_for(2), 0);
        $result = $idx->get_from(2);
        $this->assertEqual(count($result), 1);
        $this->assertTrue(isset($result[2]));
        $this->assertEqual(count($result[2]), 0);

        // remove did#100 from c#6(NOT EXISTS). 
        // affected rows(return value) must be 0.
        $this->assertEqual($idx->remove(100, 6), 0);

        // removing did#999 (NOT EXISTS) must result 0.
        $this->assertEqual($idx->remove(999, 1), 0);
        $this->assertEqual($idx->remove(999, array(1, 9, 99)), 0);

        // remove did#300 from c#1, #3. affected rows must be 2.
        $this->assertEqual($idx->remove(300, array(1, 3)), 2);
        // check affected entries.
        $this->assertEqual($idx->count_for(1), 2);
        $this->assertEqual($idx->count_for(3), 1);
        $result = $idx->get_from(array(1, 3));
        $this->assertEqual(count($result), 2);
        $this->assertTrue(isset($result[1]));
        $this->assertTrue(isset($result[3]));
        // c#1
        $ids = $result[1];
        $this->assertEqual(count($ids), 2);
        $this->assertTrue(in_array(100, $ids)); // d#100
        $this->assertTrue(in_array(400, $ids)); // d#400
        // c#3
        $ids = $result[3];
        $this->assertEqual(count($ids), 1);
        $this->assertTrue(in_array(100, $ids)); // d#100

        // }}}
        // Internal Index Structures At This Point:
        // c#1 : d#100, d#400
        // c#2 : (empty)
        // c#3 : d#100
        // c#4 : d#200
        // c#5 : d#200, d#300
        // c#10 : d#100, d#200

        $idx2 =& new grain_Index_Pair($index_file);
        // {{{ new instance $idx2 create, file load, check indice.
        $r = $idx2->get_from(array(1, 2, 3, 4, 5, 6, 10));
        $this->assertEqual(count($r), 6);
        $this->assertTrue(isset($r[1]));
        $this->assertTrue(isset($r[2]));
        $this->assertTrue(isset($r[3]));
        $this->assertTrue(isset($r[4]));
        $this->assertTrue(isset($r[5]));
        $this->assertTrue(isset($r[10]));

        $this->assertEqual(count($r[1]), 2);
        $this->assertEqual($r[1][0], 100);
        $this->assertEqual($r[1][1], 400);

        $this->assertEqual(count($r[2]), 0);

        $this->assertEqual(count($r[3]), 1);
        $this->assertEqual($r[3][0], 100);

        $this->assertEqual(count($r[4]), 1);
        $this->assertEqual($r[4][0], 200);

        $this->assertEqual(count($r[5]), 2);
        $this->assertEqual($r[5][0], 200);
        $this->assertEqual($r[5][1], 300);

        $this->assertEqual(count($r[10]), 2);
        $this->assertEqual($r[10][0], 100);
        $this->assertEqual($r[10][1], 200);
        // }}}

        // add did 500 to key #1.
        $this->assertEqual($idx2->add(500, 1), 1);

        // check affected entry : key id #1.
        $result = $idx2->get_from(1);
        $this->assertEqual(count($result), 1);
        $this->assertTrue(isset($result[1]));
        $this->assertEqual(count($result[1]), 3);
        $this->assertTrue(in_array(100, $result[1]));
        $this->assertTrue(in_array(400, $result[1]));
        $this->assertTrue(in_array(500, $result[1]));

        // now, look $idx, this instance must have old cache.
        $result = $idx->get_from(1);
        $this->assertEqual(count($result), 1);
        $this->assertTrue(isset($result[1]));
        $this->assertEqual(count($result[1]), 2);
        $this->assertTrue(in_array(100, $result[1]));
        $this->assertTrue(in_array(400, $result[1]));

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
