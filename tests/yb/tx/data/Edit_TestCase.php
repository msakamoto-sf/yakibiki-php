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
require_once('yb/tx/data/Edit.php');
require_once('yb/tx/UnitTestCaseBase.php');

class yb_tx_data_Edit_TestCase extends yb_tx_UnitTestCaseBase
{
    // {{{ test_novermod_and_vup()

    function test_go()
    {
        $dao_data =& yb_dao_Factory::get('data');
        $dao_version =& yb_dao_Factory::get('version');
        $raw_data =& grain_Factory::raw('data');

        // {{{ #1 novermod and vup
        $data = array(
            'owner' => 100,
            'title' => 'yb_tx_data_New_Title01',
            'acl' => 101,
            'categories' => array(),
            'is_versions_moderated' => false,
            'is_comments_moderated' => true,
            'published_at' => '20001231235959',
            'type' => 'text',
            'format' => 'wiki',
            'note' => 'note_01',
            'original_filename' => 'original_filename_01',
        );
        $raw = 'yb_tx_data_New_RawData01';
        yb_tx_data_New::go($data, $raw);
        $r = $dao_data->find_by_id(1);
        $d1 = $r[0];

        $updates = array(
            'format' => 'html',
            'note' => 'note_02',
            'original_filename' => 'original_filename_02',
        );
        $raw = 'yb_tx_data_Edit_RawData01';
        $changelog = 'changelog01' . "\r\n" . 'new line';
        $this->assertFalse(yb_tx_data_Edit::go(
            100, $updates, $raw, true, $changelog, 200));
        $this->assertTrue(yb_tx_data_Edit::go(
            1, $updates, $raw, true, $changelog, 200));

        $r = $dao_data->find_by_id(1);
        $d2 = $r[0];
        $this->assertEqual($d2['title'], $d1['title']);
        $this->assertEqual($d2['format'], $updates['format']);
        $this->assertEqual($d2['note'], $updates['note']);
        $this->assertEqual($d2['original_filename'], 
            $updates['original_filename']);
        $this->assertEqual($d2['current_version'], 2);
        $vs = $d2['versions'];
        $this->assertEqual(count($vs), 2);
        $this->assertEqual($vs[0], 1);
        $this->assertEqual($vs[1], 2);

        $r = $dao_version->find_by_id($d2['current_version']);
        $cv = $r[0];
        $this->assertEqual($cv['owner'], 200);
        $this->assertEqual($cv['version'], 2);
        $this->assertEqual($cv['approved'], true);
        $this->assertEqual($cv['changelog'], $changelog);
        $this->assertEqual($cv['md5'], md5($raw));
        $this->assertEqual($cv['sha1'], sha1($raw));

        $raw_id = $cv['raw_id'];
        $filename = $raw_data->filename($raw_id);
        $_raw = file_get_contents($filename);
        $this->assertEqual($_raw, $raw);

        // }}}
        // {{{ #2 novermod and no vup

        $data = array(
            'owner' => 100,
            'title' => 'yb_tx_data_New_Title01',
            'acl' => 101,
            'categories' => array(),
            'is_versions_moderated' => false,
            'is_comments_moderated' => true,
            'published_at' => '20001231235959',
            'type' => 'text',
            'format' => 'wiki',
            'note' => 'note_01',
            'original_filename' => 'original_filename_01',
        );
        $raw1 = 'yb_tx_data_New_RawData01';
        yb_tx_data_New::go($data, $raw1);
        $r = $dao_data->find_by_id(2);
        $d1 = $r[0];

        $updates = array(
            'format' => 'html',
            'note' => 'note_02',
            'original_filename' => 'original_filename_02',
        );
        $raw2 = 'yb_tx_data_Edit_RawData01';
        $changelog = 'changelog01' . "\r\n" . 'new line';
        $this->assertTrue(yb_tx_data_Edit::go(
            2, $updates, $raw2, false, $changelog, 200));

        $r = $dao_data->find_by_id(2);
        $d2 = $r[0];
        $this->assertEqual($d2['title'], $d1['title']);
        $this->assertEqual($d2['format'], $updates['format']);
        $this->assertEqual($d2['note'], $updates['note']);
        $this->assertEqual($d2['original_filename'], 
            $updates['original_filename']);
        $this->assertEqual($d2['current_version'], 3);
        $vs = $d2['versions'];
        $this->assertEqual(count($vs), 1);
        $this->assertEqual($vs[0], 3);

        $r = $dao_version->find_by_id($d2['current_version']);
        $cv = $r[0];
        $this->assertEqual($cv['owner'], 200);
        $this->assertEqual($cv['version'], 1);
        $this->assertEqual($cv['approved'], true);
        $this->assertEqual($cv['changelog'], $changelog);
        $this->assertEqual($cv['md5'], md5($raw2));
        $this->assertEqual($cv['sha1'], sha1($raw2));

        $raw_id = $cv['raw_id'];
        $filename = $raw_data->filename($raw_id);
        $_raw = file_get_contents($filename);
        $this->assertEqual($_raw, $raw2);

        // }}}
        // {{{ #3 vermod and vup

        $data = array(
            'owner' => 100,
            'title' => 'yb_tx_data_New_Title01',
            'acl' => 101,
            'categories' => array(),
            'is_versions_moderated' => true,
            'is_comments_moderated' => true,
            'published_at' => '20001231235959',
            'type' => 'text',
            'format' => 'wiki',
            'note' => 'note_01',
            'original_filename' => 'original_filename_01',
        );
        $raw1 = 'yb_tx_data_New_RawData01';
        yb_tx_data_New::go($data, $raw1);
        $r = $dao_data->find_by_id(3);
        $d1 = $r[0];

        $updates = array(
            'format' => 'html',
            'note' => 'note_02',
            'original_filename' => 'original_filename_02',
        );
        $raw2 = 'yb_tx_data_Edit_RawData01';
        $changelog = 'changelog01' . "\r\n" . 'new line';
        $this->assertTrue(yb_tx_data_Edit::go(
            3, $updates, $raw2, true, $changelog, 200));

        $r = $dao_data->find_by_id(3);
        $d2 = $r[0];
        $this->assertEqual($d2['title'], $d1['title']);
        $this->assertEqual($d2['format'], $updates['format']);
        $this->assertEqual($d2['note'], $updates['note']);
        $this->assertEqual($d2['original_filename'], 
            $updates['original_filename']);
        $this->assertEqual($d2['current_version'], 4);
        $vs = $d2['versions'];
        $this->assertEqual(count($vs), 2);
        $this->assertEqual($vs[0], 4);
        $this->assertEqual($vs[1], 5);

        // current version
        $r = $dao_version->find_by_id($d2['current_version']);
        $cv = $r[0];
        $this->assertEqual($cv['owner'], 100);
        $this->assertEqual($cv['version'], 1);
        $this->assertEqual($cv['approved'], true);
        $this->assertTrue(empty($cv['changelog']));
        $this->assertEqual($cv['md5'], md5($raw1));
        $this->assertEqual($cv['sha1'], sha1($raw1));

        $raw_id = $cv['raw_id'];
        $filename = $raw_data->filename($raw_id);
        $_raw = file_get_contents($filename);
        $this->assertEqual($_raw, $raw1);

        // updated new version (not approved yet)
        $r = $dao_version->find_by_id($d2['current_version'] + 1);
        $cv = $r[0];
        $this->assertEqual($cv['owner'], 200);
        $this->assertEqual($cv['version'], 2);
        $this->assertEqual($cv['approved'], false);
        $this->assertEqual($cv['changelog'], $changelog);
        $this->assertEqual($cv['md5'], md5($raw2));
        $this->assertEqual($cv['sha1'], sha1($raw2));

        $raw_id = $cv['raw_id'];
        $filename = $raw_data->filename($raw_id);
        $_raw = file_get_contents($filename);
        $this->assertEqual($_raw, $raw2);

        // }}}
        // {{{ #4 vermod and no vup

        $data = array(
            'owner' => 100,
            'title' => 'yb_tx_data_New_Title01',
            'acl' => 101,
            'categories' => array(),
            'is_versions_moderated' => true,
            'is_comments_moderated' => true,
            'published_at' => '20001231235959',
            'type' => 'text',
            'format' => 'wiki',
            'note' => 'note_01',
            'original_filename' => 'original_filename_01',
        );
        $raw1 = 'yb_tx_data_New_RawData01';
        yb_tx_data_New::go($data, $raw1);
        $r = $dao_data->find_by_id(4);
        $d1 = $r[0];

        $updates = array(
            'format' => 'html',
            'note' => 'note_02',
            'original_filename' => 'original_filename_02',
        );
        $raw2 = 'yb_tx_data_Edit_RawData01';
        $changelog = 'changelog01' . "\r\n" . 'new line';
        $this->assertTrue(yb_tx_data_Edit::go(
            4, $updates, $raw2, false, $changelog, 200));

        $r = $dao_data->find_by_id(4);
        $d2 = $r[0];
        $this->assertEqual($d2['title'], $d1['title']);
        $this->assertEqual($d2['format'], $updates['format']);
        $this->assertEqual($d2['note'], $updates['note']);
        $this->assertEqual($d2['original_filename'], 
            $updates['original_filename']);
        $this->assertEqual($d2['current_version'], 6);
        $vs = $d2['versions'];
        $this->assertEqual(count($vs), 2);
        $this->assertEqual($vs[0], 6);
        $this->assertEqual($vs[1], 7);

        // current version
        $r = $dao_version->find_by_id($d2['current_version']);
        $cv = $r[0];
        $this->assertEqual($cv['owner'], 100);
        $this->assertEqual($cv['version'], 1);
        $this->assertEqual($cv['approved'], true);
        $this->assertTrue(empty($cv['changelog']));
        $this->assertEqual($cv['md5'], md5($raw1));
        $this->assertEqual($cv['sha1'], sha1($raw1));

        $raw_id = $cv['raw_id'];
        $filename = $raw_data->filename($raw_id);
        $_raw = file_get_contents($filename);
        $this->assertEqual($_raw, $raw1);

        // updated new version (not approved yet)
        $r = $dao_version->find_by_id($d2['current_version'] + 1);
        $cv = $r[0];
        $this->assertEqual($cv['owner'], 200);
        $this->assertEqual($cv['version'], 2);
        $this->assertEqual($cv['approved'], false);
        $this->assertEqual($cv['changelog'], $changelog);
        $this->assertEqual($cv['md5'], md5($raw2));
        $this->assertEqual($cv['sha1'], sha1($raw2));

        $raw_id = $cv['raw_id'];
        $filename = $raw_data->filename($raw_id);
        $_raw = file_get_contents($filename);
        $this->assertEqual($_raw, $raw2);

        // }}}
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
