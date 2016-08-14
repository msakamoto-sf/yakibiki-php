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

require_once('yb/tx/acl/Finder.php');
require_once('yb/tx/acl/Create.php');
require_once('yb/tx/acl/Update.php');
require_once('yb/tx/acl/Delete.php');
require_once('yb/tx/UnitTestCaseBase.php');

class yb_tx_acl_All_TestCase extends yb_tx_UnitTestCaseBase
{
    // {{{ test_all()

    function test_all()
    {
        // make dummy users
        $dao_user =& yb_dao_Factory::get('user');
        $dao_user->create(array(
            'mail' => 'test01@hoge.com',
            'name' => 'dummy01',
            'password' => 'dummy',
            'role' => array(),
        ));
        $dao_user->create(array(
            'mail' => 'test02@hoge.com',
            'name' => 'dummy02',
            'password' => 'dummy',
            'role' => array(),
        ));

        // make dummy groups
        $dao_group =& yb_dao_Factory::get('group');
        $dao_group->create(array(
            'owner' => 1,
            'name' => 'group01',
        ));
        $dao_group->create(array(
            'owner' => 1,
            'name' => 'group02',
        ));

        $this->_test_Create_and_Finds();
        $this->_test_Update();
        $this->_test_Delete();
    }

    // }}}
    // {{{ _test_Create_and_Finds()

    function _test_Create_and_Finds()
    {
        // no acls, Finder::all() behaviour
        $r = yb_tx_acl_Finder::all();
        $this->assertEqual(count($r), 0);

        // empty perms
        $acl = array(
            'owner' => 1,
            'name' => 'acl02',
            'policy' => YB_ACL_POLICY_NEGA,
        );
        $r = yb_tx_acl_Create::go($acl);
        $this->assertEqual($r['id'], 1);
        $this->assertEqual($r['name'], $acl['name']);
        $this->assertEqual($r['owner']['id'], 1);
        $this->assertEqual($r['policy'], $acl['policy']);
        $perms = $r['perms'];
        $this->assertEqual(count($perms), 0);

        // perms including YB_GUEST_UID, YB_LOGINED_UID, and user, group
        $acl = array(
            'owner' => 3, // un registered user id
            'name' => 'acl01',
            'policy' => YB_ACL_POLICY_POSI,
            'perms' => array(
                array(
                    'type' => YB_ACL_TYPE_USER,
                    'id' => YB_GUEST_UID,
                    'perm' => YB_ACL_PERM_READ,
                ),
                array(
                    'type' => YB_ACL_TYPE_USER,
                    'id' => YB_LOGINED_UID,
                    'perm' => YB_ACL_PERM_READWRITE,
                ),
                array(
                    'type' => YB_ACL_TYPE_USER,
                    'id' => 2,
                    'perm' => YB_ACL_PERM_NONE,
                ),
                array(
                    'type' => YB_ACL_TYPE_GROUP,
                    'id' => 1,
                    'perm' => YB_ACL_PERM_NONE,
                ),
            ));
        $r = yb_tx_acl_Create::go($acl);
        $this->assertEqual($r['id'], 2);
        $this->assertEqual($r['name'], $acl['name']);
        $this->assertEqual($r['owner'], false);
        $this->assertEqual($r['policy'], $acl['policy']);
        $perms = $r['perms'];
        $this->assertEqual(count($perms), 4);
        $this->assertEqual($perms[0]['type'], $acl['perms'][0]['type']);
        $this->assertEqual($perms[0]['id'],   $acl['perms'][0]['id']);
        $this->assertEqual($perms[0]['perm'], $acl['perms'][0]['perm']);
        $this->assertEqual($perms[1]['type'], $acl['perms'][1]['type']);
        $this->assertEqual($perms[1]['id'],   $acl['perms'][1]['id']);
        $this->assertEqual($perms[1]['perm'], $acl['perms'][1]['perm']);
        $this->assertEqual($perms[2]['type'], $acl['perms'][2]['type']);
        $this->assertEqual($perms[2]['id'],   $acl['perms'][2]['id']);
        $this->assertEqual($perms[2]['perm'], $acl['perms'][2]['perm']);
        $this->assertEqual($perms[3]['type'], $acl['perms'][3]['type']);
        $this->assertEqual($perms[3]['id'],   $acl['perms'][3]['id']);
        $this->assertEqual($perms[3]['perm'], $acl['perms'][3]['perm']);

        // Finder::all() : default sort
        $r = yb_tx_acl_Finder::all();
        $this->assertEqual(count($r), 2);

        $a = $r[0];
        $this->assertEqual($a['id'], 1);
        $this->assertEqual($a['name'], 'acl02');
        $this->assertEqual($a['owner']['id'], 1);
        $this->assertEqual($a['policy'], YB_ACL_POLICY_NEGA);
        $perms = $a['perms'];
        $this->assertEqual(count($perms), 0);

        $a = $r[1];
        $this->assertEqual($a['id'], 2);
        $this->assertEqual($a['name'], 'acl01');
        $this->assertEqual($a['owner'], false);
        $this->assertEqual($a['policy'], YB_ACL_POLICY_POSI);
        $perms = $a['perms'];
        $this->assertEqual(count($perms), 4);
        $this->assertEqual($perms[0]['type'], $acl['perms'][0]['type']);
        $this->assertEqual($perms[0]['id'],   $acl['perms'][0]['id']);
        $this->assertEqual($perms[0]['perm'], $acl['perms'][0]['perm']);
        $this->assertEqual($perms[1]['type'], $acl['perms'][1]['type']);
        $this->assertEqual($perms[1]['id'],   $acl['perms'][1]['id']);
        $this->assertEqual($perms[1]['perm'], $acl['perms'][1]['perm']);
        $this->assertEqual($perms[2]['type'], $acl['perms'][2]['type']);
        $this->assertEqual($perms[2]['id'],   $acl['perms'][2]['id']);
        $this->assertEqual($perms[2]['perm'], $acl['perms'][2]['perm']);
        $this->assertEqual($perms[3]['type'], $acl['perms'][3]['type']);
        $this->assertEqual($perms[3]['id'],   $acl['perms'][3]['id']);
        $this->assertEqual($perms[3]['perm'], $acl['perms'][3]['perm']);

        // Finder::all() : sort by name, desc
        $r = yb_tx_acl_Finder::all('name', ORDER_BY_DESC);
        $this->assertEqual($r[0]['id'], 1);
        $this->assertEqual($r[1]['id'], 2);

        // Finder::by_id()
        $r = yb_tx_acl_Finder::by_id(1);
        $this->assertEqual(count($r), 1);

        $a = $r[0];
        $this->assertEqual($a['id'], 1);
        $this->assertEqual($a['name'], 'acl02');
        $this->assertEqual($a['owner']['id'], 1);
        $this->assertEqual($a['policy'], YB_ACL_POLICY_NEGA);
        $perms = $a['perms'];
        $this->assertEqual(count($perms), 0);

        // Finder::by_id()
        $r = yb_tx_acl_Finder::by_id(3);
        $this->assertEqual(count($r), 0);

        // Finder::by_id()
        $r = yb_tx_acl_Finder::by_id(array(1, 2, 3), 'name', ORDER_BY_DESC);
        $this->assertEqual($r[0]['id'], 1);
        $this->assertEqual($r[1]['id'], 2);
    }

    // }}}
    // {{{ _test_Update()

    function _test_Update()
    {
        $acl = array(
            'owner' => 3, // un registered user id
            'name' => 'acl99',
            'policy' => YB_ACL_POLICY_POSI,
            'perms' => array(
                array(
                    'type' => YB_ACL_TYPE_USER,
                    'id' => YB_GUEST_UID,
                    'perm' => YB_ACL_PERM_READ,
                ),
                array(
                    'type' => YB_ACL_TYPE_USER,
                    'id' => YB_LOGINED_UID,
                    'perm' => YB_ACL_PERM_READWRITE,
                ),
                array(
                    'type' => YB_ACL_TYPE_USER,
                    'id' => 2,
                    'perm' => YB_ACL_PERM_NONE,
                ),
                array(
                    'type' => YB_ACL_TYPE_GROUP,
                    'id' => 1,
                    'perm' => YB_ACL_PERM_NONE,
                ),
            ));
        $this->assertTrue(yb_tx_acl_Update::go(1, $acl));
        $rs = yb_tx_acl_Finder::by_id(1);
        $r = $rs[0];
        $this->assertEqual($r['id'], 1);
        $this->assertEqual($r['name'], 'acl99');
        $this->assertEqual($r['owner'], false);
        $this->assertEqual($r['policy'], YB_ACL_POLICY_POSI);
        $perms = $r['perms'];
        $this->assertEqual(count($perms), 4);
        $this->assertEqual($perms[0]['type'], $acl['perms'][0]['type']);
        $this->assertEqual($perms[0]['id'],   $acl['perms'][0]['id']);
        $this->assertEqual($perms[0]['perm'], $acl['perms'][0]['perm']);
        $this->assertEqual($perms[1]['type'], $acl['perms'][1]['type']);
        $this->assertEqual($perms[1]['id'],   $acl['perms'][1]['id']);
        $this->assertEqual($perms[1]['perm'], $acl['perms'][1]['perm']);
        $this->assertEqual($perms[2]['type'], $acl['perms'][2]['type']);
        $this->assertEqual($perms[2]['id'],   $acl['perms'][2]['id']);
        $this->assertEqual($perms[2]['perm'], $acl['perms'][2]['perm']);
        $this->assertEqual($perms[3]['type'], $acl['perms'][3]['type']);
        $this->assertEqual($perms[3]['id'],   $acl['perms'][3]['id']);
        $this->assertEqual($perms[3]['perm'], $acl['perms'][3]['perm']);

        // UNDEFINED id results false.
        $data = array(
            "name" => "acl999",
        );
        $this->assertFalse(yb_tx_acl_Update::go(999, $data));
    }

    // }}}
    // {{{ _test_Delete()

    function _test_Delete()
    {
        $this->assertTrue(yb_tx_acl_Delete::go(1));

        // unregistered id
        $this->assertFalse(yb_tx_acl_Delete::go(999));
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
