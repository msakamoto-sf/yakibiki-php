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

class yb_dao_User_TestCase extends UnitTestCase
{
    // {{{ testCUD() (entry point)

    function testCUD()
    {
        $root_dir = dirname(__FILE__) . '/tmp/user';
        mkdir($root_dir);

        $seq_dir = $root_dir . '/sequence';
        mkdir($seq_dir);

        $grain_dir = $root_dir . '/grain';
        mkdir($grain_dir);

        $old_dirs = array(
            'seq_dir' => grain_Config::set('grain.dir.sequence', $seq_dir),
            'grain_dir' => grain_Config::set('grain.dir.grain', $grain_dir),
            'chunksize' => grain_Config::set('grain.chunksize.user', 10),
        );

        $this->_testCreate();
        $this->_testUpdate();
        $this->_testDelete();

        grain_Config::set('grain.dir.sequence', $old_dirs['seq_dir']);
        grain_Config::set('grain.dir.grain', $old_dirs['grain_dir']);
        grain_Config::set('grain.chunksize.user', $old_dirs['chunksize']);
        System::rm(" -rf " . $root_dir);
    }

    // }}}
    // {{{ _testCreate()

    function _testCreate()
    {
        $d =& yb_dao_Factory::get('user');
        $yc =& yb_Cache::factory($d->_cache_name);
        $yc->clean();

        // {{{ record#1
        $data = array(
            "mail" => "create01@test.com",
            "name" => "testCreate_User1",
            "status" => YB_USER_STATUS_OK,
            "password" => "buzzword",
            "role" => array("sys", "group"),
            );
        $id = $d->create($data);
        $this->assertEqual($id, 1);
        // }}}
        // {{{ record#2
        $data = array(
            "mail" => "create02@test.com",
            "name" => "testCreate_User2",
            "status" => YB_USER_STATUS_OK,
            "password" => "abcdword",
            "role" => array("sys"),
            );
        $id = $d->create($data);
        $this->assertEqual($id, 2);
        // }}}
        // {{{ record#3
        $data = array(
            "mail" => "create03@test.com",
            "name" => "testCreate_User3",
            "status" => YB_USER_STATUS_OK,
            "password" => "efghword",
            "role" => array("group"),
            );
        $id = $d->create($data);
        $this->assertEqual($id, 3);
        // }}}
        // {{{ record#4
        $data = array(
            "mail" => "create04@test.com",
            "name" => "testCreate_User4",
            "status" => YB_USER_STATUS_OK,
            "password" => "ijklword",
            "role" => '',
            );
        $id = $d->create($data);
        $this->assertEqual($id, 4);
        // }}}

        $records = $d->find_all();
        // {{{ check registered records #1
        $this->assertEqual($records[0]['id'], 1);
        $this->assertEqual($records[0]['mail'], "create01@test.com");
        $this->assertEqual($records[0]['name'], "testCreate_User1");
        $this->assertEqual($records[0]['status'], YB_USER_STATUS_OK);
        $this->assertEqual($records[0]['password'], "buzzword");
        $this->assertTrue(in_array("sys", $records[0]['role']));
        $this->assertTrue(in_array("group", $records[0]['role']));
        // }}}
        // {{{ check registered records #2
        $this->assertEqual($records[1]['id'], 2);
        $this->assertEqual($records[1]['mail'], "create02@test.com");
        $this->assertEqual($records[1]['name'], "testCreate_User2");
        $this->assertEqual($records[1]['status'], YB_USER_STATUS_OK);
        $this->assertEqual($records[1]['password'], "abcdword");
        $this->assertTrue(in_array("sys", $records[1]['role']));
        $this->assertFalse(in_array("group", $records[1]['role']));
        // }}}
        // {{{ check registered records #3
        $this->assertEqual($records[2]['id'], 3);
        $this->assertEqual($records[2]['mail'], "create03@test.com");
        $this->assertEqual($records[2]['name'], "testCreate_User3");
        $this->assertEqual($records[2]['status'], YB_USER_STATUS_OK);
        $this->assertEqual($records[2]['password'], "efghword");
        $this->assertFalse(in_array("sys", $records[2]['role']));
        $this->assertTrue(in_array("group", $records[2]['role']));
        // }}}
        // {{{ check registered records #4
        $this->assertEqual($records[3]['id'], 4);
        $this->assertEqual($records[3]['mail'], "create04@test.com");
        $this->assertEqual($records[3]['name'], "testCreate_User4");
        $this->assertEqual($records[3]['status'], YB_USER_STATUS_OK);
        $this->assertEqual($records[3]['password'], "ijklword");
        $this->assertFalse(in_array("sys", $records[3]['role']));
        $this->assertFalse(in_array("group", $records[3]['role']));
        // }}}
    }

    // }}}
    // {{{ _testUpdate()

    function _testUpdate()
    {
        $d =& yb_dao_Factory::get('user');
        $yc =& yb_Cache::factory($d->_cache_name);
        $yc->clean();

        $olddata = $d->find_by_id(1);

        $data = array(
            "mail" => "bar@foo.com",
            "name" => "testUpdate_User",
            "status" => "abc",
            "password" => "hogehoge",
            "role" => array(),
            );
        $result = $d->update(1, $data);
        $this->assertIdentical($result, 1);

        $results = $d->find_by_id(1);
        $result = $results[0];
        $this->assertEqual($result['id'], 1);
        $this->assertEqual($result['mail'], "bar@foo.com");
        $this->assertEqual($result['name'], "testUpdate_User");
        $this->assertEqual($result['status'], "abc");
        $this->assertEqual($result['password'], "hogehoge");
        $this->assertFalse(in_array("sys", $result['role']));
        $this->assertFalse(in_array("group", $result['role']));

        // update undefined id
        $data = array(
            "mail" => "100@foo.com",
            "name" => "testUpdate_User100",
            "status" => "abc",
            "password" => "hogehoge",
            "role" => array(),
            );
        $result = $d->update(100, $data);
        $this->assertIdentical($result, 0);
    }

    // }}}
    // {{{ _testDelete()

    function _testDelete()
    {
        $d =& yb_dao_Factory::get('user');
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

        $datas = $d->find_by_id(3);
        $this->assertEqual(count($datas), 0);
        $datas = $d->find_by_id(2);
        $this->assertEqual($datas[0]['id'], 2);
        $datas = $d->find_by_id(4);
        $this->assertEqual($datas[0]['id'], 4);

        // delete #4
        $this->assertIdentical($d->delete(4), 1);

        $datas = $d->find_by_id(4);
        $this->assertEqual(count($datas), 0);
        $datas = $d->find_by_id(2);
        $this->assertEqual($datas[0]['id'], 2);

        // delete #2
        $this->assertIdentical($d->delete(2), 1);

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
            'chunksize' => grain_Config::set('grain.chunksize.user', 10),
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
        grain_Config::set('grain.chunksize.user', 
            $this->_old_dirs['chunksize']);
    }

    // }}}
    // {{{ testFinds()

    function testFinds()
    {
        $this->_find_prepare();
        $d =& new yb_dao_User();
        $d->_grain_name = 'user1';

        $yc =& yb_Cache::factory($d->_cache_name);
        $yc->clean();

        // find_by_id() : found
        $result = $d->find_by_id(1);
        $this->assertEqual(count($result), 1);
        $this->assertEqual($result[0]['id'], 1);
        $this->assertEqual($result[0]['mail'], "user01@hoge.com");
        $this->assertEqual($result[0]['name'], "user01");
        $this->assertEqual($result[0]['status'], YB_USER_STATUS_OK);
        $this->assertEqual($result[0]['password'], 'buzzword01');
        $this->assertTrue(in_array("sys", $result[0]['role']));
        $this->assertTrue(in_array("group", $result[0]['role']));

        // find_by_id() : multiple found: sort by id asc(default)
        $result = $d->find_by_id(array(1, 2, 3, 4, 5));
        $this->assertEqual(count($result), 4);

        $this->assertEqual($result[0]['id'], 1);
        $this->assertEqual($result[0]['mail'], "user01@hoge.com");
        $this->assertEqual($result[0]['name'], "user01");
        $this->assertEqual($result[0]['status'], YB_USER_STATUS_OK);
        $this->assertEqual($result[0]['password'], 'buzzword01');
        $this->assertEqual(count($result[0]['role']), 2);
        $this->assertTrue(in_array("sys", $result[0]['role']));
        $this->assertTrue(in_array("group", $result[0]['role']));

        $this->assertEqual($result[1]['id'], 2);
        $this->assertEqual($result[1]['mail'], "user02@hoge.com");
        $this->assertEqual($result[1]['name'], "user02");
        $this->assertEqual($result[1]['status'], YB_USER_STATUS_OK);
        $this->assertEqual($result[1]['password'], 'buzzword02');
        $this->assertEqual(count($result[1]['role']), 1);
        $this->assertTrue(in_array("sys", $result[1]['role']));
        $this->assertFalse(in_array("group", $result[1]['role']));

        $this->assertEqual($result[2]['id'], 3);
        $this->assertEqual($result[2]['mail'], "user03@hoge.com");
        $this->assertEqual($result[2]['name'], "user03");
        $this->assertEqual($result[2]['status'], YB_USER_STATUS_DISABLED);
        $this->assertEqual($result[2]['password'], 'buzzword03');
        $this->assertEqual(count($result[2]['role']), 1);
        $this->assertFalse(in_array("sys", $result[2]['role']));
        $this->assertTrue(in_array("group", $result[2]['role']));

        $this->assertEqual($result[3]['id'], 4);
        $this->assertEqual($result[3]['mail'], "user04@hoge.com");
        $this->assertEqual($result[3]['name'], "user04");
        $this->assertEqual($result[2]['status'], YB_USER_STATUS_DISABLED);
        $this->assertEqual($result[3]['password'], 'buzzword04');
        $this->assertEqual(count($result[3]['role']), 0);

        // find_by_id() : NOT found
        $result = $d->find_by_id(5);
        $this->assertEqual(count($result), 0);

        // find_all() sort by default("id")
        $result = $d->find_all();
        $this->assertEqual(count($result), 4);
        $this->assertEqual($result[0]['id'], "1");
        $this->assertEqual($result[3]['id'], "4");

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
