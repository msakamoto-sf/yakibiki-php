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
 */

/**
 * YakiBiki Form Builder Template Base
 *
 * @author msakamoto-sf <msakamoto-sf@users.sourceforge.net>
 * @version $Id: FormBuilderBase.php 527 2009-06-20 14:20:31Z msakamoto-sf $
 */
class yb_FormBuilderBase
{
    var $_defaults;

    var $_accepts;

    var $_required_mark = YB_FORM_REQUIRED_MARK;

    var $_checked_mark = ' checked="checked" ';

    var $_already_accepted = false;

    // {{{ singleton()

    function &singleton()
    {
    }

    // }}}
    // {{{ setDefaults()

    function setDefaults($items)
    {
        if (!$this->_already_accepted) {
            $this->_defaults = $items;
        }
    }

    // }}}
    // {{{ accept()

    function accept($method)
    {
        if ($this->_already_accepted) {
            return;
        }
        $this->_already_accepted = true;
    }

    // }}}
    // {{{ validate()

    function validate(&$errors)
    {
        return true;
    }

    // }}}
    // {{{ export()

    function export()
    {
    }

    // }}}
    // {{{ build()

    function build()
    {
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
