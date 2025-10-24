<?php

/**
 * Config.php
 *
 * @author Your Name <username@example.com>
 * @license GPL-2.0-or-later http://www.gnu.org/licenses/gpl-2.0.txt
 * @link https://example.com/plugin-name
 */

declare(strict_types=1);

namespace SzepeViktor\WordPress\MyAttackers;

/**
 * Immutable configuration.
 */
final class Config
{
    /** @var array<string, mixed>|null */
    private static ?array $container = null;

    /**
     * @param array<string, mixed> $container
     */
    public static function init(array $container): void
    {
        if (isset(self::$container)) {
            return;
        }

        self::$container = $container;
    }

    /**
     * @return mixed
     */
    public static function get(string $name)
    {
        if (! isset(self::$container) || ! array_key_exists($name, self::$container)) {
            return null;
        }

        return self::$container[$name];
    }
}
