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
 * YakiBiki Log Checker
 *
 * @author msakamoto-sf <msakamoto-sf@users.sourceforge.net>
 * @version $Id: check-logs.php 442 2008-11-15 13:20:14Z msakamoto-sf $
 */

require_once(dirname(__FILE__) . '/yb_local.php');

if (!_YB('check.support.scripts')) {
    echo "check support scripts is prohibited.";
    exit();
}

yb_Session::start();
$errorlog_out = false;
$pearlog_out = false;

if (strtolower(yb_Var::env('REQUEST_METHOD')) == 'post') {
    $_errorlog_level = (string)(yb_Var::post('errorlog_level'));
    $_errorlog = (string)(yb_Var::post('errorlog'));
    $_pearlog_level = (string)(yb_Var::post('pearlog_level'));
    $_pearlog = (string)(yb_Var::post('pearlog'));

    if (!empty($_errorlog)) {
        trigger_error(h($_errorlog), constant($_errorlog_level));
        $errorlog_out = true;
    }
    if (!empty($_pearlog)) {
        $logger =& yb_Log::get_logger();
        $logger->$_pearlog_level(h($_pearlog));
        $pearlog_out = true;
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
?>
<html>
<head>
<title>YakiBiki Log Checker</title>
</head>
<body>
<h1>YakiBiki Log Checker</h1>
<h3>session_id() : <?php echo session_id(); ?></h3>
<form method="POST">

<hr />

<h2>PHP Error Log(trigger_error())</h2>
<table border="1">
<tr>
<th>error_reporting</th><td><?php echo ini_get('error_reporting') ?></td>
</tr>
<tr>
<th>display_errors</th><td><?php echo ini_get('display_errors') ?></td>
</tr>
<tr>
<th>log_errors</th><td><?php echo ini_get('log_errors') ?></td>
</tr>
<tr>
<th>log_errors_max_len</th><td><?php echo ini_get('log_errors_max_len') ?></td>
</tr>
<tr>
<th>error_log</th><td><?php echo ini_get('error_log') ?></td>
</tr>
</table>
<select name="errorlog_level">
<option value="E_USER_NOTICE" selected>E_USER_NOTICE</option>
<option value="E_USER_WARNING">E_USER_WARNING</option>
<option value="E_USER_ERROR">E_USER_ERROR</option>
</select>
<br />
<input type="text" name="errorlog" value="test" size="128" />
<?php if ($errorlog_out) : ?>
<p style="color: red">Outputted.</p>
<?php endif; ?>

<hr />

<h2>YakiBiki Normal Application Log (PEAR Log)</h2>
<table border="1">
<tr>
<th>_YB('log.out')</th><td><?php echo realpath(_YB('log.out')); ?></td>
</tr>
</table>
<select name="pearlog_level">
<option value="debug">PEAR_LOG_DEBUG(debug())</option>
<option value="info">PEAR_LOG_INFO(info())</option>
<option value="notice" selected>PEAR_LOG_NOTICE(notice())</option>
<option value="warning">PEAR_LOG_WARNING(warning())</option>
<option value="err">PEAR_LOG_ERR(err())</option>
<option value="crit">PEAR_LOG_CRIT(crit())</option>
<option value="alert">PEAR_LOG_ALERT(alert())</option>
<option value="emerg">PEAR_LOG_EMERG(emerg())</option>
</select>
<br />
<input type="text" name="pearlog" value="test" size="128" />
<?php if ($pearlog_out) : ?>
<p style="color: red">Outputted.</p>
<?php endif; ?>

<hr />

<input type="submit" value="test output" />

</form>
</body>
</html>
