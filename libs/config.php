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
 * YakiBiki Configuration File.
 *
 * @author msakamoto-sf <msakamoto-sf@users.sourceforge.net>
 * @version $Id: config.php 563 2009-07-22 07:39:18Z msakamoto-sf $
 */

// YakiBiki Root Directory
$__base_dir = realpath(dirname(__FILE__) . '/../');

_YB('xhwlay.bookmarkcontainer.params', array(
    "dataDir" => $__base_dir . '/temp/bookmark',
    "expire" => 3600, 
    "gc_probability" => 1,
    "gc_divisor" => 10,
    "gc_maxlifetime" => 86400,
));
_YB('yb.version', '0.9.2');

// {{{ session parameters

// NOTICE: Change these parameters CAREFULLY.

// http://jp.php.net/manual/ja/function.session-set-cookie-params.php
_YB('session.save.path', $__base_dir . '/temp/session');
_YB('session.lifetime',  86400 * 14); // 2 weeks by seconds.
_YB('session.path',      '/');
_YB('session.domain',    '');
_YB('session.secure',    false);
_YB('session.name',      'ybs');

// http://jp.php.net/manual/ja/function.session-cache-limiter.php
//_YB('session.cache.limiter',    'private_no_expire');
_YB('session.cache.limiter',    'none');

// regenerate session id interval (by second)
_YB('session.id_regenerate_interval', 30 * 60); // 30 minutes.

// }}}
// {{{ smarty

_YB('smarty.compile_check', true);
_YB('smarty.force_compile', true);

// }}}
// {{{ default mime-type, resource-locale, default-module, setup-menus

// default mime-type
_YB('default.mime.type', 'text/html; charset=UTF-8');

// default time zone
_YB('default.timezone', 'Asia/Tokyo');

// displaying datetime format (strftime())
_YB('default.datetime_format', '%Y-%m-%d %H:%M:%S');

// resouce locale
_YB('resource.locale', 'ja_JP.utf8');
//_YB('resource.locale', 'en_US.utf8');

// internal encoding
if (function_exists('mb_internal_encoding')) {
    _YB('internal.encoding', mb_internal_encoding());
} else {
    _YB('internal.encoding', 'UTF-8');
}

// enable setup menu (setup.php) (1: enable, 0: disable)
_YB('setup.menu', 1);

// enable check-support-scripts (check-*.php) (1: enable, 0: disable)
_YB('check.support.scripts', 1);

// default module
_YB('default.module', 'view');

// default page name title
_YB('default.pagename', 'FrontPage');

// default side-bar page name title
_YB('default.sidebar', 'SideBar');

// guest user name
_YB('guest.user.name', 'guest');

// }}}
// {{{ mail_notify

// use mail notifier or not
_YB('mail_notify.enable', false);

// attach files or not
_YB('mail_notify.attach_files', false);

_YB('mail_notify.charset', 'iso-8859-1');
//_YB('mail_notify.charset', 'iso-2022-jp');

_YB('mail_notify.mb_func_charset', 'ASCII');
//_YB('mail_notify.mb_func_charset', 'JIS');

// Subject Prefix
_YB('mail_notify.subject_prefix', '[YakiBiki]');

// 'smtp', 'mail', 'sendmail'
_YB('mail_notify.mailer', 'smtp');

// if mailer is 'sendmail', set full-filepath to 'sendmail' program
_YB('mail_notify.sendmail_binpath', '/usr/sbin/sendmail');

// if mailer is 'smtp', set SMTP server host, port, and debug feature
_YB('mail_notify.smtp_host', 'localhost');
_YB('mail_notify.smtp_port', 25);
//_YB('mail_notify.smtp_port', 587); // submission port
_YB('mail_notify.use_smtp_debug', false);

_YB('mail_notify.mail_from', 'your@yakibiki.mailaddress');
_YB('mail_notify.mail_from_name', 'YakiBiki Mail Notifier');
_YB('mail_notify.mail_reply_to', 'yakibiki-reply-to@system.mailaddress');
_YB('mail_notify.mail_to_all', 'sendto@system.mailaddress');

// }}}
// {{{ site title, url, theme and others

_YB('title', 'YakiBiki');
_YB('theme', 'default');
_YB('url', 'http://yb-test/'); // MUST trailing '/'
_YB('index_file', 'index.php'); // optional
_YB('url.themes', _YB('url') . 'themes/' . _YB('theme'));

_YB('textarea.row', 20);
_YB('textarea.cols', 80);
_YB('textarea.wrap', 'soft');
_YB('yb.acceptable.image.extensions', 'gif,jpeg,jpg,jpe,png');
_YB('yb.view.image.width.default', 600);

// enable auto ls title when view mode.
_YB('use.title_auto_ls', true);

// }}}
// {{{ javascripts

_YB('js.enable', false);

// jquery path
_YB('js.jquery.path', _YB('url.themes') . '/javascripts/jquery-1.2.6.min.js');

// GoogleMap key
_YB('js.google.map.key', 
    trim(@file_get_contents($__base_dir . '/datas/googlemapkey')));

// GoogleMap URL (When _YB('js.enable') is false)
//_YB('jsoff.google.map.url', 'http://maps.google.com/maps'); // for U.S.
_YB('jsoff.google.map.url', 'http://maps.google.co.jp/maps'); // for Japan

// }}}
// {{{ directories

// setup data directory
_YB('dir.initdata', $__base_dir . '/misc/initdata');

// help data directory
_YB('dir.helpdocs', $__base_dir . '/misc/help');

// Library directory
_YB('dir.libs', $__base_dir . '/libs');

// PEAR directory (optional)
_YB('dir.pear', $__base_dir . '/libs/pear');
// If you want to use your system pear, comment out above line.
// (and confirm your "include_path" settings...)

// Log output directory
_YB('dir.logs', $__base_dir . '/logs');

// Theme directory
_YB('dir.themes', $__base_dir . '/themes');

// Smarty template file compile directory
// (Directory Separator Terminated)
_YB('dir.smarty.templates_c', $__base_dir . '/temp/templates_c');

// Smarty Plugin directory (Directory Separator Terminated)
_YB('dir.smarty.plugins', _YB('dir.libs'). '/yb/smarty/plugins/');

// Cache directory
_YB('dir.caches', $__base_dir . '/temp/caches');

// HTML plugin directory
_YB('dir.plugin.html', $__base_dir . '/plugins/html');

// Wiki plugin directory
_YB('dir.plugin.wiki', $__base_dir . '/plugins/wiki');

// YakiBiki Hook plugin directory
_YB('dir.plugin.hook', $__base_dir . '/plugins/hook');

// Locale directory
_YB('dir.locale', $__base_dir . '/locales');

// }}}
// {{{ grain library direcotry configuration

_YB('grain.configs', array(
    'grain.dir.grain' => $__base_dir . '/datas/grain',
    'grain.dir.index' => $__base_dir . '/datas/idx',
    'grain.dir.sequence' => $__base_dir . '/datas/seq',
    'grain.dir.raw' => $__base_dir . '/datas/raw',
    'grain.chunksize.default' => 100,
    'grain.chunksize.data' => 500,
));

// }}}
// {{{ others

// 'md5', 'crc32', 'sha1'
// If not specified or unexisted function name, password not be hashed.
_YB('password.hash.func', 'sha1');

// password 'salt'
_YB('password.salt', 'yakibiki');

// E-mail address check parameters
// Use strict regexp or not
_YB('email.check.use_strict_regexp', true);
// Use DNS MX/A record check or not
_YB('email.check.use_chkdnsrr', false);

// Disable User Deletion
_YB('disable.user.physical_delete', true);

// cache settings
_YB('cache.options', 
    array(
        'caching' => true, // enable/disable caching
        'cacheDir' => _YB('dir.caches') . '/',
        'lifeTime' => 7200, // lifetime in seconds
        'hashedDirectoryLevel' => 2,
        'automaticSerialization' => true,
    ));

// module replacement
// WARNING: FOR DEVELOPERS ONLY.
//_YB('module.convert.login', 'your_login_module');
//_YB('module.convert.logout', 'your_logout_module');

// hook replacement
// WARNING: FOR DEVELOPERS ONLY.
//_YB('hook.convert.make_url', 'your_routing_rule_hook');

// data types
_YB('datatypes', array(
    'text' => array(
        'name' => 'Text',
        'class' => 'yb_datatype_Text',
    ),
    'image' => array(
        'name' => 'Image',
        'class' => 'yb_datatype_Image',
    ),
    'attach' => array(
        'name' => 'File',
        'class' => 'yb_datatype_Attach',
    ),
    'bookmark' => array(
        'name' => 'Bookmark',
        'class' => 'yb_datatype_Bookmark',
    ),
));

// }}}
// {{{ logs

// yb_Log (default PEAR_Log wrapper logger) parameters
_YB('log.out', $__base_dir . '/logs/yakibiki.log');
// PEAR_LOG_{EMERG|ALERT|CRIT|ERR|WARNING|NOTICE|INFO|DEBUG}
// NOTE : At this point, PEAR_Log is not required yet.
// so, DON'T set constant, but string is okay.
_YB('log.level', 'PEAR_LOG_DEBUG');
_YB('log.append', 1);
_YB('log.locking', 1);
//_YB('log.mode', 0644);
//_YB('log.dirmode', 0755);
// "CR"|"CRLF"|"LF" or else, "PHP_EOL"
_YB('log.eol', 'PHP_EOL');
_YB('log.lineFormat', '%1$s %2$s [%3$s] %4$s');
_YB('log.timeFormat', '%Y-%m-%d %H:%M:%S');

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
