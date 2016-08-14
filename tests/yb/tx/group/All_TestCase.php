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

require_once('yb/tx/group/Finder.php');
require_once('yb/tx/group/Create.php');
require_once('yb/tx/group/Update.php');
require_once('yb/tx/group/Delete.php');
require_once('yb/tx/UnitTestCaseBase.php');

class yb_tx_group_All_TestCase extends yb_tx_UnitTestCaseBase
{
    // {{{ test_all()

    function test_all()
    {
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

        $this->_test_Create_and_Finds();
        $this->_test_Update();
        $this->_test_Delete();
    }

    // }}}
    // {{{ _test_Create_and_Finds()

    function _test_Create_and_Finds()
    {
        // no groups, Finder::all() behaviour
        $results = yb_tx_group_Finder::all();
        $this->assertEqual(count($results), 0);

        // create : group #1
        $g1 = array(
            'owner' => 1,
            'name' => 'group01',
            'mates' => array(1, 2, 3),
        );
        $results = yb_tx_group_Create::go($g1);
        $this->assertEqual($results['id'], 1);
        $this->assertEqual($results['name'], $g1['name']);
        $this->assertEqual($results['owner']['id'], $g1['owner']);
        $mates = $results['mates'];
        $this->assertEqual(count($mates), 3);
        $this->assertTrue(in_array(1, $mates));
        $this->assertTrue(in_array(2, $mates));
        $this->assertTrue(in_array(3, $mates));

        // create : group #2
        $g2 = array(
            'owner' => 2,
            'name' => 'group02',
        );
        $results = yb_tx_group_Create::go($g2);
        $this->assertEqual($results['id'], 2);
        $this->assertEqual($results['name'], $g2['name']);
        $this->assertEqual($results['owner']['id'], $g2['owner']);
        $mates = $results['mates'];
        $this->assertEqual(count($mates), 0);

        // create : group #3
        $g3 = array(
            'owner' => 3, // unregistered user
            'name' => 'group03',
            'mates' => array(1),
        );
        $results = yb_tx_group_Create::go($g3);
        $this->assertEqual($results['id'], 3);
        $this->assertEqual($results['name'], $g3['name']);
        $this->assertEqual($results['owner'], false); // unregistered user
        $mates = $results['mates'];
        $this->assertEqual(count($mates), 1);
        $this->assertTrue(in_array(1, $mates));

        // Finder::all() : default sort
        $results = yb_tx_group_Finder::all();
        $this->assertEqual(count($results), 3);

        $g = $results[0];
        $this->assertEqual($g['id'], 1);
        $this->assertEqual($g['name'], $g1['name']);
        $this->assertEqual($g['owner']['id'], $g1['owner']);
        $mates = $g['mates'];
        $this->assertEqual(count($mates), 3);
        $this->assertTrue(in_array(1, $mates));
        $this->assertTrue(in_array(2, $mates));
        $this->assertTrue(in_array(3, $mates));

        $g = $results[1];
        $this->assertEqual($g['id'], 2);
        $this->assertEqual($g['name'], $g2['name']);
        $this->assertEqual($g['owner']['id'], $g2['owner']);
        $mates = $g['mates'];
        $this->assertEqual(count($mates), 0);

        $g = $results[2];
        $this->assertEqual($g['id'], 3);
        $this->assertEqual($g['name'], $g3['name']);
        $this->assertEqual($g['owner'], false); // unregistered user
        $mates = $g['mates'];
        $this->assertEqual(count($mates), 1);
        $this->assertTrue(in_array(1, $mates));

        // Finder::all() : sort by name, desc
        $results = yb_tx_group_Finder::all('name', ORDER_BY_DESC);
        $this->assertEqual(count($results), 3);

        $g = $results[2];
        $this->assertEqual($g['id'], 1);
        $this->assertEqual($g['name'], $g1['name']);
        $this->assertEqual($g['owner']['id'], $g1['owner']);
        $mates = $g['mates'];
        $this->assertEqual(count($mates), 3);
        $this->assertTrue(in_array(1, $mates));
        $this->assertTrue(in_array(2, $mates));
        $this->assertTrue(in_array(3, $mates));

        $g = $results[1];
        $this->assertEqual($g['id'], 2);
        $this->assertEqual($g['name'], $g2['name']);
        $this->assertEqual($g['owner']['id'], $g2['owner']);
        $mates = $g['mates'];
        $this->assertEqual(count($mates), 0);

        $g = $results[0];
        $this->assertEqual($g['id'], 3);
        $this->assertEqual($g['name'], $g3['name']);
        $this->assertEqual($g['owner'], false); // unregistered user
        $mates = $g['mates'];
        $this->assertEqual(count($mates), 1);
        $this->assertTrue(in_array(1, $mates));

        // Finder::by_id()
        $r = yb_tx_group_Finder::by_id(1);
        $this->assertEqual(count($r), 1);
        $g = $r[0];
        $this->assertEqual($g['id'], 1);
        $this->assertEqual($g['name'], $g1['name']);
        $this->assertEqual($g['owner']['id'], $g1['owner']);
        $mates = $g['mates'];
        $this->assertEqual(count($mates), 3);
        $this->assertTrue(in_array(1, $mates));
        $this->assertTrue(in_array(2, $mates));
        $this->assertTrue(in_array(3, $mates));

        $r = yb_tx_group_Finder::by_id(2);
        $this->assertEqual(count($r), 1);
        $g = $r[0];
        $this->assertEqual($g['id'], 2);
        $this->assertEqual($g['name'], $g2['name']);
        $this->assertEqual($g['owner']['id'], $g2['owner']);
        $mates = $g['mates'];
        $this->assertEqual(count($mates), 0);

        $r = yb_tx_group_Finder::by_id(3);
        $this->assertEqual(count($r), 1);
        $g = $r[0];
        $this->assertEqual($g['id'], 3);
        $this->assertEqual($g['name'], $g3['name']);
        $this->assertEqual($g['owner'], false); // unregistered user
        $mates = $g['mates'];
        $this->assertEqual(count($mates), 1);
        $this->assertTrue(in_array(1, $mates));

        $r = yb_tx_group_Finder::by_id(4); // unregistered group id
        $this->assertEqual(count($r), 0);

        // multiple find by id
        $r = yb_tx_group_Finder::by_id(
            array(1, 3, 4), 'name', ORDER_BY_DESC);
        $this->assertEqual(count($r), 2);
        $this->assertEqual($r[0]['id'], 3);
        $this->assertEqual($r[1]['id'], 1);
    }

    // }}}
    // {{{ _test_Update()

    function _test_Update()
    {
        $this->assertTrue(yb_tx_group_Update::go(1, array(
            'owner' => 2,
            'name' => 'group01_02',
            'mates' => array(2, 3, 4, 5),
        )));
        $r = yb_tx_group_Finder::by_id(1);
        $this->assertEqual($r[0]['id'], 1);
        $this->assertEqual($r[0]['name'], 'group01_02');
        $this->assertEqual($r[0]['owner']['id'], 2);
        $mates = $r[0]['mates'];
        $this->assertEqual(count($mates), 4);
        $this->assertTrue(in_array(2, $mates));
        $this->assertTrue(in_array(3, $mates));
        $this->assertTrue(in_array(4, $mates));
        $this->assertTrue(in_array(5, $mates));

        // unregistered id
        $g = array(
            "name" => "group01_failure",
        );
        $this->assertFalse(yb_tx_group_Update::go(999, $g));
    }

    // }}}
    // {{{ _test_Delete()

    function _test_Delete()
    {
        $this->assertTrue(yb_tx_group_Delete::go(1));

        // unregistered id
        $this->assertFalse(yb_tx_group_Delete::go(999));
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
