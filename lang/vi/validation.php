<?php

return [
    'required' => 'Trường :attribute là bắt buộc.',
    'required_with' => 'Trường :attribute là bắt buộc khi :values được nhập.',
    'string' => 'Trường :attribute phải là chuỗi ký tự.',
    'email' => ':attribute phải là địa chỉ email hợp lệ.',
    'max' => [
        'string' => ':attribute không được dài hơn :max ký tự.',
    ],
    'min' => [
        'string' => ':attribute phải có ít nhất :min ký tự.',
    ],
    'confirmed' => 'Xác nhận :attribute không khớp.',
    'enum' => ':attribute được chọn không hợp lệ.',
    'in' => ':attribute được chọn không hợp lệ.',
    'current_password' => 'Mật khẩu không chính xác.',
    'unique' => ':attribute đã được sử dụng.',
    'password' => [
        'letters' => ':attribute phải chứa ít nhất một chữ cái.',
        'mixed' => ':attribute phải chứa ít nhất một chữ hoa và một chữ thường.',
        'numbers' => ':attribute phải chứa ít nhất một chữ số.',
        'symbols' => ':attribute phải chứa ít nhất một ký hiệu.',
        'uncompromised' => ':attribute đã xuất hiện trong một vụ rò rỉ dữ liệu. Vui lòng chọn :attribute khác.',
    ],
    'attributes' => [
        'name' => 'tên',
        'email' => 'địa chỉ email',
        'password' => 'mật khẩu',
        'current_password' => 'mật khẩu hiện tại',
        'password_confirmation' => 'xác nhận mật khẩu',
        'locale' => 'ngôn ngữ',
        'timezone' => 'Múi giờ',
    ],
];
