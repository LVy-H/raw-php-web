<?php

namespace App\Core;

class Csrf
{
    private const SESSION_KEY = '_csrf_token';
    public const FIELD_NAME = '_token';

    public static function token(): string
    {
        if (empty($_SESSION[self::SESSION_KEY])) {
            $_SESSION[self::SESSION_KEY] = bin2hex(random_bytes(32));
        }
        return $_SESSION[self::SESSION_KEY];
    }

    public static function field(): string
    {
        return '<input type="hidden" name="' . self::FIELD_NAME . '" value="' . View::escape(self::token()) . '">';
    }

    public static function validate(): bool
    {
        $submitted = (string) ($_POST[self::FIELD_NAME] ?? '');
        $stored = (string) ($_SESSION[self::SESSION_KEY] ?? '');
        return $stored !== '' && hash_equals($stored, $submitted);
    }
}
