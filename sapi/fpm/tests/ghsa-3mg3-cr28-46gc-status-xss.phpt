--TEST--
FPM: GHSA-3mg3-cr28-46gc - status xss via request method, uri, query, auth user and script filename
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
pm.max_children = 2
pm.status_path = /status
catch_workers_output = yes
EOT;

$code = <<<EOT
<?php
usleep(500000);
EOT;

$scriptFilename = __DIR__ . '/ghsa-3mg3-cr28-46gc-<svg>.php';
file_put_contents($scriptFilename, $code);

$tester = new FPM\Tester($cfg, $code);
$tester->start();
$tester->expectLogStartNotices();

/* keep one worker busy with the malicious request while the other one serves the status page */
list($host, $port) = explode(':', $tester->getAddr());
$client = new \Adoy\FastCGI\Client($host, $port);
$id = $client->async_request([
    'GATEWAY_INTERFACE' => 'FastCGI/1.0',
    'REQUEST_METHOD'    => '<svg>',
    'SCRIPT_FILENAME'   => $scriptFilename,
    'SCRIPT_NAME'       => '/ghsa-3mg3-cr28-46gc-<svg>.php',
    'QUERY_STRING'      => 'q="<svg>\'',
    'REQUEST_URI'       => '/ghsa-3mg3-cr28-46gc-<svg>.php?q="<svg>\'',
    'HTTP_AUTHORIZATION' => 'Basic ' . base64_encode('"<svg onload=x>:x'),
    'SERVER_PROTOCOL'   => 'HTTP/1.1',
    'REMOTE_ADDR'       => '127.0.0.1',
    'CONTENT_TYPE'      => '',
    'CONTENT_LENGTH'    => 0,
], false);
usleep(100000);

$html = $tester->request('full&html', [], '/status')->getBody('text/html');
$xml = $tester->request('full&xml', [], '/status')->getBody('text/xml');
$json = $tester->request('full&json', [], '/status')->getBody('application/json');
$client->wait_for_response($id);

foreach (['html' => $html, 'xml' => $xml] as $type => $body) {
    echo "$type:\n";
    var_dump(strpos($body, '<svg'));
    var_dump(substr_count($body, '&lt;svg'));
    var_dump(substr_count($body, '&quot;&lt;svg'));
    var_dump(substr_count($body, '&gt;&apos;'));
}

echo "json:\n";
$status = json_decode($json, true);
var_dump(is_array($status));
foreach ($status['processes'] as $proc) {
    if ($proc['request method'] === '<svg>') {
        var_dump($proc['request uri'], $proc['user'], $proc['script'] === $scriptFilename);
    }
}
$tester->terminate();
$tester->expectLogTerminatingNotices();
$tester->close();

?>
Done
--EXPECT--
html:
bool(false)
int(5)
int(2)
int(1)
xml:
bool(false)
int(5)
int(2)
int(1)
json:
bool(true)
string(40) "/ghsa-3mg3-cr28-46gc-<svg>.php?q="<svg>'"
string(15) ""<svg onload=x>"
bool(true)
Done
--CLEAN--
<?php
require_once "tester.inc";
FPM\Tester::clean();
@unlink(__DIR__ . '/ghsa-3mg3-cr28-46gc-<svg>.php');
?>
