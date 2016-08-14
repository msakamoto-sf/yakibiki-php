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
 * YakiBiki Data Transactions : Delete
 *
 * @author msakamoto-sf <msakamoto-sf@users.sourceforge.net>
 * @version $Id: Delete.php 366 2008-09-21 17:04:38Z msakamoto-sf $
 */
class yb_tx_data_Delete
{
    /**
     * @static
     * @access public
     * @param integer data id
     * @return boolean if success, return true. or else, return false.
     */
    function go($did)
    {
        $dao_data =& yb_dao_Factory::get('data');
        $r = $dao_data->find_by_id($did);
        if (count($r) != 1) {
            return false;
        }
        $data_current = $r[0];

        $dao_version =& yb_dao_Factory::get('version');
        $raw =& grain_Factory::raw('data');
        $versions = $dao_version->find_by_id($data_current['versions']);
        foreach ($versions as $v) {
            $raw->delete($v['raw_id']);
            $dao_version->delete($v['id']);
        }

        if (!$dao_data->delete($did)) {
            return false;
        }

        // refresh acl_to_data
        $idx =& grain_Factory::index('pair', 'acl_to_data');
        $idx->remove($did, $data_current['acl']);

        // refresh category_to_data
        $idx =& grain_Factory::index('pair', 'category_to_data');
        $idx->remove($did, $data_current['categories']);

        // owner_to_data
        $idx =& grain_Factory::index('pair', 'owner_to_data');
        $idx->remove($did, $data_current['owner']);

        // refresh data_by_published
        $idx =& grain_Factory::index('datetime', 'data_by_published');
        $idx->delete($did, $data_current['published_at']);

        // refresh data_by_updated
        $idx =& grain_Factory::index('datetime', 'data_by_updated');
        $idx->delete($did, $data_current['updated_at']);

        // data_by_title
        $idx =& grain_Factory::index('match', 'data_by_title');
        $idx->unregister($did);

        if (yb_Error::count() > 0) {
            return false;
        }

        return true;
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
