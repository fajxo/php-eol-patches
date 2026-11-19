--TEST--
GHSA-f6gg-rchx-xw8j: mixed-case FTPS URLs must enable TLS
--SKIPIF--
<?php
if (!extension_loaded('openssl')) die('skip openssl extension not available');
if (!function_exists('proc_open')) die('skip proc_open() not available');
?>
--INI--
default_socket_timeout=5
--FILE--
<?php
$serverCode = <<<'CODE'
$server = stream_socket_server('tcp://127.0.0.1:0', $errno, $errstr);
if (!$server) {
    echo "server error: $errstr\n";
    exit(1);
}
echo stream_socket_get_name($server, false), "\n";
$conn = stream_socket_accept($server, 10);
if (!$conn) {
    echo "no connection\n";
    exit(1);
}
fwrite($conn, "220 Ready\r\n");
echo stream_get_line($conn, 8192, "\r\n"), "\n";
fclose($conn);
fclose($server);
CODE;

$proc = proc_open(escapeshellarg(PHP_BINARY) . ' -n -r ' . escapeshellarg($serverCode), [1 => ['pipe', 'w']], $pipes);
$addr = trim(fgets($pipes[1]));

var_dump(@fopen("FTPS://$addr/secret", 'r'));
echo stream_get_contents($pipes[1]);
fclose($pipes[1]);
proc_close($proc);
?>
--EXPECT--
bool(false)
AUTH TLS
