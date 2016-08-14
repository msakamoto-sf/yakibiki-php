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

class yb_dao_Group_TestCase extends UnitTestCase
{
    // {{{ testCUD() (entry point)

    function testCUD()
    {
        $root_dir = dirname(__FILE__) . '/tmp/group';
        mkdir($root_dir);

        $seq_dir = $root_dir . '/sequence';
        mkdir($seq_dir);

        $grain_dir = $root_dir . '/grain';
        mkdir($grain_dir);

        $old_dirs = array(
            'seq_dir' => grain_Config::set('grain.dir.sequence', $seq_dir),
            'grain_dir' => grain_Config::set('grain.dir.grain', $grain_dir),
            'chunksize' => grain_Config::set('grain.chunksize.group', 10),
        );

        $this->_testCreate();
        $this->_testUpdate();
        $this->_testDelete();

        grain_Config::set('grain.dir.sequence', $old_dirs['seq_dir']);
        grain_Config::set('grain.dir.grain', $old_dirs['grain_dir']);
        grain_Config::set('grain.chunksize.group', $old_dirs['chunksize']);
        System::rm(" -rf " . $root_dir);
    }

    // }}}
    // {{{ _testCreate()

    function _testCreate()
    {
        $d =& yb_dao_Factory::get('group');
        $yc =& yb_Cache::factory($d->_cache_name);
        $yc->clean();

        // record#1
        $data = array(
            "owner" => 1,
            "name" => "testCreate_Group1",
            );
        $id = $d->create($data);
        $this->assertEqual($id, 1);

        // record#2
        $data = array(
            "owner" => 1,
            "name" => "testCreate_Group2",
            );
        $id = $d->create($data);
        $this->assertEqual($id, 2);

        // record#3 (with empty group mates)
        $data = array(
            "owner" => 2,
            "name" => "testCreate_Group3",
            "mates" => array(),
            );
        $id = $d->create($data);
        $this->assertEqual($id, 3);

        // record#4
        $data = array(
            "owner" => 2,
            "name" => "testCreate_Group4",
            );
        $id = $d->create($data);
        $this->assertEqual($id, 4);

        // record#5 (with 1 group mates)
        $data = array(
            "owner" => 3,
            "name" => "testCreate_Group5",
            "mates" => array(10),
            );
        $id = $d->create($data);
        $this->assertEqual($id, 5);

        // record#6 (with 3 group mates)
        $data = array(
            "owner" => 3,
            "name" => "testCreate_Group6",
            "mates" => array(10, 20, 30),
            );
        $id = $d->create($data);
        $this->assertEqual($id, 6);

        $records = $d->find_all();

        // check registered records #1
        $this->assertEqual($records[0]['id'], 1);
        $this->assertEqual($records[0]['owner'], 1);
        $this->assertEqual($records[0]['name'], "testCreate_Group1");

        // check registered records #2
        $this->assertEqual($records[1]['id'], 2);
        $this->assertEqual($records[1]['owner'], 1);
        $this->assertEqual($records[1]['name'], "testCreate_Group2");

        // check registered records #3
        $this->assertEqual($records[2]['id'], 3);
        $this->assertEqual($records[2]['owner'], 2);
        $this->assertEqual($records[2]['name'], "testCreate_Group3");
        $this->assertTrue(is_array($records[2]['mates']));
        $this->assertEqual(count($records[2]['mates']), 0);

        // check registered records #4
        $this->assertEqual($records[3]['id'], 4);
        $this->assertEqual($records[3]['owner'], 2);
        $this->assertEqual($records[3]['name'], "testCreate_Group4");
        $this->assertTrue(is_array($records[3]['mates']));
        $this->assertEqual(count($records[3]['mates']), 0);

        // check registered records #5
        $this->assertEqual($records[4]['id'], 5);
        $this->assertEqual($records[4]['owner'], 3);
        $this->assertEqual($records[4]['name'], "testCreate_Group5");
        $this->assertTrue(is_array($records[4]['mates']));
        $this->assertEqual(count($records[4]['mates']), 1);
        $this->assertTrue(in_array(10, $records[4]['mates']));

        // check registered records #6
        $this->assertEqual($records[5]['id'], 6);
        $this->assertEqual($records[5]['owner'], 3);
        $this->assertEqual($records[5]['name'], "testCreate_Group6");
        $this->assertTrue(is_array($records[5]['mates']));
        $this->assertEqual(count($records[5]['mates']), 3);
        $this->assertTrue(in_array(10, $records[5]['mates']));
        $this->assertTrue(in_array(20, $records[5]['mates']));
        $this->assertTrue(in_array(30, $records[5]['mates']));
    }

    // }}}
    // {{{ _testUpdate()

    function _testUpdate()
    {
        $d =& yb_dao_Factory::get('group');
        $yc =& yb_Cache::factory($d->_cache_name);
        $yc->clean();

        // update#1 : owner, name, mates
        $data = array(
            "owner" => 9,
            "name" => "testUpdate_Group1",
            "mates" => array(1, 2, 3),
            );
        $result = $d->update(1, $data);
        $this->assertIdentical($result, 1);

        $results = $d->find_by_id(1);
        $result = $results[0];
        $this->assertEqual($result['id'], 1);
        $this->assertEqual($result['owner'], 9);
        $this->assertEqual($result['name'], "testUpdate_Group1");
        $this->assertTrue(is_array($result['mates']));
        $this->assertEqual(count($result['mates']), 3);
        $this->assertTrue(in_array(1, $result['mates']));
        $this->assertTrue(in_array(2, $result['mates']));
        $this->assertTrue(in_array(3, $result['mates']));

        // update#2 : owner, name

        // save old data
        $result = $d->find_by_id(2);
        $olddata = $result[0];

        $data = array(
            "owner" => 10,
            "name" => "testUpdate_Group2",
            );
        $result = $d->update(2, $data);
        $this->assertIdentical($result, 1);

        $results = $d->find_by_id(2);
        $newdata = $results[0];
        $this->assertEqual($newdata['id'], $olddata['id']);
        $this->assertEqual($newdata['owner'], 10);
        $this->assertEqual($newdata['name'], "testUpdate_Group2");
        $this->assertEqual($newdata['created_at'], $olddata['created_at']);
        $diff_mates = array_diff($olddata['mates'], $newdata['mates']);
        $this->assertEqual(count($diff_mates), 0);

        // update#3 : name

        // save old data
        $result = $d->find_by_id(3);
        $olddata = $result[0];

        $data = array(
            "name" => "testUpdate_Group3",
            );
        $result = $d->update(3, $data);
        $this->assertIdentical($result, 1);

        $results = $d->find_by_id(3);
        $newdata = $results[0];
        $this->assertEqual($newdata['id'], $olddata['id']);
        $this->assertEqual($newdata['owner'], $olddata['owner']);
        $this->assertEqual($newdata['name'], "testUpdate_Group3");
        $this->assertEqual($newdata['created_at'], $olddata['created_at']);
        $diff_mates = array_diff($olddata['mates'], $newdata['mates']);
        $this->assertEqual(count($diff_mates), 0);

        // update#999 : UNDEFINED group

        $data = array(
            "owner" => 9,
            "name" => "testUpdate_Group",
            );
        $result = $d->update(999, $data);
        $this->assertIdentical($result, 0);
    }

    // }}}
    // {{{ _testDelete()

    function _testDelete()
    {
        $d =& yb_dao_Factory::get('group');
        $yc =& yb_Cache::factory($d->_cache_name);
        $yc->clean();

        // delete #1
        $this->assertIdentical($d->delete(1), 1);
        $datas = $d->find_by_id(1);
        $this->assertEqual(count($datas), 0);

        // delete #100 (undefined)
        $this->assertIdentical($d->delete(100), 0);

        // delete #3
        $this->assertIdentical($d->delete(3), 1);

        $datas = $d->find_all();
        $this->assertEqual(count($datas), 4);
        $this->assertEqual($datas[0]['id'], 2);
        $this->assertEqual($datas[1]['id'], 4);
        $this->assertEqual($datas[2]['id'], 5);
        $this->assertEqual($datas[3]['id'], 6);

        // delete #6
        $this->assertIdentical($d->delete(6), 1);

        $datas = $d->find_all();
        $this->assertEqual(count($datas), 3);
        $this->assertEqual($datas[0]['id'], 2);
        $this->assertEqual($datas[1]['id'], 4);
        $this->assertEqual($datas[2]['id'], 5);

        // delete #2
        $this->assertIdentical($d->delete(2), 1);

        $datas = $d->find_by_id(2);
        $this->assertEqual(count($datas), 0);

        // delete #1 (undefined)
        $this->assertIdentical($d->delete(1), 0);

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
            'chunksize' => grain_Config::set('grain.chunksize.group', 10),
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
        grain_Config::set('grain.chunksize.group', 
            $this->_old_dirs['chunksize']);
    }

    // }}}
    // {{{ testFindById()

    function testFindById()
    {
        $this->_find_prepare();
        $d =& new yb_dao_Group();
        $d->_grain_name = 'group1';

        $yc =& yb_Cache::factory($d->_cache_name);
        $yc->clean();

        // find_by_id() : found
        $result = $d->find_by_id(1);
        $this->assertEqual(count($result), 1);
        $this->assertEqual($result[0]['id'], 1);
        $this->assertEqual($result[0]['owner'], 500);
        $this->assertEqual($result[0]['name'], "Group_FindById04");

        // find_by_id() : NOT found
        $result = $d->find_by_id(6);
        $this->assertEqual(count($result), 0);

        // find_by_id() : multiple id found
        $result = $d->find_by_id(array(1, 3, 4));
        $this->assertEqual(count($result), 3);
        $this->assertEqual($result[0]['id'], 1);
        $this->assertEqual($result[0]['owner'], 500);
        $this->assertEqual($result[0]['name'], "Group_FindById04");
        $this->assertEqual($result[1]['id'], 3);
        $this->assertEqual($result[1]['owner'], 200);
        $this->assertEqual($result[1]['name'], "Group_FindById01");
        $this->assertEqual($result[2]['id'], 4);
        $this->assertEqual($result[2]['owner'], 300);
        $this->assertEqual($result[2]['name'], "Group_FindById02");

        // find_by_id() : multiple id and sort by id desc
        $result = $d->find_by_id(
            array(1, 2, 3, 4, 5), "id", ORDER_BY_DESC);
        $this->assertEqual(count($result), 5);
        $this->assertEqual($result[0]['id'], 5);
        $this->assertEqual($result[1]['id'], 4);
        $this->assertEqual($result[2]['id'], 3);
        $this->assertEqual($result[3]['id'], 2);
        $this->assertEqual($result[4]['id'], 1);

        // find_by_id() : multiple id and sort by owner asc
        $result = $d->find_by_id(array(1, 3, 4), "owner", ORDER_BY_ASC);
        $this->assertEqual(count($result), 3);
        $this->assertEqual($result[0]['id'], 3);
        $this->assertEqual($result[1]['id'], 4);
        $this->assertEqual($result[2]['id'], 1);

        // find_by_id() : multiple id and sort by owner desc
        $result = $d->find_by_id(
            array(1, 2, 3, 4, 5), "owner", ORDER_BY_DESC);
        $this->assertEqual(count($result), 5);
        $this->assertEqual($result[0]['id'], 1);
        $this->assertEqual($result[1]['id'], 5);
        $this->assertEqual($result[2]['id'], 4);
        $this->assertEqual($result[3]['id'], 3);
        $this->assertEqual($result[4]['id'], 2);

        // find_by_id() : multiple id and sort by name asc
        $result = $d->find_by_id(
            array(1, 2, 3, 4, 5), "name", ORDER_BY_ASC);
        $this->assertEqual(count($result), 5);
        $this->assertEqual($result[0]['id'], 3);
        $this->assertEqual($result[1]['id'], 4);
        $this->assertEqual($result[2]['id'], 5);
        $this->assertEqual($result[3]['id'], 1);
        $this->assertEqual($result[4]['id'], 2);

        // find_by_id() : multiple id and sort by name desc
        $result = $d->find_by_id(
            array(1, 2, 5), "name", ORDER_BY_DESC);
        $this->assertEqual(count($result), 3);
        $this->assertEqual($result[0]['id'], 2);
        $this->assertEqual($result[1]['id'], 1);
        $this->assertEqual($result[2]['id'], 5);

        $this->_find_cleanup();
    }

    // }}}
    // {{{ testFindByOwner()

    function testFindByOwner()
    {
        $this->_find_prepare();
        $d =& new yb_dao_Group();
        $d->_grain_name = 'group2';

        $yc =& yb_Cache::factory($d->_cache_name);
        $yc->clean();

        // find_by_owner(100) : found
        $result = $d->find_by_owner(100);
        $this->assertEqual(count($result), 2);
        $this->assertEqual($result[0]['id'], 1);
        $this->assertEqual($result[0]['owner'], 100);
        $this->assertEqual($result[0]['name'], "Group_FindByOwner01");
        $this->assertEqual($result[1]['id'], 2);
        $this->assertEqual($result[1]['owner'], 100);
        $this->assertEqual($result[1]['name'], "Group_FindByOwner02");

        // find_by_owner(200) : found and sort by id desc
        $result = $d->find_by_owner(200, "id", ORDER_BY_DESC);
        $this->assertEqual(count($result), 2);
        $this->assertEqual($result[0]['id'], 4);
        $this->assertEqual($result[0]['owner'], 200);
        $this->assertEqual($result[0]['name'], "Group_FindByOwner04");
        $this->assertEqual($result[1]['id'], 3);
        $this->assertEqual($result[1]['owner'], 200);
        $this->assertEqual($result[1]['name'], "Group_FindByOwner03");

        // find_by_owner(300) : NOT found
        $result = $d->find_by_owner(300);
        $this->assertEqual(count($result), 0);

        // find_by_owner(100) : sort by created_at asc
        $result = $d->find_by_owner(100, "created_at");
        $this->assertEqual(count($result), 2);
        $this->assertEqual($result[0]['id'], 2);
        $this->assertEqual($result[1]['id'], 1);

        // find_by_owner(200) : sort by created_at desc
        $result = $d->find_by_owner(200, "created_at", ORDER_BY_DESC);
        $this->assertEqual(count($result), 2);
        $this->assertEqual($result[0]['id'], 4);
        $this->assertEqual($result[1]['id'], 3);

        // find_by_owner(100) : sort by updated_at asc
        $result = $d->find_by_owner(100, "updated_at", ORDER_BY_ASC);
        $this->assertEqual(count($result), 2);
        $this->assertEqual($result[0]['id'], 1);
        $this->assertEqual($result[1]['id'], 2);

        // find_by_owner(200) : sort by updated_at desc
        $result = $d->find_by_owner(200, "updated_at", ORDER_BY_DESC);
        $this->assertEqual(count($result), 2);
        $this->assertEqual($result[0]['id'], 4);
        $this->assertEqual($result[1]['id'], 3);

        $this->_find_cleanup();
    }

    // }}}
    // {{{ testFindAll()

    function testFindAll()
    {
        $this->_find_prepare();
        $d =& new yb_dao_Group();
        $d->_grain_name = 'group3';

        $yc =& yb_Cache::factory($d->_cache_name);
        $yc->clean();

        // find_all() : found : sort by id asc
        $result = $d->find_all();
        $this->assertEqual(count($result), 5);
        $this->assertEqual($result[0]['id'], 1);
        $this->assertEqual($result[0]['owner'], 500);
        $this->assertEqual($result[0]['name'], "Group_FindAll04");
        $this->assertEqual($result[1]['id'], 2);
        $this->assertEqual($result[1]['owner'], 100);
        $this->assertEqual($result[1]['name'], "Group_FindAll05");
        $this->assertEqual($result[2]['id'], 3);
        $this->assertEqual($result[3]['id'], 4);
        $this->assertEqual($result[4]['id'], 5);

        // find_all() : found : sort by id desc
        $result = $d->find_all("id", ORDER_BY_DESC);
        $this->assertEqual(count($result), 5);
        $this->assertEqual($result[0]['id'], 5);
        $this->assertEqual($result[1]['id'], 4);
        $this->assertEqual($result[2]['id'], 3);
        $this->assertEqual($result[3]['id'], 2);
        $this->assertEqual($result[4]['id'], 1);

        // find_all() : found : sort by owner asc
        $result = $d->find_all("owner", ORDER_BY_ASC);
        $this->assertEqual(count($result), 5);
        $this->assertEqual($result[0]['id'], 2);
        $this->assertEqual($result[1]['id'], 3);
        $this->assertEqual($result[2]['id'], 4);
        $this->assertEqual($result[3]['id'], 5);
        $this->assertEqual($result[4]['id'], 1);

        // find_all() : found : sort by owner desc
        $result = $d->find_all("owner", ORDER_BY_DESC);
        $this->assertEqual(count($result), 5);
        $this->assertEqual($result[0]['id'], 1);
        $this->assertEqual($result[1]['id'], 5);
        $this->assertEqual($result[2]['id'], 4);
        $this->assertEqual($result[3]['id'], 3);
        $this->assertEqual($result[4]['id'], 2);

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
