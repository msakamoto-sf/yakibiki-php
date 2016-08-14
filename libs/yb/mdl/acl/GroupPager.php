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

require_once("yb/Pager.php");

/**
 * YakiBiki Group Page Slider
 *
 * @author msakamoto-sf <msakamoto-sf@users.sourceforge.net>
 * @version $Id: GroupPager.php 357 2008-09-18 04:43:46Z msakamoto-sf $
 */
class yb_mdl_acl_GroupPager extends yb_Pager
{
    function yb_mdl_acl_GroupPager()
    {
        $this->_name_space = __CLASS__;
        $this->_pager_extraVars['mdl'] = 'acl';
        $this->_sort_by_list = array(
            'id' => t('Group ID'),
            'name' => t('Group Name'),
            'created_at' => t('Created Date'),
            'updated_at' => t('Updated Date'),
            );
    }
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

