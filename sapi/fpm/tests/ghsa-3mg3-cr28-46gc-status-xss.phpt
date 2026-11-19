--TEST--
FPM: GHSA-3mg3-cr28-46gc - status xss via request method, auth user and script filename
--SKIPIF--
<?php include "skipif.inc"; ?>
--FILE--
<?php

include "include.inc";
require_once "fcgi.inc";

$logfile = __DIR__.'/php-fpm.log.tmp';
$srcfile = __DIR__.'/ghsa-3mg3-cr28-46gc-<svg>.php';
$port = 9000+PHP_INT_SIZE;

$cfg = <<<EOT
[global]
error_log = $logfile
[unconfined]
listen = 127.0.0.1:$port
pm = static
pm.max_children = 3
pm.status_path = /status
catch_workers_output = yes
EOT;

file_put_contents($srcfile, "<?php\nusleep(300000);\n");

function status_body($port, $query) {
	$response = run_request('127.0.0.1', $port, '/status', $query);
	$pos = strpos($response, "\r\n\r\n");
	return $pos === false ? $response : substr($response, $pos + 4);
}

$fpm = run_fpm($cfg, $tail);
if (is_resource($fpm)) {
	fpm_display_log($tail, 2);

	$slow = new Adoy\FastCGI\Client('127.0.0.1', $port);
	$uri = '/ghsa-3mg3-cr28-46gc-<svg>.php';
	$id = $slow->async_request(array(
		'GATEWAY_INTERFACE' => 'FastCGI/1.0',
		'REQUEST_METHOD'    => '<svg>',
		'SCRIPT_FILENAME'   => $srcfile,
		'SCRIPT_NAME'       => $uri,
		'REQUEST_URI'       => $uri,
		'DOCUMENT_URI'      => $uri,
		'HTTP_AUTHORIZATION' => 'Basic ' . base64_encode('"<svg onload=x>:x'),
		'SERVER_SOFTWARE'   => 'php/fcgiclient',
		'REMOTE_ADDR'       => '127.0.0.1',
		'REMOTE_PORT'       => '9985',
		'SERVER_ADDR'       => '127.0.0.1',
		'SERVER_PORT'       => '80',
		'SERVER_NAME'       => php_uname('n'),
		'SERVER_PROTOCOL'   => 'HTTP/1.1',
		'CONTENT_TYPE'      => '',
		'CONTENT_LENGTH'    => 0
	), false);
	usleep(100000);

	$html = status_body($port, 'full&html');
	var_dump(strpos($html, '<svg'));
	var_dump(substr_count($html, '&lt;svg'));

	$xml = status_body($port, 'full&xml');
	var_dump(strpos($xml, '<svg'));
	var_dump(substr_count($xml, '&lt;svg'));

	$status = json_decode(status_body($port, 'full&json'), true);
	foreach ($status['processes'] as $proc) {
		if ($proc['request method'] === '<svg>') {
			var_dump($proc['user'], $proc['script'] === $srcfile);
		}
	}

	$slow->wait_for_response($id);

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
bool(false)
int(4)
bool(false)
int(4)
string(15) ""<svg onload=x>"
bool(true)
Done
--CLEAN--
<?php
	$logfile = __DIR__.'/php-fpm.log.tmp';
	@unlink($logfile);
	@unlink(__DIR__.'/ghsa-3mg3-cr28-46gc-<svg>.php');
?>
