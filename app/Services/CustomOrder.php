<?php
declare(strict_types=1);

namespace App\Services;

use App\Controllers\Front\FormController;
use Core\Http\Request;
use Core\Support\Validator;

/**
 * ინდივიდუალური შეკვეთის ბრიფი (config/forms.php → custom; ფორმა — views/front/partials/custom-order-wizard).
 * პროექტი = მომსახურება + ქვეტიპი (sub_keys): მასზეა დამოკიდებული ველები (for), ბიუჯეტისა და ვადის არჩევანი (profiles)
 * და სავალდებულო ბმული (required_for). შეუსაბამო ველები არ მოწმდება და არ ინახება.
 */
final class CustomOrder
{
    public const FORM = 'custom';
    /** ბმული http/www-ის გარეშეც (google.com) — იგივე, რაც ფორმის pattern-ში */
    public const URL_PATTERN = '~^(https?://)?([\w-]+\.)+[a-z]{2,}([/?#]\S*)?$~iu';

    public static function definition(): array
    {
        return (array)config('forms.' . self::FORM);
    }

    /** @return array<string, array> */
    public static function fields(): array
    {
        return self::definition()['fields'];
    }

    /**
     * პროექტის ნიშნები: [მომსახურება, მომსახურება:ქვეტიპი] (ქვეტიპი არ არის → „-“)
     * @param array<string, mixed> $input
     * @return list<string>
     */
    public static function tokens(array $input): array
    {
        $service = is_string($input['service'] ?? null) ? $input['service'] : '';
        $key = self::definition()['sub_keys'][$service] ?? null;
        $sub = $key !== null && is_string($input[$key] ?? null) && $input[$key] !== '' ? $input[$key] : '-';
        return [$service, "$service:$sub"];
    }

    /** @param list<string> $tokens */
    public static function matches(?string $list, array $tokens): bool
    {
        return $list === null || array_intersect(explode(' ', $list), $tokens) !== [];
    }

    /**
     * ბიუჯეტისა და ვადის პროფილი ამ პროექტისთვის
     * @param list<string> $tokens
     * @return array{ranges: list<array{0: int, 1: ?int}>, monthly: bool, timeline: array|string}|null
     */
    public static function profile(array $tokens): ?array
    {
        foreach (self::definition()['profiles'] as $list => $profile) {
            if (self::matches($list, $tokens)) {
                return $profile;
            }
        }
        return null;
    }

    /**
     * ბიუჯეტის არჩევანი: მნიშვნელობა (1500-3000, 7000+, unknown) => [წარწერა, შენიშვნა]
     * @return array<string, array{0: string, 1: string}>
     */
    public static function budgetOptions(array $profile, ?string $locale = null): array
    {
        $locale ??= locale();
        $number = fn (int $n) => number_format($n, 0, '', $locale === 'ka' ? "\u{00A0}" : ',');
        $amount = fn (string $value) => t('custom.currency', ['amount' => $value], $locale);
        $options = [];
        foreach ($profile['ranges'] as [$from, $to]) {
            $label = match (true) {
                $from === 0  => t('custom.budget_up_to', ['amount' => $amount($number((int)$to))], $locale),
                $to === null => t('custom.budget_from', ['amount' => $amount($number($from))], $locale),
                default      => $amount($number($from) . ' – ' . $number($to)),
            };
            $options[$from . ($to === null ? '+' : "-$to")] = [$label, $profile['monthly'] ? t('custom.per_month', [], $locale) : ''];
        }
        $options['unknown'] = [t('custom.budget_unknown', [], $locale), ''];
        return $options;
    }

    /**
     * ვადის არჩევანი: მნიშვნელობა => [წარწერა, შენიშვნა (ხანგრძლივობა)]
     * @return array<string, array{0: string, 1: string}>
     */
    public static function timelineOptions(array $profile, ?string $locale = null): array
    {
        $locale ??= locale();
        $timeline = $profile['timeline'];
        if ($timeline === 'start') {
            return [
                'asap'     => [t('custom.start_asap', [], $locale), ''],
                'month'    => [t('custom.start_month', [], $locale), ''],
                'flexible' => [t('custom.timeline_flexible', [], $locale), ''],
            ];
        }
        $duration = fn (array $d) => t("custom.$d[0]", ['range' => $d[1]], $locale);
        return [
            'fast'     => [t('custom.timeline_fast', [], $locale), $duration($timeline[0])],
            'standard' => [t('custom.timeline_standard', [], $locale), $duration($timeline[1])],
            'flexible' => [t('custom.timeline_flexible', [], $locale), ''],
        ];
    }

    /**
     * ფორმის ველები მოთხოვნიდან: multiple/links — სტრიქონების სია, files — გამოტოვებულია (Attachments), დანარჩენი — სტრიქონი
     * @return array<string, string|list<string>>
     */
    public static function input(Request $request): array
    {
        $clean = fn (mixed $value, bool $multiline = false) => is_string($value)
            ? (string)preg_replace($multiline ? '/[\x00-\x09\x0B-\x1F\x7F]/u' : '/[\x00-\x1F\x7F]/u', '', str_replace("\r\n", "\n", trim($value)))
            : '';
        $input = [];
        foreach (self::fields() as $name => $field) {
            $type = $field['type'] ?? '';
            if ($type === 'files') {
                continue;
            }
            $value = $request->input($name, '');
            if (!empty($field['multiple']) || $type === 'links') {
                $list = array_map($clean, is_array($value) ? array_values($value) : []);
                $input[$name] = array_values(array_unique(array_filter($list, fn ($item) => $item !== '')));
            } else {
                $input[$name] = $clean($value, !empty($field['multiline']));
            }
        }
        return $input;
    }

    /**
     * ვალიდაცია და შესანახი მონაცემები: მხოლოდ ამ პროექტის ველები; ბიუჯეტი და ვადა — წარწერით ძირითად ენაზე
     * (როგორც პაკეტის ფასი — შეკვეთის მომენტისთვის), ბმულები — https://-ით.
     * @param array<string, string|list<string>> $input
     * @return array{data: array<string, mixed>, errors: array<string, string>}
     */
    public static function validate(array $input, string $defaultLocale): array
    {
        $fields = self::fields();
        $tokens = self::tokens($input);
        $profile = self::profile($tokens);
        $relevant = array_filter($fields, fn ($field) => self::matches($field['for'] ?? null, $tokens) && ($field['type'] ?? '') !== 'files');

        // ჩვეულებრივი წესები (required, max, email, regex); options — in:; required_if — სხვა ველის მნიშვნელობით
        $rules = [];
        foreach ($relevant as $name => $field) {
            if (!empty($field['multiple']) || in_array($field['type'] ?? '', ['links', 'budget', 'timeline'], true)) {
                continue;
            }
            $list = array_values(array_filter(is_array($field['rules']) ? $field['rules'] : explode('|', (string)$field['rules'])));
            foreach ($field['required_if'] ?? [] as $other => $values) {
                if (in_array($input[$other] ?? '', $values, true)) {
                    $list[] = 'required';
                }
            }
            if (!empty($field['options'])) {
                $list[] = 'in:' . implode(',', array_keys($field['options']));
            }
            $rules[$name] = $list;
        }
        $errors = Validator::make($input, $rules, array_map(fn ($field) => t($field['label']), $relevant), FormController::messages())->errors();

        foreach ($relevant as $name => $field) {
            $value = $input[$name] ?? '';
            if (!empty($field['multiple']) && array_diff((array)$value, array_keys($field['options'])) !== []) {
                $errors[$name] = t('validation.in');
            }
        }

        // ბიუჯეტი და ვადა — ამ პროექტის არჩევანიდან
        $choices = $profile === null ? [] : [
            'budget'   => [self::budgetOptions($profile), self::budgetOptions($profile, $defaultLocale)],
            'timeline' => [self::timelineOptions($profile), self::timelineOptions($profile, $defaultLocale)],
        ];
        foreach (['budget', 'timeline'] as $name) {
            if (!isset($choices[$name][0][(string)($input[$name] ?? '')])) {
                $errors[$name] ??= t('custom.choose_option');
            }
        }

        // ბმულები: ფორმატი, რაოდენობა; required_for — ერთი მაინც
        $links = (array)($input['references'] ?? []);
        $linkField = $fields['references'];
        if (count($links) > (int)$linkField['max']) {
            $errors['references'] = t('validation.max', ['attribute' => t($linkField['label']), 'max' => $linkField['max']]);
        } elseif (array_filter($links, fn ($url) => mb_strlen($url) > 500 || preg_match(self::URL_PATTERN, $url) !== 1) !== []) {
            $errors['references'] = t('custom.reference_invalid');
        } elseif ($links === [] && self::matches($linkField['required_for'], $tokens)) {
            $errors['references'] = t('custom.reference_required');
        }

        if ($errors !== []) {
            return ['data' => [], 'errors' => $errors];
        }

        $data = [];
        foreach ($relevant as $name => $field) {
            $value = $input[$name] ?? '';
            $data[$name] = match ($field['type'] ?? '') {
                'budget', 'timeline' => self::choiceLabel($choices[$name][1][$value]),
                'links'              => array_map(fn ($url) => preg_match('~^https?://~i', $url) ? $url : 'https://' . $url, (array)$value),
                default              => $value,
            };
        }
        return ['data' => $data, 'errors' => []];
    }

    /** @param array{0: string, 1: string} $choice */
    private static function choiceLabel(array $choice): string
    {
        return $choice[1] !== '' ? "$choice[0] ($choice[1])" : $choice[0];
    }
}
