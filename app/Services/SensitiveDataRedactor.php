<?php

namespace App\Services;

class SensitiveDataRedactor
{
    public static function text(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        return preg_replace(
            '/([?&](?:key|token|api[_-]?key|authorization|secret|password)=)[^&\s]*/iu',
            '$1[PROTEGIDO]',
            $value
        );
    }

    public static function context(mixed $value, ?string $key = null): mixed
    {
        if ($key !== null && preg_match('/token|authorization|api[_-]?key|secret|password/i', $key)) {
            return '[PROTEGIDO]';
        }

        if (is_array($value)) {
            foreach ($value as $itemKey => $itemValue) {
                $value[$itemKey] = self::context($itemValue, (string) $itemKey);
            }

            return $value;
        }

        return is_string($value) ? self::text($value) : $value;
    }
}
