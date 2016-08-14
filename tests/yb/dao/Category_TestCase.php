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

class yb_dao_Category_TestCase extends UnitTestCase
{
    // {{{ testCUD() (entry point)

    function testCUD()
    {
        $root_dir = dirname(__FILE__) . '/tmp/category';
        mkdir($root_dir);

        $seq_dir = $root_dir . '/sequence';
        mkdir($seq_dir);

        $grain_dir = $root_dir . '/grain';
        mkdir($grain_dir);

        $old_dirs = array(
            'seq_dir' => grain_Config::set('grain.dir.sequence', $seq_dir),
            'grain_dir' => grain_Config::set('grain.dir.grain', $grain_dir),
            'chunksize' => grain_Config::set('grain.chunksize.category', 10),
        );

        $this->_testCreate();
        $this->_testUpdate();
        $this->_testDelete();

        grain_Config::set('grain.dir.sequence', $old_dirs['seq_dir']);
        grain_Config::set('grain.dir.grain', $old_dirs['grain_dir']);
        grain_Config::set('grain.chunksize.category', $old_dirs['chunksize']);
        System::rm(" -rf " . $root_dir);
    }

    // }}}
    // {{{ _testCreate()

    function _testCreate()
    {
        $d =& yb_dao_Factory::get('category');
        $yc =& yb_Cache::factory($d->_cache_name);
        $yc->clean();

        // create 4 records
        $data = array(
            "owner" => 100,
            "name" => "testCreate_Category1",
        );
        $id = $d->create($data);
        $this->assertEqual($id, 1);

        $data = array(
            "owner" => 100,
            "name" => "testCreate_Category2",
        );
        $id = $d->create($data);
        $this->assertEqual($id, 2);

        $data = array(
            "owner" => 200,
            "name" => "testCreate_Category3",
        );
        $id = $d->create($data);
        $this->assertEqual($id, 3);

        $data = array(
            "owner" => 200,
            "name" => "testCreate_Category4",
        );
        $id = $d->create($data);
        $this->assertEqual($id, 4);

        $data = array(
            "owner" => 300,
            "name" => "testCreate_Category5",
        );
        $id = $d->create($data);
        $this->assertEqual($id, 5);

        $records = $d->find_all();

        // check registered records
        $this->assertEqual($records[0]['id'], 1);
        $this->assertEqual($records[0]['owner'], 100);
        $this->assertEqual($records[0]['name'], "testCreate_Category1");
        $this->assertEqual($records[1]['id'], 2);
        $this->assertEqual($records[1]['owner'], 100);
        $this->assertEqual($records[1]['name'], "testCreate_Category2");
        $this->assertEqual($records[2]['id'], 3);
        $this->assertEqual($records[2]['owner'], 200);
        $this->assertEqual($records[2]['name'], "testCreate_Category3");
        $this->assertEqual($records[3]['id'], 4);
        $this->assertEqual($records[3]['owner'], 200);
        $this->assertEqual($records[3]['name'], "testCreate_Category4");
        $this->assertEqual($records[4]['id'], 5);
        $this->assertEqual($records[4]['owner'], 300);
        $this->assertEqual($records[4]['name'], "testCreate_Category5");
    }

    // }}}
    // {{{ _testUpdate()

    function _testUpdate()
    {
        $d =& yb_dao_Factory::get('category');
        $yc =& yb_Cache::factory($d->_cache_name);
        $yc->clean();

        // update#1 : owner, category
        $data = array(
            'owner' => 900,
            'name' => 'testUpdate_Category1',
        );
        $result = $d->update(1, $data);
        $this->assertIdentical($result, 1);

        $result = $d->find_by_id(1);
        $this->assertEqual($result[0]['id'], 1);
        $this->assertEqual($result[0]['owner'], 900);
        $this->assertEqual($result[0]['name'], 'testUpdate_Category1');

        // update#4 : "id" can't be modified
        $result = $d->find_by_id(4);
        $olddata = $result[0];
        $data = array(
            'id' => 9,
            'owner' => $olddata['owner'],
            'name' => $olddata['name'],
        );
        $result = $d->update(4, $data);
        $this->assertIdentical($result, 1);

        $result = $d->find_by_id(4);
        $this->assertEqual($result[0]['id'], $olddata['id']);
        $this->assertEqual($result[0]['owner'], $olddata['owner']);
        $this->assertEqual($result[0]['name'], $olddata['name']);

        // update#999 : UNDEFINED id
        $data = array(
            'owner' => 9,
            'name' => 'testUpdate_Category',
            );
        $result = $d->update(999, $data);
        $this->assertIdentical($result, 0);
    }

    // }}}
    // {{{ _testDelete()

    function _testDelete()
    {
        $d =& yb_dao_Factory::get('category');
        $yc =& yb_Cache::factory($d->_cache_name);
        $yc->clean();

        // delete #1
        $result = $d->delete(1);
        $this->assertIdentical($result, 1);
        $datas = $d->find_by_id(1);
        $this->assertEqual(count($datas), 0);

        // delete #100 (undefined)
        $result = $d->delete(100);
        $this->assertIdentical($result, 0);

        // delete #3
        $result = $d->delete(3);
        $this->assertIdentical($result, 1);
        $datas = $d->find_all();
        $this->assertEqual(count($datas), 3);
        $this->assertEqual($datas[0]['id'], 2);
        $this->assertEqual($datas[1]['id'], 4);
        $this->assertEqual($datas[2]['id'], 5);

        // delete #5
        $result = $d->delete(5);
        $this->assertIdentical($result, 1);
        $datas = $d->find_all();
        $this->assertEqual(count($datas), 2);
        $this->assertEqual($datas[0]['id'], 2);
        $this->assertEqual($datas[1]['id'], 4);

        // delete #2
        $result = $d->delete(2);
        $this->assertIdentical($result, 1);
        $datas = $d->find_by_id(2);
        $this->assertEqual(count($datas), 0);

        // delete #1 (undefined)
        $result = $d->delete(1);
        $this->assertIdentical($result, 0);
        $datas = $d->find_by_id(1);
        $this->assertEqual(count($datas), 0);
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
            'chunksize' => grain_Config::set('grain.chunksize.category', 10),
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
        grain_Config::set('grain.chunksize.category', 
            $this->_old_dirs['chunksize']);
    }

    // }}}
    // {{{ testFindById()

    function testFindById()
    {
        $this->_find_prepare();
        $d =& new yb_dao_Category();
        $d->_grain_name = 'category1';

        $yc =& yb_Cache::factory($d->_cache_name);
        $yc->clean();

        // find_by_id() : found
        $result = $d->find_by_id(1);
        $this->assertEqual(count($result), 1);
        $this->assertEqual($result[0]['id'], 1);
        $this->assertEqual($result[0]['owner'], 100);
        $this->assertEqual($result[0]['name'], "Category_4");

        // find_by_id() : multiple id found and sort by category asc (default)
        $result = $d->find_by_id(array(2, 4, 5));
        $this->assertEqual(count($result), 3);
        $this->assertEqual($result[0]['id'], 4);
        $this->assertEqual($result[0]['owner'], 200);
        $this->assertEqual($result[0]['name'], "Category_2");
        $this->assertEqual($result[1]['id'], 5);
        $this->assertEqual($result[1]['owner'], 200);
        $this->assertEqual($result[1]['name'], "Category_3");
        $this->assertEqual($result[2]['id'], 2);
        $this->assertEqual($result[2]['owner'], 100);
        $this->assertEqual($result[2]['name'], "Category_5");

        // find_by_id() : multiple id found and sort by category desc
        $result = $d->find_by_id(array(1, 2, 3, 9), // include NODEF id
            'name', ORDER_BY_DESC);
        $this->assertEqual($result[0]['id'], 2);
        $this->assertEqual($result[0]['owner'], 100);
        $this->assertEqual($result[0]['name'], "Category_5");
        $this->assertEqual($result[1]['id'], 1);
        $this->assertEqual($result[1]['owner'], 100);
        $this->assertEqual($result[1]['name'], "Category_4");
        $this->assertEqual($result[2]['id'], 3);
        $this->assertEqual($result[2]['owner'], 200);
        $this->assertEqual($result[2]['name'], "Category_1");

        // find_by_id() : NOT found
        $result = $d->find_by_id(999);
        $this->assertEqual(count($result), 0);

        $this->_find_cleanup();
    }

    // }}}
    // {{{ testFindByOwner()

    function testFindByOwner()
    {
        $this->_find_prepare();
        $d =& new yb_dao_Category();
        $d->_grain_name = 'category1';

        $yc =& yb_Cache::factory($d->_cache_name);
        $yc->clean();

        // find_by_owner(100) : found (sort by category asc :default)
        $result = $d->find_by_owner(100);
        $this->assertEqual(count($result), 2);
        $this->assertEqual($result[0]['id'], 1);
        $this->assertEqual($result[0]['owner'], 100);
        $this->assertEqual($result[0]['name'], 'Category_4');
        $this->assertEqual($result[1]['id'], 2);
        $this->assertEqual($result[1]['owner'], 100);
        $this->assertEqual($result[1]['name'], 'Category_5');

        // find_by_owner(999) : NOT found
        $result = $d->find_by_owner(999);
        $this->assertEqual(count($result), 0);

        // find_by_owner(100) : sort by category desc
        $result = $d->find_by_owner(100, 'name', ORDER_BY_DESC);
        $this->assertEqual(count($result), 2);
        $this->assertEqual($result[0]['id'], 2);
        $this->assertEqual($result[1]['id'], 1);

        // find_by_owner(200) : sort by id asc
        $result = $d->find_by_owner(200, 'id');
        $this->assertEqual(count($result), 3);
        $this->assertEqual($result[0]['id'], 3);
        $this->assertEqual($result[1]['id'], 4);
        $this->assertEqual($result[2]['id'], 5);

        // find_by_owner(300) : sort by id desc
        $result = $d->find_by_owner(200, 'id', ORDER_BY_DESC);
        $this->assertEqual(count($result), 3);
        $this->assertEqual($result[0]['id'], 5);
        $this->assertEqual($result[1]['id'], 4);
        $this->assertEqual($result[2]['id'], 3);

        // (created_at and updated_at field sort is tested 
        // in testFindAll().)

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
