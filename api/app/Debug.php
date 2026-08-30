<?php
namespace app;

class Debug
{
    public static bool $actif = false;

    public static function debug(mixed $data, bool $die = false): void
    {
        if (!self::$actif) return;
        echo '<pre>';
        print_r($data);
        echo '</pre>';
        if ($die) die();
    }

    public static function debugDie(mixed $data): never
    {
        self::debug($data, true);
        die();
    }
}
?>