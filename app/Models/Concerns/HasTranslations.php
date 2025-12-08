<?php

declare(strict_types=1);

namespace App\Models\Concerns;

use Illuminate\Support\Arr;

trait HasTranslations
{
    public function __get($key)
    {
        if (! $this->isTranslatableAttribute($key)) {
            return parent::__get($key);
        }

        $locale = request()->attributes->get('locale', app()->getLocale());

        return $this->getTranslation($key, $locale);
    }

    protected function isTranslatableAttribute(string $key): bool
    {
        return in_array($key, $this->getTranslatableAttributes(), true);
    }

    protected function getTranslatableAttributes(): array
    {
        return $this->translatable ?? [];
    }

    public function getTranslation(string $field, string $locale): ?string
    {
        $translations = $this->{$field.'_translations'} ?? [];

        // Requested locale
        if (isset($translations[$locale])) {
            return $translations[$locale];
        }

        // Fallback: English
        if (isset($translations['en'])) {
            return $translations['en'];
        }

        // Fallback: first available
        return Arr::first($translations);
    }
}
