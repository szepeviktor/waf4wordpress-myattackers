<?php

/**
 * BlockKnownHostileNetworks.php
 *
 * @author Viktor Szépe <viktor@szepe.net>
 * @license GPL-2.0-or-later http://www.gnu.org/licenses/gpl-2.0.txt
 * @link https://github.com/szepeviktor/waf4wordpress-myattackers
 */

declare(strict_types=1);

namespace SzepeViktor\WordPress\MyAttackers;

/**
 * Block known hostile networks
 */
final class BlockKnownHostileNetworks
{
    public function do(): void
    {
        if (! $this->isBlocked($this->getClientIp())) {
            return;
        }

        wp_die('Forbidden by IP policy', 'Forbidden', ['response' => 403]);
    }

    protected function isBlocked(string $ip): bool
    {
        /** @var \wpdb $wpdb */
        global $wpdb;

        if (filter_var($ip, FILTER_VALIDATE_IP) === false) {
            return false;
        }

        $table = sprintf('%s%s', $wpdb->prefix, Config::get('tableName'));

        return $wpdb->get_var(
            $wpdb->prepare(
                'SELECT 1 FROM %i WHERE INET6_ATON(%s) BETWEEN ip_start AND ip_end LIMIT 1',
                $table,
                $this->normalizeIpToV6($ip)
            )
        ) !== null;
    }

    protected function getClientIp(): string
    {
        $remoteAddress = $_SERVER['REMOTE_ADDR'] ?? '';
        assert(is_string($remoteAddress));

        return $remoteAddress;
    }

    protected function normalizeIpToV6(string $ip): string
    {
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) !== false) {
            return sprintf('::ffff:%s', $ip);
        }

        return $ip;
    }
}
