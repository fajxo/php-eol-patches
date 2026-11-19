--TEST--
GHSA-f6gg-rchx-xw8j: mixed-case FTPS URLs must enable TLS
--SKIPIF--
<?php
if (!extension_loaded('openssl')) die('skip openssl extension not available');
if (!function_exists('proc_open')) die('skip proc_open() not available');
?>
--FILE--
<?php
$serverCode = <<<'CODE'
    $base = sys_get_temp_dir() . '/ghsa-f6gg-rchx-xw8j-ftp';
    $server = stream_socket_server('tcp://127.0.0.1:0', $errno, $errstr);
    file_put_contents("$base.addr", stream_socket_get_name($server, false));
    phpt_notify();

    $conn = stream_socket_accept($server);
    fwrite($conn, "220 Ready\r\n");
    $command = stream_get_line($conn, 8192, "\r\n");

    fclose($conn);
    fclose($server);
    file_put_contents("$base.result", $command);
    phpt_notify();
CODE;

$clientCode = <<<'CODE'
    $base = sys_get_temp_dir() . '/ghsa-f6gg-rchx-xw8j-ftp';
    phpt_wait();
    $addr = file_get_contents("$base.addr");
    var_dump(@fopen("FTPS://$addr/secret", 'r'));
    phpt_wait();
    echo file_get_contents("$base.result"), "\n";
CODE;

include sprintf('%s/../../../openssl/tests/ServerClientTestCase.inc', __DIR__);
ServerClientTestCase::getInstance()->run($clientCode, $serverCode);
?>
--EXPECT--
bool(false)
AUTH TLS
--CLEAN--
<?php
$base = sys_get_temp_dir() . '/ghsa-f6gg-rchx-xw8j-ftp';
@unlink("$base.addr");
@unlink("$base.result");
?>
