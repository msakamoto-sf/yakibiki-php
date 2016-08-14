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

class yb_Finder_TestCase extends UnitTestCase
{
    var $_grain_backups;
    var $_yb_backups;
    // {{{ _prepare()

    function _prepare($root_dir)
    {
        $GLOBALS[FACTORY_ZONE] = mt_rand();

        $grain_dir = $root_dir . '/grain';
        $index_dir = $root_dir . '/index';
        $seq_dir = $root_dir . '/seq';
        $raw_dir = $root_dir . '/raw';

        $backups = array(
            'grain.dir.grain' => $grain_dir, 
            'grain.dir.index' => $index_dir, 
            'grain.dir.sequence' => $seq_dir, 
            'grain.dir.raw' => $raw_dir,
            'grain.chunksize.default' => 100,
            'grain.chunksize.data' => 500,
        );

        foreach ($backups as $k => $v) {
            $this->_grain_backups[$k] = grain_Config::set($k, $v);
        }

        $cache_options = _YB('cache.options');
        $cache_options['caching'] = false;

        $this->_yb_backups = array(
            'cache_options' => _YB('cache.options', $cache_options),
        );

        ob_end_flush();
    }

    // }}}
    // {{{ _cleanup()

    function _cleanup()
    {
        // clean ups and restores.
        foreach ($this->_grain_backups as $k => $v) {
            $d = grain_Config::set($k, $v);
        }
        _YB('cache.options', $this->_yb_backups['cache_options']);
        ob_start();
    }

    // }}}
    // {{{ test_search()

    function test_search()
    {
        $this->_prepare(dirname(__FILE__) . '/test_Finder01');

        $this->_test_sys_role_no_cond();
        $this->_test_sys_role_cond_category();
        $this->_test_sys_role_cond_text();
        $this->_test_sys_role_cond_text_and_category();
        $this->_test_sys_role_cond_text_or_category();
        $this->_test_guest_no_cond();
        $this->_test_guest_with_cond();
        $this->_test_non_role_user_no_cond();
        $this->_test_non_role_user_with_cond();

        $this->_cleanup();
    }

    // }}}
    // {{{ _test_sys_role_no_cond()

    function _test_sys_role_no_cond()
    {
        $dummy_uc = array('id' => 1, 'role' => array('sys', 'group'));

        $finder =& new yb_Finder();
        $ids = $finder->search($dummy_uc);

        // default result must be sorted updated_at, order by desc.
        $this->assertEqual(count($ids), 75);
        $this->assertEqual($ids[0], 75);
        $this->assertEqual($ids[74], 1);

        // explicity sort updated_at, order by asc.
        $finder =& new yb_Finder();
        $finder->order_by = ORDER_BY_ASC;
        $ids = $finder->search($dummy_uc);
        $this->assertEqual(count($ids), 75);
        $this->assertEqual($ids[0], 1);
        $this->assertEqual($ids[74], 75);

        // explicity sort created_at, order by desc.
        $finder =& new yb_Finder();
        $finder->sort_by = yb_Finder::SORT_BY_CREATED_AT();
        $ids = $finder->search($dummy_uc);
        $this->assertEqual(count($ids), 75);
        $this->assertEqual($ids[0], 75);
        $this->assertEqual($ids[74], 1);

        // implicity sort order by asc.
        $finder =& new yb_Finder();
        $finder->sort_by = yb_Finder::SORT_BY_CREATED_AT();
        $finder->order_by = ORDER_BY_ASC;
        $ids = $finder->search($dummy_uc);
        $this->assertEqual(count($ids), 75);
        $this->assertEqual($ids[0], 1);
        $this->assertEqual($ids[74], 75);
    }

    // }}}
    // {{{ _test_sys_role_cond_category()

    function _test_sys_role_cond_category()
    {
        $dummy_uc = array('id' => 1, 'role' => array('sys', 'group'));

        // extract only category 1.
        $finder =& new yb_Finder();
        $finder->categories = array(1);
        $ids = $finder->search($dummy_uc);
        $this->assertEqual(count($ids), 25);
        $this->assertEqual($ids[0], 74);
        $this->assertEqual($ids[24], 2);

        // extract only category 2.
        $finder =& new yb_Finder();
        $finder->categories = array(2);
        $ids = $finder->search($dummy_uc);
        $this->assertEqual(count($ids), 25);
        $this->assertEqual($ids[0], 75);
        $this->assertEqual($ids[24], 3);

        // extract only category 3.
        $finder =& new yb_Finder();
        $finder->categories = array(3);
        $ids = $finder->search($dummy_uc);
        $this->assertEqual(count($ids), 25);
        $this->assertEqual($ids[0], 75);
        $this->assertEqual($ids[24], 3);

        // extract only category 2, 3.
        $finder =& new yb_Finder();
        $finder->categories = array(2, 3);
        $ids = $finder->search($dummy_uc);
        $this->assertEqual(count($ids), 25);
        $this->assertEqual($ids[0], 75);
        $this->assertEqual($ids[24], 3);

        // extract only category 1, 3 -> ORed. 50 records.
        $finder =& new yb_Finder();
        $finder->categories = array(1, 3);
        $ids = $finder->search($dummy_uc);
        $this->assertEqual(count($ids), 50);
        $this->assertEqual($ids[0], 75);
        $this->assertEqual($ids[49], 2);

        // extract only category 4.
        $finder =& new yb_Finder();
        $finder->categories = array(4);
        $ids = $finder->search($dummy_uc);
        $this->assertEqual(count($ids), 0);
    }

    // }}}
    // {{{ _test_sys_role_cond_text()

    function _test_sys_role_cond_text()
    {
        $dummy_uc = array('id' => 1, 'role' => array('sys', 'group'));

        // part match : "data a01"
        $finder =& new yb_Finder();
        $finder->textmatch = "data a01";
        $ids = $finder->search($dummy_uc);
        $this->assertEqual(count($ids), 3);
        $this->assertEqual($ids[0], 3);
        $this->assertEqual($ids[2], 1);

        // part match : "d02_0"
        $finder =& new yb_Finder();
        $finder->textmatch = "d02_0";
        $ids = $finder->search($dummy_uc);
        $this->assertEqual(count($ids), 3);
        $this->assertEqual($ids[0], 51);
        $this->assertEqual($ids[2], 49);

        // full match : "d02_0" : NO HIT.
        $finder =& new yb_Finder();
        $finder->textmatch = "d02_0";
        $finder->is_fullmatch = true;
        $ids = $finder->search($dummy_uc);
        $this->assertEqual(count($ids), 0);

        // case sensitive : "DATA a01" : NO HIT.
        $finder =& new yb_Finder();
        $finder->textmatch = "DATA a01";
        $finder->case_sensitive = true;
        $ids = $finder->search($dummy_uc);
        $this->assertEqual(count($ids), 0);

        // full match : "data e05_12"
        $finder =& new yb_Finder();
        $finder->textmatch = "data e05_03";
        $finder->is_fullmatch = true;
        $ids = $finder->search($dummy_uc);
        $this->assertEqual(count($ids), 1);
        $this->assertEqual($ids[0], 75);

    }

    // }}}
    // {{{ _test_sys_role_cond_text_and_category()

    function _test_sys_role_cond_text_and_category()
    {
        $dummy_uc = array('id' => 1, 'role' => array('sys', 'group'));

        // part match : "data a01" and category 1.
        $finder =& new yb_Finder();
        $finder->textmatch = "data a01";
        $finder->categories = array(1);
        $ids = $finder->search($dummy_uc);
        $this->assertEqual(count($ids), 1);
        $this->assertEqual($ids[0], 2);

        // part match : "data a01" and category 1, 2.
        $finder =& new yb_Finder();
        $finder->textmatch = "data a01";
        $finder->categories = array(1, 2);
        $ids = $finder->search($dummy_uc);
        $this->assertEqual(count($ids), 2);
        $this->assertEqual($ids[0], 3);
        $this->assertEqual($ids[1], 2);

        // full match : "data a01" and category 1. -> NO HIT.
        $finder =& new yb_Finder();
        $finder->textmatch = "data a01";
        $finder->is_fullmatch = true;
        $finder->categories = array(1);
        $ids = $finder->search($dummy_uc);
        $this->assertEqual(count($ids), 0);

        // part match : "data a01" and category 4. -> NO HIT.
        $finder =& new yb_Finder();
        $finder->textmatch = "data a01";
        $finder->categories = array(4);
        $ids = $finder->search($dummy_uc);
        $this->assertEqual(count($ids), 0);
    }

    // }}}
    // {{{ _test_sys_role_cond_text_or_category()

    function _test_sys_role_cond_text_or_category()
    {
        $dummy_uc = array('id' => 1, 'role' => array('sys', 'group'));

        // part match : "data e01_1" or category 1.
        $finder =& new yb_Finder();
        $finder->textmatch = "data e01_0";
        $finder->categories = array(1);
        $finder->andor_c_t = YB_OR;
        $ids = $finder->search($dummy_uc);
        $this->assertEqual(count($ids), 27);
        $this->assertEqual($ids[0], 74);
        $this->assertTrue(in_array(61, $ids));
        $this->assertTrue(in_array(62, $ids));
        $this->assertTrue(in_array(63, $ids));
        $this->assertEqual($ids[26], 2);

        // full match : "data e01_1" (NO HITS) or category 1.
        $finder =& new yb_Finder();
        $finder->textmatch = "data e01_0";
        $finder->is_fullmatch = true;
        $finder->categories = array(1);
        $finder->andor_c_t = YB_OR;
        $ids = $finder->search($dummy_uc);
        $this->assertEqual(count($ids), 25);
        $this->assertEqual($ids[0], 74);
        $this->assertEqual($ids[24], 2);

        // part match : "data e01_1" or category 4 (NO HITS).
        $finder =& new yb_Finder();
        $finder->textmatch = "data e01_0";
        $finder->categories = array(4);
        $finder->andor_c_t = YB_OR;
        $ids = $finder->search($dummy_uc);
        $this->assertEqual(count($ids), 3);
        $this->assertEqual($ids[0], 63);
        $this->assertEqual($ids[1], 62);
        $this->assertEqual($ids[2], 61);

        // full match : "data e01_1" (NO HITS) or category 4 (NO HITS).
        $finder =& new yb_Finder();
        $finder->textmatch = "data e01_0";
        $finder->is_fullmatch = true;
        $finder->categories = array(4);
        $finder->andor_c_t = YB_OR;
        $ids = $finder->search($dummy_uc);
        $this->assertEqual(count($ids), 0);
    }

    // }}}
    // {{{ _test_guest_no_cond()

    function _test_guest_no_cond()
    {
        $dummy_uc = yb_Session::anonymous_user_context();

        $finder =& new yb_Finder();
        $ids = $finder->search($dummy_uc);

        $idx_a2d =& grain_Factory::index('pair', 'acl_to_data');
        // ACL id is 1 which allows GUEST to read.
        $ids2 = $idx_a2d->get_from(1);

        $this->assertEqual(count($ids), count($ids2[1]));
        $this->assertEqual($ids[0], 63);
        $this->assertEqual($ids[14], 1);
    }

    // }}}
    // {{{ _test_guest_with_cond()

    function _test_guest_with_cond()
    {
        $dummy_uc = yb_Session::anonymous_user_context();

        // HIT (part match "data b01", category #1, ORDER_BY_ASC)
        $finder =& new yb_Finder();
        $finder->order_by = ORDER_BY_ASC;
        $finder->textmatch = "data b01";
        $finder->categories = array(1);
        $finder->andor_c_t = YB_OR;
        $ids = $finder->search($dummy_uc);
        $this->assertEqual(count($ids), 7);
        $this->assertEqual($ids[0], 2);
        $this->assertEqual($ids[6], 62);

        // NO-HIT (full match "data b01")
        $finder =& new yb_Finder();
        $finder->order_by = ORDER_BY_ASC;
        $finder->textmatch = "data b01";
        $finder->is_fullmatch = true;
        $ids = $finder->search($dummy_uc);
        $this->assertEqual(count($ids), 0);
    }

    // }}}
    // {{{ _test_non_role_user_no_cond()

    function _test_non_role_user_no_cond()
    {
        $dao_user =& yb_dao_Factory::get('user');
        $users = $dao_user->find_by_id(4);
        $user4_uc = $users[0];
        $users = $dao_user->find_by_id(5);
        $user5_uc = $users[0];

        $finder =& new yb_Finder();
        $ids = $finder->search($user4_uc);
        $this->assertEqual(count($ids), 51);
        $this->assertEqual($ids[0], 72);
        $this->assertEqual($ids[50], 1);

        $finder =& new yb_Finder();
        $ids = $finder->search($user5_uc);
        $this->assertEqual(count($ids), 51);
        $this->assertEqual($ids[0], 75);
        $this->assertEqual($ids[50], 1);

    }

    // }}}
    // {{{ _test_non_role_user_with_cond()

    function _test_non_role_user_with_cond()
    {
        $dao_user =& new yb_dao_User();
        $users = $dao_user->find_by_id(4);
        $user4_uc = $users[0];
        $users = $dao_user->find_by_id(5);
        $user5_uc = $users[0];

        // HIT (part match "data b01", category #1,  ORDER_BY_ASC)
        $finder =& new yb_Finder();
        $finder->order_by = ORDER_BY_ASC;
        $finder->textmatch = "data b01";
        $finder->categories = array(1);
        $finder->andor_c_t = YB_OR;
        $ids = $finder->search($user4_uc);
        $this->assertEqual(count($ids), 19);
        $this->assertEqual($ids[0], 2);
        $this->assertEqual($ids[18], 71);

        // NO-HIT (full match "data b01")
        $finder =& new yb_Finder();
        $finder->order_by = ORDER_BY_ASC;
        $finder->textmatch = "data b01";
        $finder->is_fullmatch = true;
        $ids = $finder->search($user5_uc);
        $this->assertEqual(count($ids), 0);
    }

    // }}}
    // {{{ test_find_by_id_acl_combination()

    function test_find_by_id_acl_combination()
    {
        $this->_prepare(dirname(__FILE__) . '/test_Finder02');

        // data(id=1,acl=OK) : owner=2, uc['id']=2 -> ok
        $uc = array('id' => 2, 'role' => array());
        $err = array();
        $r = yb_Finder::find_by_id($uc, 1, $err, YB_ACL_PERM_READ);
        $this->assertEqual($r['id'], 1);
        $this->assertEqual($r['display_id'], '1');
        $this->assertEqual($r['owner'], 2);
        $this->assertEqual($r['acl'], 1);
        $this->assertEqual($r['title'], 'acl_everybody_ok');
        $this->assertEqual($r['display_title'], 'acl_everybody_ok');
        $this->assertEqual($r['type'], 'text');
        $this->assertEqual($r['is_versions_moderated'], true);
        $this->assertEqual($r['is_comments_moderated'], true);
        $this->assertEqual($r['format'], 'wiki');
        $this->assertEqual($r['published_at'], '20080913201423');
        $this->assertEqual($r['created_at'], '20080913201423');
        $this->assertEqual($r['updated_at'], '20080913201423');
        $cs = $r['categories'];
        $this->assertEqual(count($cs), 0);
        $vs = $r['versions'];
        $this->assertEqual(count($vs), 1);
        $this->assertEqual($vs[0], 1);
        $this->assertEqual($r['current_version'], 1);
        $this->assertEqual($r['display_version_id'], 1);
        $this->assertEqual($r['display_version_number'], 1);
        $this->assertEqual($r['current_version_id'], 1);
        $this->assertEqual($r['current_version_number'], 1);
        $this->assertEqual($r['updated_by'], 1);
        $this->assertEqual($r['_updated_by_uid'], 1);
        $this->assertEqual($r['_raw_filepath'], realpath(dirname(__FILE__) . '/test_Finder02/raw/data/500/1'));
        $_vs = $r['_versions'];
        $_vs1 = $_vs[1];
        $this->assertEqual($_vs1['id'], 1);
        $this->assertEqual($_vs1['owner'], 1);
        $this->assertEqual($_vs1['raw_id'], 1);
        $this->assertEqual($_vs1['version'], 1);
        $this->assertEqual($_vs1['approved'], true);
        $this->assertEqual($_vs1['changelog'], '');
        $this->assertEqual($_vs1['md5'], 'd41d8cd98f00b204e9800998ecf8427e');
        $this->assertEqual($_vs1['sha1'], 'da39a3ee5e6b4b0d3255bfef95601890afd80709');
        $this->assertEqual($_vs1['created_at'], '20080913201423');
        $this->assertEqual($_vs1['updated_at'], '20080913201423');

        // data(id=2,acl=NG) : owner=2, uc['id']=2 -> ok
        $uc = array('id' => 2, 'role' => array());
        $err = array();
        $r = yb_Finder::find_by_id($uc, 2, $err, YB_ACL_PERM_READ);
        $this->assertEqual($r['id'], 2);

        // data(id=1,acl=OK) : owner=2, uc['id']=1 -> ok (sys role)
        $uc = array('id' => 1, 'role' => array('sys'));
        $err = array();
        $r = yb_Finder::find_by_id($uc, 1, $err, YB_ACL_PERM_READ);
        $this->assertEqual($r['id'], 1);

        // data(id=1,acl=NG) : owner=2, uc['id']=1 -> ok (sys role)
        $uc = array('id' => 1, 'role' => array('sys'));
        $err = array();
        $r = yb_Finder::find_by_id($uc, 2, $err, YB_ACL_PERM_READ);
        $this->assertEqual($r['id'], 2);

        // data(id=1,acl=OK) : owner=2, uc['id']=3 -> ok
        $uc = array('id' => 3, 'role' => array());
        $err = array();
        $r = yb_Finder::find_by_id($uc, 1, $err, YB_ACL_PERM_READ);
        $this->assertEqual($r['id'], 1);

        // data(id=1,acl=NG) : owner=2, uc['id']=3 -> NG
        $uc = array('id' => 3, 'role' => array());
        $err = array();
        $r = yb_Finder::find_by_id($uc, 2, $err, YB_ACL_PERM_READ);
        $this->assertNull($r);
        $this->assertEqual($err['status'], 403);
        $this->assertEqual($err['args']['id'], 2);
        $this->assertEqual($err['msg'], 'You don\'t have any permission to access specified data (ID=%id).');

        $this->_cleanup();
    }

    // }}}
    // {{{ test_find_by_id_not_found()

    function test_find_by_id_not_found()
    {
        $this->_prepare(dirname(__FILE__) . '/test_Finder02');

        // data(id=300) -> not found
        $uc = array('id' => 1, 'role' => array());
        $err = array();
        $r = yb_Finder::find_by_id($uc, 300, $err, YB_ACL_PERM_READ);
        $this->assertNull($r);
        $this->assertEqual($err['status'], 404);
        $this->assertEqual($err['args']['id'], 300);
        $this->assertEqual($err['msg'], 'data (ID=%id) was not found.');

        // data(id=3) (physical data was not found.)
        $uc = array('id' => 1, 'role' => array('sys'));
        $err = array();
        $r = yb_Finder::find_by_id($uc, 3, $err, YB_ACL_PERM_READ);
        $this->assertNull($r);
        $this->assertEqual($err['status'], 500);
        $this->assertEqual($err['args']['id'], 3);
        $this->assertEqual($err['msg'], 'physical file data (ID=%id, VERSION=%version) is not found.');

        $this->_cleanup();
    }

    // }}}
    // {{{ test_find_by_id_expand()

    function test_find_by_id_expand()
    {
        $this->_prepare(dirname(__FILE__) . '/test_Finder02');

        // data(id=4), expand = true : expand ok
        $uc = array('id' => 1, 'role' => array('sys'));
        $err = array();
        $r = yb_Finder::find_by_id($uc, 4, $err, YB_ACL_PERM_READ, true);
        $this->assertEqual($r['id'], 4);
        $this->assertEqual($r['owner']['id'], 2);
        $this->assertEqual($r['owner']['name'], 'dummy02');
        $this->assertEqual($r['owner']['mail'], 'test02@test.com');
        $this->assertEqual($r['owner']['password'], 'dummy');
        $this->assertEqual($r['owner']['status'], 2);
        $_role = $r['owner']['role'];
        $this->assertEqual(count($_role), 0);
        $this->assertEqual($r['owner']['created_at'], '20080913201422');
        $this->assertEqual($r['owner']['updated_at'], '20080913201422');
        $this->assertEqual($r['_owner_uid'], 2);
        $cs = $r['categories'];
        $this->assertEqual(count($cs), 3);
        $_c = $cs[0];
        $this->assertEqual($_c['id'], 1);
        $this->assertEqual($_c['owner'], 1);
        $this->assertEqual($_c['name'], 'category1');
        $this->assertEqual($_c['created_at'], '20080913201423');
        $this->assertEqual($_c['updated_at'], '20080913201423');
        $_c = $cs[1];
        $this->assertEqual($_c['id'], 2);
        $this->assertEqual($_c['owner'], 1);
        $this->assertEqual($_c['name'], 'category2');
        $_c = $cs[2];
        $this->assertEqual($_c['id'], 3);
        $this->assertEqual($_c['owner'], 1);
        $this->assertEqual($_c['name'], 'category3');
        $vs = $r['versions'];
        $this->assertEqual(count($vs), 1);
        $this->assertEqual($vs[0], 4);
        $this->assertEqual($r['current_version'], 4);
        $this->assertEqual($r['display_version_id'], 4);
        $this->assertEqual($r['display_version_number'], 1);
        $this->assertEqual($r['current_version_id'], 4);
        $this->assertEqual($r['current_version_number'], 1);
        $_u = $r['updated_by'];
        $this->assertEqual($_u['id'], 3);
        $this->assertEqual($_u['name'], 'dummy03');
        $this->assertEqual($_u['mail'], 'test03@test.com');
        $this->assertEqual($_u['password'], 'dummy');
        $this->assertEqual($_u['status'], 2);
        $_role = $_u['role'];
        $this->assertEqual(count($_role), 0);
        $this->assertEqual($_u['created_at'], '20080913201422');
        $this->assertEqual($_u['updated_at'], '20080913201422');
        $this->assertEqual($r['_updated_by_uid'], 3);
        $this->assertEqual($r['_raw_filepath'], realpath(dirname(__FILE__) . '/test_Finder02/raw/data/500/4'));
        $_vs = $r['_versions'];
        $_vs1 = $_vs[1];
        $this->assertEqual($_vs1['id'], 4);
        $this->assertEqual($_vs1['owner'], 3);
        $this->assertEqual($_vs1['raw_id'], 4);
        $this->assertEqual($_vs1['version'], 1);
        $this->assertEqual($_vs1['approved'], true);

        // data(id=5), expand = true : expand ng
        $uc = array('id' => 1, 'role' => array('sys'));
        $err = array();
        $r = yb_Finder::find_by_id($uc, 5, $err, YB_ACL_PERM_READ, true);
        $this->assertEqual($r['id'], 5);
        $this->assertEqual($r['owner'], false);
        $this->assertEqual($r['_owner_uid'], 9);
        $cs = $r['categories'];
        $this->assertEqual(count($cs), 0);
        $vs = $r['versions'];
        $this->assertEqual(count($vs), 1);
        $this->assertEqual($vs[0], 5);
        $this->assertEqual($r['current_version'], 5);
        $this->assertEqual($r['updated_by'], false);
        $this->assertEqual($r['_updated_by_uid'], 9);
        $this->assertEqual($r['_raw_filepath'], realpath(dirname(__FILE__) . '/test_Finder02/raw/data/500/5'));
        $_vs = $r['_versions'];
        $_vs1 = $_vs[1];
        $this->assertEqual($_vs1['id'], 5);
        $this->assertEqual($_vs1['owner'], 9);
        $this->assertEqual($_vs1['raw_id'], 5);
        $this->assertEqual($_vs1['version'], 1);
        $this->assertEqual($_vs1['approved'], true);

        $this->_cleanup();
    }

    // }}}
    // {{{ test_find_by_id_multiple_versions()

    function test_find_by_id_multiple_versions()
    {
        $this->_prepare(dirname(__FILE__) . '/test_Finder02');

        // data(id=6), multiple versions
        $uc = array('id' => 1, 'role' => array('sys'));
        $err = array();
        $r = yb_Finder::find_by_id($uc, 6, $err, YB_ACL_PERM_READ);
        $this->assertEqual($r['id'], 6);
        $this->assertEqual($r['display_id'], '6');
        $this->assertIdentical($r['title'], $r['display_title']);
        $vs = $r['versions'];
        $this->assertEqual(count($vs), 3);
        $this->assertEqual($vs[0], 6);
        $this->assertEqual($vs[1], 7);
        $this->assertEqual($vs[2], 8);
        $this->assertEqual($r['current_version'], 7);
        $this->assertEqual($r['display_version_id'], 7);
        $this->assertEqual($r['display_version_number'], 5);
        $this->assertEqual($r['current_version_id'], 7);
        $this->assertEqual($r['current_version_number'], 5);
        $this->assertEqual($r['updated_by'], 2);
        $this->assertEqual($r['_updated_by_uid'], 2);
        $this->assertEqual($r['_raw_filepath'], realpath(dirname(__FILE__) . '/test_Finder02/raw/data/500/5'));
        $_vs = $r['_versions'];
        $_v = $_vs[1];
        $this->assertEqual($_v['id'], 6);
        $this->assertEqual($_v['owner'], 1);
        $this->assertEqual($_v['raw_id'], -1);
        $this->assertEqual($_v['version'], 1);
        $this->assertEqual($_v['approved'], true);
        $this->assertEqual($_v['changelog'], '');
        $this->assertEqual($_v['md5'], 'd41d8cd98f00b204e9800998ecf8427e');
        $this->assertEqual($_v['sha1'], 'da39a3ee5e6b4b0d3255bfef95601890afd80709');
        $this->assertEqual($_v['created_at'], '20080913201429');
        $this->assertEqual($_v['updated_at'], '20080913201429');
        $_v = $_vs[5];
        $this->assertEqual($_v['id'], 7);
        $this->assertEqual($_v['owner'], 2);
        $this->assertEqual($_v['raw_id'], 5);
        $this->assertEqual($_v['version'], 5);
        $this->assertEqual($_v['approved'], true);
        $this->assertEqual($_v['changelog'], '');
        $this->assertEqual($_v['md5'], 'd41d8cd98f00b204e9800998ecf8427e');
        $this->assertEqual($_v['sha1'], 'da39a3ee5e6b4b0d3255bfef95601890afd80709');
        $this->assertEqual($_v['created_at'], '20080913201430');
        $this->assertEqual($_v['updated_at'], '20080913201430');
        $_v = $_vs[3];
        $this->assertEqual($_v['id'], 8);
        $this->assertEqual($_v['owner'], 3);
        $this->assertEqual($_v['raw_id'], -1);
        $this->assertEqual($_v['version'], 3);
        $this->assertEqual($_v['approved'], true);
        $this->assertEqual($_v['changelog'], '');
        $this->assertEqual($_v['md5'], 'd41d8cd98f00b204e9800998ecf8427e');
        $this->assertEqual($_v['sha1'], 'da39a3ee5e6b4b0d3255bfef95601890afd80709');
        $this->assertEqual($_v['created_at'], '20080913201431');
        $this->assertEqual($_v['updated_at'], '20080913201431');

        // data(id=7), multiple versions, but current is not found.
        $uc = array('id' => 1, 'role' => array('sys'));
        $err = array();
        $r = yb_Finder::find_by_id($uc, 7, $err, YB_ACL_PERM_READ);
        $this->assertNull($r);
        $this->assertEqual($err['status'], 404);
        $this->assertEqual($err['args']['id'], 7);
        $this->assertEqual($err['msg'], 'data (ID=%id) was not found.');

        $this->_cleanup();
    }

    // }}}
    // {{{ test_find_by_id_multiversion_approved()

    function test_find_by_id_multiversion_approved()
    {
        $this->_prepare(dirname(__FILE__) . '/test_Finder02');

        // data(id=8), uc['id'] = 1(sys), all versions found.
        $uc = array('id' => 1, 'role' => array('sys'));
        $err = array();
        $r = yb_Finder::find_by_id($uc, 8, $err, YB_ACL_PERM_READ);
        $this->assertEqual($r['id'], 8);
        $vs = $r['versions'];
        $this->assertEqual(count($vs), 3);
        $this->assertEqual($vs[0], 9);
        $this->assertEqual($vs[1], 10);
        $this->assertEqual($vs[2], 11);
        $this->assertEqual($r['current_version'], 10);
        $this->assertEqual($r['display_version_id'], 10);
        $this->assertEqual($r['display_version_number'], 2);
        $this->assertEqual($r['current_version_id'], 10);
        $this->assertEqual($r['current_version_number'], 2);
        $this->assertEqual($r['updated_by'], 1);
        $this->assertEqual($r['_updated_by_uid'], 1);
        $this->assertEqual($r['_raw_filepath'], realpath(dirname(__FILE__) . '/test_Finder02/raw/data/500/5'));
        $_vs = $r['_versions'];
        $_v = $_vs[1];
        $this->assertEqual($_v['id'], 9);
        $this->assertEqual($_v['owner'], 1);
        $this->assertEqual($_v['raw_id'], 5);
        $this->assertEqual($_v['version'], 1);
        $this->assertEqual($_v['approved'], true);
        $this->assertEqual($_v['changelog'], '');
        $_v = $_vs[2];
        $this->assertEqual($_v['id'], 10);
        $this->assertEqual($_v['owner'], 1);
        $this->assertEqual($_v['raw_id'], 5);
        $this->assertEqual($_v['version'], 2);
        $this->assertEqual($_v['approved'], false);
        $_v = $_vs[3];
        $this->assertEqual($_v['id'], 11);
        $this->assertEqual($_v['owner'], 1);
        $this->assertEqual($_v['raw_id'], 5);
        $this->assertEqual($_v['version'], 3);
        $this->assertEqual($_v['approved'], false);

        // data(id=8), uc['id'] = 3(none), current is not approved.
        $uc = array('id' => 3, 'role' => array());
        $err = array();
        $r = yb_Finder::find_by_id($uc, 8, $err, YB_ACL_PERM_READ);
        $this->assertNull($r);
        $this->assertEqual($err['status'], 404);
        $this->assertEqual($err['args']['id'], 8);
        $this->assertEqual($err['msg'], 'data (ID=%id) was not found.');

        $this->_cleanup();
    }

    // }}}
    // {{{ test_find_by_id_version_specify()

    function test_find_by_id_version_specify()
    {
        $this->_prepare(dirname(__FILE__) . '/test_Finder02');

        // data(id=9), version is specified.
        $uc = array('id' => 1, 'role' => array('sys'));
        $err = array();
        $r = yb_Finder::find_by_id($uc, 9, $err, YB_ACL_PERM_READ, false, 2);
        $this->assertEqual($r['id'], 9);
        $this->assertEqual($r['title'], 'version is specified');
        $this->assertEqual($r['display_id'], '9_2');
        $this->assertEqual($r['display_title'], 'version is specified (v2)');
        $vs = $r['versions'];
        $this->assertEqual(count($vs), 3);
        $this->assertEqual($vs[0], 12);
        $this->assertEqual($vs[1], 13);
        $this->assertEqual($vs[2], 14);
        $this->assertEqual($r['current_version'], 12);
        $this->assertEqual($r['display_version_id'], 13);
        $this->assertEqual($r['display_version_number'], 2);
        $this->assertEqual($r['current_version_id'], 12);
        $this->assertEqual($r['current_version_number'], 1);
        $this->assertEqual($r['updated_by'], 1);
        $this->assertEqual($r['_updated_by_uid'], 1);
        $this->assertEqual($r['_raw_filepath'], realpath(dirname(__FILE__) . '/test_Finder02/raw/data/500/5'));
        $_vs = $r['_versions'];
        $_v = $_vs[1];
        $this->assertEqual($_v['id'], 12);
        $this->assertEqual($_v['owner'], 1);
        $this->assertEqual($_v['raw_id'], -1);
        $this->assertEqual($_v['version'], 1);
        $this->assertEqual($_v['approved'], false);
        $_v = $_vs[2];
        $this->assertEqual($_v['id'], 13);
        $this->assertEqual($_v['owner'], 1);
        $this->assertEqual($_v['raw_id'], 5);
        $this->assertEqual($_v['version'], 2);
        $this->assertEqual($_v['approved'], false);
        $_v = $_vs[3];
        $this->assertEqual($_v['id'], 14);
        $this->assertEqual($_v['owner'], 1);
        $this->assertEqual($_v['raw_id'], -1);
        $this->assertEqual($_v['version'], 3);
        $this->assertEqual($_v['approved'], false);

        // data(id=9), version is specified but not exists.
        $uc = array('id' => 1, 'role' => array('sys'));
        $err = array();
        $r = yb_Finder::find_by_id($uc, 9, $err, YB_ACL_PERM_READ, false, -1);
        $this->assertNull($r);
        $this->assertEqual($err['status'], 404);
        $this->assertEqual($err['args']['id'], 9);
        $this->assertEqual($err['msg'], 'data (ID=%id) was not found.');

        // data(id=9), version is specified, but not approved.
        $uc = array('id' => 3, 'role' => array());
        $err = array();
        $r = yb_Finder::find_by_id($uc, 9, $err, YB_ACL_PERM_READ, false, 2);
        $this->assertNull($r);
        $this->assertEqual($err['status'], 404);
        $this->assertEqual($err['args']['id'], 9);
        $this->assertEqual($err['msg'], 'data (ID=%id) was not found.');

        $this->_cleanup();
    }

    // }}}
    // {{{ test_find_by_title_1hit()

    function test_find_by_title_1hit()
    {
        $this->_prepare(dirname(__FILE__) . '/test_Finder02');

        // data(id=1,acl=OK) : owner=2, uc['id']=3 -> ok
        $uc = array('id' => 3, 'role' => array());
        $err = array();
        $r = yb_Finder::find_by_title(
            $uc, 'find_by_title1', $err, YB_ACL_PERM_READ, true);
        $this->assertEqual($r['id'], 1);
        $this->assertEqual($r['display_id'], '1');
        $this->assertEqual($r['owner']['id'], 2);
        $this->assertEqual($r['owner']['name'], 'dummy02');
        $this->assertEqual($r['owner']['mail'], 'test02@test.com');
        $this->assertEqual($r['owner']['password'], 'dummy');
        $this->assertEqual($r['owner']['status'], 2);
        $_role = $r['owner']['role'];
        $this->assertEqual(count($_role), 0);
        $this->assertEqual($r['owner']['created_at'], '20080913201422');
        $this->assertEqual($r['owner']['updated_at'], '20080913201422');
        $this->assertEqual($r['_owner_uid'], 2);
        $this->assertEqual($r['acl'], 1);
        $this->assertEqual($r['title'], 'acl_everybody_ok');
        $this->assertEqual($r['display_title'], 'acl_everybody_ok');
        $this->assertEqual($r['type'], 'text');
        $this->assertEqual($r['is_versions_moderated'], true);
        $this->assertEqual($r['is_comments_moderated'], true);
        $this->assertEqual($r['format'], 'wiki');
        $this->assertEqual($r['published_at'], '20080913201423');
        $this->assertEqual($r['created_at'], '20080913201423');
        $this->assertEqual($r['updated_at'], '20080913201423');
        $cs = $r['categories'];
        $this->assertEqual(count($cs), 0);
        $vs = $r['versions'];
        $this->assertEqual(count($vs), 1);
        $this->assertEqual($vs[0], 1);
        $this->assertEqual($r['current_version'], 1);
        $this->assertEqual($r['display_version_id'], 1);
        $this->assertEqual($r['display_version_number'], 1);
        $this->assertEqual($r['current_version_id'], 1);
        $this->assertEqual($r['current_version_number'], 1);
        $this->assertEqual($r['updated_by']['id'], 1);
        $this->assertEqual($r['updated_by']['name'], 'dummy01');
        $this->assertEqual($r['updated_by']['mail'], 'test01@test.com');
        $this->assertEqual($r['updated_by']['password'], 'dummy');
        $this->assertEqual($r['updated_by']['status'], 2);
        $_role = $r['updated_by']['role'];
        $this->assertEqual(count($_role), 1);
        $this->assertTrue(in_array('sys', $_role));
        $this->assertEqual($r['updated_by']['created_at'], '20080913201422');
        $this->assertEqual($r['updated_by']['updated_at'], '20080913201422');
        $this->assertEqual($r['_updated_by_uid'], 1);
        $this->assertEqual($r['_raw_filepath'], realpath(dirname(__FILE__) . '/test_Finder02/raw/data/500/1'));
        $_vs = $r['_versions'];
        $_vs1 = $_vs[1];
        $this->assertEqual($_vs1['id'], 1);
        $this->assertEqual($_vs1['owner'], 1);
        $this->assertEqual($_vs1['raw_id'], 1);
        $this->assertEqual($_vs1['version'], 1);
        $this->assertEqual($_vs1['approved'], true);
        $this->assertEqual($_vs1['changelog'], '');
        $this->assertEqual($_vs1['md5'], 'd41d8cd98f00b204e9800998ecf8427e');
        $this->assertEqual($_vs1['sha1'], 'da39a3ee5e6b4b0d3255bfef95601890afd80709');
        $this->assertEqual($_vs1['created_at'], '20080913201423');
        $this->assertEqual($_vs1['updated_at'], '20080913201423');

        // data(id=1,acl=NG) : owner=2, uc['id']=3 -> NG
        $uc = array('id' => 3, 'role' => array());
        $err = array();
        $r = yb_Finder::find_by_title(
            $uc, 'find_by_title2', $err, YB_ACL_PERM_READ, true);
        $this->assertNull($r);
        $this->assertEqual($err['status'], 403);
        $this->assertEqual($err['args']['id'], 2);
        $this->assertEqual($err['msg'], 'You don\'t have any permission to access specified data (ID=%id).');

        $this->_cleanup();
    }

    // }}}
    // {{{ test_find_by_title_nohit()

    function test_find_by_title_nohit()
    {
        $this->_prepare(dirname(__FILE__) . '/test_Finder02');

        $uc = array('id' => 1, 'role' => array('sys'));
        $err = array();
        $r = yb_Finder::find_by_title(
            $uc, 'abc', $err, YB_ACL_PERM_READ);
        $this->assertNull($r);
        $this->assertEqual($err['status'], 404);
        $this->assertEqual($err['args']['title'], 'abc');
        $this->assertEqual($err['msg'], 'data (title=%title) was not found.');

        $this->_cleanup();
    }

    // }}}
    // {{{ test_find_by_title_multi_hit()

    function test_find_by_title_multi_hit()
    {
        $this->_prepare(dirname(__FILE__) . '/test_Finder02');

        $uc = array('id' => 1, 'role' => array('sys'));
        $err = array();
        $r = yb_Finder::find_by_title(
            $uc, 'multi hit', $err, YB_ACL_PERM_READ);
        $this->assertNull($r);
        $this->assertEqual($err['status'], 500);
        $this->assertEqual($err['args']['title'], 'multi hit');
        $this->assertEqual($err['msg'], 'data (title=%title) multiple found.');

        $this->_cleanup();
    }

    // }}}
    // {{{ test_listmatch()

    function test_listmatch()
    {
        $this->_prepare(dirname(__FILE__) . '/test_Finder03');

        $uc = array('id' => 1, 'role' => array('sys'));
        $finder =& new yb_Finder();
        $finder->textmatch = 'dir1/dir2';
        $finder->use_listmatch = true;
        $ids = $finder->search($uc);
        $this->assertEqual(count($ids), 2);
        $this->assertEqual($ids[0], 2);
        $this->assertEqual($ids[1], 1);

        $finder =& new yb_Finder();
        $finder->textmatch = 'dir_A';
        $finder->use_listmatch = true;
        $ids = $finder->search($uc);
        $this->assertEqual(count($ids), 3);
        $this->assertEqual($ids[0], 5);
        $this->assertEqual($ids[1], 4);
        $this->assertEqual($ids[2], 3);

        $finder =& new yb_Finder();
        $finder->textmatch = 'dir_A/';
        $finder->use_listmatch = true;
        $ids = $finder->search($uc);
        $this->assertEqual(count($ids), 2);
        $this->assertEqual($ids[0], 4);
        $this->assertEqual($ids[1], 3);

        $this->_cleanup();
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
