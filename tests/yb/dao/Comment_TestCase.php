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

class yb_dao_Comment_TestCase extends UnitTestCase
{
    // {{{ testCUD() (entry point)

    function testCUD()
    {
        $root_dir = dirname(__FILE__) . '/tmp/comment';
        mkdir($root_dir);

        $seq_dir = $root_dir . '/sequence';
        mkdir($seq_dir);

        $grain_dir = $root_dir . '/grain';
        mkdir($grain_dir);

        $old_dirs = array(
            'seq_dir' => grain_Config::set('grain.dir.sequence', $seq_dir),
            'grain_dir' => grain_Config::set('grain.dir.grain', $grain_dir),
            'chunksize' => grain_Config::set('grain.chunksize.comment', 10),
        );

        $this->_testCreate();
        $this->_testUpdate();
        $this->_testDelete();

        grain_Config::set('grain.dir.sequence', $old_dirs['seq_dir']);
        grain_Config::set('grain.dir.grain', $old_dirs['grain_dir']);
        grain_Config::set('grain.chunksize.comment', $old_dirs['chunksize']);
        System::rm(" -rf " . $root_dir);
    }

    // }}}
    // {{{ _testCreate()

    function _testCreate()
    {
        $d =& yb_dao_Factory::get('comment');
        $yc =& yb_Cache::factory($d->_cache_name);
        $yc->clean();

        $data1 = array(
            'owner' => 10,
            'text' => 'testCreate_Comment01',
            'approved' => false,
            );
        $this->assertEqual($d->create($data1), 1);
        $data2 = array(
            'owner' => 20,
            'text' => 'testCreate_Comment02',
            'approved' => true,
            );
        $this->assertEqual($d->create($data2), 2);
        $data3 = array(
            'owner' => 30,
            'text' => 'testCreate_Comment03',
            'approved' => true,
            );
        $this->assertEqual($d->create($data3), 3);

        $records = $d->find_all();
        $this->assertEqual($records[0]['id'], 1);
        $this->assertEqual($records[0]['owner'], $data1['owner']);
        $this->assertEqual($records[0]['text'], $data1['text']);
        $this->assertEqual($records[0]['approved'], $data1['approved']);
        $this->assertEqual($records[1]['id'], 2);
        $this->assertEqual($records[1]['owner'], $data2['owner']);
        $this->assertEqual($records[1]['text'], $data2['text']);
        $this->assertEqual($records[1]['approved'], $data2['approved']);
        $this->assertEqual($records[2]['id'], 3);
        $this->assertEqual($records[2]['owner'], $data3['owner']);
        $this->assertEqual($records[2]['text'], $data3['text']);
        $this->assertEqual($records[2]['approved'], $data3['approved']);
    }

    // }}}
    // {{{ _testUpdate()

    function _testUpdate()
    {
        $d =& yb_dao_Factory::get('comment');
        $yc =& yb_Cache::factory($d->_cache_name);
        $yc->clean();

        // update#1
        $result = $d->find_by_id(1);
        $olddata = $result[0];
        $data = array(
            'id' => 2, // ignored
            'owner' => 91, // ignored
            'text' => 'testUpdate_Comment01',
            'approved' => true,
            );
        $this->assertIdentical($d->update(1, $data), 1);

        $results = $d->find_by_id(1);
        $result = $results[0];
        $this->assertEqual($result['id'], $olddata['id']);
        $this->assertEqual($result['owner'], $olddata['owner']);
        $this->assertEqual($result['text'], $data['text']);
        $this->assertEqual($result['approved'], $data['approved']);

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
            'text' => 'testUpdate_Comment01',
            'approved' => false,
            );
        $this->assertIdentical($d->update(999, $data), 0);
    }

    // }}}
    // {{{ _testDelete()

    function _testDelete()
    {
        $d =& yb_dao_Factory::get('comment');
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
        $this->assertEqual($datas[0]['text'], 'testCreate_Comment02');
        $this->assertEqual($datas[0]['approved'], true);

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
