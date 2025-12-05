<?php

use App\Models\User;
use App\Models\Consumable;
use App\Models\Request;
use App\Models\RequestItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StockReservationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_creates_stock_reservations_when_request_is_approved()
    {
        // Create office and category if needed
        \DB::table('offices')->insert(['id' => 1, 'name' => 'Test Office']);
        \DB::table('categories')->insert(['id' => 1, 'name' => 'Test Category']);

        // Create an admin user
        $admin = User::create([
            'name' => 'Admin User',
            'username' => 'admin',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'office_id' => 1,
            'must_set_password' => false,
        ]);

        // Create a faculty user
        $faculty = User::create([
            'name' => 'Faculty User',
            'username' => 'faculty',
            'email' => 'faculty@example.com',
            'password' => bcrypt('password'),
            'role' => null,
            'office_id' => 1,
            'must_set_password' => false,
        ]);

        // Create a consumable item with stock
        $item = Consumable::create([
            'category_id' => 1,
            'name' => 'Test Item',
            'product_code' => 'TEST001',
            'quantity' => 10,
            'unit' => 'pieces',
            'min_stock' => 1,
            'max_stock' => 100,
        ]);

        // Create a request
        $request = Request::create([
            'user_id' => $faculty->id,
            'status' => 'pending',
            'priority' => 'normal',
        ]);

        // Create request item
        $requestItem = RequestItem::create([
            'request_id' => $request->id,
            'item_id' => $item->id,
            'item_type' => 'consumable',
            'quantity' => 2,
        ]);

        // Assert no reservations exist initially
        $this->assertDatabaseCount('stock_reservations', 0);

        // Approve the request as admin
        $this->actingAs($admin);
        $response = $this->post(route('requests.approve-admin', $request));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Refresh the request from DB
        $request->refresh();

        // Assert request status is approved
        $this->assertEquals('approved_by_admin', $request->status);

        // Assert stock reservations were created
        $this->assertDatabaseCount('stock_reservations', 1);

        // Assert the reservation details
        $this->assertDatabaseHas('stock_reservations', [
            'request_item_id' => $requestItem->id,
            'item_id' => $item->id,
            'item_type' => 'consumable',
            'quantity_reserved' => 2,
            'status' => 'active'
        ]);
    }

    /** @test */
    public function it_does_not_create_reservations_if_insufficient_stock()
    {
        // Create admin and faculty
        $admin = User::create([
            'name' => 'Admin User2',
            'username' => 'admin2',
            'email' => 'admin2@example.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'office_id' => 1,
            'must_set_password' => false,
        ]);
        $faculty = User::create([
            'name' => 'Faculty User2',
            'username' => 'faculty2',
            'email' => 'faculty2@example.com',
            'password' => bcrypt('password'),
            'role' => null,
            'office_id' => 1,
            'must_set_password' => false,
        ]);

        // Create item with low stock
        $item = Consumable::create([
            'category_id' => 1,
            'name' => 'Test Item Low',
            'product_code' => 'TEST002',
            'quantity' => 1,
            'unit' => 'pieces',
            'min_stock' => 1,
            'max_stock' => 100,
        ]);

        // Create request
        $request = Request::create([
            'user_id' => $faculty->id,
            'status' => 'pending',
            'priority' => 'normal',
        ]);

        // Create request item asking for more than available
        $requestItem = RequestItem::create([
            'request_id' => $request->id,
            'item_id' => $item->id,
            'item_type' => 'consumable',
            'quantity' => 5, // More than available
        ]);

        // Approve
        $this->actingAs($admin);
        $this->post(route('requests.approve-admin', $request));

        // Assert no reservations created due to insufficient stock
        $this->assertDatabaseCount('stock_reservations', 0);

        // Assert request item status is unavailable
        $requestItem->refresh();
        $this->assertEquals('unavailable', $requestItem->status);
    }
}