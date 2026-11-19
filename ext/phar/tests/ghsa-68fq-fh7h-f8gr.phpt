--TEST--
GHSA-68fq-fh7h-f8gr (OpenSSL signature bypass by downgrading the algorithm to a keyless one)
--SKIPIF--
<?php
if (!extension_loaded("phar")) die("skip phar extension not available");
if (!extension_loaded("openssl")) die("skip openssl extension not available");
if (!in_array("OpenSSL", Phar::getSupportedSignatures())) die("skip openssl support required");
if (!in_array("SHA-256", Phar::getSupportedSignatures())) die("skip SHA-256 support required");
?>
--INI--
phar.readonly=0
phar.require_hash=1
--FILE--
<?php
$base = __DIR__ . '/' . basename(__FILE__, '.php');
$private = openssl_pkey_get_private(file_get_contents(__DIR__ . '/files/private.pem'));
$pkey = '';
openssl_pkey_export($private, $pkey, null, array('config' => __DIR__ . '/files/openssl.cnf'));
$details = openssl_pkey_get_details($private);
$pubkey = $details['key'];

function build($file, $pkey)
{
    $phar = new Phar($file);
    $phar['main.php'] = '<?php echo "GENUINE\n";';
    $phar->setStub('<?php __HALT_COMPILER();');
    $phar->setSignatureAlgorithm(Phar::OPENSSL, $pkey);

    return file_get_contents($file);
}

// a phar built in this process comes back from the cache, so each case is read under its own name
function open($file, $archive, $pubkey)
{
    file_put_contents($file, $archive);
    if ($pubkey !== null) {
        file_put_contents($file . '.pubkey', $pubkey);
    }

    try {
        $phar = new Phar($file);
        $signature = $phar->getSignature();
        echo $signature['hash_type'], "\n";
    } catch (UnexpectedValueException $e) {
        echo $e->getMessage(), "\n";
    }
}

function downgrade_phar($archive)
{
    // the last 12 bytes are [length][algorithm]["GBMB"], the signature sits just before them
    $siglen = unpack('V', substr($archive, -12, 4));
    $siglen = $siglen[1];
    $message = substr($archive, 0, strlen($archive) - 12 - $siglen);

    return $message . hash('sha256', $message, true) . pack('V', 0x0003) . 'GBMB'; // 0x0003 is PHAR_SIG_SHA256
}

function downgrade_tar($archive)
{
    for ($offset = 0; $offset + 512 <= strlen($archive); ) {
        $size = (int) octdec(trim(substr($archive, $offset + 124, 12), " \0")); // octal size field of a 512 byte header

        if (rtrim(substr($archive, $offset, 100), "\0") === '.phar/signature.bin') {
            // the signed message stops at this header, so rewriting the entry content leaves it alone
            $blob = pack('VV', 0x0003, 32) . hash('sha256', substr($archive, 0, $offset), true); // [algorithm][digest length][digest]

            return substr_replace($archive, str_pad($blob, $size, "\0"), $offset + 512, $size);
        }

        $offset += 512 + (($size + 511) & ~511);
    }

    throw new RuntimeException('no signature entry');
}

function u16($data, $offset)
{
    $v = unpack('v', substr($data, $offset, 2));
    return $v[1];
}

function u32($data, $offset)
{
    $v = unpack('V', substr($data, $offset, 4));
    return $v[1];
}

function downgrade_zip($archive)
{
    $eocd = strrpos($archive, "PK\x05\x06");
    $cdir = u32($archive, $eocd + 16); // the eocd holds the directory offset at +16 and the archive comment length at +20
    $comment = substr($archive, $eocd + 22, u16($archive, $eocd + 20));

    for ($record = $cdir; substr($archive, $record, 4) === "PK\x01\x02"; ) {
        $namelen = u16($archive, $record + 28);

        if (substr($archive, $record + 46, $namelen) === '.phar/signature.bin') {
            $size = u32($archive, $record + 24);
            $local = u32($archive, $record + 42);
            $content = $local + 30 + u16($archive, $local + 26) + u16($archive, $local + 28);
            // the signed message is everything before the local entry plus the directory up to this record
            $message = substr($archive, 0, $local) . substr($archive, $cdir, $record - $cdir) . $comment;
            $blob = pack('VV', 0x0003, 32) . hash('sha256', $message, true);

            return substr_replace($archive, str_pad($blob, $size, "\0"), $content, $size);
        }

        $record += 46 + $namelen + u16($archive, $record + 30) + u16($archive, $record + 32);
    }

    throw new RuntimeException('no signature entry');
}

foreach (array('phar' => 'downgrade_phar', 'phar.tar' => 'downgrade_tar', 'phar.zip' => 'downgrade_zip') as $suffix => $downgrade) {
    $archive = build($base . '.source.' . $suffix, $pkey);
    open($base . '.signed.' . $suffix, $archive, $pubkey);
    open($base . '.tampered.' . $suffix, $downgrade($archive), $pubkey);
    open($base . '.nokey.' . $suffix, $downgrade($archive), null);
}
?>
--CLEAN--
<?php
$base = __DIR__ . '/' . basename(__FILE__, '.clean.php');
foreach (glob($base . '.*.phar*') as $file) {
    unlink($file);
}
?>
--EXPECTF--
OpenSSL
phar "%stampered.phar" SHA256 signature could not be verified: openssl public key found, but the signature does not use openssl
SHA-256
OpenSSL
phar error: tar-based phar "%stampered.phar.tar" signature cannot be verified: openssl public key found, but the signature does not use openssl
SHA-256
OpenSSL
phar error: signature cannot be verified: openssl public key found, but the signature does not use openssl in zip-based phar "%stampered.phar.zip"
SHA-256
