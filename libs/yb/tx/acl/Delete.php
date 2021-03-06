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
 * YakiBiki Acl Transactions : Delete
 *
 * @author msakamoto-sf <msakamoto-sf@users.sourceforge.net>
 * @version $Id: Delete.php 356 2008-09-17 16:23:37Z msakamoto-sf $
 */
class yb_tx_acl_Delete
{
    /**
     * @static
     * @access public
     * @param integer acl id
     * @return return of yb_dao_Base::delete()
     */
    function go($id)
    {
        $dao =& yb_dao_Factory::get('acl');
        return $dao->delete((integer)$id);
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
