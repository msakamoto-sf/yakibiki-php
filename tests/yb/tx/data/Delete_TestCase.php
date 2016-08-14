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
require_once('yb/tx/data/Delete.php');
require_once('yb/tx/UnitTestCaseBase.php');

class yb_tx_data_Delete_TestCase extends yb_tx_UnitTestCaseBase
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

        $this->assertFalse(yb_tx_data_Delete::go(100));
        $this->assertTrue(yb_tx_data_Delete::go(1));
        $r = $dao->find_by_id(1);
        $this->assertEqual(count($r), 0);

        // version dao
        $dao_version =& yb_dao_Factory::get('version');
        $r = $dao_version->find_all();
        $this->assertEqual(count($r), 0);

        // acl_to_data
        $idx =& grain_Factory::index('pair', 'acl_to_data');
        $a = $idx->get_from($d1['acl']);
        $this->assertEqual(count($a), 1);
        $this->assertEqual($idx->count_for($d1['acl']), 0);

        // category_to_data
        $idx =& grain_Factory::index('pair', 'category_to_data');
        $c1 = $d1['categories'][0];
        $c2 = $d1['categories'][1];
        $a = $idx->get_from($d1['categories']);
        $this->assertEqual(count($a), 2);
        $this->assertEqual(count($a[$c1]), 0);
        $this->assertEqual(count($a[$c2]), 0);
        $this->assertFalse(in_array($d1['id'], $a[$c1]));
        $this->assertEqual($idx->count_for($c1), 0);
        $this->assertEqual($idx->count_for($c2), 0);

        // owner_to_data
        $idx =& grain_Factory::index('pair', 'owner_to_data');
        $a = $idx->get_from($d1['owner']);
        $this->assertEqual(count($a), 1);
        $this->assertEqual($idx->count_for($d1['owner']), 0);

        // data_by_published
        $idx =& grain_Factory::index('datetime', 'data_by_published');
        $idx->order(ORDER_BY_ASC);
        $a = $idx->gets();
        $this->assertEqual(count($a), 0);

        // data_by_updated
        $idx =& grain_Factory::index('datetime', 'data_by_updated');
        $idx->order(ORDER_BY_ASC);
        $a = $idx->gets();
        $this->assertEqual(count($a), 0);

        // data_by_title
        $idx =& grain_Factory::index('match', 'data_by_title');
        $a = $idx->search('yb_tx_data_New');
        $this->assertEqual(count($a), 0);
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
