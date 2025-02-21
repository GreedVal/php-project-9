<?php

namespace App\Http\Validators;

use Valitron\Validator;

class UrlValidator
{
    public static function validate($urlName): array
    {
        $validator = new Validator(['url' => $urlName]);
        $validator->stopOnFirstFail();
        $validator->rule('required', 'url')->message('URL не должен быть пустым');
        $validator->rule('url', 'url')->message('Некорректный URL');
        $validator->rule('lengthMax', 'url', 255)->message('Длина URL более 255 символов');

        $errors = [];

        if (! $validator->validate() && isset($validator->errors()['url'])) {
            $errors = $validator->errors()['url'];
        }

        if (!filter_var($urlName, FILTER_VALIDATE_URL)) {
            $errors[] = 'Некорректный URL';
        }

        return $errors;
    }
}