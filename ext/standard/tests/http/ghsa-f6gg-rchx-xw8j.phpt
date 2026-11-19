--TEST--
GHSA-f6gg-rchx-xw8j: mixed-case HTTPS URLs must use TLS
--SKIPIF--
<?php
if (!extension_loaded('openssl')) die('skip openssl extension not available');
if (!function_exists('proc_open')) die('skip proc_open() not available');
?>
--FILE--
<?php
$serverCode = <<<'CODE'
    $base = sys_get_temp_dir() . '/ghsa-f6gg-rchx-xw8j-http';
    $server = stream_socket_server('tcp://127.0.0.1:0', $errno, $errstr);
    file_put_contents("$base.addr", stream_socket_get_name($server, false));
    phpt_notify();

    $conn = stream_socket_accept($server);
    $request = fread($conn, 1);

    if ($request === "\x16") {
        $result = 'TLS';
    } else {
        while (($line = stream_get_line($conn, 8192, "\r\n")) !== false) {
            $request .= $line . "\r\n";
            if ($line === '') {
                break;
            }
        }
        $request .= stream_get_contents($conn, strlen('opensec-sensitive-body'));
        $result = strpos($request, 'Authorization: Basic ') !== false
            && strpos($request, 'X-Opensec-Request: validation') !== false
            && strpos($request, 'opensec-sensitive-body') !== false
                ? 'plaintext credentials and body'
                : 'unexpected plaintext request';
    }

    fclose($conn);
    fclose($server);
    file_put_contents("$base.result", $result);
    phpt_notify();
CODE;

$clientCode = <<<'CODE'
    $base = sys_get_temp_dir() . '/ghsa-f6gg-rchx-xw8j-http';
    phpt_wait();
    $addr = file_get_contents("$base.addr");
    $context = stream_context_create(array(
        'http' => array(
            'method' => 'POST',
            'content' => 'opensec-sensitive-body',
            'header' => "X-Opensec-Request: validation\r\n",
        ),
        'ssl' => array(
            'verify_peer' => false,
            'verify_peer_name' => false,
        ),
    ));
    var_dump(@file_get_contents("HTTPS://alice:secret@$addr/secret", false, $context));
    phpt_wait();
    echo file_get_contents("$base.result"), "\n";
CODE;

include sprintf('%s/../../../openssl/tests/ServerClientTestCase.inc', __DIR__);
ServerClientTestCase::getInstance()->run($clientCode, $serverCode);
?>
--EXPECT--
bool(false)
TLS
--CLEAN--
<?php
$base = sys_get_temp_dir() . '/ghsa-f6gg-rchx-xw8j-http';
@unlink("$base.addr");
@unlink("$base.result");
?>
