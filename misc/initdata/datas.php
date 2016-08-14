<?php
// YakiBiki setup.php initialize data
// $Id: datas.php 443 2008-11-15 14:07:38Z msakamoto-sf $

$owner = _YB('setup.tmp.sysuser.id');
function setupT($s, $args = null)
{
    return t($s, $args, 'setup');
}

$YB_INIT_DATA = array();

// {{{ group
$YB_INIT_DATA['group'] = array();
$YB_INIT_DATA['group']['sag'] = array(
    'owner' => $owner,
    'name' => setupT('YakiBiki Site Administrator Group'),
    'mates' => array($owner),
);
$YB_INIT_DATA['group']['family'] = array(
    'owner' => $owner,
    'name' => setupT('Family'),
    'mates' => array($owner),
);
$YB_INIT_DATA['group']['friends'] = array(
    'owner' => $owner,
    'name' => setupT('Friends'),
    'mates' => array($owner),
);
// }}}
// {{{ category
$YB_INIT_DATA['category'] = array();
$YB_INIT_DATA['category']['images'] = array(
    'owner' => $owner,
    'name' => setupT('Images'),
);
$YB_INIT_DATA['category']['files'] = array(
    'owner' => $owner,
    'name' => setupT('Files'),
);
$YB_INIT_DATA['category']['bookmarks'] = array(
    'owner' => $owner,
    'name' => setupT('Bookmarks'),
);
// }}}
// {{{ acl
$YB_INIT_DATA['acl'] = array();
$YB_INIT_DATA['acl']['draft'] = array(
    'owner' => $owner,
    'name' => setupT('Draft'),
    'policy' => YB_ACL_POLICY_POSI,
    'perms' => array(
        array(
            'type' => YB_ACL_TYPE_USER,
            'id' => YB_GUEST_UID,
            'perm' => YB_ACL_PERM_NONE,
        ),
        array(
            'type' => YB_ACL_TYPE_USER,
            'id' => YB_LOGINED_UID,
            'perm' => YB_ACL_PERM_NONE,
        ),
    ),
);
$YB_INIT_DATA['acl']['publish'] = array(
    'owner' => $owner,
    'name' => setupT('Publish'),
    'policy' => YB_ACL_POLICY_POSI,
    'perms' => array(
        array(
            'type' => YB_ACL_TYPE_USER,
            'id' => YB_GUEST_UID,
            'perm' => YB_ACL_PERM_READ,
        ),
        array(
            'type' => YB_ACL_TYPE_USER,
            'id' => YB_LOGINED_UID,
            'perm' => YB_ACL_PERM_READ,
        ),
    ),
);
$YB_INIT_DATA['acl']['publish_local'] = array(
    'owner' => $owner,
    'name' => setupT('Publish (only for logined users)'),
    'policy' => YB_ACL_POLICY_POSI,
    'perms' => array(
        array(
            'type' => YB_ACL_TYPE_USER,
            'id' => YB_GUEST_UID,
            'perm' => YB_ACL_PERM_NONE,
        ),
        array(
            'type' => YB_ACL_TYPE_USER,
            'id' => YB_LOGINED_UID,
            'perm' => YB_ACL_PERM_READ,
        ),
    ),
);
$YB_INIT_DATA['acl']['wiki_public'] = array(
    'owner' => $owner,
    'name' => setupT('Open Wiki'),
    'policy' => YB_ACL_POLICY_POSI,
    'perms' => array(
        array(
            'type' => YB_ACL_TYPE_USER,
            'id' => YB_GUEST_UID,
            'perm' => YB_ACL_PERM_READWRITE,
        ),
        array(
            'type' => YB_ACL_TYPE_USER,
            'id' => YB_LOGINED_UID,
            'perm' => YB_ACL_PERM_READWRITE,
        ),
    ),
);
$YB_INIT_DATA['acl']['wiki_local'] = array(
    'owner' => $owner,
    'name' => setupT('Wiki (editable only for logined users)'),
    'policy' => YB_ACL_POLICY_POSI,
    'perms' => array(
        array(
            'type' => YB_ACL_TYPE_USER,
            'id' => YB_GUEST_UID,
            'perm' => YB_ACL_PERM_READ,
        ),
        array(
            'type' => YB_ACL_TYPE_USER,
            'id' => YB_LOGINED_UID,
            'perm' => YB_ACL_PERM_READWRITE,
        ),
    ),
);
$YB_INIT_DATA['acl']['publish_family'] = array(
    'owner' => $owner,
    'name' => setupT('Family ONLY'),
    'policy' => YB_ACL_POLICY_POSI,
    'perms' => array(
        array(
            'type' => YB_ACL_TYPE_USER,
            'id' => YB_GUEST_UID,
            'perm' => YB_ACL_PERM_NONE,
        ),
        array(
            'type' => YB_ACL_TYPE_USER,
            'id' => YB_LOGINED_UID,
            'perm' => YB_ACL_PERM_NONE,
        ),
        array(
            'type' => YB_ACL_TYPE_GROUP,
            'id' => 2, // umm... maybe.
            'perm' => YB_ACL_PERM_READ,
        ),
    ),
);
$YB_INIT_DATA['acl']['publish_friends'] = array(
    'owner' => $owner,
    'name' => setupT('Friends ONLY'),
    'policy' => YB_ACL_POLICY_POSI,
    'perms' => array(
        array(
            'type' => YB_ACL_TYPE_USER,
            'id' => YB_GUEST_UID,
            'perm' => YB_ACL_PERM_NONE,
        ),
        array(
            'type' => YB_ACL_TYPE_USER,
            'id' => YB_LOGINED_UID,
            'perm' => YB_ACL_PERM_NONE,
        ),
        array(
            'type' => YB_ACL_TYPE_GROUP,
            'id' => 3, // umm... maybe.
            'perm' => YB_ACL_PERM_READ,
        ),
    ),
);
// }}}
// {{{ default wiki datas
$YB_INIT_DATA['wiki'] = array();
$YB_INIT_DATA['wiki'][] = array('frontpage.txt', setupT('FrontPage'));
$YB_INIT_DATA['wiki'][] = array('sidebar.txt', setupT('SideBar'));
$YB_INIT_DATA['wiki'][] = array('sandbox.txt', 'SandBox');
// }}}


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
