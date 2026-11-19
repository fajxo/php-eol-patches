--TEST--
FPM: GHSA-3mg3-cr28-46gc - status xss via request method, query, auth user and script filename
--SKIPIF--
<?php include "skipif.inc"; ?>
--FILE--
<?php

require_once "tester.inc";

$cfg = <<<EOT
[global]
error_log = {{FILE:LOG}}
[unconfined]
listen = {{ADDR}}
pm = static
pm.max_children = 3
pm.status_path = /status
catch_workers_output = yes
EOT;

$code = <<<EOT
<?php
usleep(500000);
EOT;

$scriptFilename = __DIR__ . '/ghsa-3mg3-cr28-46gc-<svg>.php';
file_put_contents($scriptFilename, $code);
$scriptName = '/ghsa-3mg3-cr28-46gc-<svg>.php';
$query = 'a=<svg>&b=\'"\\/&c=' . "\x01";

$tester = new FPM\Tester($cfg, $code);
$tester->start();
$tester->expectLogStartNotices();

/* keep one worker busy with the malicious request (7.4 multiRequest has no delay) */
[$host, $port] = explode(':', $tester->getAddr());
$client = new \Adoy\FastCGI\Client($host, $port);
$requestId = $client->async_request([
    'GATEWAY_INTERFACE' => 'FastCGI/1.0',
    'REQUEST_METHOD'    => '<svg>',
    'SCRIPT_FILENAME'   => $scriptFilename,
    'SCRIPT_NAME'       => $scriptName,
    'QUERY_STRING'      => $query,
    'REQUEST_URI'       => $scriptName . '?' . $query,
    'DOCUMENT_URI'      => $scriptName,
    'SERVER_SOFTWARE'   => 'php/fcgiclient',
    'REMOTE_ADDR'       => '127.0.0.1',
    'REMOTE_PORT'       => '7777',
    'SERVER_ADDR'       => '127.0.0.1',
    'SERVER_PORT'       => '80',
    'SERVER_NAME'       => php_uname('n'),
    'SERVER_PROTOCOL'   => 'HTTP/1.1',
    'DOCUMENT_ROOT'     => __DIR__,
    'CONTENT_TYPE'      => '',
    'CONTENT_LENGTH'    => 0,
    'HTTP_AUTHORIZATION' => 'Basic ' . base64_encode('"<svg onload=x>:x'),
], false);
usleep(150000);

$html = $tester->request('full&html', [], '/status')->getBody('text/html');
$xml = $tester->request('full&xml', [], '/status')->getBody('text/xml');
$json = $tester->request('full&json', [], '/status')->getBody('application/json');
$client->wait_for_response_data($requestId);

foreach (['html' => $html, 'xml' => $xml] as $type => $body) {
    echo "$type: ";
    var_dump(strpos($body, '<svg'), substr_count($body, '&lt;svg'));
}
$status = json_decode($json, true);
var_dump(json_last_error_msg());
foreach ($status['processes'] as $proc) {
    if ($proc['request method'] === '<svg>') {
        var_dump(
            $proc['user'],
            $proc['script'] === $scriptFilename,
            substr($proc['request uri'], -strlen($query)) === $query
        );
    }
}
$tester->terminate();
$tester->expectLogTerminatingNotices();
$tester->close();

?>
Done
--EXPECT--
html: bool(false)
int(5)
xml: bool(false)
int(5)
string(8) "No error"
string(15) ""<svg onload=x>"
bool(true)
bool(true)
Done
--CLEAN--
<?php
require_once "tester.inc";
FPM\Tester::clean();
@unlink(__DIR__ . '/ghsa-3mg3-cr28-46gc-<svg>.php');
?>
