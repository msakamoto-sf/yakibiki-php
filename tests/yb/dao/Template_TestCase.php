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

class yb_dao_Template_TestCase extends UnitTestCase
{
    // {{{ testCUD() (entry point)

    function testCUD()
    {
        $root_dir = dirname(__FILE__) . '/tmp/template';
        mkdir($root_dir);

        $seq_dir = $root_dir . '/sequence';
        mkdir($seq_dir);

        $grain_dir = $root_dir . '/grain';
        mkdir($grain_dir);

        $old_dirs = array(
            'seq_dir' => grain_Config::set('grain.dir.sequence', $seq_dir),
            'grain_dir' => grain_Config::set('grain.dir.grain', $grain_dir),
            'chunksize' => grain_Config::set('grain.chunksize.template', 10),
        );

        $this->_testCreate();
        $this->_testUpdate();
        $this->_testDelete();

        grain_Config::set('grain.dir.sequence', $old_dirs['seq_dir']);
        grain_Config::set('grain.dir.grain', $old_dirs['grain_dir']);
        grain_Config::set('grain.chunksize.template', $old_dirs['chunksize']);
        System::rm(" -rf " . $root_dir);
    }

    // }}}
    // {{{ _testCreate()

    function _testCreate()
    {
        $d =& yb_dao_Factory::get('template');
        $yc =& yb_Cache::factory($d->_cache_name);
        $yc->clean();

        // text type
        $data1 = array(
            'owner' => 10,
            'name' => 'testCreate_Template01',
            'title' => 'testCreate_Data01',
            'acl' => 110,
            'categories' => array(),
            'is_versions_moderated' => true,
            'is_comments_moderated' => true,
            'type' => 'text',
            'format' => 'wiki',
            'raw_id' => 100,
            );
        $this->assertEqual($d->create($data1), 1);
        // image, attach
        $data2 = array(
            'owner' => 20,
            'name' => 'testCreate_Template02',
            'title' => 'testCreate_Data02',
            'acl' => 210,
            'categories' => array(1, 2, 3),
            'is_versions_moderated' => false,
            'is_comments_moderated' => false,
            'type' => 'image',
            );
        $this->assertEqual($d->create($data2), 2);
        // bookmark
        $data3 = array(
            'owner' => 30,
            'name' => 'testCreate_Template03',
            'title' => 'testCreate_Data03',
            'acl' => 310,
            'categories' => array(31, 32, 33),
            'is_versions_moderated' => true,
            'is_comments_moderated' => false,
            'type' => 'bookmark',
            );
        $this->assertEqual($d->create($data3), 3);

        $records = $d->find_all();
        $this->assertEqual($records[0]['id'], 1);
        $this->assertEqual($records[0]['owner'], $data1['owner']);
        $this->assertEqual($records[0]['name'], $data1['name']);
        $this->assertEqual($records[0]['title'], $data1['title']);
        $this->assertEqual($records[0]['acl'], $data1['acl']);
        $categories = $records[0]['categories'];
        $this->assertEqual(count($categories), 0);
        $this->assertEqual($records[0]['is_versions_moderated'], 
            $data1['is_versions_moderated']);
        $this->assertEqual($records[0]['is_comments_moderated'], 
            $data1['is_comments_moderated']);
        $this->assertEqual($records[0]['type'], $data1['type']);
        $this->assertEqual($records[0]['format'], $data1['format']);
        $this->assertEqual($records[0]['raw_id'], $data1['raw_id']);

        $this->assertEqual($records[1]['id'], 2);
        $this->assertEqual($records[1]['owner'], $data2['owner']);
        $this->assertEqual($records[1]['name'], $data2['name']);
        $this->assertEqual($records[1]['title'], $data2['title']);
        $this->assertEqual($records[1]['acl'], $data2['acl']);
        $categories = $records[1]['categories'];
        $this->assertEqual(count($categories), 3);
        $this->assertTrue(in_array(1, $categories));
        $this->assertTrue(in_array(2, $categories));
        $this->assertTrue(in_array(3, $categories));
        $this->assertEqual($records[1]['is_versions_moderated'], 
            $data2['is_versions_moderated']);
        $this->assertEqual($records[1]['is_comments_moderated'], 
            $data2['is_comments_moderated']);
        $this->assertEqual($records[1]['type'], $data2['type']);

        $this->assertEqual($records[2]['id'], 3);
        $this->assertEqual($records[2]['owner'], $data3['owner']);
        $this->assertEqual($records[2]['name'], $data3['name']);
        $this->assertEqual($records[2]['title'], $data3['title']);
        $this->assertEqual($records[2]['acl'], $data3['acl']);
        $categories = $records[2]['categories'];
        $this->assertEqual(count($categories), 3);
        $this->assertTrue(in_array(31, $categories));
        $this->assertTrue(in_array(32, $categories));
        $this->assertTrue(in_array(33, $categories));
        $this->assertEqual($records[2]['is_versions_moderated'], 
            $data3['is_versions_moderated']);
        $this->assertEqual($records[2]['is_comments_moderated'], 
            $data3['is_comments_moderated']);
        $this->assertEqual($records[2]['type'], $data3['type']);
    }

    // }}}
    // {{{ _testUpdate()

    function _testUpdate()
    {
        $d =& yb_dao_Factory::get('template');
        $yc =& yb_Cache::factory($d->_cache_name);
        $yc->clean();

        // update#1
        $result = $d->find_by_id(1);
        $olddata = $result[0];

        $data = array(
            'id' => 90, // ignored
            'owner' => 91, // ignored
            'name' => 'testUpdate_Template01',
            'title' => 'testUpdate_Data01',
            'acl' => 120,
            'categories' => array(4, 5, 6),
            'is_versions_moderated' => false,
            'is_comments_moderated' => true,
            'type' => 'image', // ignored
            'format' => 'html',
            'raw_id' => 120,
            );
        $this->assertIdentical($d->update(1, $data), 1);

        $results = $d->find_by_id(1);
        $result = $results[0];
        $this->assertEqual($result['id'], $olddata['id']);
        $this->assertEqual($result['owner'], $olddata['owner']);
        $this->assertEqual($result['name'], $data['name']);
        $this->assertEqual($result['title'], $data['title']);
        $this->assertEqual($result['acl'], $data['acl']);
        $categories = $result['categories'];
        $this->assertEqual(count($categories), 3);
        $this->assertTrue(in_array(4, $categories));
        $this->assertTrue(in_array(5, $categories));
        $this->assertTrue(in_array(6, $categories));
        $this->assertEqual($result['is_versions_moderated'], 
            $data['is_versions_moderated']);
        $this->assertEqual($result['is_comments_moderated'], 
            $data['is_comments_moderated']);
        $this->assertEqual($result['type'], $olddata['type']);
        $this->assertEqual($result['format'], $data['format']);
        $this->assertEqual($result['raw_id'], $data['raw_id']);

        // update#2
        $result = $d->find_by_id(2);
        $olddata = $result[0];

        $data = array(
            'title' => 'testUpdate_Data02',
            'categories' => array(),
            'is_versions_moderated' => true,
            );
        $this->assertIdentical($d->update(2, $data), 1);

        $results = $d->find_by_id(2);
        $result = $results[0];
        $this->assertEqual($result['id'], $olddata['id']);
        $this->assertEqual($result['owner'], $olddata['owner']);
        $this->assertEqual($result['name'], $olddata['name']);
        $this->assertEqual($result['title'], $data['title']);
        $this->assertEqual($result['acl'], $olddata['acl']);
        $categories = $result['categories'];
        $this->assertEqual(count($categories), 0);
        $this->assertEqual($result['is_versions_moderated'], 
            $data['is_versions_moderated']);
        $this->assertEqual($result['is_comments_moderated'], 
            $olddata['is_comments_moderated']);
        $this->assertEqual($result['type'], $olddata['type']);

        // update#999 : UNDEFINED id
        $data = array(
            'owner' => 99,
            'title' => 'testUpdate_Data',
            );
        $this->assertIdentical($d->update(999, $data), 0);
    }

    // }}}
    // {{{ _testDelete()

    function _testDelete()
    {
        $d =& yb_dao_Factory::get('template');
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
        $this->assertEqual($datas[0]['title'], 'testUpdate_Data02');

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
