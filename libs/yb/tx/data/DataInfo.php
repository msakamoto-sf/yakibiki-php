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
 * YakiBiki Data Transactions : Update Data Infos
 *
 * acl, categories, published_at, is_XXXX_moderateds are updatable.
 *
 * 'acl_to_data', 'category_to_data', 'data_by_published', 'data_by_updated' 
 * indice are updated.
 *
 * @author msakamoto-sf <msakamoto-sf@users.sourceforge.net>
 * @version $Id: DataInfo.php 494 2009-01-04 02:23:39Z msakamoto-sf $
 */
class yb_tx_data_DataInfo
{
    /**
     * @static
     * @access public
     * @param integer data id
     * @param array new data
     * @return boolean if success, return true. or else, return false.
     */
    function go($did, $data_new)
    {
        $dao_data =& yb_dao_Factory::get('data');
        $r = $dao_data->find_by_id($did);
        if (count($r) != 1) {
            return false;
        }
        $data_current = $r[0];

        $acceptable = array('title', 'acl', 'categories', 'published_at', 
            'is_versions_moderated', 
            'is_comments_moderated', 
        );
        $accepted = array();
        foreach ($acceptable as $k) {
            $accepted[$k] = $data_new[$k];
        }

        if (!$dao_data->update($did, $accepted)) {
            return false;
        }

        // refresh local copy
        $r = $dao_data->find_by_id($did);
        $data_new = $r[0];

        // refresh data_by_title
        $idx =& grain_Factory::index('match', 'data_by_title');
        $idx->unregister($did);
        $idx->register($did, $data_new['title']);

        // refresh acl_to_data
        $idx =& grain_Factory::index('pair', 'acl_to_data');
        $idx->remove($did, $data_current['acl']);
        $idx->add($did, $data_new['acl']);

        // refresh category_to_data
        $idx =& grain_Factory::index('pair', 'category_to_data');
        $idx->remove($did, $data_current['categories']);
        $idx->add($did, $data_new['categories']);

        // refresh data_by_published
        $idx =& grain_Factory::index('datetime', 'data_by_published');
        $idx->delete($did, $data_current['published_at']);
        $idx->append($did, $data_new['published_at']);

        // refresh data_by_updated
        $idx =& grain_Factory::index('datetime', 'data_by_updated');
        $idx->delete($did, $data_current['updated_at']);
        $idx->append($did, $data_new['updated_at']);

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
