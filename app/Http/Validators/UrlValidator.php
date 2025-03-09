<?php

namespace App\Http\Validators;

use Valitron\Validator;

class UrlValidator
{
    public static function validate(string $urlName): array
    {
        $validator = new Validator(['url' => $urlName]);
        $validator->stopOnFirstFail();
        $validator->rule('required', 'url')->message('URL не должен быть пустым');
        $validator->rule('url', 'url')->message('Некорректный URL');
        $validator->rule('lengthMax', 'url', 255)->message('Длина URL более 255 символов');

        $validator->rule(function ($field, $value) {
            return preg_match('/^[a-zA-Z0-9\-_~:\/?#\[\]@!$&\'()*+,;=.]+$/', $value);
        }, 'url')->message('URL содержит недопустимые символы');

        $validator->rule(function ($field, $value) {
            return preg_match('/^(https?):\/\//', $value);
        }, 'url')->message('URL должен начинаться с http:// или https://');

        $errors = [];

        if (! $validator->validate() && isset($validator->errors()['url'])) {
            $errors = $validator->errors()['url'];
        }

        return $errors;
    }
}
