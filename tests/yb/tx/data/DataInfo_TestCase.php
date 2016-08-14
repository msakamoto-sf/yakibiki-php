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

require_once('yb/tx/data/New.php');
require_once('yb/tx/data/DataInfo.php');
require_once('yb/tx/UnitTestCaseBase.php');

class yb_tx_data_DataInfo_TestCase extends yb_tx_UnitTestCaseBase
{
    // {{{ test_go()

    function test_go()
    {
        $dao =& yb_dao_Factory::get('data');

        // #1
        $data = array(
            'owner' => 100,
            'title' => 'yb_tx_data_New_Title01',
            'acl' => 101,
            'categories' => array(110, 111),
            'is_versions_moderated' => true,
            'is_comments_moderated' => true,
            'published_at' => '20001231235959',
            'type' => 'text',
            'format' => 'wiki',
        );
        $raw = 'yb_tx_data_New_RawData01';
        yb_tx_data_New::go($data, $raw);
        $r = $dao->find_by_id(1);
        $d1 = $r[0];

        yb_test_sleep(1, __FUNCTION__);

        $updates = array(
            'title' => 'yb_tx_data_New_Title02',
            'acl' => 102,
            'categories' => array(),
            'is_versions_moderated' => false,
            'is_comments_moderated' => false,
            'published_at' => '20010101000000',
        );
        $this->assertFalse(yb_tx_data_DataInfo::go(100, $updates));
        $this->assertTrue(yb_tx_data_DataInfo::go(1, $updates));
        $r = $dao->find_by_id(1);
        $d2 = $r[0];

        $this->assertEqual($d2['owner'], $d1['owner']);
        $this->assertEqual($d2['title'], $updates['title']);
        $this->assertEqual($d2['acl'], $updates['acl']);
        $a = $d2['categories'];
        $this->assertEqual(count($a), 0);
        $this->assertFalse($d2['is_versions_moderated']);
        $this->assertFalse($d2['is_comments_moderated']);
        $this->assertEqual($d2['published_at'], $updates['published_at']);
        $this->assertEqual($d2['type'], $d1['type']);
        $this->assertEqual($d2['format'], $d1['format']);
        $this->assertEqual($d2['created_at'], $d1['created_at']);
        $this->assertNotEqual($d2['updated_at'], $d1['updated_at']);

        // acl_to_data
        $idx =& grain_Factory::index('pair', 'acl_to_data');
        $a = $idx->get_from($d1['acl']);
        $this->assertEqual(count($a), 1);
        $this->assertEqual($idx->count_for($d1['acl']), 0);
        $a = $idx->get_from($d2['acl']);
        $this->assertEqual(count($a), 1);
        $this->assertTrue(in_array($d2['id'], $a[$d2['acl']]));
        $this->assertEqual($idx->count_for($d2['acl']), 1);

        // category_to_data
        $idx =& grain_Factory::index('pair', 'category_to_data');
        $c1 = $d1['categories'][0];
        $c2 = $d1['categories'][1];
        $a = $idx->get_from($d1['categories']);
        $this->assertEqual(count($a), 2);
        $this->assertEqual(count($a[$c1]), 0);
        $this->assertEqual(count($a[$c2]), 0);
        $this->assertFalse(in_array($d1['id'], $a[$c1]));
        $this->assertFalse(in_array($d2['id'], $a[$c2]));
        $this->assertEqual($idx->count_for($c1), 0);
        $this->assertEqual($idx->count_for($c2), 0);

        // data_by_title
        $idx =& grain_Factory::index('match', 'data_by_title');
        $r = $idx->fullmatch($updates['title']);
        $this->assertEqual(1, count($r));
        $this->assertEqual(1, $r[0]);

        // data_by_published
        $idx =& grain_Factory::index('datetime', 'data_by_published');
        $idx->order(ORDER_BY_ASC);
        $a = $idx->gets();
        $this->assertEqual(count($a), 1);
        $this->assertEqual($a[0], $d2['id']);

        // data_by_updated
        $idx =& grain_Factory::index('datetime', 'data_by_updated');
        $idx->order(ORDER_BY_ASC);
        $a = $idx->gets();
        $this->assertEqual(count($a), 1);
        $this->assertEqual($a[0], $d2['id']);
    }

    // }}}
    // {{{ test_go_same_params()

    function test_go_same_params()
    {
        $dao =& yb_dao_Factory::get('data');

        // #1
        $data = array(
            'owner' => 100,
            'title' => 'yb_tx_data_New2_Title01',
            'acl' => 101,
            'categories' => array(110, 111),
            'is_versions_moderated' => true,
            'is_comments_moderated' => true,
            'published_at' => '20001231235959',
            'type' => 'text',
            'format' => 'wiki',
        );
        $raw = 'yb_tx_data_New_RawData01';
        yb_tx_data_New::go($data, $raw);
        $r = $dao->find_by_id(1);
        $d1 = $r[0];

        yb_test_sleep(1, __FUNCTION__);

        $_copies = array('title', 'acl', 'categories', 
            'is_versions_moderated', 'is_comments_moderated', 
            'published_at', 
        );
        $updates = array();
        foreach ($_copies as $_c) {
            $updates[$_c] = $data[$_c];
        }
        $this->assertTrue(yb_tx_data_DataInfo::go(1, $updates));
        $r = $dao->find_by_id(1);
        $d2 = $r[0];

        $this->assertEqual($d2['owner'], $d1['owner']);
        $this->assertEqual($d2['title'], $d1['title']);
        $this->assertEqual($d2['acl'], $d1['acl']);
        $a = $d2['categories'];
        $this->assertEqual(count($a), 2);
        $this->assertEqual(
            $d2['is_versions_moderated'], $d1['is_versions_moderated']);
        $this->assertEqual(
            $d2['is_comments_moderated'], $d1['is_comments_moderated']);
        $this->assertEqual($d2['published_at'], $d1['published_at']);
        $this->assertEqual($d2['type'], $d1['type']);
        $this->assertEqual($d2['format'], $d1['format']);
        $this->assertEqual($d2['created_at'], $d1['created_at']);
        $this->assertNotEqual($d2['updated_at'], $d1['updated_at']);

        // acl_to_data
        $idx =& grain_Factory::index('pair', 'acl_to_data');
        $a = $idx->get_from($d1['acl']);
        $this->assertEqual(count($a), 1);
        $this->assertTrue(in_array($d1['id'], $a[$d2['acl']]));
        $this->assertEqual($idx->count_for($d2['acl']), 1);

        // category_to_data
        $idx =& grain_Factory::index('pair', 'category_to_data');
        $c1 = $d2['categories'][0];
        $c2 = $d2['categories'][1];
        $a = $idx->get_from($d2['categories']);
        $this->assertEqual(count($a), 2);
        $this->assertEqual(count($a[$c1]), 1);
        $this->assertEqual(count($a[$c2]), 1);
        $this->assertTrue(in_array($d1['id'], $a[$c1]));
        $this->assertTrue(in_array($d2['id'], $a[$c2]));
        $this->assertEqual($idx->count_for($c1), 1);
        $this->assertEqual($idx->count_for($c2), 1);

        // data_by_title
        $idx =& grain_Factory::index('match', 'data_by_title');
        $r = $idx->fullmatch($d1['title']);
        $this->assertEqual(1, count($r));
        $this->assertEqual(1, $r[0]);

        // data_by_published
        $idx =& grain_Factory::index('datetime', 'data_by_published');
        $idx->order(ORDER_BY_ASC);
        $a = $idx->gets();
        $this->assertEqual(count($a), 1);
        $this->assertEqual($a[0], $d1['id']);

        // data_by_updated
        $idx =& grain_Factory::index('datetime', 'data_by_updated');
        $idx->order(ORDER_BY_ASC);
        $a = $idx->gets();
        $this->assertEqual(count($a), 1);
        $this->assertEqual($a[0], $d1['id']);
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
