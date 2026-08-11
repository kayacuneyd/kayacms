<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

/**
 * KayaCMS application version (semver).
 *
 * Bump here for every release; update CHANGELOG.md and tag the git commit
 * with the same version.
 */
class Version extends BaseConfig
{
    public const MAJOR = 1;

    public const MINOR = 0;

    public const PATCH = 0;

    /**
     * Optional pre-release suffix, e.g. "dev", "beta.1". Empty for stable.
     *
     * @var string
     */
    public const PRE_RELEASE = '';

    public static function current(): string
    {
        $version = implode('.', [self::MAJOR, self::MINOR, self::PATCH]);

        if (self::PRE_RELEASE !== '') {
            $version .= '-' . self::PRE_RELEASE;
        }

        return $version;
    }
}