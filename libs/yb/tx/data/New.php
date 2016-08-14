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
 * YakiBiki Data Transactions : New
 *
 * @author msakamoto-sf <msakamoto-sf@users.sourceforge.net>
 * @version $Id: New.php 332 2008-09-12 06:08:26Z msakamoto-sf $
 */

/**
 * YakiBiki Data Transactions : New
 *
 * @author msakamoto-sf <msakamoto-sf@users.sourceforge.net>
 */
class yb_tx_data_New
{
    /**
     * @static
     * @access public
     * @param array data
     * @param string raw data
     * @return array created data, if error occurrs, return null.
     */
    function go($data, $raw_data)
    {
        // save raw data
        $raw =& grain_Factory::raw('data');
        $raw_seq =& grain_Factory::sequence('data_raw');
        $raw_id = $raw_seq->next();
        if (!$raw->save($raw_id, $raw_data)) {
            return null;
        }

        // create version
        $dao_version =& yb_dao_Factory::get('version');
        $version_data = array(
            'owner' => $data['owner'],
            'raw_id' => $raw_id,
            'version' => 1,
            'approved' => true,
            'changelog' => '',
            'md5' => md5($raw_data),
            'sha1' => sha1($raw_data),
        );
        $version_id = $dao_version->create($version_data);
        if (empty($version_id)) {
            return null;
        }

        $dao_data =& yb_dao_Factory::get('data');
        $t =& yb_Time::singleton();
        $data['comments'] = array();
        $data['versions'] = array($version_id);
        $data['current_version'] = $version_id;

        $data_id = $dao_data->create($data);
        if (empty($data_id)) {
            return null;
        }

        $result = $dao_data->find_by_id($data_id);
        $r = $result[0];

        // acl_to_data
        $idx =& grain_Factory::index('pair', 'acl_to_data');
        $idx->add($data_id, $r['acl']);

        // category_to_data
        $cs = @$data['categories'];
        if (is_array($cs) && count($cs) > 0) {
            $idx =& grain_Factory::index('pair', 'category_to_data');
            $idx->add($data_id, $cs);
        }

        // owner_to_data
        $idx =& grain_Factory::index('pair', 'owner_to_data');
        $idx->add($data_id, $r['owner']);

        // data_by_published
        $idx =& grain_Factory::index('datetime', 'data_by_published');
        $idx->append($data_id, $r['published_at']);

        // data_by_updated
        $idx =& grain_Factory::index('datetime', 'data_by_updated');
        $idx->append($data_id, $r['updated_at']);

        // data_by_title
        $idx =& grain_Factory::index('match', 'data_by_title');
        $idx->register($data_id, $r['title']);

        if (yb_Error::count() > 0) {
            return null;
        }

        return $r;
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
