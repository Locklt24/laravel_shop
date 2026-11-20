<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\User;
use Database\Factories\OrderFactory;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class AdminManagementTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_admin_can_view_dashboard_category_and_user_management(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $customer = User::factory()->create(['role' => 'user']);
        Category::create(['name' => 'Thời trang', 'slug' => 'thoi-trang']);
        OrderFactory::new()->for($customer)->create([
            'status' => 'completed',
            'total_amount' => 1250000,
        ]);

        $this->actingAs($admin);

        $this->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSeeText('Tổng quan cửa hàng')
            ->assertSeeText('Doanh thu hoàn thành');

        $this->get(route('admin.categories.index'))
            ->assertOk()
            ->assertSeeText('Thời trang');

        $this->get(route('admin.users.index'))
            ->assertOk()
            ->assertSeeText($customer->email);
    }

    public function test_admin_can_create_update_and_delete_empty_category(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        $this->post(route('admin.categories.store'), ['name' => 'Phụ kiện'])
            ->assertRedirectToRoute('admin.categories.index');

        $category = Category::where('name', 'Phụ kiện')->firstOrFail();
        $this->assertSame('phu-kien', $category->slug);

        $this->put(route('admin.categories.update', $category), ['name' => 'Phụ kiện thời trang'])
            ->assertRedirectToRoute('admin.categories.index');

        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'name' => 'Phụ kiện thời trang',
            'slug' => 'phu-kien-thoi-trang',
        ]);

        $this->delete(route('admin.categories.destroy', $category))
            ->assertRedirectToRoute('admin.categories.index');
        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
    }

    public function test_admin_can_change_user_role_but_cannot_demote_self(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $customer = User::factory()->create(['role' => 'user']);
        $this->actingAs($admin);

        $this->patch(route('admin.users.update-role', $customer), ['role' => 'admin'])
            ->assertRedirect();
        $this->assertDatabaseHas('users', ['id' => $customer->id, 'role' => 'admin']);

        $this->patch(route('admin.users.update-role', $admin), ['role' => 'user'])
            ->assertRedirect()
            ->assertSessionHas('error');
        $this->assertDatabaseHas('users', ['id' => $admin->id, 'role' => 'admin']);
    }

    public function test_admin_cannot_delete_user_with_order_history(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $customer = User::factory()->create(['role' => 'user']);
        OrderFactory::new()->for($customer)->create();
        $this->actingAs($admin);

        $this->delete(route('admin.users.destroy', $customer))
            ->assertRedirect()
            ->assertSessionHas('error', 'Không thể xóa tài khoản đã có lịch sử đơn hàng.');

        $this->assertModelExists($customer);
    }
}
