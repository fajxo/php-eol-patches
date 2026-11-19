--TEST--
GHSA-f6gg-rchx-xw8j: mixed-case FTPS URLs must enable TLS
--EXTENSIONS--
openssl
--SKIPIF--
<?php
if (!function_exists('proc_open')) die('skip proc_open() not available');
?>
--FILE--
<?php
$resultFile = sys_get_temp_dir() . '/ghsa-f6gg-rchx-xw8j-ftp.result';
@unlink($resultFile);

$serverCode = <<<'CODE'
    $server = stream_socket_server('tcp://127.0.0.1:64332', $errno, $errstr);
    phpt_notify();

    $conn = stream_socket_accept($server);
    fwrite($conn, "220 Ready\r\n");
    $command = stream_get_line($conn, 8192, "\r\n");
    file_put_contents(sys_get_temp_dir() . '/ghsa-f6gg-rchx-xw8j-ftp.result', $command);

    fclose($conn);
    fclose($server);
    phpt_notify();
CODE;

$clientCode = <<<'CODE'
    phpt_wait();
    var_dump(@fopen('FTPS://127.0.0.1:64332/secret', 'r'));
    phpt_wait();
CODE;

include sprintf('%s/../../../openssl/tests/ServerClientTestCase.inc', __DIR__);
ServerClientTestCase::getInstance()->run($clientCode, $serverCode);
echo file_get_contents($resultFile), "\n";
?>
--EXPECT--
bool(false)
AUTH TLS
--CLEAN--
<?php
@unlink(sys_get_temp_dir() . '/ghsa-f6gg-rchx-xw8j-ftp.result');
?>
