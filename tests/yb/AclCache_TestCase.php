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

require_once('yb/AclCache.php');

class yb_AclCache_TestCase extends UnitTestCase
{
    var $_backups;

    // {{{ _prepare()

    function _prepare($testDir)
    {
        $GLOBALS[FACTORY_ZONE] = mt_rand();

        $_tmp_dir = dirname(__FILE__) . '/' . $testDir;
        $cache_options = _YB('cache.options');
        $cache_options['caching'] = false;

        // backup data file name
        $this->_backups = array(
            'grain_dir' => grain_Config::set('grain.dir.grain', $_tmp_dir),
            'chunksize' => grain_Config::set('grain.chunksize.default', 10),
            'cache_options' => _YB('cache.options', $cache_options),
        );
    }

    // }}}
    // {{{ _terminate()

    function _terminate()
    {
        // restores
        grain_Config::set('grain.dir.grain', $this->_backups['grain_dir']);
        grain_Config::set('grain.chunksize.default', $this->_backups['chunksize']);
        _YB('cache.options', $this->_backups['cache_options']);
    }

    // }}}
    // {{{ test_normalize_permlist()

    function test_normalize_permlist()
    {
        $this->_prepare('test_AclCache01');

        $this->assertFalse(yb_AclCache::normalize_permlist(99));

        // {{{ acl#1 : positive policy
        // user#1 : NONE, READ, READWRITE
        // user#2 : READ, READWRITE
        // user#3 : NONE, READWRITE
        $n = yb_AclCache::normalize_permlist(1);
        $this->assertEqual(count($n), 3);
        $user = $n[1]; // user #1
        $this->assertTrue($user[YB_ACL_PERM_READ]);
        $this->assertTrue($user[YB_ACL_PERM_READWRITE]);
        $user = $n[2]; // user #2
        $this->assertTrue($user[YB_ACL_PERM_READ]);
        $this->assertTrue($user[YB_ACL_PERM_READWRITE]);
        $user = $n[3]; // user #3
        $this->assertTrue($user[YB_ACL_PERM_READ]);
        $this->assertTrue($user[YB_ACL_PERM_READWRITE]);
        // }}}
        // {{{ acl#2 : negative policy
        // user#1 : NONE, READ, READWRITE
        // user#2 : READ, READWRITE
        // user#3 : NONE, READWRITE
        $n = yb_AclCache::normalize_permlist(2);
        $this->assertEqual(count($n), 3);
        $user = $n[1]; // user #1
        $this->assertFalse($user[YB_ACL_PERM_READ]);
        $this->assertFalse($user[YB_ACL_PERM_READWRITE]);
        $user = $n[2]; // user #2
        $this->assertTrue($user[YB_ACL_PERM_READ]);
        $this->assertFalse($user[YB_ACL_PERM_READWRITE]);
        $user = $n[3]; // user #3
        $this->assertFalse($user[YB_ACL_PERM_READ]);
        $this->assertFalse($user[YB_ACL_PERM_READWRITE]);
        // }}}
        // {{{ acl#3 : positive policy
        // user#1 : NONE
        // user#2 : READ
        // user#3 : READWRITE
        $n = yb_AclCache::normalize_permlist(3);
        $this->assertEqual(count($n), 3);
        $user = $n[1]; // user #1
        $this->assertFalse($user[YB_ACL_PERM_READ]);
        $this->assertFalse($user[YB_ACL_PERM_READWRITE]);
        $user = $n[2]; // user #2
        $this->assertTrue($user[YB_ACL_PERM_READ]);
        $this->assertFalse($user[YB_ACL_PERM_READWRITE]);
        $user = $n[3]; // user #3
        $this->assertTrue($user[YB_ACL_PERM_READ]);
        $this->assertTrue($user[YB_ACL_PERM_READWRITE]);
        // }}}
        // {{{ acl#4 : negative policy
        // user#1 : NONE
        // user#2 : READ
        // user#3 : READWRITE
        $n = yb_AclCache::normalize_permlist(4);
        $this->assertEqual(count($n), 3);
        $user = $n[1]; // user #1
        $this->assertFalse($user[YB_ACL_PERM_READ]);
        $this->assertFalse($user[YB_ACL_PERM_READWRITE]);
        $user = $n[2]; // user #2
        $this->assertTrue($user[YB_ACL_PERM_READ]);
        $this->assertFalse($user[YB_ACL_PERM_READWRITE]);
        $user = $n[3]; // user #3
        $this->assertTrue($user[YB_ACL_PERM_READ]);
        $this->assertTrue($user[YB_ACL_PERM_READWRITE]);
        // }}}
        // {{{ acl#5 : positive policy (include YB_GUEST_UID, YB_LOGINED_UID)
        // YB_LOGINED_UID : NONE
        // YB_GUEST_UID : READ
        // user#1 : READWRITE
        $n = yb_AclCache::normalize_permlist(5);
        $this->assertEqual(count($n), 3);
        $user = $n[YB_LOGINED_UID];
        $this->assertFalse($user[YB_ACL_PERM_READ]);
        $this->assertFalse($user[YB_ACL_PERM_READWRITE]);
        $user = $n[YB_GUEST_UID];
        $this->assertTrue($user[YB_ACL_PERM_READ]);
        $this->assertFalse($user[YB_ACL_PERM_READWRITE]);
        $user = $n[1]; // user #1
        $this->assertTrue($user[YB_ACL_PERM_READ]);
        $this->assertTrue($user[YB_ACL_PERM_READWRITE]);
        // }}}

        $this->_terminate();
    }

    // }}}
    // {{{ test_evaluate()

    function test_evaluate()
    {
        $this->_prepare('test_AclCache02');

        // {{{ YB_GUEST_UID : READ
        $acls = yb_AclCache::evaluate(YB_GUEST_UID, YB_ACL_PERM_READ);
        $this->assertEqual(count($acls), 2);
        $this->assertTrue(in_array(1, $acls));
        $this->assertTrue(in_array(2, $acls));
        // }}}
        // {{{ YB_GUEST_UID : READWRITE
        $acls = yb_AclCache::evaluate(YB_GUEST_UID, YB_ACL_PERM_READWRITE);
        $this->assertEqual(count($acls), 1);
        $this->assertTrue(in_array(1, $acls));
        // }}}
        // {{{ user#1 : READ
        $acls = yb_AclCache::evaluate(1, YB_ACL_PERM_READ);
        $this->assertEqual(count($acls), 3);
        $this->assertTrue(in_array(1, $acls));
        $this->assertTrue(in_array(2, $acls));
        $this->assertTrue(in_array(4, $acls));
        // }}}
        // {{{ user#1 : READWRITE
        $acls = yb_AclCache::evaluate(1, YB_ACL_PERM_READWRITE);
        $this->assertEqual(count($acls), 2);
        $this->assertTrue(in_array(1, $acls));
        $this->assertTrue(in_array(2, $acls));
        // }}}
        // {{{ user#2 : READ
        $acls = yb_AclCache::evaluate(2, YB_ACL_PERM_READ);
        $this->assertEqual(count($acls), 5);
        $this->assertTrue(in_array(1, $acls));
        $this->assertTrue(in_array(2, $acls));
        $this->assertTrue(in_array(3, $acls));
        $this->assertTrue(in_array(4, $acls));
        $this->assertTrue(in_array(5, $acls));
        // }}}
        // {{{ user#2 : READWRITE
        $acls = yb_AclCache::evaluate(2, YB_ACL_PERM_READWRITE);
        $this->assertEqual(count($acls), 4);
        $this->assertTrue(in_array(1, $acls));
        $this->assertTrue(in_array(2, $acls));
        $this->assertTrue(in_array(4, $acls));
        $this->assertTrue(in_array(5, $acls));
        // }}}
        // {{{ user#3 : READ
        $acls = yb_AclCache::evaluate(3, YB_ACL_PERM_READ);
        $this->assertEqual(count($acls), 4);
        $this->assertTrue(in_array(1, $acls));
        $this->assertTrue(in_array(2, $acls));
        $this->assertTrue(in_array(3, $acls));
        $this->assertTrue(in_array(5, $acls));
        // }}}
        // {{{ user#3 : READWRITE
        $acls = yb_AclCache::evaluate(3, YB_ACL_PERM_READWRITE);
        $this->assertEqual(count($acls), 2);
        $this->assertTrue(in_array(1, $acls));
        $this->assertTrue(in_array(2, $acls));
        // }}}

        $this->_terminate();
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
