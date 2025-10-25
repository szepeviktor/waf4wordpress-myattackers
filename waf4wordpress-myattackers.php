<?php

/**
 * WAF for WordPress MyAttackers
 *
 * @author            Viktor Szépe <viktor@szepe.net>
 * @license           GPL-2.0-or-later http://www.gnu.org/licenses/gpl-2.0.txt
 * @link              https://github.com/szepeviktor/waf4wordpress-myattackers
 *
 * @wordpress-plugin
 * Plugin Name:       WAF for WordPress MyAttackers
 * Plugin URI:        https://github.com/szepeviktor/waf4wordpress-myattackers
 * Description:       Block known hostile networks
 * Version:           1.0.0
 * Requires at least: 6.3
 * Requires PHP:      8.1
 * Author:            Viktor Szépe
 * License:           GPL v2 or later
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 */

declare(strict_types=1);

namespace SzepeViktor\WordPress\MyAttackers;

use function add_action;
use function current_user_can;
use function deactivate_plugins;
use function esc_html;
use function esc_html__;
use function plugin_basename;
use function register_activation_hook;
use function register_deactivation_hook;
use function register_uninstall_hook;

// phpcs:disable PSR12NeutronRuleset.Strings.ConcatenationUsage

// Prevent direct execution.
if (! defined('ABSPATH')) {
    // phpcs:ignore Generic.PHP.ForbiddenFunctions.Found
    exit;
}

// Load autoloader.
if (! class_exists(Config::class) && is_file(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
}

// Prevent double activation.
if (Config::get('version') !== null) {
    add_action(
        'admin_notices',
        static function (): void {
            // phpcs:ignore Generic.PHP.ForbiddenFunctions.Found
            error_log('WAF for WordPress MyAttackers double activation. Please remove all but one copies. ' . __FILE__);

            if (! current_user_can('activate_plugins')) {
                return;
            }

            printf(
                '<div class="notice notice-warning"><p>%1$s<br>%2$s&nbsp;<code>%3$s</code></p></div>',
                esc_html__('WAF for WordPress MyAttackers already installed! Please deactivate all but one copies.', 'waf4wordpress-myattackers'),
                esc_html__('Current plugin path:', 'waf4wordpress-myattackers'),
                esc_html(__FILE__)
            );
        },
        0,
        0
    );

    return;
}

// Define constant values.
Config::init(
    [
        'version' => '1.0.0',
        'filePath' => __FILE__,
        'baseName' => plugin_basename(__FILE__),
        'slug' => 'waf4wordpress-myattackers',
        'ipRangesPath' => __DIR__ . '/data/ip-ranges.php',
        'tableName' => 'waf_myattackers',
    ]
);

// Check requirements.
if (
    (new Requirements())
        ->php('8.1')
        ->wp('6.3')
        ->multisite(false)
        ->met()
) {
    // Hook plugin activation functions.
    register_activation_hook(__FILE__, [Plugin::class, 'activate']);
    add_action('upgrader_process_complete', [Plugin::class, 'upgrade'], 10, 2);
    register_deactivation_hook(__FILE__, [Plugin::class, 'deactivate']);
    register_uninstall_hook(__FILE__, [Plugin::class, 'uninstall']);
    add_action('plugins_loaded', [Plugin::class, 'boot'], 10, 0);
} else {
    // Suppress "Plugin activated." notice.
    unset($_GET['activate']); // phpcs:ignore WordPress.Security.NonceVerification.Recommended

    add_action('admin_notices', [Plugin::class, 'printRequirementsNotice'], 0, 0);

    require_once \ABSPATH . 'wp-admin/includes/plugin.php';
    /** @phpstan-ignore-next-line argument.type */
    deactivate_plugins([Config::get('baseName')], true);
}
