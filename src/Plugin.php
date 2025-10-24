<?php

/**
 * Plugin.php - Procedural part of Plugin Name.
 *
 * @author Your Name <username@example.com>
 * @license GPL-2.0-or-later http://www.gnu.org/licenses/gpl-2.0.txt
 * @link https://example.com/plugin-name
 */

declare(strict_types=1);

namespace SzepeViktor\WordPress\MyAttackers;

use function current_user_can;
use function dbDelta;
use function esc_html__;
use function esc_url;
use function load_plugin_textdomain;

/**
 * Plugin functions.
 */
class Plugin
{
    private function __construct()
    {
    }

    public static function activate(): void
    {
        /** @var \wpdb $wpdb */
        global $wpdb;
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        self::uninstall();

        $table = $wpdb->prefix . Config::get('tableName');
        $cc = $wpdb->get_charset_collate();
        $sql = "
CREATE TABLE `{$table}` (
  ip_start VARBINARY(16) NOT NULL,
  ip_end VARBINARY(16) NOT NULL,
  network VARCHAR(32)  NOT NULL,
  PRIMARY KEY (ip_start),
  KEY idx_start_end (ip_start, ip_end),
  KEY idx_network (network)
) {$cc};
";
        dbDelta($sql);

        /** @var list<array{string, string, string}> */
        $ranges = require Config::get('ipRangesPath');
        $values = [];
        $params = [];
        foreach ($ranges as [$s, $e, $n]) {
            $values[] = '(INET6_ATON(%s),INET6_ATON(%s),%s)';
            $params[] = self::normalizeIpToV6($s);
            $params[] = self::normalizeIpToV6($e);
            $params[] = $n;
        }

        $wpdb->query($wpdb->prepare(
            /** @phpstan-ignore-next-line argument.type */
            'INSERT INTO ' . $table . ' (ip_start, ip_end, network) VALUES ' . implode(',', $values),
            $params
        ));
    }

    /**
     * @param \WP_Upgrader $upgrader
     * @param array{action:string, type:string, bulk:bool, plugins:list<string>, themes:list<string>} $options
     */
    public static function upgrade($upgrader, $options): void
    {
        if (
            $options['action'] !== 'update'
            || $options['type'] !== 'plugin'
            || !in_array(Config::get('baseName'), $options['plugins'], true)
        ) {
            return;
        }

        self::activate();
    }

    public static function deactivate(): void
    {
        // Do nothing.
    }

    public static function uninstall(): void
    {
        /** @var \wpdb $wpdb */
        global $wpdb;

        $table = $wpdb->prefix . Config::get('tableName');
        $wpdb->query("DROP TABLE IF EXISTS `{$table}`");
    }

    public static function printRequirementsNotice(): void
    {
        // phpcs:ignore Generic.PHP.ForbiddenFunctions.Found
        error_log('Plugin Name requirements are not met. Please read the Installation instructions.');

        if (! current_user_can('activate_plugins')) {
            return;
        }

        printf(
            '<div class="notice notice-error"><p>%1$s <a href="%2$s" target="_blank">%3$s</a> %4$s</p></div>',
            esc_html__('Plugin Name activation failed! Please read', 'plugin-slug'),
            esc_url('https://github.com/szepeviktor/starter-plugin#installation'),
            esc_html__('the Installation instructions', 'plugin-slug'),
            esc_html__('for list of requirements.', 'plugin-slug')
        );
    }

    /**
     * Start!
     */
    public static function boot(): void
    {
        $blocker = new BlockKnownHostileNetworks();
        $blocker->do();
    }

    protected static function normalizeIpToV6(string $ip): string
    {
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) !== false) {
            return '::ffff:' . $ip;
        }

        return $ip;
    }
}
