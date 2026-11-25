--TEST--
GHSA-vc5h-9ppw-p5f3 (circular symlinks in tar should not cause stack overflow)
--CREDITS--
Calvin Young - eWalker Consulting (HK) Limited
Enoch Chow - Isomorph Cyber
--SKIPIF--
<?php if (!extension_loaded("phar")) die("skip");?>
--FILE--
<?php
/* The archives are built here instead of shipped as files: patches for this
 * tree must be text only. Same bytes as upstream's tests/tar/files/*.tar. */
function vc5h_tar($path, array $links) {
    $tar = '';
    foreach ($links as $name => $target) {
        $h = pack('a100a8a8a8a12a12a8a1a100a6a2a32a32a8a8a155a12',
            $name, '0000644', '0000000', '0000000', '00000000000', '00000000000',
            '        ', '2', $target, 'ustar', '00', '', '', '', '', '', '');
        $sum = 0;
        for ($i = 0; $i < 512; $i++) {
            $sum += ord($h[$i]);
        }
        $tar .= substr_replace($h, sprintf("%06o\0 ", $sum), 148, 8);
    }
    $tar .= str_repeat("\0", 1024);
    $tar .= str_repeat("\0", (10240 - strlen($tar) % 10240) % 10240);
    file_put_contents($path, $tar);
}
$base = __DIR__ . '/ghsa-vc5h-9ppw-p5f3';

// simple 2-cycle
vc5h_tar("$base-2.tar", array('file_a' => 'file_b', 'file_b' => 'file_a'));
$phar = new PharData("$base-2.tar");
var_dump($phar['file_a']->getContent() === '');

// rho-shaped cycle (tail leading into a loop)
vc5h_tar("$base-rho.tar", array('file_a' => 'file_b', 'file_b' => 'file_c', 'file_c' => 'file_d', 'file_d' => 'file_b'));
$phar = new PharData("$base-rho.tar");
var_dump($phar['file_a']->getContent() === '');

// long cycle (400 entries)
$links = array();
for ($i = 0; $i < 400; $i++) {
    $links["link_$i"] = 'link_' . (($i + 1) % 400);
}
vc5h_tar("$base-long.tar", $links);
$phar = new PharData("$base-long.tar");
var_dump($phar['link_0']->getContent() === '');
?>
--CLEAN--
<?php
foreach (array('2', 'rho', 'long') as $n) {
    @unlink(__DIR__ . "/ghsa-vc5h-9ppw-p5f3-$n.tar");
}
?>
--EXPECT--
bool(true)
bool(true)
bool(true)
