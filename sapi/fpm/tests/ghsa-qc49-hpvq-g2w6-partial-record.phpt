--TEST--
FPM: partially read STDIN record does not make the worker reject later connections (GHSA-qc49-hpvq-g2w6)
--SKIPIF--
<?php include "skipif.inc"; ?>
--FILE--
<?php

include "include.inc";
include "ghsa-qc49-hpvq-g2w6.inc";

$logfile = __DIR__.'/php-fpm.log.tmp';
$srcfile = __DIR__.'/php-fpm.tmp.php';
$port = 9000+PHP_INT_SIZE;

$cfg = <<<EOT
[global]
error_log = $logfile
[unconfined]
listen = 127.0.0.1:$port
pm = static
pm.max_children = 1
catch_workers_output = yes
EOT;

$code = <<<'EOT'
<?php
if ($_SERVER['REMOTE_ADDR'] === '203.0.113.1') {
    echo "smuggled\n";
} else {
    echo "outer ", strlen(file_get_contents('php://input')), "\n";
}
EOT;
file_put_contents($srcfile, $code);

$fpm = run_fpm($cfg, $tail);
if (is_resource($fpm)) {
	fpm_display_log($tail, 2);

	$hiddenParams = array(
		'GATEWAY_INTERFACE' => 'FastCGI/1.0',
		'REQUEST_METHOD' => 'GET',
		'SCRIPT_FILENAME' => $srcfile,
		'SCRIPT_NAME' => '/smuggled.php',
		'REQUEST_URI' => '/smuggled.php',
		'SERVER_PROTOCOL' => 'HTTP/1.1',
		'REMOTE_ADDR' => '203.0.113.1',
		'CONTENT_LENGTH' => '0',
	);
	$hidden = ghsa_request(2, false, $hiddenParams, '');
	$outerParams = array(
		'GATEWAY_INTERFACE' => 'FastCGI/1.0',
		'REQUEST_METHOD' => 'POST',
		'SCRIPT_FILENAME' => $srcfile,
		'SCRIPT_NAME' => '/open.php',
		'REQUEST_URI' => '/open.php',
		'SERVER_PROTOCOL' => 'HTTP/1.1',
		'REMOTE_ADDR' => '127.0.0.1',
		'CONTENT_TYPE' => 'application/octet-stream',
		'CONTENT_LENGTH' => '1',
	);
	$plainParams = array('REQUEST_METHOD' => 'GET', 'CONTENT_LENGTH' => '0') + $outerParams;
	unset($plainParams['CONTENT_TYPE']);

	/* The STDIN record is longer than CONTENT_LENGTH so it is left partially read. */
	foreach (array(false, true) as $keep) {
		$response = ghsa_transact('127.0.0.1', $port, ghsa_request(1, $keep, $outerParams, 'A' . $hidden));
		echo 'keep ', $keep ? 'on' : 'off', ': ',
			ghsa_has($response, 'smuggled') ? 'smuggled' : 'safe', ', ',
			ghsa_has($response, 'outer 1') ? 'served' : 'not served', "\n";
		/* The only worker must still serve new connections. */
		foreach (array(1, 2) as $n) {
			$response = ghsa_transact('127.0.0.1', $port, ghsa_request(1, false, $plainParams, ''));
			echo "  next connection $n: ", ghsa_has($response, 'outer 0') ? 'served' : 'not served', "\n";
		}
	}

	proc_terminate($fpm);
	stream_get_contents($tail);
	fclose($tail);
	proc_close($fpm);
}

?>
Done
--EXPECTF--
[%d-%s-%d %d:%d:%d] NOTICE: fpm is running, pid %d
[%d-%s-%d %d:%d:%d] NOTICE: ready to handle connections
keep off: safe, served
  next connection 1: served
  next connection 2: served
keep on: safe, served
  next connection 1: served
  next connection 2: served
Done
--CLEAN--
<?php
	$logfile = __DIR__.'/php-fpm.log.tmp';
	$srcfile = __DIR__.'/php-fpm.tmp.php';
	@unlink($logfile);
	@unlink($srcfile);
?>
