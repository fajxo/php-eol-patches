--TEST--
FPM: unread request body cannot become a new request on a kept connection (GHSA-qc49-hpvq-g2w6)
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
    $input = fopen('php://input', 'r');
    echo "outer\n";
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
	$body = str_pad("--B\r\nContent-Disposition: form-data\r\n\r\n", 12000, 'A');
	$body = substr_replace($body, $hidden, 5120, strlen($hidden));

	$outerParams = array(
		'GATEWAY_INTERFACE' => 'FastCGI/1.0',
		'REQUEST_METHOD' => 'POST',
		'SCRIPT_FILENAME' => $srcfile,
		'SCRIPT_NAME' => '/open.php',
		'REQUEST_URI' => '/open.php',
		'SERVER_PROTOCOL' => 'HTTP/1.1',
		'REMOTE_ADDR' => '127.0.0.1',
		'CONTENT_TYPE' => 'multipart/form-data; boundary=B',
		'CONTENT_LENGTH' => (string) strlen($body),
	);
	foreach (array(false, true) as $keep) {
		$response = ghsa_transact('127.0.0.1', $port, ghsa_request(1, $keep, $outerParams, $body));
		echo 'keep ', $keep ? 'on' : 'off', ': ',
			ghsa_has($response, 'smuggled') ? 'smuggled' : 'safe', "\n";
	}

	$body = 'A' . $hidden;
	$outerParams['CONTENT_TYPE'] = 'application/octet-stream';
	$outerParams['CONTENT_LENGTH'] = '1';
	$response = ghsa_transact('127.0.0.1', $port, ghsa_request(1, true, $outerParams, $body));
	echo 'partial frame: ',
		ghsa_has($response, 'smuggled') ? 'smuggled' : 'safe', "\n";

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
keep off: safe
keep on: safe
partial frame: safe
Done
--CLEAN--
<?php
	$logfile = __DIR__.'/php-fpm.log.tmp';
	$srcfile = __DIR__.'/php-fpm.tmp.php';
	@unlink($logfile);
	@unlink($srcfile);
?>
