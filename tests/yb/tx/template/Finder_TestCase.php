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

require_once('yb/tx/template/Finder.php');
require_once('yb/tx/UnitTestCaseBase.php');

class yb_tx_template_Finder_TestCase extends yb_tx_UnitTestCaseBase
{
    // {{{ test_Finder()

    function test_Finder()
    {
        $dao_user =& yb_dao_Factory::get('user');
        $dao_user->create(array(
            'mail' => 'test01@hoge.com',
            'name' => 'dummy01',
            'password' => 'dummy',
            'role' => array(),
        ));
        $dao_cat =& yb_dao_Factory::get('category');
        $dao_cat->create(array('owner' => 1, 'name' => 'category01'));
        $dao_cat->create(array('owner' => 1, 'name' => 'category02'));
        $dao_cat->create(array('owner' => 1, 'name' => 'category03'));
        $dao_acl =& yb_dao_Factory::get('acl');
        $dao_acl->create(array('owner' => 1, 'name' => 'acl01', 
            'policy' => YB_ACL_POLICY_POSI));
        $dao_acl->create(array('owner' => 1, 'name' => 'acl02', 
            'policy' => YB_ACL_POLICY_POSI));

        // no templates, Finder::all() behaviour
        $results = yb_tx_template_Finder::all();
        $this->assertEqual(count($results), 0);

        $dao_template =& yb_dao_Factory::get('template');
        // create : template #1
        $t1 = array(
            'owner' => 1,
            'name' => 'testCreate_Template02',
            'title' => 'testCreate_Data02',
            'acl' => 1,
            'categories' => array(1, 2, 3, 4, 5),
            'type' => 'text',
            'format' => 'wiki',
            'raw_id' => 100,
        );
        $dao_template->create($t1);
        $t2 = array(
            'owner' => 3, // un registered user
            'name' => 'testCreate_Template01',
            'title' => 'testCreate_Data01',
            'acl' => 3, // un registered acl
            'categories' => array(), // empty
            'type' => 'image',
        );
        $dao_template->create($t2);

        // Finder::all() : default sort
        $results = yb_tx_template_Finder::all();
        $this->assertEqual(count($results), 2);

        $t = $results[0];
        $this->assertEqual($t['id'], 1);
        $this->assertEqual($t['name'], $t1['name']);
        $this->assertEqual($t['title'], $t1['title']);
        $this->assertEqual($t['owner']['id'], $t1['owner']);
        $this->assertEqual($t['owner']['name'], 'dummy01');
        $this->assertEqual($t['acl']['id'], $t1['acl']);
        $this->assertEqual($t['acl']['name'], 'acl01');
        $cs = $t['categories'];
        $this->assertEqual(count($cs), 3);
        $this->assertEqual($cs[0]['id'], 1);
        $this->assertEqual($cs[0]['name'], 'category01');
        $this->assertEqual($cs[1]['id'], 2);
        $this->assertEqual($cs[1]['name'], 'category02');
        $this->assertEqual($cs[2]['id'], 3);
        $this->assertEqual($cs[2]['name'], 'category03');
        $this->assertEqual($t['type'], $t1['type']);
        $this->assertEqual($t['format'], $t1['format']);
        $this->assertEqual($t['raw_id'], $t1['raw_id']);

        $t = $results[1];
        $this->assertEqual($t['id'], 2);
        $this->assertEqual($t['name'], $t2['name']);
        $this->assertEqual($t['title'], $t2['title']);
        $this->assertEqual($t['owner'], false);
        $this->assertEqual($t['acl'], false);
        $cs = $t['categories'];
        $this->assertEqual(count($cs), 0);
        $this->assertEqual($t['type'], $t2['type']);

        // Finder::all() : sort by name, desc
        $rs = yb_tx_template_Finder::all('name', ORDER_BY_DESC);
        $this->assertEqual(count($rs), 2);
        $this->assertEqual($rs[0]['id'], 1);
        $this->assertEqual($rs[1]['id'], 2);

        // Finder::by_id()
        $r = yb_tx_template_Finder::by_id(1);
        $this->assertEqual(count($r), 1);
        $t = $r[0];
        $this->assertEqual($t['id'], 1);
        $this->assertEqual($t['name'], $t1['name']);
        $this->assertEqual($t['title'], $t1['title']);
        $this->assertEqual($t['owner']['id'], $t1['owner']);
        $this->assertEqual($t['owner']['name'], 'dummy01');
        $this->assertEqual($t['acl']['id'], $t1['acl']);
        $this->assertEqual($t['acl']['name'], 'acl01');
        $cs = $t['categories'];
        $this->assertEqual(count($cs), 3);
        $this->assertEqual($cs[0]['id'], 1);
        $this->assertEqual($cs[0]['name'], 'category01');
        $this->assertEqual($cs[1]['id'], 2);
        $this->assertEqual($cs[1]['name'], 'category02');
        $this->assertEqual($cs[2]['id'], 3);
        $this->assertEqual($cs[2]['name'], 'category03');
        $this->assertEqual($t['type'], $t1['type']);
        $this->assertEqual($t['format'], $t1['format']);
        $this->assertEqual($t['raw_id'], $t1['raw_id']);

        $r = yb_tx_template_Finder::by_id(2);
        $this->assertEqual(count($r), 1);
        $t = $r[0];
        $this->assertEqual($t['id'], 2);
        $this->assertEqual($t['name'], $t2['name']);
        $this->assertEqual($t['title'], $t2['title']);
        $this->assertEqual($t['owner'], false);
        $this->assertEqual($t['acl'], false);
        $cs = $t['categories'];
        $this->assertEqual(count($cs), 0);
        $this->assertEqual($t['type'], $t2['type']);

        $r = yb_tx_template_Finder::by_id(4); // unregistered template id
        $this->assertEqual(count($r), 0);

        // multiple find by id
        $r = yb_tx_template_Finder::by_id(
            array(1, 2, 4), 'name', ORDER_BY_DESC);
        $this->assertEqual(count($r), 2);
        $this->assertEqual($r[0]['id'], 1);
        $this->assertEqual($r[1]['id'], 2);
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
