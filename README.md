# Block known hostile networks

Blocking known hostile networks is ideally done in the Linux firewall.
https://github.com/szepeviktor/debian-server-tools/tree/master/security/myattackers-ipsets/ipset

You need this plugin if you don't have access to the Linux firewall.

## Requirements

- PHP 8.1+
- WordPress 6.3+

## Installation

Download the plugin ZIP from [releases](https://github.com/szepeviktor/waf4wordpress-myattackers/releases),
or use Composer: `composer require szepeviktor/waf4wordpress-myattackers`

## Configuration

There is no configuration at all!

## Usage

On plugin activation IP ranges are imported from the `data/` directory to a database table.

On every WordPress run client IP is being checked against the database.
See `BlockKnownHostileNetworks::isBlocked`!

## Conversion shell script

Convert ipsets to PHP arrays.

```shell
#!/usr/bin/env bash
echo "<?php"
echo "return ["
git ls-files "*.ipset" | xargs -- grep -h '^add' | while read -r _ network range; do
  read -r start end < <(iprange -j - <<<"$range" | awk -F- '{e=$1; if(NF==2) e=$2; print $1, e}')
  printf "    ['%s','%s','%s'],\n" "$start" "$end" "$network"
done
echo "];"
```
