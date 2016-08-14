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
 *   limitations under the License.
 *
 */

require_once('yb/tx/category/Finder.php');
require_once('yb/tx/category/Create.php');
require_once('yb/tx/category/Update.php');
require_once('yb/tx/category/Delete.php');
require_once('yb/tx/UnitTestCaseBase.php');

class yb_tx_category_All_TestCase extends yb_tx_UnitTestCaseBase
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

        // create test 'category_to_data' index datas.
        $c2d =& grain_Factory::index('pair', 'category_to_data');
        $c2d->add(10, array(1));
        $c2d->add(20, array(1));

        $this->_test_Create_and_Finds();
        $this->_test_Update();
        $this->_test_Delete();
    }

    // }}}
    // {{{ _test_Create_and_Finds()

    function _test_Create_and_Finds()
    {
        $r = yb_tx_category_Finder::all();
        $this->assertEqual(count($r), 0);

        $r = yb_tx_category_Create::go(1, 'category1');
        $this->assertEqual($r['id'], 1);
        $this->assertEqual($r['count'], 0);
        $this->assertEqual($r['owner']['id'], 1);
        $this->assertEqual($r['owner']['name'], 'dummy01');
        $this->assertEqual($r['name'], 'category1');

        $r = yb_tx_category_Create::go(2, 'category2');
        $this->assertEqual($r['id'], 2);
        $this->assertEqual($r['count'], 0);
        $this->assertEqual($r['owner']['id'], 2);
        $this->assertEqual($r['owner']['name'], 'dummy02');
        $this->assertEqual($r['name'], 'category2');

        $r = yb_tx_category_Finder::by_id(1);
        $this->assertEqual(count($r), 1);
        $this->assertEqual($r[0]['id'], 1);
        $this->assertEqual($r[0]['count'], 2);
        $this->assertEqual($r[0]['name'], 'category1');
        $this->assertEqual($r[0]['owner']['id'], 1);
        $this->assertEqual($r[0]['owner']['name'], 'dummy01');

        $r = yb_tx_category_Finder::by_id(999);
        $this->assertEqual(count($r), 0);

        $r = yb_tx_category_Finder::by_id(array(1, 2, 3), 'name', ORDER_BY_DESC);
        $this->assertEqual(count($r), 2);
        $c = $r[0];
        $this->assertEqual($c['id'], 2);
        $this->assertEqual($c['count'], 0);
        $this->assertEqual($c['name'], 'category2');
        $this->assertEqual($c['owner']['id'], 2);
        $this->assertEqual($c['owner']['name'], 'dummy02');
        $c = $r[1];
        $this->assertEqual($c['id'], 1);
        $this->assertEqual($c['count'], 2);
        $this->assertEqual($c['name'], 'category1');
        $this->assertEqual($c['owner']['id'], 1);
        $this->assertEqual($c['owner']['name'], 'dummy01');

        $r = yb_tx_category_Finder::all();
        $this->assertEqual(count($r), 2);
        $this->assertEqual($r[0]['id'], 1);
        $this->assertEqual($r[0]['count'], 2);
        $this->assertEqual($r[0]['name'], 'category1');
        $this->assertEqual($r[0]['owner']['id'], 1);
        $this->assertEqual($r[0]['owner']['name'], 'dummy01');
        $this->assertEqual($r[1]['id'], 2);
        $this->assertEqual($r[1]['count'], 0);
        $this->assertEqual($r[1]['name'], 'category2');
        $this->assertEqual($r[1]['owner']['id'], 2);
        $this->assertEqual($r[1]['owner']['name'], 'dummy02');

        $r = yb_tx_category_Finder::all('name', ORDER_BY_DESC);
        $this->assertEqual(count($r), 2);
        $this->assertEqual($r[0]['id'], 2);
        $this->assertEqual($r[1]['id'], 1);
    }

    // }}}
    // {{{ _test_Update()

    function _test_Update()
    {
        $this->assertTrue(yb_tx_category_Update::go(1, 'category1_1'));
        $this->assertFalse(yb_tx_category_Update::go(99, 'category1_1'));

        $r = yb_tx_category_Finder::by_id(1);
        $this->assertEqual(count($r), 1);
        $this->assertEqual($r[0]['id'], 1);
        $this->assertEqual($r[0]['name'], 'category1_1');
        $this->assertEqual($r[0]['owner']['id'], 1);
        $this->assertEqual($r[0]['owner']['name'], 'dummy01');
    }

    // }}}
    // {{{ _test_Delete()

    function _test_Delete()
    {
        $this->assertTrue(yb_tx_category_Delete::go(2));
        $this->assertFalse(yb_tx_category_Delete::go(99));
        $r = yb_tx_category_Finder::by_id(2);
        $this->assertEqual(count($r), 0);

        $r = yb_tx_category_Finder::by_id(1);
        $this->assertEqual(count($r), 1);
        $this->assertEqual($r[0]['name'], 'category1_1');

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
