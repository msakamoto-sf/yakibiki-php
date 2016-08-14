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
 * YakiBiki data finder
 *
 * @author msakamoto-sf <msakamoto-sf@users.sourceforge.net>
 * @version $Id: Finder.php 452 2008-11-16 15:26:38Z msakamoto-sf $
 */
class yb_Finder
{
    // {{{ properties

    /**
     * @access public
     * @type integer(ORDER_BY_ASC or ORDER_BY_DESC
     */
    var $order_by = ORDER_BY_DESC;

    /**
     * @access public
     * @type string
     */
    var $sort_by = 'u';

    /**
     * @access public
     * @type string
     */
    var $textmatch = null;

    /**
     * @access public
     * @type boolean
     */
    var $is_fullmatch = false;

    /**
     * @access public
     * @type boolean
     */
    var $use_listmatch = false;

    /**
     * @access public
     * @type boolean
     */
    var $case_sensitive = false;

    /**
     * @access public
     * @type array
     */
    var $categories = array();

    /**
     * Filtering mode between categories and text.
     *
     * @access public
     * @type integer (YB_AND or YB_OR)
     */
    var $andor_c_t = YB_AND;

    /**
     * Apply or not apply text filter
     *
     * @access protected
     * @type boolean
     */
    var $_by_text = false;

    /**
     * Apply or not apply categories filter
     *
     * @access protected
     * @type boolean
     */
    var $_by_categories = false;

    // }}}
    // {{{ SORT_BY_CREATED_AT()

    /**
     * @access public
     * @static
     */
    function SORT_BY_CREATED_AT()
    {
        return "c";
    }

    // }}}
    // {{{ SORT_BY_UPDATED_AT()

    /**
     * @access public
     * @static
     */
    function SORT_BY_UPDATED_AT()
    {
        return "u";
    }

    // }}}
    // {{{ search()

    function search($user_context)
    {
        // {{{ By Text Search against data names

        $ids_by_dst = array();
        if (!is_null($this->textmatch)) {
            $this->_by_text = true;
            $idx_dst =& grain_Factory::index('match', 'data_by_title');
            $idx_dst->case_sensitive($this->case_sensitive);
            if ($this->is_fullmatch) {
                $ids_by_dst = $idx_dst->fullmatch($this->textmatch);
            } else if ($this->use_listmatch) {
                $_ids = $idx_dst->listmatch($this->textmatch);
                foreach ($_ids as $_id => $_title) {
                    $ids_by_dst[] = $_id;
                }
            } else {
                $ids_by_dst = $idx_dst->search($this->textmatch);
            }
        }

        // }}}
        // {{{ By Category IDs

        $ids_by_c2d = array();
        if (is_array($this->categories) && count($this->categories) > 0) {
            $this->_by_categories = true;
            $idx_c2d =& grain_Factory::index('pair', 'category_to_data');
            $_indice = $idx_c2d->get_from($this->categories);
            foreach ($_indice as $_k => $_ids) {
                $ids_by_c2d = array_merge($ids_by_c2d, $_ids);
            }
        }

        // }}}
        // {{{ BASE INDEX Sort Order (created_at/updated_ad)

        if ($this->sort_by == 'u') {
            $idx_dbx =& grain_Factory::index('datetime', 'data_by_updated');
        } else {
            $idx_dbx =& grain_Factory::index('datetime', 'data_by_published');
        }
        $idx_dbx->order($this->order_by);

        // }}}

        // condition filter (title/name text, category)
        $ids_cond_filter = $this->_make_cond_filter($ids_by_dst, $ids_by_c2d);

        if (in_array('sys', $user_context['role'])) {
            // {{{ 'sys' role's special unlimited procedure.

            if ($this->_by_text || $this->_by_categories) {
                // if any condition is specified:

                if (count($ids_cond_filter) > 0) {
                    // if any id is filtered, apply.
                    $idx_dbx->filters($ids_cond_filter);
                } else {
                    // if id filter is empty, return no-hit-empty-array.
                    return array();
                }
            }
            // get (filtered or not) ids.
            $ids = $idx_dbx->gets();
            return $ids;

            // }}}
        } else {
            // {{{ normal role/users procedure.
            $uid = $user_context['id'];
            $ids_by_aclowners = array();

            // get YB_ACL_PERM_READ allowed acl ids.
            $allowed_acls = yb_AclCache::evaluate($uid, YB_ACL_PERM_READ);
            $idx_a2d =& grain_Factory::index('pair', 'acl_to_data');
            $_indice = $idx_a2d->get_from($allowed_acls);
            foreach ($_indice as $_k => $_ids) {
                $ids_by_aclowners = array_merge($ids_by_aclowners , $_ids);
            }
            $ids_by_aclowners = array_unique($ids_by_aclowners);

            // If usercontext is LOGINED user, then, use O2D index.
            if ($uid != YB_GUEST_UID) {
                $idx_o2d =& grain_Factory::index('pair', 'owner_to_data');
                $ids_by_o2d = $idx_o2d->get_from($uid);
                if (isset($ids_by_o2d[$uid])) {
                    $ids_by_o2d = $ids_by_o2d[$uid];
                }

                // get ACL OR O2D
                $ids_by_aclowners = yb_Util::array_or(
                    $ids_by_aclowners, $ids_by_o2d);
            }

            if ($this->_by_text || $this->_by_categories) {
                // if any condition is specified:

                if (count($ids_cond_filter) > 0) {
                    // if any condition id is filtered, get AND with acl
                    // filters, apply it.
                    $_filter = yb_Util::array_and(
                        $ids_cond_filter, $ids_by_aclowners);
                    $idx_dbx->filters($_filter);
                } else {
                    // if condition id filter is empty, 
                    // return no-hit-empty-array.
                    return array();
                }
            } else {
                // if condition is NOT specified:
                // only acl/owner filters are applied.
                $idx_dbx->filters($ids_by_aclowners);
            }

            $ids = $idx_dbx->gets();
            return $ids;

            // }}}
        }
    }

    // }}}
    // {{{ _make_cond_filter()

    function _make_cond_filter($by_dst, $by_c2d)
    {
        $buf = array();

        if ($this->_by_text && $this->_by_categories) {
            // both text and categories are specified:

            if ($this->andor_c_t == YB_OR) {
                $buf = yb_Util::array_or($by_dst, $by_c2d);
            } else {
                $buf = yb_Util::array_and($by_dst, $by_c2d);
            }

        } else if ($this->_by_text && !$this->_by_categories) {
            // only text are specified:

            $buf = $by_dst;

        } else if (!$this->_by_text && $this->_by_categories) {
            // only categories are specified:

            $buf = $by_c2d;
        }

        return $buf;
    }

    // }}}
    // {{{ find_by_id()

    /**
     * @static
     * @access public
     * @param array user_context
     * @param integer data id
     * @param array error reference 
     *              'msg' => locale message
     *              'args' => locale message args
     *              'status' => HTTP status code
     * @param integer YB_ACL_PERM_{READ|READWRITE}
     * @param boolean expand owner, updated_by, categories info or not.
     *                (if omitted, default is false : not expand)
     * @param integer specifiy version number.
     *                (if omitted, current_version is used)
     * @return mixed if found, return data array. or not, return null.
     */
    function find_by_id($user_context, $did, &$error, $acl_perm, 
        $expand = false, $version = null)
    {
        $dao_data =& yb_dao_Factory::get('data');

        $datas = $dao_data->find_by_id($did);
        if (count($datas) != 1) {
            $error['msg'] = 'data (ID=%id) was not found.';
            $error['args'] = array('id' => $did);
            $error['status'] = 404;
            return null;
        }
        $data = $datas[0];

        // get "read" allowed acl ids for current user
        $allowed_acls = yb_AclCache::evaluate($user_context['id'], $acl_perm);

        // 'sys' role or data owner have read permissions automatically.
        $allow_special = (in_array('sys', $user_context['role']) || 
            $data['owner'] == $user_context['id']);

        if (!$allow_special && !in_array($data['acl'], $allowed_acls)) {
            $error['msg'] = 'You don\'t have any permission to access specified data (ID=%id).';
            $error['args'] = array('id' => $did);
            $error['status'] = 403;
            return null;
        }

        // get versions => $data['_versions']
        $dao_version =& yb_dao_Factory::get('version');
        $vinfos = $dao_version->find_by_id($data['versions']);
        $data['_versions'] = array();
        foreach ($vinfos as $v) {
            $v_num = $v['version'];
            if ($allow_special || ($v['approved'] == true)) {
                $data['_versions'][$v_num] = $v;
            }
        }

        // get raw_id, $data['updated_by'], $data['_updated_by_uid']
        $raw_id = null;
        $is_version_specified = !is_null($version);
        if ($is_version_specified) {
            // if version is specified, search specified version.
            foreach ($data['_versions'] as $vnum => $vinfo) {
                if ($vinfo['version'] == $version) {
                    $raw_id = $vinfo['raw_id'];
                    $data['updated_by'] = $vinfo['owner'];
                    $data['_updated_by_uid'] = $vinfo['owner'];
                    $display_vnum = $vinfo['version'];
                    $data['display_version_number'] = $display_vnum;
                    $data['display_version_id'] = $vinfo['id'];
                }
                if ($vinfo['id'] == $data['current_version']) {
                    $data['current_version_number'] = $vinfo['version'];
                    $data['current_version_id'] = $vinfo['id'];
                }
            }
        } else {
            // if version is not specified, current_version is used.
            $vid = $data['current_version'];
            foreach ($data['_versions'] as $vnum => $vinfo) {
                if ($vinfo['id'] == $data['current_version']) {
                    $raw_id = $vinfo['raw_id'];
                    $data['updated_by'] = $vinfo['owner'];
                    $data['_updated_by_uid'] = $vinfo['owner'];
                    $display_vnum = $vinfo['version'];
                    $data['display_version_number'] = $display_vnum;
                    $data['current_version_number'] = $display_vnum;
                    $data['display_version_id'] = $vinfo['id'];
                    $data['current_version_id'] = $vinfo['id'];
                }
            }
        }

        if (empty($raw_id)) {
            $error['msg'] = 'data (ID=%id) was not found.';
            $error['args'] = array('id' => $did);
            $error['status'] = 404;
            return null;
        }

        // retrieve raw file name.
        $raw_data =& grain_Factory::raw('data');
        $filepath = $raw_data->filename($raw_id);

        if (empty($filepath)) {
            $error['msg'] = 'physical file data (ID=%id, VERSION=%version) is not found.';
            $error['args'] = array('id' => $did, 'version' => $display_vnum);
            $error['status'] = 500;
            return null;
        }
        $data['_raw_filepath'] = $filepath;

        $display_id = $data['id'];
        $display_title = $data['title'];
        if ($is_version_specified) {
            $display_id .= '_' . $display_vnum;
            $display_title .= ' (v' . $display_vnum . ')';
        }
        $data['display_id'] = $display_id;
        $data['display_title'] = $display_title;

        if ($expand) {
            // get owner info
            $data['_owner_uid'] = $data['owner'];
            $data['owner'] = yb_Util::get_user_info_ex($data['owner']);

            // get updated_by user info
            $data['updated_by'] = yb_Util::get_user_info_ex(
                $data['_updated_by_uid']);

            // get category info
            $category_info = array();
            $category_ids = $data['categories'];
            if (count($category_ids) > 0) {
                $category_dao =& yb_dao_Factory::get('category');
                $data['categories'] = $category_dao->find_by_id($category_ids);
            } else {
                $data['categories'] = array();
            }
        }

        return $data;
    }

    // }}}
    // {{{ find_by_title()

    /**
     * @static
     * @access public
     * @param array user_context
     * @param string data title
     * @param array error reference 
     *              'msg' => locale message
     *              'args' => locale message args
     *              'status' => HTTP status code
     *              'hits' => more than 2 records hit count.
     * @param integer YB_ACL_PERM_{READ|READWRITE}
     * @param boolean expand owner, updated_by, categories info or not.
     *                (if omitted, default is false : not expand)
     * @return mixed if found, return data array. or not, return null.
     */
    function find_by_title($user_context, $title, &$error, $acl_perm, 
        $expand = false)
    {

        $idx =& grain_Factory::index('match', 'data_by_title');
        $idx->case_sensitive(true);
        $data_ids = $idx->fullmatch($title);

        $cnt = count($data_ids);
        switch ($cnt) {
        case 0:
            $error['msg'] = 'data (title=%title) was not found.';
            $error['args'] = array('title' => $title);
            $error['status'] = 404;
            return null;
        case 1:
            return yb_Finder::find_by_id(
                $user_context, 
                $data_ids[0], 
                $error, 
                $acl_perm, 
                $expand, 
                null);
        default:
            $error['msg'] = 'data (title=%title) multiple found.';
            $error['args'] = array('title' => $title);
            $error['status'] = 500; // logic collision
            $error['hits'] = $cnt;
        }

        return null;
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
