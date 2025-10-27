<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class YoutubeUrl implements ValidationRule
{
    protected $videoId = null;

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (empty($value)) {
            return;
        }

        $pattern = '/^(?:https?:\/\/(?:www\.)?youtube\.com\/watch\?(?:.*&)?v=|https?:\/\/(?:www\.)?youtu\.be\/)([a-zA-Z0-9_-]{11})(?:[?&].*)?$/';

        if (preg_match($pattern, $value, $matches)) {
            $this->videoId = $matches[1];
        } else {
            $fail('The :attribute must be a valid YouTube video URL.');
        }
    }

    public function videoId()
    {
        return $this->videoId;
    }
}
