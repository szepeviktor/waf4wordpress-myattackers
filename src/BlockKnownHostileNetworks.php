<?php

declare(strict_types=1);

namespace SzepeViktor\WordPress\MyAttackers;

/**
 * Block known hostile networks
 */
final class BlockKnownHostileNetworks
{
    public function do(): void
    {
        if (!$this->isBlocked($this->getClientIp())) {
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

        $table = $wpdb->prefix . Config::get('tableName');

        return $wpdb->get_var($wpdb->prepare(
            'SELECT 1 FROM %i WHERE INET6_ATON(%s) BETWEEN ip_start AND ip_end LIMIT 1',
            $table,
            $this->normalizeIpToV6($ip)
        )) !== null;
    }

    protected function getClientIp(): string
    {
        return $_SERVER['REMOTE_ADDR'] ?? '';
    }

    protected function normalizeIpToV6(string $ip): string
    {
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) !== false) {
            return '::ffff:' . $ip;
        }

        return $ip;
    }
}
