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
 * Cache_Lite's group : ACL Permission expanded result cache.
 */
define('YB_ACLCACHE_GROUP_ACL', 'yb_AclCache_acl');

/**
 * Cache_Lite's group : User ACL evaluate result cache.
 */
define('YB_ACLCACHE_GROUP_USER', 'yb_AclCache_user');

/**
 * YakiBiki acl cache
 *
 * @author msakamoto-sf <msakamoto-sf@users.sourceforge.net>
 * @version $Id: AclCache.php 335 2008-09-12 06:15:39Z msakamoto-sf $
 */
class yb_AclCache
{
    // {{{ getCache()

    /**
     * Singleton interface for each Cache_Lite instance.
     *
     * @static
     * @access public
     * @param string YB_ACLCACHE_GROUP_{ACL|USER}
     * @return object referenct of Cache_Lite instance.
     */
    function &getCache($group)
    {
        static $cache_acl = null;
        static $cache_user = null;

        $options = _YB('cache.options');
        $options['defaultGroup'] = $group;
        $options['dontCacheWhenTheResultIsFalse'] = true;
        $options['dontCacheWhenTheResultIsNull'] = true;

        if ($group == YB_ACLCACHE_GROUP_ACL) {
            if (is_null($cache_acl)) {
                $cache_acl =& new Cache_Lite($options);
            }
            return $cache_acl;
        } else {
            if (is_null($cache_user)) {
                $cache_user =& new Cache_Lite($options);
            }
            return $cache_user;
        }
    }

    // }}}
    // {{{ clean()

    /**
     * Cache_Lite's clean() method wrapper.
     *
     * @static
     * @access public
     * @param string YB_ACLCACHE_GROUP_{ACL|USER}
     *               if omitted, both cache are cleaned.
     */
    function clean($cachegroups = null)
    {
        $_cache_groups = array();
        if (!is_null($cachegroups)) {
            $_cache_groups[] = $cachegroups;
        } else {
            $_cache_groups[] = YB_ACLCACHE_GROUP_ACL;
            $_cache_groups[] = YB_ACLCACHE_GROUP_USER;
        }

        foreach ($_cache_groups as $_group) {
            $c =& yb_AclCache::getCache($_group);
            if (!is_null($c)) {
                $c->clean($_group);
            }
        }
    }

    // }}}
    // {{{ normalize_permlist()

    /**
     * Expand and Convert ACL Permission list.
     *
     * @static
     * @access public
     * @param integer ACL ID
     * @return array assoc arrays which key is user id , and value is assoc
     *               array which represent permission and allow/disallow.
     */
    function normalize_permlist($aid)
    {
        $cache =& yb_AclCache::getCache(YB_ACLCACHE_GROUP_ACL);
        if ($data = $cache->get($aid, YB_ACLCACHE_GROUP_ACL)) {
            return $data;
        }

        $dao_acl =& yb_dao_Factory::get('acl');
        $dao_group =& yb_dao_Factory::get('group');
        $results = $dao_acl->find_by_id($aid);
        if (count($results) != 1) {
            return false;
        }
        $data = $results[0];

        $policy = $data['policy'];
        $perms = $data['perms'];
        $uid2p_tmp = array();
        foreach ($perms as $p) {
            $level = $p['perm'];
            $_id = $p['id'];
            if ($p['type'] == YB_ACL_TYPE_GROUP) {
                $_uids = $dao_group->groupMates($_id);
                foreach ($_uids as $_uid) {
                    $uid2p_tmp[$_uid][] = $level;
                }
            } else {
                $uid2p_tmp[$_id][] = $level;
            }
        }
        $uid2p_tmp2 = array();
        foreach ($uid2p_tmp as $_uid => $levels) {
            $uid2p_tmp2[$_uid]['min'] = min($levels);
            $uid2p_tmp2[$_uid]['max'] = max($levels);
        }

        $_results = array();
        $_levels = array(YB_ACL_PERM_READ, YB_ACL_PERM_READWRITE);
        foreach ($uid2p_tmp2 as $_uid => $_range) {
            $_expanded = array();
            $_min = $_range['min'];
            $_max = $_range['max'];
            foreach ($_levels as $_l) {
                if ($policy == YB_ACL_POLICY_POSI) {
                    $_expanded[$_l] = ($_l <= $_max);
                } else {
                    $_expanded[$_l] = ($_l <= $_min);
                }
            }
            $_results[$_uid] = $_expanded;
        }

        $cache->save($_results, $aid, YB_ACLCACHE_GROUP_ACL);
        return $_results;
    }

    // }}}
    // {{{ evaluate()

    /**
     * Get ACL IDs which allows specified permission to specified user id.
     *
     * @static
     * @access public
     * @param integer User ID
     * @param integer ACL Permission (YB_ACL_PERM_{READ|READWRITE})
     * @return array array of acl ids which allow given acl permission to 
     *               given user id.
     */
    function evaluate($uid, $perm)
    {
        $cache_key = (string)$uid . '_' . (string)$perm;
        $cache =& yb_AclCache::getCache(YB_ACLCACHE_GROUP_USER);
        if ($data = $cache->get($cache_key, YB_ACLCACHE_GROUP_USER)) {
            return $data;
        }

        $dao_acl =& yb_dao_Factory::get('acl');
        $results = $dao_acl->find_all();

        $acl_ids = array();
        foreach ($results as $acl) {
            $aid = $acl['id'];
            $expanded = yb_AclCache::normalize_permlist($aid);
            if (!isset($expanded[$uid])) {
                if ($uid != YB_GUEST_UID && 
                    isset($expanded[YB_LOGINED_UID]) &&
                    $expanded[YB_LOGINED_UID][$perm] == true) {

                    $acl_ids[] = $aid;
                }
                continue;
            }
            if ($expanded[$uid][$perm]) {
                $acl_ids[] = $aid;
            }
        }

        $cache->save($acl_ids, $cache_key, YB_ACLCACHE_GROUP_USER);
        return $acl_ids;
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
