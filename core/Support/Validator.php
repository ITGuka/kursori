<?php
declare(strict_types=1);

namespace Core\Support;

use Core\Database\DB;
use InvalidArgumentException;

/**
 * Validator::make($data, ['email' => 'required|email|max:190|unique:admin_users,email,5'])
 *
 * წესები: required, nullable, string, email, numeric, integer, boolean, min:n, max:n,
 * confirmed (field_confirmation), same:other, in:a,b, unique:table,column[,ignoreId], url (http/https),
 * regex:/pattern/ (pattern-ში "|"-ის გამო წესები მასივად გადაეცით).
 * არასავალდებულო ცარიელ ველზე დანარჩენი წესები არ მოწმდება.
 * შეტყობინებები: config('validation.messages'), ველების სახელები: $attributes ან config('validation.attributes').
 */
final class Validator
{
    private const DEFAULT_MESSAGES = [
        'required'  => 'The :attribute field is required.',
        'string'    => 'The :attribute must be text.',
        'email'     => 'The :attribute must be a valid email address.',
        'numeric'   => 'The :attribute must be a number.',
        'integer'   => 'The :attribute must be an integer.',
        'boolean'   => 'The :attribute must be true or false.',
        'min'       => 'The :attribute must be at least :min characters.',
        'max'       => 'The :attribute may not be greater than :max characters.',
        'confirmed' => 'The :attribute confirmation does not match.',
        'same'      => 'The :attribute must match :other.',
        'in'        => 'The selected :attribute is invalid.',
        'unique'    => 'The :attribute has already been taken.',
        'regex'     => 'The :attribute format is invalid.',
        'url'       => 'The :attribute must be a valid URL (http/https).',
    ];

    /** @var array<string, string>|null */
    private ?array $errors = null;

    /**
     * @param array<string, string|list<string>> $rules
     * @param array<string, string> $attributes
     */
    public function __construct(
        private readonly array $data,
        private readonly array $rules,
        private readonly array $attributes = [],
        private readonly ?DB $db = null,
    ) {
    }

    public static function make(array $data, array $rules, array $attributes = []): self
    {
        return new self($data, $rules, $attributes, app(DB::class));
    }

    public function fails(): bool
    {
        return $this->errors() !== [];
    }

    /** @return array<string, string> ველი => პირველი შეცდომა */
    public function errors(): array
    {
        if ($this->errors !== null) {
            return $this->errors;
        }

        $this->errors = [];
        foreach ($this->rules as $field => $rules) {
            $rules = is_array($rules) ? $rules : explode('|', $rules);
            $value = $this->data[$field] ?? null;

            if (self::isEmpty($value)) {
                if (in_array('required', $rules, true)) {
                    $this->errors[$field] = $this->message('required', $field);
                }
                continue;
            }

            foreach ($rules as $rule) {
                [$name, $param] = array_pad(explode(':', $rule, 2), 2, '');
                if (in_array($name, ['required', 'nullable'], true)) {
                    continue;
                }
                if (!$this->passes($name, $param, $field, $value)) {
                    $this->errors[$field] = $this->message($name, $field, $param);
                    break;
                }
            }
        }
        return $this->errors;
    }

    /** @return array<string, mixed> მხოლოდ წესებში ჩამოთვლილი ველები */
    public function validated(): array
    {
        return array_intersect_key($this->data, $this->rules);
    }

    private function passes(string $rule, string $param, string $field, mixed $value): bool
    {
        return match ($rule) {
            'string'    => is_string($value),
            'email'     => is_string($value) && filter_var($value, FILTER_VALIDATE_EMAIL) !== false,
            'numeric'   => is_numeric($value),
            'integer'   => filter_var($value, FILTER_VALIDATE_INT) !== false,
            'boolean'   => in_array($value, [true, false, 0, 1, '0', '1', 'on', 'off'], true),
            'min'       => self::size($value) >= (float)$param,
            'max'       => self::size($value) <= (float)$param,
            'confirmed' => ($this->data[$field . '_confirmation'] ?? null) === $value,
            'same'      => ($this->data[$param] ?? null) === $value,
            'in'        => in_array((string)$value, explode(',', $param), true),
            'regex'     => is_string($value) && preg_match($param, $value) === 1,
            'url'       => is_string($value) && filter_var($value, FILTER_VALIDATE_URL) !== false
                           && in_array(strtolower((string)parse_url($value, PHP_URL_SCHEME)), ['http', 'https'], true),
            'unique'    => $this->unique($param, $value),
            default     => throw new InvalidArgumentException("Unknown validation rule [$rule]."),
        };
    }

    private function unique(string $param, mixed $value): bool
    {
        [$table, $column, $ignoreId] = array_pad(explode(',', $param), 3, null);
        $sql = 'SELECT COUNT(*) FROM ' . DB::ident((string)$table) . ' WHERE ' . DB::ident((string)$column) . ' = ?';
        $bindings = [$value];
        if ($ignoreId !== null && $ignoreId !== '') {
            $sql .= ' AND id <> ?';
            $bindings[] = (int)$ignoreId;
        }
        return (int)$this->db?->value($sql, $bindings) === 0;
    }

    /** სტრიქონი — სიმბოლოების რაოდენობა, რიცხვი — მნიშვნელობა, მასივი — ელემენტები */
    private static function size(mixed $value): float
    {
        return match (true) {
            is_array($value) => count($value),
            is_int($value), is_float($value) => (float)$value,
            default => mb_strlen((string)$value),
        };
    }

    private static function isEmpty(mixed $value): bool
    {
        return $value === null || $value === [] || (is_string($value) && trim($value) === '');
    }

    private function message(string $rule, string $field, string $param = ''): string
    {
        $template = config("validation.messages.$rule") ?? self::DEFAULT_MESSAGES[$rule] ?? 'The :attribute is invalid.';
        $attribute = $this->attributes[$field] ?? config("validation.attributes.$field") ?? str_replace('_', ' ', $field);
        $other = $rule === 'same' ? ($this->attributes[$param] ?? config("validation.attributes.$param") ?? $param) : '';

        return strtr($template, [':attribute' => $attribute, ':min' => $param, ':max' => $param, ':other' => $other]);
    }
}
