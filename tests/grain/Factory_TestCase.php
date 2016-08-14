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

require_once('grain/Factory.php');

class grain_Factory_TestCase extends UnitTestCase
{
    var $_dir = '';

    // {{{ setUp()

    function setUp()
    {
        $GLOBALS[FACTORY_ZONE] = mt_rand();
        $this->_dir = dirname(__FILE__) . '/tmp';
        mkdir($this->_dir);
    }

    // }}}
    // {{{ tearDown()

    function tearDown()
    {
        System::rm(" -rf " . $this->_dir);
    }

    // }}}
    // {{{ test_sequence()

    function test_sequence()
    {
        $seq_dir = $this->_dir . '/sequence';
        mkdir($seq_dir);
        $_old = grain_Config::set('grain.dir.sequence', $seq_dir);

        $s1 =& grain_Factory::sequence('class1');
        $this->assertEqual($s1->next(), 1);
        $this->assertEqual($s1->next(), 2);
        $this->assertEqual($s1->next(), 3);
        $this->assertEqual($s1->current(), 3);
        $this->assertEqual($s1->next(), 4);

        $s2 =& grain_Factory::sequence('class2');
        $this->assertEqual($s2->next(), 1);
        $this->assertEqual($s2->next(), 2);
        $this->assertEqual($s2->next(), 3);
        $this->assertEqual($s2->current(), 3);
        $this->assertEqual($s2->next(), 4);

        $this->assertTrue($s1->set(10));
        $this->assertEqual($s1->current(), 10);
        $this->assertEqual($s1->next(), 11);

        $this->assertTrue($s2->set(0));
        $this->assertEqual($s2->current(), 0);
        $this->assertEqual($s2->next(), 1);

        $s3 =& grain_Factory::sequence('class1');
        $this->assertEqual($s3->next(), 12);

        $s4 =& grain_Factory::sequence('class2');
        $this->assertEqual($s2->next(), 2);

        grain_Config::set('grain.dir.sequence', $_old);
    }

    // }}}
    // {{{ test_grain()

    function test_grain()
    {
        // check null return
        $_old = grain_Config::set('grain.dir.grain', null);
        $g =& grain_Factory::grain('grain0');
        $this->assertNull($g);
        grain_Config::set('grain.dir.grain', $_old);

        $grain_dir = $this->_dir . '/grain';
        mkdir($grain_dir);
        $_olds = array(
            // grain's root dir
            'dir' => grain_Config::set('grain.dir.grain', $grain_dir),
            // chunksize for default
            'csd' => grain_Config::set('grain.chunksize.default', 10),
            // chunksize for 'grain1'
            'cs1' => grain_Config::set('grain.chunksize.grain1', 5),
            // chunksize for 'grain2'
            'cs2' => grain_Config::set('grain.chunksize.grain2', 3),
            );

        $g1 =& grain_Factory::grain('grain1');
        $g2 =& grain_Factory::grain('grain2');
        $g3 =& grain_Factory::grain('grain3');

        $r = $g1->find_data_files();
        $this->assertEqual(count($r), 0);
        $r = $g2->find_data_files();
        $this->assertEqual(count($r), 0);
        $r = $g3->find_data_files();
        $this->assertEqual(count($r), 0);

        // check chunksize for grain1
        $this->assertEqual(basename($g1->data_filename(1)), '5.dat');
        $this->assertEqual(basename($g1->data_filename(4)), '5.dat');
        $this->assertEqual(basename($g1->data_filename(5)), '5.dat');
        $this->assertEqual(basename($g1->data_filename(6)), '10.dat');
        // check chunksize for grain2
        $this->assertEqual(basename($g2->data_filename(1)), '3.dat');
        $this->assertEqual(basename($g2->data_filename(2)), '3.dat');
        $this->assertEqual(basename($g2->data_filename(3)), '3.dat');
        $this->assertEqual(basename($g2->data_filename(4)), '6.dat');
        // check chunksize for grain3
        $this->assertEqual(basename($g3->data_filename(1)), '10.dat');
        $this->assertEqual(basename($g3->data_filename(9)), '10.dat');
        $this->assertEqual(basename($g3->data_filename(10)), '10.dat');
        $this->assertEqual(basename($g3->data_filename(11)), '20.dat');

        // save()
        $data = array(
            'column1' => 'ABC',
            'column2' => 'DEF' . GRAIN_DATA_RS . 'GHI',
            'column3 foo' => 'JKL',
        );
        $this->assertTrue($g1->save(10, $data));

        // find()
        $r = $g1->find();
        $this->assertEqual(count($r), 1);
        $this->assertEqual(count($r[10]), 3);
        $this->assertEqual($r[10]['column1'], 'ABC');
        $this->assertEqual($r[10]['column2'], 'DEFGHI');
        $this->assertEqual($r[10]['column3 foo'], 'JKL');

        // delete()
        $this->assertTrue($g1->delete(10));

        $r = $g1->find(10);
        $this->assertEqual(count($r), 0);

        // test factory zone
        $g1->dummy = true;
        $GLOBALS[FACTORY_ZONE] = mt_rand();
        $g1_ =& grain_Factory::grain('grain1');
        $this->assertFalse(isset($g1_->dummy));

        grain_Config::set('grain.dir.grain', $_olds['dir']);
        grain_Config::set('grain.chunksize.default', $_olds['csd']);
        grain_Config::set('grain.chunksize.grain1', $_olds['cs1']);
        grain_Config::set('grain.chunksize.grain2', $_olds['cs2']);
    }

    // }}}
    // {{{ test_raw()

    function test_raw()
    {
        // check null return
        $_old = grain_Config::set('grain.dir.raw', null);
        $r =& grain_Factory::raw('raw0');
        $this->assertNull($r);
        grain_Config::set('grain.dir.raw', $_old);

        $raw_dir = $this->_dir . '/raw';
        mkdir($raw_dir);
        $_olds = array(
            // raw's root dir
            'dir' => grain_Config::set('grain.dir.raw', $raw_dir),
            // chunksize for default
            'csd' => grain_Config::set('grain.chunksize.default', 10),
            // chunksize for 'grain1'
            'cs1' => grain_Config::set('grain.chunksize.raw1', 5),
            // chunksize for 'grain2'
            'cs2' => grain_Config::set('grain.chunksize.raw2', 3),
        );

        $r1 =& grain_Factory::raw('raw1');
        $r2 =& grain_Factory::raw('raw2');
        $r3 =& grain_Factory::raw('raw3');

        // save()
        $this->assertTrue($r1->save(1, 'data1_1'));
        $this->assertTrue($r1->save(5, 'data1_5'));
        $this->assertTrue($r1->save(6, 'data1_6'));
        $this->assertTrue($r2->save(1, 'data2_1'));
        $this->assertTrue($r2->save(3, 'data2_3'));
        $this->assertTrue($r2->save(4, 'data2_4'));
        $this->assertTrue($r3->save(1, 'data3_1'));
        $this->assertTrue($r3->save(10, 'data3_10'));
        $this->assertTrue($r3->save(11, 'data3_11'));

        // filename()
        $this->assertEqual($r1->filename(1), realpath($raw_dir . '/raw1/5/1'));
        $this->assertEqual($r1->filename(5), realpath($raw_dir . '/raw1/5/5'));
        $this->assertEqual($r1->filename(6), realpath($raw_dir . '/raw1/10/6'));
        $this->assertEqual(file_get_contents($r1->filename(1)), 'data1_1');
        $this->assertEqual(file_get_contents($r1->filename(5)), 'data1_5');
        $this->assertEqual(file_get_contents($r1->filename(6)), 'data1_6');

        // overwrite test
        $this->assertTrue($r1->save(1, 'data1_1_b'));
        $this->assertEqual(file_get_contents($r1->filename(1)), 'data1_1_b');

        // delete()
        $this->assertTrue($r1->delete(1));
        $this->assertFalse($r1->delete(1));
        $this->assertNull($r1->filename(1));

        // test factory zone
        $r1->dummy = true;
        $GLOBALS[FACTORY_ZONE] = mt_rand();
        $r1_ =& grain_Factory::raw('raw1');
        $this->assertFalse(isset($r1_->dummy));

        grain_Config::set('grain.dir.raw', $_olds['dir']);
        grain_Config::set('grain.chunksize.default', $_olds['csd']);
        grain_Config::set('grain.chunksize.grain1', $_olds['cs1']);
        grain_Config::set('grain.chunksize.grain2', $_olds['cs2']);
    }

    // }}}
    // {{{ test_index_pair()

    function test_index_pair()
    {
        // check null return
        $_old = grain_Config::set('grain.dir.index', null);
        $idx =& grain_Factory::index('pair', 'pair0');
        $this->assertNull($idx);
        grain_Config::set('grain.dir.index', $_old);

        $dir = $this->_dir . '/index';
        mkdir($dir);
        $_old = grain_Config::set('grain.dir.index', $dir);

        // invalid index type
        $idx =& grain_Factory::index('foobar', 'pair1');
        $this->assertNull($idx);

        // pair index
        $idx1 =& grain_Factory::index('pair', 'pair1');
        $idx2 =& grain_Factory::index('pair', 'pair2');

        // add did #100 to key id #1, #2, #3.
        $this->assertEqual($idx1->add(100, array(1, 2, 3)), 3);

        // get_from() #1
        $result = $idx1->get_from(1);
        $this->assertEqual(count($result), 1);
        $this->assertTrue(isset($result[1]));
        $this->assertTrue(in_array(100, $result[1]));
        // count_for() #1
        $this->assertEqual($idx1->count_for(1), 1);
        // remove() #2, 100
        $this->assertEqual($idx1->remove(100, 2), 1);

        // add did #100 to key id #1, #2, #3.
        $this->assertEqual($idx2->add(100, array(1, 2, 3)), 3);

        // get_from() #1
        $result = $idx2->get_from(1);
        $this->assertEqual(count($result), 1);
        $this->assertTrue(isset($result[1]));
        $this->assertTrue(in_array(100, $result[1]));
        // count_for() #1
        $this->assertEqual($idx2->count_for(1), 1);
        // remove() #2, 100
        $this->assertEqual($idx2->remove(100, 2), 1);

        // test factory zone
        $idx1->dummy = true;
        $GLOBALS[FACTORY_ZONE] = mt_rand();
        $idx1_ =& grain_Factory::index('pair', 'pair1');
        $this->assertFalse(isset($idx1_->dummy));

        grain_Config::set('grain.dir.index', $_old);
    }

    // }}}
    // {{{ test_index_match()

    function test_index_match()
    {
        $dir = $this->_dir . '/index';
        mkdir($dir);
        $_old = grain_Config::set('grain.dir.index', $dir);

        // match index
        $idx1 =& grain_Factory::index('match', 'match1');
        $idx2 =& grain_Factory::index('match', 'match2');

        // register()
        $this->assertTrue($idx1->register(10, "Foo Bar"));
        // search()
        $result = $idx1->search("foo");
        $this->assertEqual(count($result), 1);
        $this->assertEqual($result[0], 10);
        // unregister()
        $this->assertTrue($idx1->unregister(10));
        $result = $idx1->search("Bar");
        $this->assertEqual(count($result), 0);

        // register()
        $this->assertTrue($idx2->register(10, "Foo Bar"));
        // search()
        $result = $idx2->search("foo");
        $this->assertEqual(count($result), 1);
        $this->assertEqual($result[0], 10);
        // unregister()
        $this->assertTrue($idx2->unregister(10));
        $result = $idx2->search("Bar");
        $this->assertEqual(count($result), 0);

        // test factory zone
        $idx1->dummy = true;
        $GLOBALS[FACTORY_ZONE] = mt_rand();
        $idx1_ =& grain_Factory::index('match', 'match1');
        $this->assertFalse(isset($idx1_->dummy));

        grain_Config::set('grain.dir.index', $_old);
    }

    // }}}
    // {{{ test_index_datetime()

    function test_index_datetime()
    {
        $dir = $this->_dir . '/index';
        mkdir($dir);
        $_old = grain_Config::set('grain.dir.index', $dir);

        // datetime index
        $dt1 =& grain_Factory::index('datetime', 'dt1');
        $dt2 =& grain_Factory::index('datetime', 'dt2');

        // confirm reset() behaviour.
        // ... now, set filter (consider search processing)
        $dt1->filters(array(1, 2, 3));
        $dt1->order(ORDER_BY_ASC);
        // ... (imagine some gets(), append() processing...)
        // ... now, another module use in another search condition...
        $dt1 =& grain_Factory::index('datetime', 'dt1');
        // at this point, datetime's filter condition MUST be resetted!!
        $this->assertNull($dt1->filters());
        $this->assertIdentical($dt1->order(), ORDER_BY_DESC);

        $_time = '235959'; // dummy filling

        // append()
        $this->assertTrue($dt1->append(10, '20080301' . $_time));
        $this->assertTrue($dt2->append(20, '20080401' . $_time));

        // gets()
        $result = $dt1->gets();
        $this->assertEqual(count($result), 1);
        $this->assertEqual($result[0], 10);
        $result = $dt2->gets();
        $this->assertEqual(count($result), 1);
        $this->assertEqual($result[0], 20);

        // delete()
        $this->assertTrue($dt1->delete(10, '20080301' . $_time));
        $this->assertTrue($dt2->delete(20, '20080401' . $_time));

        // test factory zone
        $dt1->dummy = true;
        $GLOBALS[FACTORY_ZONE] = mt_rand();
        $dt1_ =& grain_Factory::index('datetime', 'dt1');
        $this->assertFalse(isset($dt1_->dummy));

        grain_Config::set('grain.dir.index', $_old);
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
