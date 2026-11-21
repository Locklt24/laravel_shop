<?php

namespace Tests\Feature;

use Tests\TestCase;

class StoreInformationTest extends TestCase
{
    public function test_store_information_page_displays_the_public_shop_policies_and_contact_details(): void
    {
        $response = $this->get(route('store.information'));

        $response->assertSeeText('HuceShop')
            ->assertSeeText('0867497473')
            ->assertSeeText('Trường Đại học Xây dựng Hà Nội')
            ->assertSeeText('30.000đ')
            ->assertSeeText('500.000đ')
            ->assertSeeText('Nội thành Hà Nội: 1–2 ngày làm việc.')
            ->assertSeeText('Các tỉnh khác: 2–5 ngày làm việc.')
            ->assertSeeText('7 ngày kể từ ngày khách hàng nhận hàng.')
            ->assertSeeText('Khách hàng chịu phí vận chuyển gửi hàng về và phí gửi hàng đổi lại.')
            ->assertSeeText('Techcombank')
            ->assertSeeText('DO TIEN LOC')
            ->assertSeeText('8625488888');
    }
}
