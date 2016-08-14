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

require_once('yb/Error.php');

$GLOBALS['Test_yb_Error_Debug'] = array();
function Test_yb_Error_Callback($error)
{
    $GLOBALS['Test_yb_Error_Debug'][] = $error;
}

class yb_Error_TestCase extends UnitTestCase
{
    // {{{ test_available()

    function test_available()
    {
        $this->assertEqual(yb_Error::count(), 0);
        $errors = yb_Error::get();
        $this->assertEqual(count($errors), 0);
        $this->assertEqual(yb_Error::count(), 0);

        yb_Error::raise('Message1');
        yb_Error::raise('Message2', 'Code2');
        yb_Error::set_raise_callback('Test_yb_Error_Callback');
        yb_Error::raise('Message3');
        yb_Error::raise('Message4', 'Code4');

        $this->assertEqual(yb_Error::count(), 4);
        $errors = yb_Error::get(false);
        $this->assertEqual(count($errors), 4);
        $this->assertEqual(yb_Error::count(), 4);

        $errors = yb_Error::get();
        $this->assertEqual(count($errors), 4);
        $this->assertEqual(yb_Error::count(), 0);

        $this->assertEqual(count($GLOBALS['Test_yb_Error_Debug']), 2);
        // Message3
        $this->assertEqual($errors[2], $GLOBALS['Test_yb_Error_Debug'][0]);
        // Message4
        $this->assertEqual($errors[3], $GLOBALS['Test_yb_Error_Debug'][1]);
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
