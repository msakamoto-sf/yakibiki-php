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


require_once('yb/dao/Base.php');

class yb_dao_Base_TestDao extends yb_dao_Base
{
    // {{{ constructor

    function yb_dao_Base_TestDao()
    {
        $this->_cache_name = __CLASS__;

        $this->_grain_name = 'test';

        $this->_updatable_fields = array('col1', 'col2', 'col3');

        $this->_sortable = array('id', 'col1', 'col2', 
            'created_at', 'updated_at');
    }

    // }}}
    // {{{ flesh2grain()

    function flesh2grain($flesh)
    {
        $v = array_map('trim', explode(GRAIN_DATA_GS, $flesh['col3']));
        $__v = array();
        foreach ($v as $_v) {
            if ($_v != '') {
                $__v[] = $_v;
            }
        }
        $flesh['col3'] = $__v;
        return $flesh;
    }

    // }}}
    // {{{ grain2flesh()

    function grain2flesh($grain)
    {
        if (!isset($grain['col3'])) {
            $grain['col3'] = array();
        }
        if (is_array($grain['col3'])) {
            $grain['col3'] = implode(GRAIN_DATA_GS, $grain['col3']);
        }
        return $grain;
    }

    // }}}
}

class yb_dao_Base_TestFind extends yb_dao_Base_TestDao
{
    // {{{ constructor

    function yb_dao_Base_TestFind()
    {
        parent::yb_dao_Base_TestDao();
        $this->_grain_name = 'base1';
    }

    // }}}
    // {{{ flesh2grain()

    function flesh2grain($flesh)
    {
        return $flesh;
    }

    // }}}
    // {{{ grain2flesh()

    function grain2flesh($grain)
    {
        return $grain;
    }

    // }}}
}

class yb_dao_Base_TestFind2 extends yb_dao_Base_TestFind
{
    // {{{ constructor

    function yb_dao_Base_TestFind2()
    {
        parent::yb_dao_Base_TestFind();
        $this->_grain_name = 'base2';
    }

    // }}}
}

class yb_dao_Base_TestCase extends UnitTestCase
{
    var $_old_dirs = array();

    // {{{ testCUD() (entry point)

    function testCUD()
    {
        $root_dir = dirname(__FILE__) . '/tmp/base';
        mkdir($root_dir);

        $seq_dir = $root_dir . '/sequence';
        mkdir($seq_dir);

        $grain_dir = $root_dir . '/grain';
        mkdir($grain_dir);

        $old_dirs = array(
            'seq_dir' => grain_Config::set('grain.dir.sequence', $seq_dir),
            'grain_dir' => grain_Config::set('grain.dir.grain', $grain_dir),
            'chunksize' => grain_Config::set('grain.chunksize.test', 10),
        );

        $this->_testCreate();
        $this->_testUpdate();
        $this->_testDelete();
        $this->_testDestroy();

        grain_Config::set('grain.dir.sequence', $old_dirs['seq_dir']);
        grain_Config::set('grain.dir.grain', $old_dirs['grain_dir']);
        grain_Config::set('grain.chunksize.test', $old_dirs['chunksize']);
        System::rm(" -rf " . $root_dir);
    }

    // }}}
    // {{{ _testCreate()

    function _testCreate()
    {
        $dao =& new yb_dao_Base_TestDao();

        $data = array(
            "col1" => 10,
            "col2" => "testCreate_Base01",
            "col3" => 100,
            "col4" => "abc",
            );
        $this->assertEqual($dao->create($data), 1);
        $data = array(
            "col1" => 20,
            "col2" => "testCreate_Base02",
            "col3" => array(200, 210, 220),
            "col4" => "def",
            );
        $this->assertEqual($dao->create($data), 2);
        $data = array(
            "col1" => 30,
            "col2" => "testCreate_Base03",
            "col4" => "ghi",
            );
        $this->assertEqual($dao->create($data), 3);

        $records = $dao->find_all();
        $this->assertEqual($records[0]['id'], 1);
        $this->assertEqual($records[0]['col1'], 10);
        $this->assertEqual($records[0]['col2'], "testCreate_Base01");
        $this->assertEqual($records[0]['col4'], "abc");
        $v = $records[0]['col3'];
        $this->assertTrue(is_array($v));
        $this->assertEqual(count($v), 1);
        $this->assertEqual($v[0], 100);

        $this->assertEqual($records[1]['id'], 2);
        $this->assertEqual($records[1]['col1'], 20);
        $this->assertEqual($records[1]['col2'], "testCreate_Base02");
        $this->assertEqual($records[1]['col4'], "def");
        $v = $records[1]['col3'];
        $this->assertTrue(is_array($v));
        $this->assertEqual(count($v), 3);
        $this->assertTrue(in_array(200, $v));
        $this->assertTrue(in_array(210, $v));
        $this->assertTrue(in_array(220, $v));

        $this->assertEqual($records[2]['id'], 3);
        $this->assertEqual($records[2]['col1'], 30);
        $this->assertEqual($records[2]['col2'], "testCreate_Base03");
        $this->assertEqual($records[2]['col4'], "ghi");
        $v = $records[2]['col3'];
        $this->assertTrue(is_array($v));
        $this->assertEqual(count($v), 0);
    }

    // }}}
    // {{{ _testUpdate()

    function _testUpdate()
    {
        $dao =& new yb_dao_Base_TestDao();

        echo "... wait 1 minutes for checking 'updated_at' field updating.\n";
        sleep(1);

        // {{{ update#1 : col1, col2, col3

        $result = $dao->find_by('id', 1);
        $olddata = $result[0];

        $data = array(
            "col1" => 91,
            "col2" => "testUpdate_Base01",
            "col3" => array(901, 902),
            "col4" => "ABC", // not updatable
            );
        $result = $dao->update(1, $data);
        $this->assertIdentical($result, 1);

        $results = $dao->find_by('id', 1);
        $result = $results[0];
        $this->assertEqual($result['id'], 1);
        $this->assertEqual($result['col1'], 91);
        $this->assertEqual($result['col2'], "testUpdate_Base01");
        $this->assertEqual($result['col4'], "abc"); // not updatable
        $v = $result['col3'];
        $this->assertTrue(is_array($v));
        $this->assertEqual(count($v), 2);
        $this->assertTrue(in_array(901, $v));
        $this->assertTrue(in_array(902, $v));
        $this->assertEqual($result['created_at'], $olddata['created_at']);
        $this->assertNotEqual($result['updated_at'], $olddata['updated_at']);

        // }}}
        // {{{ update#2 : id (ignored), col1, col2

        $result = $dao->find_by('id', 2);
        $olddata = $result[0];

        $data = array(
            "id" => 99,
            "col1" => 92,
            "col2" => "testUpdate_Base02",
            );
        $result = $dao->update(2, $data);
        $this->assertIdentical($result, 1);

        $results = $dao->find_by('id', 2);
        $result = $results[0];
        $this->assertEqual($result['id'], $olddata['id']);
        $this->assertEqual($result['col1'], 92);
        $this->assertEqual($result['col2'], "testUpdate_Base02");

        $old_v = $olddata['col3'];
        sort($old_v);
        $new_v = $result['col3'];
        sort($new_v);
        foreach ($new_v as $i => $v) {
            $this->assertEqual($old_v[$i], $v);
        }

        $this->assertEqual($result['created_at'], $olddata['created_at']);
        $this->assertNotEqual($result['updated_at'], $olddata['updated_at']);

        // }}}
        // {{{ update#999 : UNDEFINED id

        $data = array(
            "col1" => 99,
            "col2" => "testUpdate_Base",
            );
        $this->assertIdentical($dao->update(999, $data), 0);

        // }}}
    }

    // }}}
    // {{{ _testDelete()

    function _testDelete()
    {
        $dao =& new yb_dao_Base_TestDao();

        // delete #1
        $this->assertIdentical($dao->delete(1), 1);
        $datas = $dao->find_by('id', 1);
        $this->assertEqual(count($datas), 0);

        // delete #100 (undefined)
        $this->assertIdentical($dao->delete(100), 0);

        // delete #3
        $this->assertIdentical($dao->delete(3), 1);
        $datas = $dao->find_all();
        $this->assertEqual(count($datas), 1);
        $this->assertEqual($datas[0]['id'], 2);
        $this->assertEqual($datas[0]['col1'], 92);
        $this->assertEqual($datas[0]['col2'], "testUpdate_Base02");

        $v = $datas[0]['col3'];
        $this->assertTrue(is_array($v));
        $this->assertEqual(count($v), 3);
        $this->assertTrue(in_array(200, $v));
        $this->assertTrue(in_array(210, $v));
        $this->assertTrue(in_array(220, $v));

        // delete #1 (undefined)
        $this->assertIdentical($dao->delete(1), 0);

    }

    // }}}
    // {{{ _testDestroy()

    function _testDestroy()
    {
        $dao =& new yb_dao_Base_TestDao();
        $this->assertIdentical($dao->destroy(), 1);
    }

    // }}}
    // {{{ _find_prepare()

    function _find_prepare()
    {
        $root_dir = dirname(__FILE__) . '/testgrains';
        $seq_dir = $root_dir . '/sequence';
        $grain_dir = $root_dir . '/grain';

        $this->_old_dirs = array(
            'seq_dir' => grain_Config::set('grain.dir.sequence', $seq_dir),
            'grain_dir' => grain_Config::set('grain.dir.grain', $grain_dir),
            'chunksize' => grain_Config::set('grain.chunksize.test', 10),
        );
    }

    // }}}
    // {{{ _find_cleanup()

    function _find_cleanup()
    {
        grain_Config::set('grain.dir.sequence', 
            $this->_old_dirs['seq_dir']);
        grain_Config::set('grain.dir.grain', 
            $this->_old_dirs['grain_dir']);
        grain_Config::set('grain.chunksize.test', 
            $this->_old_dirs['chunksize']);
    }

    // }}}
    // {{{ testFindBy_id()

    function testFindBy_id()
    {
        $this->_find_prepare();
        $find =& new yb_dao_Base_TestFind();

        $yc =& yb_Cache::factory($find->_cache_name);
        $yc->clean();

        // {{{ find_by('id') : found 1 item
        $result = $find->find_by('id', 1);
        $this->assertEqual(count($result), 1);
        $this->assertEqual($result[0]['id'], 1);
        $this->assertEqual($result[0]['col1'], 100);
        $this->assertEqual($result[0]['col2'], "Base_006");
        $this->assertEqual($result[0]['col3'], 1);
        // }}}
        // {{{ find_by('id') : NOT found
        $result = $find->find_by('id', 7);
        $this->assertEqual(count($result), 0);
        // }}}
        // {{{ find_by('id') : multiple id found (default sort by id asc)
        $result = $find->find_by('id', array(1, 3, 4));
        $this->assertEqual(count($result), 3);
        $this->assertEqual($result[0]['id'], 1);
        $this->assertEqual($result[0]['col1'], 100);
        $this->assertEqual($result[0]['col2'], "Base_006");
        $this->assertEqual($result[0]['col3'], 1);
        $this->assertEqual($result[1]['id'], 3);
        $this->assertEqual($result[1]['col1'], 100);
        $this->assertEqual($result[1]['col2'], "Base_002");
        $this->assertEqual($result[1]['col3'], 1);
        $this->assertEqual($result[2]['id'], 4);
        $this->assertEqual($result[2]['col1'], 200);
        $this->assertEqual($result[2]['col2'], "Base_003");
        $this->assertEqual($result[2]['col3'], 3);
        // }}}
        // {{{ find_by('id') : multiple id and sort by id desc
        $result = $find->find_by('id', 
            array(1, 2, 3, 4, 5), "id", ORDER_BY_DESC);
        $this->assertEqual(count($result), 5);
        $this->assertEqual($result[0]['id'], 5);
        $this->assertEqual($result[1]['id'], 4);
        $this->assertEqual($result[2]['id'], 3);
        $this->assertEqual($result[3]['id'], 2);
        $this->assertEqual($result[4]['id'], 1);
        // }}}
        // {{{ find_by('id') : multiple id and sort by 'col2' asc
        $result = $find->find_by('id', 
            array(1, 2, 3, 4, 5), "col2", ORDER_BY_ASC);
        $this->assertEqual(count($result), 5);
        $this->assertEqual($result[0]['id'], 2);
        $this->assertEqual($result[1]['id'], 3);
        $this->assertEqual($result[2]['id'], 4);
        $this->assertEqual($result[3]['id'], 5);
        $this->assertEqual($result[4]['id'], 1);
        // }}}
        // {{{ find_by('id') : multiple id and sort by 'col2' desc
        $result = $find->find_by('id', 
            array(1, 2, 5), "col2", ORDER_BY_DESC);
        $this->assertEqual(count($result), 3);
        $this->assertEqual($result[0]['id'], 1);
        $this->assertEqual($result[1]['id'], 5);
        $this->assertEqual($result[2]['id'], 2);
        // }}}
        // (created_at and updated_at field sort is tested 
        // in testFindBy_col1().)

        $this->_find_cleanup();
    }

    // }}}
    // {{{ testFindBy_col1()

    function testFindBy_col1()
    {
        $this->_find_prepare();
        $find =& new yb_dao_Base_TestFind();

        $yc =& yb_Cache::factory($find->_cache_name);
        $yc->clean();

        // {{{ find_by('col1', 100) : found (default sort by id asc)
        $result = $find->find_by('col1', 100);
        $this->assertEqual(count($result), 3);
        $this->assertEqual($result[0]['id'], 1);
        $this->assertEqual($result[0]['col1'], 100);
        $this->assertEqual($result[0]['col2'], "Base_006");
        $this->assertEqual($result[0]['col3'], 1);
        $this->assertEqual($result[1]['id'], 2);
        $this->assertEqual($result[1]['col1'], 100);
        $this->assertEqual($result[1]['col2'], "Base_001");
        $this->assertEqual($result[1]['col3'], 2);
        $this->assertEqual($result[2]['id'], 3);
        $this->assertEqual($result[2]['col1'], 100);
        $this->assertEqual($result[2]['col2'], "Base_002");
        $this->assertEqual($result[2]['col3'], 1);
        // }}}
        // {{{ find_by('col1', 200) : found and sort by id desc
        $result = $find->find_by('col1', 200, "id", ORDER_BY_DESC);
        $this->assertEqual(count($result), 3);
        $this->assertEqual($result[0]['id'], 6);
        $this->assertEqual($result[0]['col1'], 200);
        $this->assertEqual($result[0]['col2'], "Base_005");
        $this->assertEqual($result[0]['col3'], 1);
        $this->assertEqual($result[1]['id'], 5);
        $this->assertEqual($result[1]['col1'], 200);
        $this->assertEqual($result[1]['col2'], "Base_004");
        $this->assertEqual($result[1]['col3'], 2);
        $this->assertEqual($result[2]['id'], 4);
        $this->assertEqual($result[2]['col1'], 200);
        $this->assertEqual($result[2]['col2'], "Base_003");
        $this->assertEqual($result[2]['col3'], 3);
        // }}}
        // {{{ find_by('col1', 300) : NOT found
        $result = $find->find_by('col1', 300);
        $this->assertEqual(count($result), 0);
        // }}}
        // {{{ find_by('col1', 100) : sort by created_at asc
        $result = $find->find_by('col1', 100, "created_at");
        $this->assertEqual(count($result), 3);
        $this->assertEqual($result[0]['id'], 1);
        $this->assertEqual($result[1]['id'], 2);
        $this->assertEqual($result[2]['id'], 3);
        // }}}
        // {{{ find_by('col1', 200) : sort by created_at desc
        $result = $find->find_by('col1', 200, "created_at", ORDER_BY_DESC);
        $this->assertEqual(count($result), 3);
        $this->assertEqual($result[0]['id'], 6);
        $this->assertEqual($result[1]['id'], 5);
        $this->assertEqual($result[2]['id'], 4);
        // }}}
        // {{{ find_by('col1', 100) : sort by updated_at asc
        $result = $find->find_by('col1', 100, "updated_at", ORDER_BY_ASC);
        $this->assertEqual(count($result), 3);
        $this->assertEqual($result[0]['id'], 1);
        $this->assertEqual($result[1]['id'], 2);
        $this->assertEqual($result[2]['id'], 3);
        // }}}
        // {{{ find_by('col1', 200) : sort by updated_at desc
        $result = $find->find_by('col1', 200, "updated_at", ORDER_BY_DESC);
        $this->assertEqual(count($result), 3);
        $this->assertEqual($result[0]['id'], 4);
        $this->assertEqual($result[1]['id'], 6);
        $this->assertEqual($result[2]['id'], 5);
        // }}}

        $this->_find_cleanup();
    }

    // }}}
    // {{{ testFindBy_foobar()

    function testFindBy_foobar()
    {
        $this->_find_prepare();
        $find =& new yb_dao_Base_TestFind();

        $yc =& yb_Cache::factory($find->_cache_name);
        $yc->clean();

        // indeed, it is equal to 'col1' find-by :P
        // see yb_dao_Basae_TestDao source code :)

        // {{{ find_by('foobar', 100) : found (default sort by id asc)
        $result = $find->find_by('col1', 100);
        $this->assertEqual(count($result), 3);
        $this->assertEqual($result[0]['id'], 1);
        $this->assertEqual($result[0]['col1'], 100);
        $this->assertEqual($result[0]['col2'], "Base_006");
        $this->assertEqual($result[0]['col3'], 1);
        $this->assertEqual($result[1]['id'], 2);
        $this->assertEqual($result[1]['col1'], 100);
        $this->assertEqual($result[1]['col2'], "Base_001");
        $this->assertEqual($result[1]['col3'], 2);
        $this->assertEqual($result[2]['id'], 3);
        $this->assertEqual($result[2]['col1'], 100);
        $this->assertEqual($result[2]['col2'], "Base_002");
        $this->assertEqual($result[2]['col3'], 1);
        // }}}

        $this->_find_cleanup();
    }

    // }}}
    // {{{ testFindBy_col3()

    function testFindBy_col3()
    {
        $this->_find_prepare();
        $find =& new yb_dao_Base_TestFind();

        $yc =& yb_Cache::factory($find->_cache_name);
        $yc->clean();

        // {{{ find_by('col3', 1) : found (default sort by id asc)
        $result = $find->find_by('col3', 1);
        $this->assertEqual(count($result), 3);
        $this->assertEqual($result[0]['id'], 1);
        $this->assertEqual($result[0]['col1'], 100);
        $this->assertEqual($result[0]['col2'], "Base_006");
        $this->assertEqual($result[0]['col3'], 1);
        $this->assertEqual($result[1]['id'], 3);
        $this->assertEqual($result[1]['col1'], 100);
        $this->assertEqual($result[1]['col2'], "Base_002");
        $this->assertEqual($result[1]['col3'], 1);
        $this->assertEqual($result[2]['id'], 6);
        $this->assertEqual($result[2]['col1'], 200);
        $this->assertEqual($result[2]['col2'], "Base_005");
        $this->assertEqual($result[2]['col3'], 1);
        // }}}
        // {{{ find_by('col3', 2) : found and sort by id desc
        $result = $find->find_by('col3', 2, "id", ORDER_BY_DESC);
        $this->assertEqual(count($result), 2);
        $this->assertEqual($result[0]['id'], 5);
        $this->assertEqual($result[0]['col1'], 200);
        $this->assertEqual($result[0]['col2'], "Base_004");
        $this->assertEqual($result[0]['col3'], 2);
        $this->assertEqual($result[1]['id'], 2);
        $this->assertEqual($result[1]['col1'], 100);
        $this->assertEqual($result[1]['col2'], "Base_001");
        $this->assertEqual($result[1]['col3'], 2);
        // }}}
        // {{{ find_by('col3', 999) : NOT found
        $result = $find->find_by('col3', 999);
        $this->assertEqual(count($result), 0);
        // }}}

        $this->_find_cleanup();
    }

    // }}}
    // {{{ testFindAll()

    function testFindAll()
    {
        $this->_find_prepare();
        $find =& new yb_dao_Base_TestFind();

        $yc =& yb_Cache::factory($find->_cache_name);
        $yc->clean();

        // {{{ find_all() : found : sort by id asc
        $result = $find->find_all();
        $this->assertEqual(count($result), 6);
        $this->assertEqual($result[0]['id'], 1);
        $this->assertEqual($result[0]['col1'], 100);
        $this->assertEqual($result[0]['col2'], "Base_006");
        $this->assertEqual($result[0]['col3'], 1);

        $this->assertEqual($result[1]['id'], 2);
        $this->assertEqual($result[1]['col1'], 100);
        $this->assertEqual($result[1]['col2'], "Base_001");
        $this->assertEqual($result[1]['col3'], 2);

        $this->assertEqual($result[2]['id'], 3);
        $this->assertEqual($result[2]['col1'], 100);
        $this->assertEqual($result[2]['col2'], "Base_002");
        $this->assertEqual($result[2]['col3'], 1);

        $this->assertEqual($result[3]['id'], 4);
        $this->assertEqual($result[3]['col1'], 200);
        $this->assertEqual($result[3]['col2'], "Base_003");
        $this->assertEqual($result[3]['col3'], 3);

        $this->assertEqual($result[4]['id'], 5);
        $this->assertEqual($result[4]['col1'], 200);
        $this->assertEqual($result[4]['col2'], "Base_004");
        $this->assertEqual($result[4]['col3'], 2);

        $this->assertEqual($result[5]['id'], 6);
        $this->assertEqual($result[5]['col1'], 200);
        $this->assertEqual($result[5]['col2'], "Base_005");
        $this->assertEqual($result[5]['col3'], 1);
        // }}}
        // {{{ find_all() : found : sort by id desc
        $result = $find->find_all("id", ORDER_BY_DESC);
        $this->assertEqual(count($result), 6);
        $this->assertEqual($result[0]['id'], 6);
        $this->assertEqual($result[1]['id'], 5);
        $this->assertEqual($result[2]['id'], 4);
        $this->assertEqual($result[3]['id'], 3);
        $this->assertEqual($result[4]['id'], 2);
        $this->assertEqual($result[5]['id'], 1);
        // }}}
        // {{{ find_all() : found : sort by name asc
        $result = $find->find_all("col2", ORDER_BY_ASC);
        $this->assertEqual(count($result), 6);
        $this->assertEqual($result[0]['id'], 2);
        $this->assertEqual($result[1]['id'], 3);
        $this->assertEqual($result[2]['id'], 4);
        $this->assertEqual($result[3]['id'], 5);
        $this->assertEqual($result[4]['id'], 6);
        $this->assertEqual($result[5]['id'], 1);
        // }}}
        // {{{ find_all() : found : sort by name desc
        $result = $find->find_all("col2", ORDER_BY_DESC);
        $this->assertEqual(count($result), 6);
        $this->assertEqual($result[0]['id'], 1);
        $this->assertEqual($result[1]['id'], 6);
        $this->assertEqual($result[2]['id'], 5);
        $this->assertEqual($result[3]['id'], 4);
        $this->assertEqual($result[4]['id'], 3);
        $this->assertEqual($result[5]['id'], 2);
         // }}}

        $this->_find_cleanup();
    }

    // }}}
    // {{{ testFinds_Sort_By_Duplicated_Value_Columns()

    function testFinds_Sort_By_Duplicated_Value_Columns()
    {
        $this->_find_prepare();
        $find =& new yb_dao_Base_TestFind2();

        $yc =& yb_Cache::factory($find->_cache_name);
        $yc->clean();

        // {{{ find_by('id') : multiple found : sort by created_at asc
        $result = $find->find_by('id', array(1, 2, 3), "created_at");
        $this->assertEqual(count($result), 3);
        $this->assertTrue(
            ($result[0]['id'] == 1 && $result[1]['id'] == 2) ||
            ($result[0]['id'] == 2 && $result[1]['id'] == 1)
        );
        $this->assertTrue($result[2]['id'] == 3);
        // }}}
        // {{{ find_by('col1', 200) : multiple found : sort by created_at desc
        $result = $find->find_by('col1', 200, "created_at", ORDER_BY_DESC);
        $this->assertEqual(count($result), 3);
        $this->assertTrue(
            ($result[0]['id'] == 5 && $result[1]['id'] == 6) ||
            ($result[0]['id'] == 6 && $result[1]['id'] == 5)
        );
        $this->assertTrue($result[2]['id'] == 4);
        // }}}
        // {{{ find_by('col3', 3) : multiple found : sort by updated_at asc
        $result = $find->find_by('col3', 3, "updated_at", ORDER_BY_ASC);
        $this->assertEqual(count($result), 3);
        $this->assertEqual($result[0]['id'], 8);
        $this->assertEqual($result[1]['id'], 7);
        $this->assertEqual($result[2]['id'], 9);
        // }}}
        // {{{ find_all() : found : sort by "updated_at" desc
        $result = $find->find_all("updated_at", ORDER_BY_DESC);
        $this->assertEqual(count($result), 9);
        $this->assertEqual($result[0]['id'], 6);
        $this->assertTrue(
            ($result[1]['id'] == 4 && $result[2]['id'] == 5) ||
            ($result[1]['id'] == 5 && $result[2]['id'] == 4)
        );
        $this->assertTrue(
            ($result[3]['id'] == 2 && $result[4]['id'] == 3) ||
            ($result[3]['id'] == 3 && $result[4]['id'] == 2)
        );
        $this->assertTrue(
            ($result[5]['id'] == 1 && $result[6]['id'] == 9) ||
            ($result[5]['id'] == 9 && $result[6]['id'] == 1)
        );
        $this->assertEqual($result[7]['id'], 7);
        $this->assertEqual($result[8]['id'], 8);
        // }}}

        $this->_find_cleanup();
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
