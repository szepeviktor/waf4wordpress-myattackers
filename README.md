# Block known hostile networks

Blocking known hostile networks is ideally done in the Linux firewall.
https://github.com/szepeviktor/debian-server-tools/tree/master/security/myattackers-ipsets/ipset

You need this plugin if you don't have access to the Linux firewall.

## Conversion shell script

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
