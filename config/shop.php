<?php

return [
    'name' => 'HuceShop',
    'contact' => [
        'phone' => '0867497473',
        'address' => 'Trường Đại học Xây dựng Hà Nội',
    ],
    'shipping' => [
        'fee' => 30000,
        'free_threshold' => 500000,
        'delivery_times' => [
            'hanoi' => 'Nội thành Hà Nội: 1–2 ngày làm việc.',
            'other' => 'Các tỉnh khác: 2–5 ngày làm việc.',
        ],
    ],
    'returns' => [
        'period_days' => 7,
        'period_starts' => 'kể từ ngày khách hàng nhận hàng',
        'condition' => 'Sản phẩm cần còn nguyên vẹn.',
        'shipping_cost' => 'Khách hàng chịu phí vận chuyển gửi hàng về và phí gửi hàng đổi lại.',
    ],
    'bank_transfer' => [
        'bank' => 'Techcombank',
        'account_name' => 'DO TIEN LOC',
        'account_number' => '8625488888',
    ],
];
