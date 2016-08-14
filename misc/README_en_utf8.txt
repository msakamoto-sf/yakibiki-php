//
//   Copyright (c) 2007 msakamoto-sf <msakamoto-sf@users.sourceforge.net>
//
//   Licensed under the Apache License, Version 2.0 (the "License");
//   you may not use this file except in compliance with the License.
//   You may obtain a copy of the License at
//
//       http://www.apache.org/licenses/LICENSE-2.0
//
//   Unless required by applicable law or agreed to in writing, software
//   distributed under the License is distributed on an "AS IS" BASIS,
//   WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
//   See the License for the specific language governing permissions and
//   limitations under the License.
//

YakiBiki : Yet Another Blog Like Wiki

READ ME DOCUMENTS

$Id: README_en_utf8.txt 557 2009-07-21 07:03:45Z msakamoto-sf $

* 1. LICENSE

YakiBiki is distributed under Apache License, Version 2.0.

* 2. REQUIREMENTS

PHP version >= PHP4.3.0
(development environment is 4.4.x/5.2.x + Apache 2.0.x, Apache Module)

YakiBiki includes PEAR libraries required in default setup.
If you want to use PEARs in system, not in YakiBiki, comment out 
_YB('dir.pear') or adjust its path to your system PEAR library path.
You can confirm final include_path value in YakiBiki, at setup.php.

* 3. INSTALL

** 3-1. Outline of directories and main files.

/index.php ... PHP execution main script.
/ybprepare.php ... common require file.
/ybwebtools/
    check-logs.php ... confirming log output.
    check-perms.php ... checking directory permissions.
    check-phpini.php ... confirming php.ini settings in YakiBiki environment.
    check-ybvars.php ... confirming _YB() config values in libs/config.php.
    clean-cache.php ... cleaning up YakiBiki's cache files.
    clean-yb.php ... cleaning up YakiBiki's data, log, cache files.
    fix-perms.php ... fixing YakiBiki's data, log, cache files/directories permissions
    sample_yb_append.php ... rename this file to 'yb_append.php' when using 
                             these support tools in production environment.
    setup.php ... setup scripts.
    yb_local.php ... common require file for ybwebtools

(*) datas/ ... YakiBiki data's top directory
        grain/ ... data directory
        idx/ ... indice data directory
        raw/ ... physical page data directory
        seq/ ... sequence data directory
    libs/ ... YakiBiki library directory
        init.php ... common pre-loaded script
        config.php ... configuration script
        funcs.php ... common functions
        timezones.php ... timezone definition script
        mime_types.php ... mime-type definition script
    locales/ ... Translation message catalog directory
(*) logs/ ... YakiBiki's log directory
    misc/ ... help pages, setup files.
    plugins/ ... YakiBiki's plugin directory
(*) temp/
        bookmark/ ... Xhwlay's bookmark save directory
        caches/ ... Cache_Lite uses for cache datas
        session/ ... PHP session save directory
        templates_c/ ... Smarty template compile directory
    themes/ ... Themes directory


You must allow user who execute web server to write files, make directories 
in directories marked with asterisk above list.
Check by check-perms.php, and adjust directory permissions.

NOTE : ybwebtools/{setup.php|clean-yb.php} are available only 
when _YB('setup.menu') is '1' in your config.php.

** 3-2. Configure directoy positions

You can change most directory positions in your config.php.
It is recommended that you move directories except ybwebtools/, themes/ 
direcotries to out of web public directory.
Fix your config.php and init.php requirement path in ybprepare.php

** 3-3. Configure your config.php

*** [Sessions] ... same to php.ini entries

_YB('session.save.path', $__base_dir . '/temp/session');

In default, browser maintain sessions for 2 weeks : 
_YB('session.lifetime',  86400 * 14); // 2 weeks by seconds.
(Change proper value for your security requirements.)

_YB('session.path',      '/');
_YB('session.domain',    '');
_YB('session.secure',    false); // Set true whe you use SSL !
_YB('session.name',      'ybs');
_YB('session.cache.limiter',    'none');

*** [Smarty settings] ... Default values are for development.

Change to false :
_YB('smarty.force_compile', true);
(When you are customizing theme template files, set true.)

*** [Core settings]

--- YOU MUST CHANGES ... ----

Set 0 after finishing setup :
_YB('setup.menu', 1); // setup.php
_YB('check.support.scripts', 1); // check-*.php

You can confirm these settings in setup.php :
_YB('title', 'YakiBiki');
_YB('theme', 'default');
_YB('url', 'http://yb-test/'); // MUST trailing '/'
_YB('url.themes', _YB('url') . 'themes/' . _YB('theme'));

Set PEAR_LOG_INFO in normal usage :
_YB('log.level', 'PEAR_LOG_DEBUG');

Set your own random strings. MUST.
_YB('password.salt', 'yakibiki');

--- optional ---

Set timezone name defined in timezone.php :
_YB('default.timezone', 'Asia/Tokyo');

Set default module :
_YB('default.module', 'view');

Set default page name :
_YB('default.pagename', 'FrontPage');

Set sidebar page name :
_YB('default.sidebar', 'SideBar');

If you want to customize <textarea> attribute, change these settings :
_YB('textarea.row', 20);
_YB('textarea.cols', 80);
_YB('textarea.wrap', 'soft');

Set default width of image when displaying :
_YB('yb.view.image.width.default', 600);

Set true if you want to check DNS record when e-mail address check.
_YB('email.check.use_chkdnsrr', false);
(requires socket php extension.)

Set true if you want to use GoogleMaps and javascripts.
_YB('js.enable', false);

To use GoogleMaps, you must adjust jquery and get your API key.
You can enable javascript and GoogleMaps after setup.
See <yb_gmap> plugin section in help document after setup.

** 3-4. Ready, Setup

Check permissions and log output after setting up your config.php.

1. Access URL for ybwebtools/check-perms.php by browser, 
check directory permissions are all green.

2. Access URL for ybwebtools/check-logs.php by browser, 
check log ouput is correct.

3. Finally, Access URL for setup.php by browser, 
register first user account("Sytem role").

YOU MUST SET ZERO FOR THESE TWO ITEM IN YOUR CONFIG.PHP!!
_YB('setup.menu')
_YB('check.support.scripts')

"login" links will be displayed above setup.php page after regsitration 
first user and initalization.
Click "login" link or access index.php, then YakiBiki starts.

* SPECIAL THANKS *

PukiWiki : http://pukiwiki.sourceforge.jp/
KinoWiki : http://kinowiki.net/index.php

NSD Corporation : http://www.nsd-ltd.co.jp/
Asial Corporation : http://www.asial.co.jp/

