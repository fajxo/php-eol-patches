# php-eol-patches

Security and build fixes for the end-of-life PHP releases **5.6.40**,
**7.2.34** and **7.4.33**.

PHP no longer ships fixes for these versions. We still run them for old sites,
so we backport the fixes we need. This repository publishes them so other
people running the same versions can use them too.

This is not an official PHP project. The patches come with no warranty. An EOL
PHP stays EOL: these fixes cover only the issues listed below, not every bug
fixed in newer PHP versions since these releases. Moving to a supported PHP is
still the real fix.

## What is fixed

| Fix | 5.6.40 | 7.2.34 | 7.4.33 |
| --- | :-: | :-: | :-: |
| CVE-2026-109618 + CVE-2026-109619 ("PHP2Shell") | ✔ | ✔ | ✔ |
| Build against OpenSSL 4.x (1.1 – 3.x still work) | ✔ | ✔ | ✔ |
| phar: use-after-free in `phar_call_openssl_signverify()` | ✔ | – ¹ | – ¹ |
| GHSA-qc49-hpvq-g2w6 (CVE-2026-129289): drain unread FastCGI request bodies | ✔ | ✔ | ✔ |
| GHSA-3mg3-cr28-46gc (CVE-2026-129292): XSS in the FPM full status page | ✔ | ✔ | ✔ |
| GHSA-gr4f-wgx5-jpfv (CVE-2026-129496): `DATEG(tzcache)` use-after-free | ✔ | ✔ | ✔ |
| GHSA-f6gg-rchx-xw8j (CVE-2026-128064): mixed-case `HTTPS://` / `FTPS://` fall back to plaintext | ✔ | ✔ | ✔ |
| GHSA-q23r-hr55-xmpg (CVE-2026-15353): out-of-bounds read on empty `SOAP-ENC:arraySize` | ✔ | ✔ | ✔ |
| GHSA-4646-p932-gw34 (CVE-2026-129506): SOAP DTD freed too early | ✔ | ✔ | ✔ |
| GHSA-fm2m-fjf5-v3cp (CVE-2026-126714): RCE via a corrupted session file | ✔ | ✔ | ✔ |
| GHSA-68fq-fh7h-f8gr (CVE-2026-128090): keyless phar signature accepted when a `.pubkey` exists | ✔ | ✔ | ✔ |
| GHSA-5j3f-64r7-f564 (CVE-2026-129538): `PATH_INFO` underflow in CGI | ✔ | ✔ | ✔ |
| GHSA-jm4m-qfqx-967m (CVE-2026-129291): FPM master keeps a freed worker's stdio event queued | ✔ | ✔ | ✔ |
| GHSA-658j-r8rw-q6mx (CVE-2026-115560): `session.upload_progress.enabled` off by default ³ | ✔ | ✔ | ✔ |
| CVE-2019-11043 (bug #78599): `env_path_info` underflow in FPM | ✔ | – ² | – ² |

¹ Only the 5.6 code has this bug.
² Already fixed in the 7.2.34 and 7.4.33 releases; 5.6 never got the fix.
³ The upload progress hook these trees could reach is already a no-op since
the PHP2Shell fix; this only changes the default to match.

The GHSA rows are the 2026-11-19 PHP security release. Advisories from that
release that are not in the table touch code these versions do not have.
GHSA-mmf9-xm3v-hg2x (CVE-2026-127960) is the exception: it is in the HTTP
header folding code that the 2025 fix for CVE-2025-1217 added. It is listed
with that fix under the 2026-11-25 fixes below.

### Older fixes (2026-11-24)

Fixes for remotely reachable input and for SOAP that the Debian EOL packages
by Ondřej Surý carry and these trees lacked. Each is a backport of the
upstream php-src fix; the commit messages name it and Surý's patch.

| Fix | 5.6.40 | 7.2.34 | 7.4.33 |
| --- | :-: | :-: | :-: |
| GHSA-3qrf-m4j2-pcrr (CVE-2023-3823): libxml2 global state / external entities | ✔ | ✔ | ✔ |
| CVE-2022-31629 (bug #81727): cookie names mangled into `__Host-`/`__Secure-` | ✔ | ✔ | – ¹ |
| GHSA-wpj3-hf5j-x4v4 (CVE-2024-2756): `__Host-`/`__Secure-` cookie bypass | ✔ | ✔ | ✔ |
| CVE-2020-7070 (bug #79699): cookie names URL-decoded | ✔ | – ¹ | – ¹ |
| GHSA-q6x7-frmf-grcw (CVE-2024-3096): `password_hash()` with a leading NUL byte | ✔ | ✔ | ✔ |
| CVE-2020-7071 (bug #77423): `FILTER_VALIDATE_URL` accepts invalid userinfo | ✔ | ✔ ⁴ | – ¹ |
| CVE-2021-21705 (bug #81122): SSRF bypass in `FILTER_VALIDATE_URL` | ✔ | ✔ | – ¹ |
| GHSA-w8qr-v226-r27w (CVE-2024-5458): `FILTER_VALIDATE_URL` accepts invalid IPv6 URLs | – ² | ✔ | ✔ |
| GHSA-9pqp-7h25-4f32 (CVE-2024-8925): multipart boundary longer than the read buffer | ✔ | ✔ | ✔ |
| CVE-2019-11048 (bugs #78875, #78876): multipart lengths in signed ints | ✔ | – ¹ | – ¹ |
| CVE-2019-11050, CVE-2019-11047 (bugs #78793, #78910): exif MakerNote over-reads | ✔ | – ¹ | – ¹ |
| CVE-2020-7064 (bug #79282): exif reads uninitialised memory | ✔ | – ¹ | – ¹ |
| CVE-2025-14177: `getimagesize()` returns uninitialised heap memory in `$imageinfo` | ✔ | – ¹ | – ¹ |
| CVE-2020-7059 (bug #79099): `strip_tags()` reads before the buffer | ✔ | – ¹ | – ¹ |
| CVE-2026-9672: bundled libgd GIF LZW decoder works on stale table data | ✔ | ✔ | ✔ |
| GHSA-ch8v-r6jh-4vvr: `FILTER_SANITIZE_ENCODED` leaves 0xFF to stack garbage | ✔ | ✔ | ✔ |
| GHSA-76gg-c692-v2mw (CVE-2023-3247): SoapClient HTTP Digest cnonce | ✔ ³ | ✔ | ✔ |
| CVE-2021-21702 (bug #80672): NULL dereference in SoapClient on a hostile WSDL | ✔ | ✔ | – ¹ |
| GHSA-453j-q27h-5p8x (CVE-2025-6491): SoapVar with a name over 2 GB | ✔ | ✔ | ✔ |
| GHSA-85c2-q967-79q5 (CVE-2026-6722): stale SOAP `ref_map` pointer with Apache Map | ✔ | ✔ | ✔ |
| GHSA-m33r-qmcv-p97q (CVE-2026-7261): SoapServer use-after-free with `SOAP_PERSISTENCE_SESSION` | ✔ | ✔ | ✔ |
| GHSA-hmxp-6pc4-f3vv (CVE-2026-7262): NULL dereference on an Apache Map without value | ✔ | ✔ | ✔ |
| GHSA-rgrp-mwpx-f6rm: unbounded recursion in SOAP XML parsing and decoding | ✔ | ✔ | ✔ |
| GHSA-cj93-vc83-wgqv (CVE-2025-14181): SoapClient chunked response length overflow | ✔ | ✔ | ✔ |
| SOAP: a DOCTYPE named like the root element was matched as an element (regression of the GHSA-4646 fix) | ✔ | ✔ | ✔ |
| Follow-ups to CVE-2021-21702 and CVE-2021-21705: `SAFE_STR` cast to `const char *` (php-src e5d767d27f, a build error on gcc 16) and parentheses in the `FILTER_VALIDATE_URL` userinfo check (190013787b) | ✔ ⁵ | ✔ | – ¹ |

¹ Already fixed in that release.
² The code is not in 5.6.
³ Only the minimal fix: 5.6 has no CSPRNG API, so the cnonce is less
predictable but not cryptographically random.
⁴ Only the `ext/filter` half: these trees keep Gentoo's `parse_url()` fix
for the same bug.
⁵ Folded into the commits of the fixes they follow.

### Outbound HTTP and TLS fixes (2026-11-25)

Fixes for the `http://` stream wrapper and for TLS peer verification, from the
same Surý packages, plus the real CVE-2023-0568 fix. They matter when a site
fetches URLs itself (`file_get_contents()`, `fopen()`, libxml, SOAP) or opens
TLS connections.

| Fix | 5.6.40 | 7.2.34 | 7.4.33 |
| --- | :-: | :-: | :-: |
| CVE-2023-0568: one-byte overrun when appending a slash to a path (`main/fopen_wrappers.c`) | ✔ | ✔ | ✔ |
| GHSA-c5f2-jwm7-mmq2 (CVE-2024-11234): CRLF injection through `request_fulluri` | ✔ | ✔ | ✔ |
| GHSA-v8xr-gpvj-cx9g (CVE-2025-1217): folded HTTP response headers parsed wrongly | ✔ ⁷ | ✔ | ✔ ¹ |
| GHSA-pcmh-g36c-qc44 (CVE-2025-1734): HTTP response header lines without a colon | ✔ ⁷ | ✔ | ✔ |
| GHSA-52jp-hrpf-2jff (CVE-2025-1861): redirect `Location` truncated at 1024 bytes | ✔ ⁷ | ✔ | ✔ |
| GHSA-hgf5-96fm-v528 (CVE-2025-1736): user header check accepts a name not at a line start | ✔ | ✔ | ✔ |
| GHSA-p3x9-6h7p-cgfc (CVE-2025-1219): libxml uses the charset of the first response after a redirect | – ⁴ | – ⁴ | ✔ |
| GHSA-3cr5-j632-f35r (CVE-2025-1220): NUL bytes in hostnames | ✔ | ✔ | ✔ |
| GHSA-vvx9-73fr-5jjx (CVE-2026-91769): TLS name check falls back to the CN after a SAN mismatch | ✔ ⁵ | ✔ ⁵ | ✔ |
| GHSA-xr7j-rvgx-xq5p (CVE-2026-91767): heap over-read in wildcard certificate name matching | ✔ | ✔ | ✔ |
| GHSA-fpwc-w8rq-cr92 (CVE-2026-91766): credentials sent on to another origin after a redirect | ✔ ² | ✔ ² | ✔ ² |
| GH-23467: out-of-bounds read on an empty `Location` header | ✔ ⁷ | ✔ | ✔ ³ |
| GHSA-mmf9-xm3v-hg2x (CVE-2026-127960): quadratic time on many folded header lines | ✔ ⁷ | ✔ | ✔ |
| Bug #78719: long response header lines silently dropped (needed by the fixes above) | ✔ ⁷ | ✔ | – ⁶ |
| Bug #79265: user `Host:` and other headers checked only at their first match, so a second Host was added | ✔ | ✔ | – ⁶ |

¹ The fix also changes the shared TLS test helper
(`ext/openssl/tests/ServerClientTestCase.inc`) to ephemeral ports. A separate
commit keeps the older tests that use fixed ports working with it.
² Adapted: these trees do not have GH-11274 (keep the method on 307/308), so
the redirect flag handling and the test expectation follow these releases'
behaviour, where a 307 turns a POST into a GET.
³ On 7.4 the tests include the TLS helper from `ext/openssl/tests/`, where
7.4 keeps it.
⁴ 7.2 and 5.6 do not take the charset from the response headers at all, so
the code this fixes is not there.
⁵ With the newer openssl test certificate generator (SAN support, SHA-256)
the tests need. 7.2 keeps its own TLS test helper for the older tests; the
new http tests use a copy of 7.4's in `ext/standard/tests/http/`. 5.6 had
no generator at all and gets the same one.
⁶ Already fixed in that release.
⁷ Our own port: Surý has no 5.6 patch for it. It follows his 7.2 patch in the
5.6 API. Because 5.6 still matched `Location: ` and the other response
headers literally, the GHSA-v8xr commit also brings the 7.x header matching
(bug #47021).

### Remaining Surý backports (2026-11-25b)

The rest of the fixes in Surý's EOL packages that these trees lacked, apart
from Windows-only ones (e.g. bug #78862) and Debian packaging changes. Most
are php-src fixes from later 7.x releases that never reached these versions.

| Fix | 5.6.40 | 7.2.34 | 7.4.33 |
| --- | :-: | :-: | :-: |
| GHSA-jqcx-ccgc-xwhv (CVE-2023-3824): buffer overflow in `phar_dir_read()` | ✔ | ✔ | ✔ |
| GHSA-j3wh-g957-2m85 (CVE-2026-6103): phar tar entry injection | ✔ | ✔ | ✔ |
| GHSA-vc5h-9ppw-p5f3 (CVE-2026-7260): crash on a circular phar symlink | ✔ | ✔ | ✔ |
| Bug #81211: symlinks followed when creating a phar | ✔ | ✔ | – ¹ |
| Bug #81726 (CVE-2022-31628): phar wrapper DoS with a quine gzip file | ✔ | ✔ | – ¹ |
| Bug #79797 (CVE-2020-7068): phar zip parser uses a freed hash key | ✔ | – ¹ | – ¹ |
| Bug #79082 (CVE-2020-7063): `Phar::buildFromIterator()` adds tar entries as 0777 | ✔ | – ¹ | – ¹ |
| GHSA-h35g-vwh6-m678 (CVE-2024-8929): mysqlnd heap over-reads from a hostile server | ✔ | ✔ | ✔ |
| GHSA-r6x9-5r99-36j7 (CVE-2025-1218): mysqlnd packet over-reads | ✔ ³ | ✔ ⁴ | ✔ ⁴ |
| mysqlnd: OK packet message length past the packet body | ✔ | ✔ | ✔ |
| Bug #81719 (CVE-2022-31626): mysqlnd/pdo password buffer overflow | ✔ | ✔ | – ¹ |
| Bug #81740 (CVE-2022-31631): `PDO::quote()` may return an unquoted string (sqlite) | ✔ | ✔ | – ⁵ |
| CVE-2023-0567: bcrypt hashes with a `$` in the salt | ✔ | ✔ | – ⁵ |
| GHSA-www2-q4fc-65wf: NUL bytes in `dns_get_record()`, `ip2long()` and other network functions | ✔ ⁶ | ✔ ⁶ | ✔ |
| Bug #79971 (CVE-2021-21707): NUL bytes in URIs given to libxml functions | ✔ | ✔ | – ¹ |
| Bug #78863 (CVE-2019-11045): `DirectoryIterator` cuts the path at a NUL byte | ✔ | – ¹ | – ¹ |
| Bug #79329 (CVE-2020-7066): `get_headers()` cuts the URL at a NUL byte | ✔ | – ¹ | – ¹ |
| Bug #79330: `shell_exec()` and friends cut the command at a NUL byte | ✔ | – ¹ | – ¹ |
| Bug #79877: `getimagesize()` cuts the path at a NUL byte | ✔ | – ¹ | – ¹ |
| GHSA-r977-prxv-hc43 (CVE-2024-11233): over-read in `convert.quoted-printable-decode` | ✔ | ✔ | ✔ |
| GHSA-88hq-2827-7pg6 (CVE-2026-92842): convert filter `line-break-chars` with a NUL byte | ✔ | ✔ | ✔ |
| GHSA-4w77-75f9-2c8w: use after free in the CLI server POST reader | ✔ | ✔ | ✔ |
| GHSA-62xp-839h-2637 (CVE-2026-91768): FastCGI `allowed_clients` with IPv6 and IPv4-mapped addresses | ✔ | ✔ | ✔ |
| GHSA-865w-9rf3-2wh5 (CVE-2024-9026): FPM worker output log forging | – ² | – ² | ✔ |
| GH-22187: `openssl_encrypt()` heap overflow with AES-WRAP-PAD | ✔ | ✔ | ✔ |
| GHSA-wg4p-4hqh-c3g9: out-of-bounds read with `XML_OPTION_SKIP_TAGSTART` | – ⁷ | ✔ | ✔ |
| GHSA-h96m-rvf9-jgm2 (CVE-2025-14178): `array_merge()` size overflow | test ⁷ | ✔ | ✔ |
| GHSA-96wq-48vp-hh57 (CVE-2026-7568): `metaphone()` offset overflow | – ⁸ | ✔ | ✔ |
| Bug #81738 (CVE-2022-37454): SHA-3 overflow on inputs of 4 GB or more | – ² | ✔ | – ¹ |
| Bug #78878 (CVE-2019-11046): bcmath buffer underflow | ✔ | – ¹ | – ¹ |
| Bug #79037 (CVE-2020-7060): mbstring big5 over-read | ✔ | – ¹ | – ¹ |
| Bug #79221 (CVE-2020-7062): NULL dereference in session upload progress | ✔ | – ¹ | – ¹ |
| Bug #78804: `Locale::filterMatches()` crash | ✔ | – ¹ | – ¹ |
| Bug #81739 (CVE-2022-31630): `imageloadfont()` out-of-bounds read | test ⁷ | test ⁷ | – ¹ |
| SOAP: a `SoapFault` from `__call()` cannot be caught when built with GCC 14 or later | ✔ | ✔ | – ¹ |
| Bug #79589: TLS streams fail with "unexpected eof" on OpenSSL 3 (not a security fix) | ✔ | ✔ | ✔ |
| http wrapper: two request-scoped leaks (dechunk filter, repeated `Location`) | – ⁹ | ✔ | ✔ |

¹ Already fixed in that release.
² The code is not in that version.
³ Only the part Surý carries for 5.6: the binary row decoders for DATE,
TIME and DATETIME.
⁴ Upstream calls its fix partial; the tests expect warnings, not exceptions.
⁵ In the Gentoo base patches the 7.4 branch starts from (see below), not in
the plain php.net tarball.
⁶ The wrong-type warnings of these functions now say "expects parameter N to
be a valid path" instead of "to be string", as upstream does since the fix.
⁷ Not affected: the commit adds the upstream test so that stays proven.
⁸ 5.6 string lengths are an `int`, so no string over 2 GB can exist.
⁹ Our own fix (upstream has the same code). Our 5.6 port of the 2026-11-25
fixes already frees both.

Each commit message explains the fix, how it differs from the upstream one, and
how it was tested.

## Repository layout

| Branch | Contents |
| --- | --- |
| `main` | this README, `patches/`, `tools/` |
| `php-5.6` | PHP 5.6.40 source tree with the fixes as commits |
| `php-7.2` | PHP 7.2.34 source tree with the fixes as commits |
| `php-7.4` | PHP 7.4.33 source tree with the fixes as commits |

In `patches/` on `main`:

| File | What it is |
| --- | --- |
| `php-X.Y.Z.patch` | all fixes for that version in one file, in order |
| `php-X.Y.Z/NNNN-*.patch` | the same fixes, one file per commit (`git format-patch`) |
| `base/php-X.Y.Z-gentoo-base.patch` | the Gentoo patches the branches start from (see below) |

`tools/export-patches.sh` rebuilds `patches/php-X.Y.Z*` from the branches.

## Based on the Gentoo patch set

The branches do **not** start from the plain php.net tarball. Each one starts
from the tarball with the patches that Gentoo's `dev-lang/php` ebuild applies
already in place (OpenSSL 3, ICU, libxml2 and newer-compiler build fixes, plus
some earlier security backports):

- **5.6.40:** Gentoo's `php-patches-7.1.31bp` backport set (OpenSSL 1.1 support
  and other fixes backported from PHP 7.1.31) and the ebuild patches
  `php-5.6-htmltidy-changed-headerfile.patch`, `php-5.6-bug81026.patch`,
  `php-openssl30.patch`, `php-icu70.patch`, `php-5.6.40-libxml2.patch`.
- **7.2.34:** `php-7.2.13-intl-use-icu-namespace.patch`,
  `php-7.2.34-use-after-free-bug76047.patch`, `php-7.2.34-77423.patch`,
  `php-7.2-bug81026.patch`, `php-icu70.patch`, `php-7.4-openssl3.patch`,
  `php-7.2.34-libxml2.patch`.
- **7.4.33:** `php-iodbc-header-location.patch`, `bug81656-gcc-11.patch`,
  `php-7.4.33-CVE-2022-31631.patch`, `php-7.4.33-CVE-2023-0567.patch`,
  `php-7.4.33-CVE-2023-0568.patch`, `php-7.4.33-CVE-2023-0662.patch`,
  `php-7.4-openssl3.patch`, `php-7.4.33-libxml2.patch`.

  Gentoo's `php-7.4.33-CVE-2023-0568.patch` is misnamed: it holds the
  `php_crypt()` short-salt over-read fix, which is part of CVE-2023-0567.
  The real CVE-2023-0568 fix (1-byte overrun in the open_basedir check,
  `main/fopen_wrappers.c`) is not in the base of any version; it is one of
  the 2026-11-25 fixes.

`patches/base/php-X.Y.Z-gentoo-base.patch` is that whole difference as one
patch (php.net tarball → first commit of the branch). It is here because the
5.6 backport set is no longer on the Gentoo mirrors. The 5.6 base adds nine
binary test files, so apply the base patches with `git apply`, which also
works outside a git repository. Plain `patch` cannot apply binary diffs.

## How to use the patches

### On Gentoo

The ebuild already applies the Gentoo base, so only the fixes are needed. Put
the single-file patch in your user patches directory, e.g.

```sh
mkdir -p /etc/portage/patches/dev-lang/php-7.4.33
cp patches/php-7.4.33.patch /etc/portage/patches/dev-lang/php-7.4.33/
emerge --oneshot =dev-lang/php-7.4.33*
```

For 5.6 the ebuild has to call `eapply_user` **after** it applies the
`php-patches-7.1.31bp` set, otherwise one OpenSSL hunk does not apply.

### On another distribution, or from the php.net tarball

| Version | Fixes on the plain tarball | Fixes on tarball + Gentoo base |
| --- | --- | --- |
| 7.4.33 | applies, no fuzz | applies, no fuzz |
| 7.2.34 | applies, no fuzz, except one test-file rename ¹ | applies, no fuzz |
| 5.6.40 | **3 OpenSSL hunks fail** (they need the backport set) | applies, no fuzz |

¹ `ext/standard/tests/url/bug77423.phpt` comes from Gentoo's
`php-7.2.34-77423.patch`, so the tarball does not have it to move. Only that
test is missing; the code applies.

Checked with GNU patch 2.8.1 (`patch -p1 -F0`). BSD/macOS `patch` does not
apply the renames and deletions in these patches.

So for 7.2 and 7.4 the fix patch can be used directly on the tarball, or on
top of your distribution's own patches if they do not touch the same lines.
On 7.4 the tarball then lacks the two fixes Gentoo's base adds (footnote ⁵
of the 2026-11-25b table). For 5.6 apply the base first:

```sh
tar xf php-5.6.40.tar.xz && cd php-5.6.40
git apply ../php-eol-patches/patches/base/php-5.6.40-gentoo-base.patch
patch -p1 < ../php-eol-patches/patches/php-5.6.40.patch
./configure ... && make
```

Applying the base and then the fixes to the php.net tarball gives the same
files as the branch head. This was checked file by file for all three
versions. The only differences are the `$Id: <hash> $` keyword lines of a
few files the fixes change (e.g. `ext/phar/phar.c`), which git's `ident`
attribute rewrites on checkout.

### Building straight from a branch

You can also clone one branch (`git clone --single-branch -b php-7.4 ...`).
The branches leave out the files PHP's own `.gitignore` excludes, such as
`configure` and the generated parser and scanner sources, so building from
the branch needs `./buildconf` plus `bison` and `re2c`, like building from
php-src. The tarball route above does not need them.

## Checking that a build has the fixes

These strings exist only in patched binaries:

```sh
# PHP2Shell fix (5.6, 7.2, 7.4)
strings /usr/lib64/php7.4/bin/php-fpm | grep -c 'Fatal error: %s in %s on line %d'   # 1 = patched
# GHSA-68fq (phar) fix
strings /usr/lib64/php7.4/bin/php-fpm | grep -c 'but the signature does not use openssl'   # 1 = patched
# 2026-11-24 fixes (GHSA-9pqp multipart boundary)
strings /usr/lib64/php7.4/bin/php-fpm | grep -c 'Boundary too large'   # 1 = patched
# 2026-11-25 fixes (GHSA-52jp Location limit; 5.6, 7.2, 7.4)
strings /usr/lib64/php7.4/bin/php-fpm | grep -c 'HTTP Location header size is over the limit'   # 1 = patched
# 2026-11-25b fixes (GHSA-j3wh phar tar; 5.6, 7.2, 7.4; needs phar built in)
strings /usr/lib64/php7.4/bin/php-fpm | grep -c 'unsupported GNU long link entry'   # 1 = patched
```

Adjust the path for your system.

## Testing

Each version was built with gcc 13, once without and once with the patches,
and the PHP test suite (`.phpt`) was run on both builds:

- The tests added for the 2026-11-19 advisories go from FAIL to PASS: 12 of 12
  on 5.6, 11 of 11 on 7.2, 10 of 11 on 7.4. The one left on 7.4 (the
  `tzcache` use-after-free) passes on both builds, because the bug only shows
  under valgrind.
- The GHSA-jm4m test (7.2, 7.4) passes on both builds: the freed event only
  shows under valgrind or ASAN. 5.6 has no FPM test harness, so it has no test.
- The tests added for the 2026-11-24 fixes go from FAIL to PASS: 10 on 7.4,
  13 on 7.2, 22 on 5.6. The others pass on both builds or skip: the
  GHSA-3qrf tests need the `zend_test` extension, the GHSA-cj93 test is
  marked resource-heavy, and on 5.6 the GHSA-453j test cannot build a string
  over 2 GB.
- The tests added or changed by the 2026-11-25 fixes (7.4): 30, all PASS on
  the patched build. 26 FAIL on the unpatched build; the other four
  (`san_absent_cn_fallback`, `ghsa-hgf5-96fm-v528-002/-003`,
  `http_single_char_location_redirect`) pass on both, as they guard cases
  that were already handled. This run was the full test suite, built with
  gcc 16.
- On 7.2 (full suite, gcc 16): 32 added or changed tests, all PASS on the
  patched build, 26 FAIL on the unpatched one. 14 older openssl tests pass
  now as well: the test certificate generator signs with SHA-256, which
  current OpenSSL accepts. FAIL 125 -> 111.
- On 5.6 (full suite, gcc 16): 32 added or changed tests, all PASS on the
  patched build, 26 FAIL on the unpatched one. FAIL 156 -> 153.
- The 2026-11-25b fixes (gcc 16): a full-suite run of each version, then,
  after test-only adjustments (the 5.6 and 7.2 warning and exception formats,
  the FPM test harness of 7.x), a rerun of every suite whose tests changed
  together with all added tests. Added or changed tests that FAIL on the
  unpatched build and PASS on the patched one: 59 on 7.4, 41 on 7.2, 44 on
  5.6. Another 12, 7 and 10 pass on both builds, as they guard cases that
  were already handled (or, marked "test" above, a version that is not
  affected). The rest skip: the mysqli tests that need a MySQL server, the
  `bug79589` test that needs the internet, and the resource-heavy GHSA-96wq
  test.
- No test that passed before fails after the patches. The tests that still
  fail are the same ones that fail on the unpatched build.
- The OpenSSL 4 change was built and tested against OpenSSL 4.1.0 and 3.6.5,
  with identical TLS behaviour on both.
- With all patches applied, each version was also built with gcc 16 using
  `CFLAGS="... -std=gnu17 -fpermissive"`. Plain gcc 16 fails to build these
  trees, and configure silently changes some results, because its test
  programs are old C. With these two flags, the gcc 16 build has the same
  `php_config.h` and the same extensions (`php -m`) as the gcc 13 build, and
  the full test suite gives the same result on both. The one exception is on
  5.6: two curl SSL-locking defines differ, which matters only for a
  thread-safe (ZTS) build.

Details are in each commit message.

## License

The patches change PHP source code and are under the same licence as the
code they change: the PHP License 3.01 (Zend Engine License 2.00 for `Zend/`).
See `LICENSE` in each branch.

## Contributing

Issues and pull requests are welcome, especially reports that a patch does
or does not apply on another distribution's PHP build. Fixes for a new
upstream security release are added to all three branches together, and
`patches/` is regenerated with `tools/export-patches.sh`.
