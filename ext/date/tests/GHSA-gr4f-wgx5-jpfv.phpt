--TEST--
GHSA-gr4f-wgx5-jpfv: DATEG(tzcache) use-after-free
--CREDITS--
Sebastian Bergmann
--INI--
error_log="{TMP}/GHSA-gr4f-wgx5-jpfv.log"
log_errors=1
display_startup_errors=0
max_input_vars=1
date.timezone=America/New_York
track_errors=1
--GET--
a=1&b=2
--FILE--
<?php
echo "Hello world\n";
/* the startup deprecation (track_errors) and the request startup warning
 * must both be logged in UTC, without touching DATEG(tzcache); neither is
 * displayed (display_startup_errors=0 hides request startup errors too) */
$log = file_get_contents(ini_get('error_log'));
var_dump(preg_match_all('/^\[\d{2}-\w{3}-\d{4} \d{2}:\d{2}:\d{2} UTC\] PHP (Deprecated|Warning): /m', $log));
var_dump(substr_count($log, 'America/New_York]'));
?>
--CLEAN--
<?php
@unlink(sys_get_temp_dir() . '/GHSA-gr4f-wgx5-jpfv.log');
?>
--EXPECTF--
%AHello world
int(2)
int(0)%A
