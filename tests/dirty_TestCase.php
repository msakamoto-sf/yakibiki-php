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

/**
 * This test case is prepared for whitebox testing with changing internal
 * source code.
 *
 * This file is controlled by Suvbersion, but each downloaded file 
 * is not stable because each developers change, break, various codes 
 * in it. So, there's no mean to controll by svn and trust stability.
 *
 * @author msakamoto-sf <msakamoto-sf@users.sourceforge.net>
 * @version $Id: dirty_TestCase.php 378 2008-10-05 13:52:39Z msakamoto-sf $
 */
class dirty_TestCase extends UnitTestCase
{
    // {{{ setUp()

    function setUp()
    {
    }

    // }}}
    // {{{ tearDown()

    function tearDown()
    {
    }

    // }}}
    // {{{ test_yb_util_file_func()

    function _test_yb_util_file_func()
    {
        yb_Error::set_raise_callback('yb_test_error_raise_callback');

        //$dir = _YB('idx.directory.dst');
        $filename = _YB('dir.datas') . '/test.dat';

        $fp = yb_Util::file_open_lock($filename, 'a+b', LOCK_SH);
        fclose($fp);
        yb_Util::file_write($fp, "ABC", 3, true, $filename);

        @unlink($filename);

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
