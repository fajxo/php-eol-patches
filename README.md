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
release that are not in the table touch code these versions do not have. For
example, GHSA-mmf9-xm3v-hg2x (CVE-2026-127960) is in the HTTP header folding
code that the 2025 fix for CVE-2025-1217 added, and these trees do not have
that code.

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
  `main/fopen_wrappers.c`) is not in the base of any version.

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
| 7.2.34 | applies, no fuzz | applies, no fuzz |
| 5.6.40 | **1 OpenSSL hunk fails** (it needs the backport set) | applies, no fuzz |

So for 7.2 and 7.4 the fix patch can be used directly on the tarball, or on
top of your distribution's own patches if they do not touch the same lines.
For 5.6 apply the base first:

```sh
tar xf php-5.6.40.tar.xz && cd php-5.6.40
git apply ../php-eol-patches/patches/base/php-5.6.40-gentoo-base.patch
patch -p1 < ../php-eol-patches/patches/php-5.6.40.patch
./configure ... && make
```

Applying the base and then the fixes to the php.net tarball gives the same
files as the branch head. This was checked file by file for all three
versions. The only difference is in four 5.6 files: their `$Id: <hash> $`
keyword lines, which git's `ident` attribute rewrites on checkout.

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
- No test that passed before fails after the patches. The tests that still
  fail are the same ones that fail on the unpatched build.
- The OpenSSL 4 change was built and tested against OpenSSL 4.1.0 and 3.6.5,
  with identical TLS behaviour on both.

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
