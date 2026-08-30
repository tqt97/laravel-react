<?php

return [
    'required' => 'The :attribute field is required.',
    'string' => 'The :attribute must be a string.',
    'email' => 'The :attribute must be a valid email address.',
    'max' => [
        'string' => 'The :attribute may not be greater than :max characters.',
    ],
    'confirmed' => 'The :attribute confirmation does not match.',
    'enum' => 'The selected :attribute is invalid.',
    'current_password' => 'The password is incorrect.',
    'unique' => 'The :attribute has already been taken.',
    'password' => 'The password is incorrect.',
    'attributes' => [
        'current_password' => 'current password',
        'password_confirmation' => 'password confirmation',
        'locale' => 'language',
    ],
];
