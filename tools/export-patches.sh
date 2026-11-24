#!/bin/sh
# Regenerate patches/ from the version branches.
#
#   tools/export-patches.sh            # all versions
#   tools/export-patches.sh php-7.4    # one branch
#
# For each branch php-X.Y it writes
#   patches/php-X.Y.Z/NNNN-*.patch   one file per fix commit (git format-patch)
#   patches/php-X.Y.Z.patch          all of them in one file, in order
# The base is the branch's root commit (the release tarball tree, see README).
# Run it from the top of the repository on the main branch, then commit.
set -eu

cd "$(git rev-parse --show-toplevel)"
[ "$(git symbolic-ref --short HEAD)" = main ] || { echo "run this on main" >&2; exit 1; }

branches=${*:-"php-5.6 php-7.2 php-7.4"}

for b in $branches; do
	git rev-parse --verify -q "refs/heads/$b" >/dev/null ||
		git rev-parse --verify -q "refs/remotes/origin/$b" >/dev/null ||
		{ echo "no branch $b" >&2; exit 1; }
	ref=$b
	git rev-parse --verify -q "refs/heads/$b" >/dev/null || ref=origin/$b

	root=$(git rev-list --max-parents=0 "$ref")
	# "initial php-7.4.33 commit" -> 7.4.33
	ver=$(git log -1 --format=%s "$root" | sed -n 's/^initial php-\([0-9.]*\) commit$/\1/p')
	[ -n "$ver" ] || { echo "$b: cannot read the version from the root commit" >&2; exit 1; }

	dir=patches/php-$ver
	rm -rf "$dir" "$dir.patch"
	mkdir -p "$dir"
	git format-patch -q --no-signature --no-numbered -o "$dir" "$root..$ref"
	cat "$dir"/*.patch > "$dir.patch"
	echo "$b -> $dir ($(ls "$dir" | wc -l | tr -d ' ') patches)"
done
