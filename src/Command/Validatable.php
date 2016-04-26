<?php

namespace Appkr\Importer\Command;

interface Validatable
{
    /**
     * List of validation rules.
     *
     * @return array
     */
    public static function rules();

    /**
     * Custom error messages.
     *
     * @return array
     */
    public static function messages();

    /**
     * Custom attributes name.
     *
     * @return mixed
     */
    public static function attributes();
}