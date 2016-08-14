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

class yb_dao_Acl_TestCase extends UnitTestCase
{
    // {{{ testCUD() (entry point)

    function testCUD()
    {
        $root_dir = dirname(__FILE__) . '/tmp/acl';
        mkdir($root_dir);

        $seq_dir = $root_dir . '/sequence';
        mkdir($seq_dir);

        $grain_dir = $root_dir . '/grain';
        mkdir($grain_dir);

        $old_dirs = array(
            'seq_dir' => grain_Config::set('grain.dir.sequence', $seq_dir),
            'grain_dir' => grain_Config::set('grain.dir.grain', $grain_dir),
            'chunksize' => grain_Config::set('grain.chunksize.acl', 10),
        );

        $this->_testCreate();
        $this->_testUpdate();
        $this->_testDelete();

        grain_Config::set('grain.dir.sequence', $old_dirs['seq_dir']);
        grain_Config::set('grain.dir.grain', $old_dirs['grain_dir']);
        grain_Config::set('grain.chunksize.acl', $old_dirs['chunksize']);
        System::rm(" -rf " . $root_dir);
    }

    // }}}
    // {{{ _testCreate()

    function _testCreate()
    {
        $d =& yb_dao_Factory::get('acl');
        $yc =& yb_Cache::factory($d->_cache_name);
        $yc->clean();

        // record#1 (with user perm)
        $data = array(
            'owner' => 100,
            'name' => 'testCreate_Acl1_1',
            'policy' => YB_ACL_POLICY_POSI,
            'perms' => array(
                array(
                    'type' => YB_ACL_TYPE_USER,
                    'id' => 100,
                    'perm' => YB_ACL_PERM_NONE,
                ),
            ),
        );
        $id = $d->create($data);
        $this->assertEqual($id, 1);

        // record#2 (with group perm)
        $data = array(
            'owner' => 100,
            'name' => 'testCreate_Acl1_2',
            'policy' => YB_ACL_POLICY_NEGA,
            'perms' => array(
                array(
                    'type' => YB_ACL_TYPE_GROUP,
                    'id' => 20,
                    'perm' => YB_ACL_PERM_READWRITE,
                ),
            ),
        );
        $id = $d->create($data);
        $this->assertEqual($id, 2);

        // record#3 (without perms entry)
        $data = array(
            'owner' => 200,
            'name' => 'testCreate_Acl2_3',
            'policy' => YB_ACL_POLICY_POSI,
            );
        $id = $d->create($data);
        $this->assertEqual($id, 3);

        // record#4 (with empty perms array)
        $data = array(
            'owner' => 200,
            'name' => 'testCreate_Acl2_4',
            'policy' => YB_ACL_POLICY_NEGA,
            'perms' => array(),
            );
        $id = $d->create($data);
        $this->assertEqual($id, 4);

        // record#5 (with complex perms)
        $data = array(
            'owner' => 300,
            'name' => 'testCreate_Acl3_5',
            'policy' => YB_ACL_POLICY_POSI,
            'perms' => array(
                array(
                    'type' => YB_ACL_TYPE_USER,
                    'id' => 100,
                    'perm' => YB_ACL_PERM_READ,
                ),
                array(
                    'type' => YB_ACL_TYPE_GROUP,
                    'id' => 200,
                    'perm' => YB_ACL_PERM_NONE,
                ),
            ),
        );
        $id = $d->create($data);
        $this->assertEqual($id, 5);

        $records = $d->find_all();

        // check registered records #1
        $this->assertEqual($records[0]['id'], 1);
        $this->assertEqual($records[0]['owner'], 100);
        $this->assertEqual($records[0]['name'], 'testCreate_Acl1_1');
        $this->assertEqual($records[0]['policy'], YB_ACL_POLICY_POSI);
        $perms = $records[0]['perms'];
        $this->assertEqual(count($perms), 1);
        $this->assertEqual($perms[0]['type'], YB_ACL_TYPE_USER);
        $this->assertEqual($perms[0]['id'], 100);
        $this->assertEqual($perms[0]['perm'], YB_ACL_PERM_NONE);

        // check registered records #2
        $this->assertEqual($records[1]['id'], 2);
        $this->assertEqual($records[1]['owner'], 100);
        $this->assertEqual($records[1]['name'], 'testCreate_Acl1_2');
        $this->assertEqual($records[1]['policy'], YB_ACL_POLICY_NEGA);
        $perms = $records[1]['perms'];
        $this->assertEqual(count($perms), 1);
        $this->assertEqual($perms[0]['type'], YB_ACL_TYPE_GROUP);
        $this->assertEqual($perms[0]['id'], 20);
        $this->assertEqual($perms[0]['perm'], YB_ACL_PERM_READWRITE);

        // check registered records #3
        $this->assertEqual($records[2]['id'], 3);
        $this->assertEqual($records[2]['owner'], 200);
        $this->assertEqual($records[2]['name'], 'testCreate_Acl2_3');
        $this->assertEqual($records[2]['policy'], YB_ACL_POLICY_POSI);
        $perms = $records[2]['perms'];
        $this->assertEqual(count($perms), 0);

        // check registered records #4
        $this->assertEqual($records[3]['id'], 4);
        $this->assertEqual($records[3]['owner'], 200);
        $this->assertEqual($records[3]['name'], 'testCreate_Acl2_4');
        $this->assertEqual($records[3]['policy'], YB_ACL_POLICY_NEGA);
        $perms = $records[3]['perms'];
        $this->assertEqual(count($perms), 0);

        // check registered records #5
        $this->assertEqual($records[4]['id'], 5);
        $this->assertEqual($records[4]['owner'], 300);
        $this->assertEqual($records[4]['name'], 'testCreate_Acl3_5');
        $this->assertEqual($records[4]['policy'], YB_ACL_POLICY_POSI);
        $perms = $records[4]['perms'];
        $this->assertEqual(count($perms), 2);
        $this->assertEqual($perms[0]['type'], YB_ACL_TYPE_USER);
        $this->assertEqual($perms[0]['id'], 100);
        $this->assertEqual($perms[0]['perm'], YB_ACL_PERM_READ);
        $this->assertEqual($perms[1]['type'], YB_ACL_TYPE_GROUP);
        $this->assertEqual($perms[1]['id'], 200);
        $this->assertEqual($perms[1]['perm'], YB_ACL_PERM_NONE);
    }

    // }}}
    // {{{ _testUpdate()

    function _testUpdate()
    {
        $d =& yb_dao_Factory::get('acl');
        $yc =& yb_Cache::factory($d->_cache_name);
        $yc->clean();

        // update#1 : owner, name, policy, perms
        $data = array(
            'owner' => 900,
            'name' => 'testUpdate_Acl1_1',
            'policy' => YB_ACL_POLICY_NEGA,
            'perms' => array(
                array(
                    'type' => YB_ACL_TYPE_GROUP,
                    'id' => 900,
                    'perm' => YB_ACL_PERM_READWRITE,
                ),
                array(
                    'type' => YB_ACL_TYPE_USER,
                    'id' => 100,
                    'perm' => YB_ACL_PERM_READ,
                ),
            ),
        );
        $result = $d->update(1, $data);
        $this->assertIdentical($result, 1);

        $result = $d->find_by_id(1);

        $this->assertEqual($result[0]['id'], 1);
        $this->assertEqual($result[0]['owner'], 900);
        $this->assertEqual($result[0]['name'], 'testUpdate_Acl1_1');
        $this->assertEqual($result[0]['policy'], YB_ACL_POLICY_NEGA);
        $perms = $result[0]['perms'];
        $this->assertEqual(count($perms), 2);
        $this->assertEqual($perms[0]['type'], YB_ACL_TYPE_GROUP);
        $this->assertEqual($perms[0]['id'], 900);
        $this->assertEqual($perms[0]['perm'], YB_ACL_PERM_READWRITE);
        $this->assertEqual($perms[1]['type'], YB_ACL_TYPE_USER);
        $this->assertEqual($perms[1]['id'], 100);
        $this->assertEqual($perms[1]['perm'], YB_ACL_PERM_READ);

        // update#2 : perms shrinks to empty
        $result = $d->find_by_id(2);
        $olddata = $result[0];
        $data = array(
            'perms' => array(),
        );
        $result = $d->update(2, $data);
        $this->assertIdentical($result, 1);

        $result = $d->find_by_id(2);
        $this->assertEqual($result[0]['id'], $olddata['id']);
        $this->assertEqual($result[0]['owner'], $olddata['owner']);
        $this->assertEqual($result[0]['name'], $olddata['name']);
        $this->assertEqual($result[0]['policy'], $olddata['policy']);
        $perms = $result[0]['perms'];
        $this->assertEqual(count($perms), 0);

        // update#3 : perms expand one item
        $result = $d->find_by_id(3);
        $olddata = $result[0];
        $data = array(
            'owner' => $olddata['owner'],
            'name' => $olddata['name'],
            'policy' => $olddata['policy'],
            'perms' => array(
                array(
                    'type' => YB_ACL_TYPE_USER,
                    'id' => 309,
                    'perm' => YB_ACL_PERM_NONE,
                ),
            ),
        );
        $result = $d->update(3, $data);
        $this->assertIdentical($result, 1);

        $result = $d->find_by_id(3);
        $this->assertEqual($result[0]['id'], $olddata['id']);
        $this->assertEqual($result[0]['owner'], $olddata['owner']);
        $this->assertEqual($result[0]['name'], $olddata['name']);
        $this->assertEqual($result[0]['policy'], $olddata['policy']);
        $perms = $result[0]['perms'];
        $this->assertEqual(count($perms), 1);
        $this->assertEqual($perms[0]['type'], YB_ACL_TYPE_USER);
        $this->assertEqual($perms[0]['id'], 309);
        $this->assertEqual($perms[0]['perm'], YB_ACL_PERM_NONE);

        // update#4 : 'id' can't be modified
        $result = $d->find_by_id(4);
        $olddata = $result[0];
        $data = array(
            'id' => 9,
            'owner' => $olddata['owner'],
            'name' => $olddata['name'],
            'policy' => $olddata['policy'],
            'perms' => $olddata['perms'],
        );
        $result = $d->update(4, $data);
        $this->assertIdentical($result, 1);

        $result = $d->find_by_id(4);
        $this->assertEqual($result[0]['id'], $olddata['id']);
        $this->assertEqual($result[0]['owner'], $olddata['owner']);
        $this->assertEqual($result[0]['name'], $olddata['name']);
        $this->assertEqual($result[0]['policy'], $olddata['policy']);
        $perms = $result[0]['perms'];
        $this->assertEqual(count($perms), 0);

        // update#999 : UNDEFINED id
        $data = array(
            'owner' => 9,
            'name' => 'testUpdate_Acl',
            );
        $result = $d->update(999, $data);
        $this->assertIdentical($result, 0);
    }

    // }}}
    // {{{ index data contents at this point
    // ACLID:
    //      contents.
    // 1:
    //      G,900,2
    //      U,100,1
    // 2:
    //      (empty)
    // 3:
    //      U,309,0
    // 4:
    //      (empty)
    // 5:
    //      U,100,1
    //      G,200,0
    //
    // }}}
    // {{{ _testDelete()

    function _testDelete()
    {
        $d =& yb_dao_Factory::get('acl');
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
            'chunksize' => grain_Config::set('grain.chunksize.acl', 10),
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
        grain_Config::set('grain.chunksize.acl', 
            $this->_old_dirs['chunksize']);
    }

    // }}}
    // {{{ testFindById()

    function testFindById()
    {
        $this->_find_prepare();
        $d =& new yb_dao_Acl();
        $d->_grain_name = 'acl1';

        $yc =& yb_Cache::factory($d->_cache_name);
        $yc->clean();

        // find_by_id() : found
        $result = $d->find_by_id(101);
        $this->assertEqual(count($result), 1);
        $this->assertEqual($result[0]['id'], 101);
        $this->assertEqual($result[0]['owner'], 100);
        $this->assertEqual($result[0]['name'], "Acl_101");
        $this->assertEqual($result[0]['policy'], YB_ACL_POLICY_POSI);
        $perms = $result[0]['perms'];
        $this->assertEqual(count($perms), 3);

        $this->assertEqual($perms[0]['type'], YB_ACL_TYPE_USER);
        $this->assertEqual($perms[0]['id'], 100);
        $this->assertEqual($perms[0]['perm'], YB_ACL_PERM_READWRITE);

        $this->assertEqual($perms[1]['type'], YB_ACL_TYPE_GROUP);
        $this->assertEqual($perms[1]['id'], 101);
        $this->assertEqual($perms[1]['perm'], YB_ACL_PERM_READ);

        $this->assertEqual($perms[2]['type'], YB_ACL_TYPE_USER);
        $this->assertEqual($perms[2]['id'], 200);
        $this->assertEqual($perms[2]['perm'], YB_ACL_PERM_NONE);

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
        $d =& new yb_dao_Acl();
        $d->_grain_name = 'acl1';

        $yc =& yb_Cache::factory($d->_cache_name);
        $yc->clean();

        // find_by_owner(100) : multiple found (sort by id asc :default)
        $result = $d->find_by_owner(100);
        $this->assertEqual(count($result), 2);

        // data#1
        $data = $result[0];
        $this->assertEqual($data['id'], 101);
        $this->assertEqual($data['owner'], 100);
        $this->assertEqual($data['name'], "Acl_101");
        $this->assertEqual($data['policy'], YB_ACL_POLICY_POSI);
        $perms = $data['perms'];
        $this->assertEqual(count($perms), 3);
        $this->assertEqual($perms[0]['type'], YB_ACL_TYPE_USER);
        $this->assertEqual($perms[0]['id'], 100);
        $this->assertEqual($perms[0]['perm'], YB_ACL_PERM_READWRITE);
        $this->assertEqual($perms[1]['type'], YB_ACL_TYPE_GROUP);
        $this->assertEqual($perms[1]['id'], 101);
        $this->assertEqual($perms[1]['perm'], YB_ACL_PERM_READ);
        $this->assertEqual($perms[2]['type'], YB_ACL_TYPE_USER);
        $this->assertEqual($perms[2]['id'], 200);
        $this->assertEqual($perms[2]['perm'], YB_ACL_PERM_NONE);
        // data#2
        $data = $result[1];
        $this->assertEqual($data['id'], 102);
        $this->assertEqual($data['owner'], 100);
        $this->assertEqual($data['name'], "Acl_102");
        $this->assertEqual($data['policy'], YB_ACL_POLICY_NEGA);
        $perms = $data['perms'];
        $this->assertEqual(count($perms), 1);
        $this->assertEqual($perms[0]['type'], YB_ACL_TYPE_USER);
        $this->assertEqual($perms[0]['id'], 100);
        $this->assertEqual($perms[0]['perm'], YB_ACL_PERM_READ);

        // find_by_owner(999) : NOT found
        $result = $d->find_by_owner(999);
        $this->assertEqual(count($result), 0);

        // find_by_owner(100) : sort by id desc
        $result = $d->find_by_owner(100, 'id', ORDER_BY_DESC);
        $this->assertEqual(count($result), 2);
        $this->assertEqual($result[0]['id'], 102);
        $this->assertEqual($result[1]['id'], 101);

        // find_by_owner(200) : sort by name asc
        $result = $d->find_by_owner(200, 'name');
        $this->assertEqual(count($result), 3);
        $this->assertEqual($result[0]['id'], 202);
        $this->assertEqual($result[0]['name'], 'Acl_20x_1');
        $this->assertEqual($result[1]['id'], 201);
        $this->assertEqual($result[1]['name'], 'Acl_20x_2');
        $this->assertEqual($result[2]['id'], 203);
        $this->assertEqual($result[2]['name'], 'Acl_20x_3');

        // find_by_owner(300) : sort by name desc
        $result = $d->find_by_owner(200, 'name', ORDER_BY_DESC);
        $this->assertEqual(count($result), 3);
        $this->assertEqual($result[0]['id'], 203);
        $this->assertEqual($result[1]['id'], 201);
        $this->assertEqual($result[2]['id'], 202);

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
