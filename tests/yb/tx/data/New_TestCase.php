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
require_once('yb/tx/UnitTestCaseBase.php');

class yb_tx_data_New_TestCase extends yb_tx_UnitTestCaseBase
{
    // {{{ test_go()

    function test_go()
    {
        // #1 : owner#1, acl#1, NO categories, NO threads
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

        $r = yb_tx_data_New::go($data, $raw);
        $this->assertEqual($r['id'], 1);
        $this->assertEqual($r['owner'], $data['owner']);
        $this->assertEqual($r['title'], $data['title']);
        $this->assertEqual($r['acl'], $data['acl']);
        $a = $r['categories'];
        $this->assertEqual(count($a), 2);
        $this->assertTrue(in_array(110, $a));
        $this->assertTrue(in_array(111, $a));
        $a = $r['versions'];
        $this->assertEqual(count($a), 1);
        $this->assertTrue(in_array(1, $a));
        $a = $r['comments'];
        $this->assertEqual(count($a), 0);
        $this->assertEqual($r['current_version'], 1);
        $this->assertTrue($r['is_versions_moderated']);
        $this->assertTrue($r['is_comments_moderated']);
        $this->assertEqual($r['published_at'], $data['published_at']);
        $this->assertEqual($r['type'], $data['type']);
        $this->assertEqual($r['format'], $data['format']);
        $this->assertTrue(isset($r['created_at']));
        $this->assertTrue(isset($r['updated_at']));

        // version dao
        $dao_version =& yb_dao_Factory::get('version');
        $v_id = $r['current_version'];
        $vs = $dao_version->find_by_id($v_id);
        $this->assertEqual($vs[0]['owner'], $r['owner']);
        $this->assertEqual($vs[0]['version'], 1);
        $this->assertEqual($vs[0]['approved'], true);
        $this->assertEqual($vs[0]['changelog'], '');
        $this->assertEqual($vs[0]['md5'], md5($raw));
        $this->assertEqual($vs[0]['sha1'], sha1($raw));

        // saved raw data
        $raw_id = $vs[0]['raw_id'];
        $raw_grain =& grain_Factory::raw('data');
        $fn = $raw_grain->filename($raw_id);
        $this->assertEqual(file_get_contents($fn), $raw);

        // acl_to_data
        $idx =& grain_Factory::index('pair', 'acl_to_data');
        $a = $idx->get_from($data['acl']);
        $this->assertEqual(count($a), 1);
        $this->assertTrue(in_array($r['id'], $a[$data['acl']]));
        $this->assertEqual($idx->count_for($data['acl']), 1);

        // category_to_data
        $idx =& grain_Factory::index('pair', 'category_to_data');
        $c1 = $data['categories'][0];
        $c2 = $data['categories'][1];
        $a = $idx->get_from($data['categories']);
        $this->assertEqual(count($a), 2);
        $this->assertEqual(count($a[$c1]), 1);
        $this->assertEqual(count($a[$c2]), 1);
        $this->assertTrue(in_array($r['id'], $a[$c1]));
        $this->assertTrue(in_array($r['id'], $a[$c2]));
        $this->assertEqual($idx->count_for($c1), 1);
        $this->assertEqual($idx->count_for($c2), 1);

        // owner_to_data
        $idx =& grain_Factory::index('pair', 'owner_to_data');
        $a = $idx->get_from($data['owner']);
        $this->assertEqual(count($a), 1);
        $this->assertTrue(in_array($r['id'], $a[$data['owner']]));
        $this->assertEqual($idx->count_for($data['owner']), 1);

        // data_by_published
        $idx =& grain_Factory::index('datetime', 'data_by_published');
        $idx->order(ORDER_BY_ASC);
        $a = $idx->gets();
        $this->assertEqual(count($a), 1);
        $this->assertEqual($a[0], $r['id']);

        // data_by_updated
        $idx =& grain_Factory::index('datetime', 'data_by_updated');
        $idx->order(ORDER_BY_ASC);
        $a = $idx->gets();
        $this->assertEqual(count($a), 1);
        $this->assertEqual($a[0], $r['id']);

        // data_by_title
        $idx =& grain_Factory::index('match', 'data_by_title');
        $a = $idx->search('yb_tx_data_New');
        $this->assertEqual(count($a), 1);
        $this->assertEqual($a[0], $r['id']);

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
