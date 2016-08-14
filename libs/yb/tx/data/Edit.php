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
 * YakiBiki Data Transactions : Edit
 *
 * @author msakamoto-sf <msakamoto-sf@users.sourceforge.net>
 * @version $Id: Edit.php 490 2009-01-03 13:06:26Z msakamoto-sf $
 */
class yb_tx_data_Edit
{
    /**
     * @static
     * @access public
     * @param array data
     * @param string raw data
     * @param boolean version up(true) or not(false)
     * @param string changelog
     * @param string edit user's id
     * @return boolean if success, return true. or else, return false.
     */
    function go($id, $data_new, $raw_data, $do_vup, $changelog, $edit_by)
    {
        $dao_data =& yb_dao_Factory::get('data');
        $r = $dao_data->find_by_id($id);
        if (count($r) != 1) {
            return false;
        }
        $data_current = $r[0];
        $current_vids = $data_current['versions'];

        $dao_version =& yb_dao_Factory::get('version');
        $current_versions = $dao_version->find_by_id($current_vids);
        $new_version_num = 1;
        foreach ($current_versions as $v) {
            $vnum = $v['version'];
            if ($new_version_num <= $vnum) {
                $new_version_num = $vnum + 1;
            }
        }

        $raw =& grain_Factory::raw('data');

        if ($data_current['is_versions_moderated'] || $do_vup) {

            $raw_seq =& grain_Factory::sequence('data_raw');
            $raw_id = $raw_seq->next();
            if (!$raw->save($raw_id, $raw_data)) {
                return false;
            }

            // create new version
            $new_v = array(
                'owner' => $edit_by,
                'raw_id' => $raw_id,
                'version' => $new_version_num,
                'approved' => !$data_current['is_versions_moderated'],
                'changelog' => $changelog,
                'md5' => md5($raw_data),
                'sha1' => sha1($raw_data),
            );
            $new_vid = $dao_version->create($new_v);
            if (empty($new_vid)) {
                return false;
            }

            $current_vids[] = $new_vid;
            $data_new['versions'] = $current_vids;
            if (!$data_current['is_versions_moderated']) {
                $data_new['current_version'] = $new_vid;
            }

        } else {

            // update current version
            $curr_vid = $data_current['current_version'];
            $r = $dao_version->find_by_id($curr_vid);
            if (count($r) != 1) {
                return false;
            }
            $raw_id = $r[0]['raw_id'];
            if (!$raw->save($raw_id, $raw_data)) {
                return false;
            }
            $new_v = array(
                'owner' => $edit_by,
                'changelog' => $changelog,
                'md5' => md5($raw_data),
                'sha1' => sha1($raw_data),
            );

            if (!$dao_version->update($curr_vid, $new_v)) {
                return false;
            }
        }

        if (!$dao_data->update($id, $data_new)) {
            return false;
        }

        $r = $dao_data->find_by_id($id);
        $d_new = $r[0];

        // data_by_updated
        $idx =& grain_Factory::index('datetime', 'data_by_updated');
        $idx->delete($id, $data_current['updated_at']);
        $idx->append($id, $d_new['updated_at']);

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
