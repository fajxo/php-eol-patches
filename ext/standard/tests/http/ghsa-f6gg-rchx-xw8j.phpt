--TEST--
GHSA-f6gg-rchx-xw8j: mixed-case HTTPS URLs must use TLS
--EXTENSIONS--
openssl
--SKIPIF--
<?php
if (!function_exists('proc_open')) die('skip proc_open() not available');
?>
--FILE--
<?php
$resultFile = sys_get_temp_dir() . '/ghsa-f6gg-rchx-xw8j-http.result';
@unlink($resultFile);

$serverCode = <<<'CODE'
    $server = stream_socket_server('tcp://127.0.0.1:64331', $errno, $errstr);
    phpt_notify();

    $conn = stream_socket_accept($server);
    $request = fread($conn, 1);
    file_put_contents(sys_get_temp_dir() . '/ghsa-f6gg-rchx-xw8j-http.result',
        $request === "\x16" ? 'TLS' : 'plaintext');

    fclose($conn);
    fclose($server);
    phpt_notify();
CODE;

$clientCode = <<<'CODE'
    $context = stream_context_create([
        'http' => [
            'method' => 'POST',
            'content' => 'opensec-sensitive-body',
            'header' => "X-Opensec-Request: validation\r\n",
            'timeout' => 5,
        ],
        'ssl' => [
            'verify_peer' => false,
            'verify_peer_name' => false,
        ],
    ]);
    phpt_wait();
    var_dump(@file_get_contents('HTTPS://alice:secret@127.0.0.1:64331/secret', false, $context));
    phpt_wait();
CODE;

include sprintf('%s/../../../openssl/tests/ServerClientTestCase.inc', __DIR__);
ServerClientTestCase::getInstance()->run($clientCode, $serverCode);
echo file_get_contents($resultFile), "\n";
?>
--EXPECT--
bool(false)
TLS
--CLEAN--
<?php
@unlink(sys_get_temp_dir() . '/ghsa-f6gg-rchx-xw8j-http.result');
?>
