<?php
/*
 *   Copyright (c) 2009 msakamoto-sf <msakamoto-sf@users.sourceforge.net>
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
 * YakiBiki: backup module
 *
 * @author msakamoto-sf <msakamoto-sf@users.sourceforge.net>
 * @version $Id: dispatcher.php 564 2009-07-22 09:51:58Z msakamoto-sf $
 */

require_once('Archive/Tar.php');

yb_Session::start();
$uc = yb_Session::user_context();

// only sys role can backup.
if (!yb_Session::hasRole('sys')) {
    yb_Util::forward_error_die(t('Illegal operation.'), null, 500);
}

$renderer =& new yb_smarty_Renderer();

$do_backup = yb_Var::post('do_backup');
if ($do_backup == 'ok') {
    yb_Util::ticket_is_valid_or_error_die();
    $dir_logs = _YB('dir.logs');
    $t =& new yb_Time();
    $basename = 'backup_'.$t->get('%Y%m%d%H%M%S');
    $backupfile = $basename.'.tar';
    $backupfull = $dir_logs.'/'.$backupfile;
    // don't zip (for php environment without zlib)
    $tar =& new Archive_Tar($backupfull);
    $tar->setErrorHandling(PEAR_ERROR_TRIGGER);

    // backups for each grain related directories
    $_grains = _YB('grain.configs');
    $_keys = array('grain', 'index', 'sequence', 'raw');
    $r = false;
    foreach ($_keys as $_k) {
        $__k = 'grain.dir.'.$_k;
        $_dir = $_grains[$__k];
        $r = $tar->addModify($_dir.'/', $_k, $_dir);
        if (!$r) { continue; }
    }

    if ($r) {
        $msg = t('Success, backup file created as "logs/%file".', 
            array('file' => $backupfile));
    } else {
        $msg = t('Failure, see php error log.');
    }
    $renderer->set('backup_msg', $msg);
}

list($ticket_form, $ticket_id) = yb_Util::issue_ticket();
$renderer->set('ticket_form', $ticket_form);
$renderer->set('ticket_id', $ticket_id);

$renderer->setTitle(t('Data Backup'));
$renderer->set('user_context', $uc);
$renderer->setViewName("theme:modules/backup/backup_tpl.html");
$output = $renderer->render();

echo $output;

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
