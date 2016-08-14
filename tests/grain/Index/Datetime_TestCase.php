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

require_once('grain/Index/Datetime.php');

class grain_Index_Datetime_TestCase extends UnitTestCase
{
    // {{{ test_properties()

    function test_properties()
    {
        $dat = dirname(__FILE__) . '/tmp/Datetime_Dummy';
        $idx =& new grain_Index_Datetime($dat);
        $this->assertTrue(is_dir($dat));
        System::rm(" -rf " . $dat);

        $idx =& new grain_Index_Datetime(
            dirname(__FILE__) . '/Datetime_TestIdx00');

        // check default values.
        $this->assertEqual($idx->order(), ORDER_BY_DESC);
        $this->assertNull($idx->filters());

        $old = $idx->order(ORDER_BY_ASC);
        $this->assertEqual($old, ORDER_BY_DESC);
        $old = $idx->order(ORDER_BY_DESC);
        $this->assertEqual($old, ORDER_BY_ASC);
        $now = $idx->order();
        $this->assertEqual($now, ORDER_BY_DESC);

        $old = $idx->filters(100);
        $this->assertNull($old);
        $old = $idx->filters(array(10, 30, 40));
        $this->assertEqual(count($old), 1);
        $this->assertEqual($old[0], 100);
        $old = $idx->filters(array(20, 50, 60));
        $this->assertEqual(count($old), 3);
        $this->assertEqual($old[0], 10);
        $this->assertEqual($old[1], 30);
        $this->assertEqual($old[2], 40);
        $now = $idx->filters();
        $this->assertEqual(count($now), 3);
        $this->assertEqual($now[0], 20);
        $this->assertEqual($now[1], 50);
        $this->assertEqual($now[2], 60);

        $idx->reset();
        $this->assertEqual($idx->order(), ORDER_BY_DESC);
        $this->assertNull($idx->filters());
    }

    // }}}
    // {{{ test_find_index_files()

    function test_find_index_files()
    {
        // Datetime_TestIdx00 : empty directory, 0 count.
        $dat = dirname(__FILE__) . '/Datetime_TestIdx00';
        $idx =& new grain_Index_Datetime($dat);
        $result = $idx->find_index_files();
        $this->assertEqual(count($result), 0);

        // Datetime_TestIdx01 : 1 index file should be found.
        $dat = dirname(__FILE__) . '/Datetime_TestIdx01';
        $idx =& new grain_Index_Datetime($dat);
        $result = $idx->find_index_files();
        $this->assertEqual(count($result), 1);
        $this->assertEqual($result[0], 
            realpath($dat . '/200001.idx'));

        // Datetime_TestIdx02 : 2 index file should be found.
        $dat = dirname(__FILE__) . '/Datetime_TestIdx02';
        $idx =& new grain_Index_Datetime($dat);
        $result = $idx->find_index_files();
        $this->assertEqual(count($result), 2);
        $this->assertEqual($result[0], 
            realpath($dat . '/200801.idx'));
        $this->assertEqual($result[1], 
            realpath($dat . '/200802.idx'));

    }

    // }}}
    // {{{ test_index_filename()

    function test_index_filename()
    {
        // Datetime_TestIdx00
        $dat = dirname(__FILE__) . '/Datetime_TestIdx00';
        $idx =& new grain_Index_Datetime($dat);
        $result = $idx->index_filename('20021231235959');
        $this->assertEqual($result, $dat . '/200212.idx');

        // Datetime_TestIdx01
        $dat = dirname(__FILE__) . '/Datetime_TestIdx00';
        $idx =& new grain_Index_Datetime($dat);
        $result = $idx->index_filename('20000101000000');
        $this->assertEqual($result, $dat . '/200001.idx');
    }

    // }}}
    // {{{ test_gets()

    function test_gets()
    {
        // Datetime_TestIdx00
        $dat = dirname(__FILE__) . '/Datetime_TestIdx00';
        $idx =& new grain_Index_Datetime($dat);
        $result = $idx->gets();
        $this->assertEqual(count($result), 0);

        // Datetime_TestIdx01
        $dat = dirname(__FILE__) . '/Datetime_TestIdx01';
        $idx =& new grain_Index_Datetime($dat);
        $result = $idx->gets();
        $this->assertEqual(count($result), 1);

        // Datetime_TestIdx02
        $dat = dirname(__FILE__) . '/Datetime_TestIdx02';
        $idx =& new grain_Index_Datetime($dat);
        $result = $idx->gets();
        $this->assertEqual(count($result), 10);
        $this->assertEqual($result[0], 505);
        $this->assertEqual($result[1], 404);
        $this->assertEqual($result[2], 303);
        $this->assertEqual($result[3], 202);
        $this->assertEqual($result[4], 101);
        $this->assertEqual($result[5], 500);
        $this->assertEqual($result[6], 400);
        $this->assertEqual($result[7], 300);
        $this->assertEqual($result[8], 200);
        $this->assertEqual($result[9], 100);
    }

    // }}}
    // {{{ test_gets_with_order()

    function test_gets_with_order()
    {
        //  Datetime_TestIdx00
        $idx =& new grain_Index_Datetime(
            dirname(__FILE__) . '/Datetime_TestIdx00');
        $idx->order(ORDER_BY_ASC);
        $result = $idx->gets();
        $this->assertEqual(count($result), 0);

        // Datetime_TestIdx01 (asc)
        $idx =& new grain_Index_Datetime(
            dirname(__FILE__) . '/Datetime_TestIdx01');
        $idx->order(ORDER_BY_ASC);
        $result = $idx->gets();
        $this->assertEqual(count($result), 1);

        // Datetime_TestIdx01 (desc)
        $idx =& new grain_Index_Datetime(
            dirname(__FILE__) . '/Datetime_TestIdx01');
        $idx->order(ORDER_BY_DESC);
        $result = $idx->gets();
        $this->assertEqual(count($result), 1);

        // Datetime_TestIdx02 (asc)
        $idx =& new grain_Index_Datetime(
            dirname(__FILE__) . '/Datetime_TestIdx02');
        $idx->order(ORDER_BY_ASC);
        $result = $idx->gets();
        $this->assertEqual(count($result), 10);
        $this->assertEqual($result[9], 505);
        $this->assertEqual($result[8], 404);
        $this->assertEqual($result[7], 303);
        $this->assertEqual($result[6], 202);
        $this->assertEqual($result[5], 101);
        $this->assertEqual($result[4], 500);
        $this->assertEqual($result[3], 400);
        $this->assertEqual($result[2], 300);
        $this->assertEqual($result[1], 200);
        $this->assertEqual($result[0], 100);

        // Datetime_TestIdx02 (desc)
        $idx =& new grain_Index_Datetime(
            dirname(__FILE__) . '/Datetime_TestIdx02');
        $idx->order(ORDER_BY_DESC);
        $result = $idx->gets();
        $this->assertEqual(count($result), 10);
        $this->assertEqual($result[0], 505);
        $this->assertEqual($result[1], 404);
        $this->assertEqual($result[2], 303);
        $this->assertEqual($result[3], 202);
        $this->assertEqual($result[4], 101);
        $this->assertEqual($result[5], 500);
        $this->assertEqual($result[6], 400);
        $this->assertEqual($result[7], 300);
        $this->assertEqual($result[8], 200);
        $this->assertEqual($result[9], 100);
    }

    // }}}
    // {{{ test_gets_with_filters()

    function test_gets_with_filters()
    {
        // Datetime_TestIdx00 : 0 indice
        $idx =& new grain_Index_Datetime(
            dirname(__FILE__) . '/Datetime_TestIdx00');

        $idx->filters(array());
        $result = $idx->gets();
        $this->assertEqual(count($result), 0);

        $idx->reset();
        $idx->filters(array(100, 200, 303, 404, 505));
        $result = $idx->gets();
        $this->assertEqual(count($result), 0);

        // Datetime_TestIdx00 : 2 physical files, 10 records
        $idx =& new grain_Index_Datetime(
            dirname(__FILE__) . '/Datetime_TestIdx02');

        $idx->filters(array());
        $result = $idx->gets();
        $this->assertEqual(count($result), 0);

        $idx->reset();
        $idx->filters(array(100, 200, 303, 404, 505));
        $result = $idx->gets();
        $this->assertEqual(count($result), 5);
        $this->assertEqual($result[0], 505);
        $this->assertEqual($result[1], 404);
        $this->assertEqual($result[2], 303);
        $this->assertEqual($result[3], 200);
        $this->assertEqual($result[4], 100);
    }

    // }}}
    // {{{ test_gets_with_order_and_filters()

    function test_gets_with_order_and_filters()
    {
        // Datetime_TestIdx00 : 0 indice (asc)
        $idx =& new grain_Index_Datetime(
            dirname(__FILE__) . '/Datetime_TestIdx00');
        $idx->order(ORDER_BY_ASC);
        $idx->filters(array());
        $result = $idx->gets();
        $this->assertEqual(count($result), 0);

        $idx->reset();
        $idx->order(ORDER_BY_ASC);
        $idx->filters(array(100, 200, 303, 404, 505));
        $result = $idx->gets();
        $this->assertEqual(count($result), 0);

        // Datetime_TestIdx00 : 0 indice (desc)
        $idx =& new grain_Index_Datetime(
            dirname(__FILE__) . '/Datetime_TestIdx00');
        $idx->order(ORDER_BY_DESC);
        $idx->filters(array());
        $result = $idx->gets();
        $this->assertEqual(count($result), 0);

        $idx->reset();
        $idx->order(ORDER_BY_DESC);
        $idx->filters(array(100, 200, 303, 404, 505));
        $result = $idx->gets();
        $this->assertEqual(count($result), 0);

        // Datetime_TestIdx00 : 2 physical files, 10 records (asc)
        $idx =& new grain_Index_Datetime(
            dirname(__FILE__) . '/Datetime_TestIdx02');
        $idx->order(ORDER_BY_ASC);
        $idx->filters(array());
        $result = $idx->gets();
        $this->assertEqual(count($result), 0);

        $idx->reset();
        $idx->order(ORDER_BY_ASC);
        $idx->filters(array(100, 200, 303, 404, 505));
        $result = $idx->gets();
        $this->assertEqual(count($result), 5);
        $this->assertEqual($result[4], 505);
        $this->assertEqual($result[3], 404);
        $this->assertEqual($result[2], 303);
        $this->assertEqual($result[1], 200);
        $this->assertEqual($result[0], 100);

        // Datetime_TestIdx00 : 2 physical files, 10 records (desc)
        $idx =& new grain_Index_Datetime(
            dirname(__FILE__) . '/Datetime_TestIdx02');
        $idx->order(ORDER_BY_DESC);
        $idx->filters(array());
        $result = $idx->gets();
        $this->assertEqual(count($result), 0);

        $idx->reset();
        $idx->order(ORDER_BY_DESC);
        $idx->filters(array(100, 200, 303, 404, 505));
        $result = $idx->gets();
        $this->assertEqual(count($result), 5);
        $this->assertEqual($result[0], 505);
        $this->assertEqual($result[1], 404);
        $this->assertEqual($result[2], 303);
        $this->assertEqual($result[3], 200);
        $this->assertEqual($result[4], 100);
    }

    // }}}
    // {{{ test_cud()

    function test_cud()
    {
        $dat = dirname(__FILE__) . '/tmp/Datetime_TestIdx';
        $idx =& new grain_Index_Datetime($dat);
        $_time = '235959'; // dummy filling

        // {{{ append()

        // 2008-03-XX
        $this->assertTrue($idx->append(10, '20080301' . $_time));
        $this->assertTrue($idx->append(20, '20080302' . $_time)); // same key
        $this->assertTrue($idx->append(30, '20080302' . $_time)); // same key
        $this->assertTrue($idx->append(40, '20080304' . $_time));
        $this->assertTrue($idx->append(50, '20080305' . $_time));
        // 2008-04-XX
        $this->assertTrue($idx->append(60, '20080401' . $_time));
        $this->assertTrue($idx->append(70, '20080402' . $_time));
        $this->assertTrue($idx->append(80, '20080403' . $_time));
        $this->assertTrue($idx->append(90, '20080404' . $_time));

        $result = $idx->gets();
        $this->assertEqual(count($result), 9);
        $this->assertEqual($result[0], 90);
        $this->assertEqual($result[1], 80);
        $this->assertEqual($result[2], 70);
        $this->assertEqual($result[3], 60);
        $this->assertEqual($result[4], 50);
        $this->assertEqual($result[5], 40);
        $this->assertTrue(
            ($result[6] == 30 && $result[7] == 20) ||
            ($result[7] == 30 && $result[6] == 20)
        );
        $this->assertEqual($result[8], 10);

        $this->assertTrue($idx->append(100, '20080304' . $_time));
        $result = $idx->gets();
        $this->assertEqual(count($result), 10);

        $this->assertEqual($result[0], 90);
        $this->assertEqual($result[1], 80);
        $this->assertEqual($result[2], 70);
        $this->assertEqual($result[3], 60);
        $this->assertEqual($result[4], 50);
        $this->assertTrue(
            ($result[5] == 40 && $result[6] == 100) ||
            ($result[6] == 40 && $result[5] == 100)
        );
        $this->assertTrue(
            ($result[7] == 30 && $result[8] == 20) ||
            ($result[8] == 30 && $result[7] == 20)
        );
        $this->assertEqual($result[9], 10);
        // }}}
        /*
         * Now, index file and contents are:
         * 200803.idx : 0301-10, 0302-20, 0302-30, 0304-40, 0304-100, 0305-50
         * 200804.idx : 0401-60, 0402-70, 0403-80, 0404-90
         */

        // {{{ delete()
        $idx->reset();

        $this->assertTrue($idx->delete(60, '20080401' . $_time));
        $this->assertTrue($idx->delete(70, '20080402' . $_time));
        $this->assertTrue($idx->delete(40, '20080304' . $_time));
        $this->assertFalse($idx->delete(1000, '20080301' . $_time));
        $this->assertFalse($idx->delete(100, '20080305' . $_time));
        $this->assertFalse($idx->delete(10, '20080401' . $_time));

        $result = $idx->gets();
        $this->assertEqual(count($result), 7);

        $this->assertEqual($result[0], 90);
        $this->assertEqual($result[1], 80);
        $this->assertEqual($result[2], 50);
        $this->assertEqual($result[3], 100);
        $this->assertTrue(
            ($result[4] == 30 && $result[5] == 20) ||
            ($result[5] == 30 && $result[4] == 20)
        );
        $this->assertEqual($result[6], 10);

        // }}}
        /*
         * Now, index file and contents are:
         * 200803.idx : 0301-10, 0302-20, 0302-30, 0304-100, 0305-50
         * 200804.idx : 0403-80, 0404-90
         */

        // {{{ update operation ('delete', 'append', 'exist' combination)
        $idx->reset();

        $this->assertTrue(
            $idx->delete(10, '20080301' . $_time) &&
            $idx->append(10, '20080404' . $_time)
        );
        $this->assertTrue(
            $idx->delete(30, '20080302' . $_time) &&
            $idx->append(30, '20080405' . $_time)
        );
        $this->assertTrue(
            $idx->delete(20, '20080302' . $_time) &&
            $idx->append(20, '20080406' . $_time)
        );
        $this->assertTrue(
            $idx->delete(100, '20080304' . $_time) &&
            $idx->append(100, '20080510' . $_time)
        );
        $this->assertTrue(
            $idx->delete(90, '20080404' . $_time) &&
            $idx->append(90, '20080520' . $_time)
        );
        // }}}
        /*
         * Now, index file and contents are:
         * 200803.idx : 0305-50
         * 200804.idx : 0403-80, 0404-10, 0405-30, 0406-20
         * 200805.idx : 0510-100, 0520-90
         */

        $result = $idx->gets();
        $this->assertEqual(count($result), 7);
        $this->assertEqual($result[0], 90);
        $this->assertEqual($result[1], 100);
        $this->assertEqual($result[2], 20);
        $this->assertEqual($result[3], 30);
        $this->assertEqual($result[4], 10);
        $this->assertEqual($result[5], 80);
        $this->assertEqual($result[6], 50);

        // clean up temporary index directory.
        System::rm(" -rf " . $dat);
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
