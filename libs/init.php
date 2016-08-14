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
 *
 */

/*
 * YakiBiki Initializing Boot-Strap File.
 *
 * @author msakamoto-sf <msakamoto-sf@users.sourceforge.net>
 * @version $Id: init.php 566 2009-07-23 13:57:45Z msakamoto-sf $
 */

// {{{ NOTICE: DONT'T CHANGE THIS BLOCK!!

// factory zone indicator
define('FACTORY_ZONE', "\0__GLOBAL_FACTORY_ZONE_INDICATOR");

// initial factory zone indicator
$GLOBALS[FACTORY_ZONE] = 'default';

define('ORDER_BY_ASC', 1);
define('ORDER_BY_DESC', -1);
define('YB_AND', 1);
define('YB_OR', 2);
define('YB_DATA_FS', "\x1C"); // Field Separator
define('YB_DATA_GS', "\x1D"); // Group Separator
define('YB_DATA_RS', "\r\n"); // Record Separator

// User Status : OK
define('YB_USER_STATUS_OK', 1);

// User Status : Login Disabled
define('YB_USER_STATUS_DISABLED', 2);

// YakiBiki ACL : Permission Level Constants
define('YB_ACL_PERM_READ', 1);
define('YB_ACL_PERM_READWRITE', 2);

// YakiBiki ACL : Permission Type Constants
define('YB_ACL_TYPE_USER', 'U');
define('YB_ACL_TYPE_GROUP', 'G');
define('YB_ACL_PERM_NONE', 0);

// YakiBiki ACL : Policy Constants
define('YB_ACL_POLICY_POSI', 'P');
define('YB_ACL_POLICY_NEGA', 'N');

// Image, Attachment file name max length limit
define('UPLOAD_FILENAME_MAXLEN', 256);

/**
 * Date/Time input box format (strftime)
 * ex. 2007-10-14 23:29:10
 *
 * @var string
 */
define('YB_FMT_DATA_TIMESTAMP', '%Y-%m-%d %H:%M:%S');

/**
 * YB data timestamp validation regexp
 *
 * @var string
 */
define('YB_REGEXP_DATA_TIMESTAMP', 
    '/^(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2}):(\d{2})$/mi');

// YakiBiki input form reqired mark
define('YB_FORM_REQUIRED_MARK', '<font color="red">*</font>');

/**
 * Guest User ID (equal NOT LOGINED user)
 * @var integer
 */
define('YB_GUEST_UID', -1);

/**
 * ACL special virtual user : represent ALL logined users
 * @var integer
 */
define('YB_LOGINED_UID', -2);

/**
 * YB user name validattion regexp
 *
 * @var string
 */
define('YB_REGEXP_USER_NAME', '/^([0-9A-Za-z_\.-]+)$/mi');

/**
 * YakiBiki session ticket id default name space
 * @var string
 */
define('YB_UTIL_TICKET_NAMESPACE', 'yb.util.ticket.namespace');

/**
 * YakiBiki session ticket id for comment feature name space
 * @var string
 */
define('YB_UTIL_TICKET_NS4COMMENT', 'comment');

// }}}

/*
 * __DS__ as direcotry separator short cut.
 */
define('__DS__', DIRECTORY_SEPARATOR);
if(!defined('PATH_SEPARATOR')) {
    define('PATH_SEPARATOR',
        (isset($_ENV['OS']) && preg_match("/window/i", $_ENV['OS']))
         ? ';' : ':');
}

require_once(dirname(__FILE__) . '/funcs.php');
require_once(dirname(__FILE__) . '/timezones.php');

// load config.php
require_once(YB_CONFIG_PHP);

if (defined('YB_CONFIG_PHP_APPEND')) {
    if (is_readable(YB_CONFIG_PHP_APPEND)) {
        // load appendix config.php
        require_once(YB_CONFIG_PHP_APPEND);
    }
}

/*
 * Add Library base directory
 */
set_include_path(_YB('dir.libs') 
    . PATH_SEPARATOR . realpath(_YB('dir.libs') . '/phpmailer') 
    . PATH_SEPARATOR . get_include_path());

/*
 * Add PEAR directory if specified.
 */
if(!is_null(_YB('dir.pear')) && _YB('dir.pear') != "") {
    set_include_path(_YB('dir.pear') . PATH_SEPARATOR . get_include_path());
}

// protector.php by GIJOE : Mineaki Gotou
require_once('protector.php');

require_once('yb/Log.php');
require_once('yb/ErrorStackLogger.php');
require_once('yb/Util.php');
require_once('yb/Session.php');
require_once('yb/Var.php');
require_once('yb/Cache.php');
require_once('yb/AclCache.php');
require_once('yb/smarty/Renderer.php');
require_once('yb/smarty/Theme.php');
require_once('yb/DataContext.php');
require_once('yb/Html.php');
require_once('yb/Wiki.php');
require_once('yb/Time.php');
require_once('yb/FormBuilderBase.php');
require_once('yb/Trans.php');
require_once('yb/Error.php');
require_once('grain/Factory.php');
require_once('yb/dao/Factory.php');
require_once('yb/Finder.php');
require_once('yb/jQuery.php');

// copy grain direcotie's _YB() config to grain_Config.
$__grain_yb_configs = _YB('grain.configs');
foreach ($__grain_yb_configs as $k => $v) {
    grain_Config::set($k, $v);
}

// parse url and merge to _YB() as 'url.*'
$__url_parts = yb_Util::url_parse(_YB('url'));
foreach ($__url_parts as $k => $v) {
    $k = 'url.' . $k;
    _YB($k, $v);
}

_YB('current_url', yb_Util::current_url());

if (php_sapi_name() != 'cli') {
    header('Content-Type: ' . _YB('default.mime.type'));
    yb_Error::set_raise_callback('yb_error_webhandler');
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
