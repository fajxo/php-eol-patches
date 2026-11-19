--TEST--
GHSA-5j3f-64r7-f564 (PATH_INFO underflow when the stripped suffix is longer than PATH_INFO)
--SKIPIF--
<?php include "skipif.inc"; ?>
--FILE--
<?php
include "include.inc";

$php = get_cgi_path();
$script = __DIR__ . "/ghsa-5j3f-64r7-f564.target.php";
file_put_contents($script, '<?php var_dump(isset($_SERVER["PATH_INFO"]) ? $_SERVER["PATH_INFO"] : null, isset($_SERVER["ORIG_PATH_INFO"]) ? $_SERVER["ORIG_PATH_INFO"] : null);');

function run_cgi($php, $env) {
    $base_env = array();
    if (is_string(getenv("PATH"))) {
        $base_env["PATH"] = getenv("PATH");
    }
    $p = proc_open(
        escapeshellarg($php) . " -n -d cgi.fix_pathinfo=1",
        array(0 => array("pipe", "r"), 1 => array("pipe", "w")),
        $pipes,
        null,
        $env + array("REDIRECT_STATUS" => "1", "REQUEST_METHOD" => "GET") + $base_env
    );
    fclose($pipes[0]);
    $out = stream_get_contents($pipes[1]);
    fclose($pipes[1]);
    proc_close($p);
    echo substr($out, strpos($out, "\r\n\r\n") + 4);
}

echo "Suffix shorter than PATH_INFO\n";
run_cgi($php, array("SCRIPT_FILENAME" => "$script/b", "PATH_INFO" => "/a/b"));

echo "Suffix longer than PATH_INFO\n";
run_cgi($php, array("SCRIPT_FILENAME" => "$script/xxxxxxxxxxxxxxxx", "PATH_INFO" => "/x"));

echo "Suffix taken from PATH_TRANSLATED\n";
run_cgi($php, array(
    "SCRIPT_FILENAME" => $php,
    "PATH_TRANSLATED" => "$script/xxxxxxxxxxxxxxxx",
    "REDIRECT_URL" => "/redirected",
    "PATH_INFO" => "/x",
));
?>
--CLEAN--
<?php
@unlink(__DIR__ . "/ghsa-5j3f-64r7-f564.target.php");
?>
--EXPECT--
Suffix shorter than PATH_INFO
string(2) "/b"
string(4) "/a/b"
Suffix longer than PATH_INFO
string(2) "/x"
NULL
Suffix taken from PATH_TRANSLATED
string(2) "/x"
NULL
