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
     * @template TName of 'version'|'filePath'|'baseName'|'slug'|'ipRangesPath'|'tableName'
     * @param TName $name
     * @return string|null
     */
    public static function get(string $name)
    {
        if (! isset(self::$container) || ! array_key_exists($name, self::$container)) {
            return null;
        }

        /** @phpstan-ignore-next-line return.type */
        return self::$container[$name];
    }
}
