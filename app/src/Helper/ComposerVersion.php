<?php
namespace App\Helper;

use Composer\InstalledVersions;

class ComposerVersion
{
    public static function getVersion(): string
    {
        return InstalledVersions::getRootPackage()['pretty_version'];
    }

    public function __toString(): string
    {
        return self::getVersion();
    }
}