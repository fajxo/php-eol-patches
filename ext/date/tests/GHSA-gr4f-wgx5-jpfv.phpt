--TEST--
GHSA-gr4f-wgx5-jpfv: DATEG(tzcache) use-after-free
--CREDITS--
Sebastian Bergmann
--DESCRIPTION--
5.6 port: instead of zend_test.warn_on_startup, a failing extension load
logs a warning during module startup (before the fix this allocated
DATEG(tzcache), which the startup memory-manager reset then freed). The
max_input_vars warning is logged during request startup, before
date's RINIT, and used the dangling cache. Best run under valgrind (-m).
Neither warning is displayed (display_startup_errors=0 hides request
startup errors too), so the test reads them back from the log: both must
carry a UTC timestamp, i.e. neither touched date.timezone/the tz cache.
--GET--
a=1&b=2
--INI--
error_log={PWD}/GHSA-gr4f-wgx5-jpfv.log
log_errors=1
display_startup_errors=0
max_input_vars=1
date.timezone=America/New_York
extension=ghsa_gr4f_wgx5_jpfv_does_not_exist.so
--FILE--
<?php
echo "Hello world\n";
$log = file_get_contents(ini_get('error_log'));
var_dump(preg_match_all('/^\[\d{2}-\w{3}-\d{4} \d{2}:\d{2}:\d{2} UTC\] PHP Warning:  (PHP Startup: Unable to load dynamic library|Unknown: Input variables exceeded 1)/m', $log));
var_dump(substr_count($log, 'America/New_York]'));
?>
--EXPECTF--
%AHello world
int(2)
int(0)%A
--CLEAN--
<?php
@unlink(__DIR__ . '/GHSA-gr4f-wgx5-jpfv.log');
?>
