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

class yb_dao_Version_TestCase extends UnitTestCase
{
    // {{{ testCUD() (entry point)

    function testCUD()
    {
        $root_dir = dirname(__FILE__) . '/tmp/version';
        mkdir($root_dir);

        $seq_dir = $root_dir . '/sequence';
        mkdir($seq_dir);

        $grain_dir = $root_dir . '/grain';
        mkdir($grain_dir);

        $old_dirs = array(
            'seq_dir' => grain_Config::set('grain.dir.sequence', $seq_dir),
            'grain_dir' => grain_Config::set('grain.dir.grain', $grain_dir),
            'chunksize' => grain_Config::set('grain.chunksize.version', 10),
        );

        $this->_testCreate();
        $this->_testUpdate();
        $this->_testDelete();

        grain_Config::set('grain.dir.sequence', $old_dirs['seq_dir']);
        grain_Config::set('grain.dir.grain', $old_dirs['grain_dir']);
        grain_Config::set('grain.chunksize.version', $old_dirs['chunksize']);
        System::rm(" -rf " . $root_dir);
    }

    // }}}
    // {{{ _testCreate()

    function _testCreate()
    {
        $d =& yb_dao_Factory::get('version');
        $yc =& yb_Cache::factory($d->_cache_name);
        $yc->clean();

        $data1 = array(
            'owner' => 10,
            'data_id' => 100,
            'raw_id' => 110,
            'version' => 1,
            'approved' => false,
            'changelog' => 'testCreate_Changelog01',
            'md5' => 'hash_md5_01',
            'sha1' => 'hash_sha1_01',
            );
        $this->assertEqual($d->create($data1), 1);
        $data2 = array(
            'owner' => 20,
            'data_id' => 200,
            'raw_id' => 210,
            'version' => 2,
            'approved' => true,
            'changelog' => "testCreate_\r\n_Changelog02 \t\r\n",
            'md5' => 'hash_md5_02',
            'sha1' => 'hash_sha1_02',
            );
        $this->assertEqual($d->create($data2), 2);
        $data3 = array(
            'owner' => 30,
            'data_id' => 300,
            'raw_id' => 310,
            'version' => 3,
            'approved' => true,
            'changelog' => "testCreate_\t_Changelog03 \t\r\n",
            'md5' => 'hash_md5_03',
            'sha1' => 'hash_sha1_03',
            );
        $this->assertEqual($d->create($data3), 3);

        $records = $d->find_all();
        $this->assertEqual($records[0]['id'], 1);
        $this->assertEqual($records[0]['owner'], $data1['owner']);
        $this->assertEqual($records[0]['data_id'], $data1['data_id']);
        $this->assertEqual($records[0]['raw_id'], $data1['raw_id']);
        $this->assertEqual($records[0]['version'], $data1['version']);
        $this->assertEqual($records[0]['approved'], $data1['approved']);
        $this->assertEqual($records[0]['changelog'], $data1['changelog']);
        $this->assertEqual($records[0]['md5'], $data1['md5']);
        $this->assertEqual($records[0]['sha1'], $data1['sha1']);
        $this->assertEqual($records[1]['id'], 2);
        $this->assertEqual($records[1]['owner'], $data2['owner']);
        $this->assertEqual($records[1]['data_id'], $data2['data_id']);
        $this->assertEqual($records[1]['raw_id'], $data2['raw_id']);
        $this->assertEqual($records[1]['version'], $data2['version']);
        $this->assertEqual($records[1]['approved'], $data2['approved']);
        $this->assertEqual($records[1]['changelog'], $data2['changelog']);
        $this->assertEqual($records[1]['md5'], $data2['md5']);
        $this->assertEqual($records[1]['sha1'], $data2['sha1']);
        $this->assertEqual($records[2]['id'], 3);
        $this->assertEqual($records[2]['owner'], $data3['owner']);
        $this->assertEqual($records[2]['data_id'], $data3['data_id']);
        $this->assertEqual($records[2]['raw_id'], $data3['raw_id']);
        $this->assertEqual($records[2]['version'], $data3['version']);
        $this->assertEqual($records[2]['approved'], $data3['approved']);
        $this->assertEqual($records[2]['changelog'], $data3['changelog']);
        $this->assertEqual($records[2]['md5'], $data3['md5']);
        $this->assertEqual($records[2]['sha1'], $data3['sha1']);
    }

    // }}}
    // {{{ _testUpdate()

    function _testUpdate()
    {
        $d =& yb_dao_Factory::get('version');
        $yc =& yb_Cache::factory($d->_cache_name);
        $yc->clean();

        // update#1
        $result = $d->find_by_id(1);
        $olddata = $result[0];
        $data = array(
            'id' => 2, // ignored
            'owner' => 91,
            'data_id' => 999, // ignored
            'raw_id' => 910,
            'version' => 9,
            'approved' => true,
            'changelog' => 'testUpdate_Changelog02',
            'md5' => 'hash_md5_02',
            'sha1' => 'hash_sha1_02',
            );
        $this->assertIdentical($d->update(1, $data), 1);

        $results = $d->find_by_id(1);
        $result = $results[0];
        $this->assertEqual($result['id'], $olddata['id']);
        $this->assertEqual($result['owner'], $data['owner']);
        $this->assertEqual($result['data_id'], $olddata['data_id']);
        $this->assertEqual($result['raw_id'], $data['raw_id']);
        $this->assertEqual($result['version'], $data['version']);
        $this->assertEqual($result['approved'], $data['approved']);
        $this->assertEqual($result['changelog'], $data['changelog']);
        $this->assertEqual($result['md5'], $data['md5']);
        $this->assertEqual($result['sha1'], $data['sha1']);

        // update#2 : 'approved' boolean type casts
        $result = $d->find_by_id(2);
        $this->assertIdentical(true, $result[0]['approved']);

        $this->assertIdentical($d->update(2, array('approved' => false)), 1);
        $result = $d->find_by_id(2);
        $this->assertIdentical(false, $result[0]['approved']);

        $this->assertIdentical($d->update(2, array('approved' => 1)), 1);
        $result = $d->find_by_id(2);
        $this->assertIdentical(true, $result[0]['approved']);

        $this->assertIdentical($d->update(2, array('approved' => 0)), 1);
        $result = $d->find_by_id(2);
        $this->assertIdentical(false, $result[0]['approved']);

        $this->assertIdentical($d->update(2, array('approved' => -1)), 1);
        $result = $d->find_by_id(2);
        $this->assertIdentical(true, $result[0]['approved']);

        // update#999 : UNDEFINED id
        $data = array(
            'owner' => 99,
            'raw_id' => 'testUpdate_Version',
            );
        $this->assertIdentical($d->update(999, $data), 0);
    }

    // }}}
    // {{{ _testDelete()

    function _testDelete()
    {
        $d =& yb_dao_Factory::get('version');
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
        $this->assertEqual(count($datas), 1);
        $this->assertEqual($datas[0]['id'], 2);
        $this->assertEqual($datas[0]['owner'], 20);
        $this->assertEqual($datas[0]['data_id'], 200);
        $this->assertEqual($datas[0]['raw_id'], 210);

        // delete #1 (undefined)
        $this->assertIdentical($d->delete(1), 0);
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
