<?php
declare(strict_types=1);
namespace Noor\Core;

/** Small, explicit validator. Does not replace domain-specific business rules. */
final class Validation
{
    private static function length(string $value): int
    {
        if (preg_match_all('/./us', $value, $matches) === false) { return strlen($value); }
        return count($matches[0]);
    }

    public static function check(array $data, array $rules): array
    {
        $errors = [];
        foreach ($rules as $field => $fieldRules) {
            $value = $data[$field] ?? null;
            foreach (explode('|', $fieldRules) as $rule) {
                [$name, $arg] = array_pad(explode(':', $rule, 2), 2, null);
                $empty = $value === null || $value === '';
                if ($name === 'required' && ($empty || (is_string($value) && trim($value) === ''))) {
                    $errors[$field][] = 'required'; break;
                }
                if ($empty) { continue; }
                $valid = match ($name) {
                    'required' => true,
                    'string' => is_string($value),
                    'integer' => filter_var($value, FILTER_VALIDATE_INT) !== false,
                    'email' => is_string($value) && filter_var($value, FILTER_VALIDATE_EMAIL) !== false,
                    'min' => is_string($value) && self::length($value) >= (int) $arg,
                    'max' => is_string($value) && self::length($value) <= (int) $arg,
                    default => throw new \InvalidArgumentException('Unknown validation rule: ' . $name),
                };
                if (!$valid) { $errors[$field][] = $name; }
            }
        }
        return $errors;
    }
}
