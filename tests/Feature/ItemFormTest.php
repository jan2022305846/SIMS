<?php

use App\Models\User;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('loads the item create form with correct fields', function () {
    // Create admin user
    $admin = User::factory()->create(['id' => 6, 'role' => 'admin']);

    // Create a category
    Category::factory()->create();

    // Create offices
    \App\Models\Office::create(['name' => 'Main Office', 'location' => 'Building A']);
    \App\Models\Office::create(['name' => 'Branch Office', 'location' => 'Building B']);

    // Act as admin
    $this->actingAs($admin);

    // Visit create form
    $response = $this->get(route('items.create'));

    // Assert form loads successfully
    $response->assertStatus(200);

    // Assert form contains correct field names
    $response->assertSee('name="quantity"', false);
    $response->assertSee('name="min_stock"', false);
    $response->assertSee('name="max_stock"', false);
    $response->assertSee('name="unit"', false);

    // Assert old field names are not present
    $response->assertDontSee('name="current_stock"', false);
    $response->assertDontSee('name="minimum_stock"', false);
    $response->assertDontSee('name="maximum_stock"', false);
});

it('loads the item edit form with correct fields', function () {
    // Create admin user
    $admin = User::factory()->create(['id' => 6, 'role' => 'admin']);

    // Create a category and consumable item
    $category = Category::factory()->create();
    $item = \App\Models\Consumable::factory()->create(['category_id' => $category->id]);

    // Create offices
    \App\Models\Office::create(['name' => 'Main Office', 'location' => 'Building A']);
    \App\Models\Office::create(['name' => 'Branch Office', 'location' => 'Building B']);

    // Act as admin
    $this->actingAs($admin);

    // Visit edit form
    $response = $this->get(route('items.edit', $item));

    // Assert form loads successfully
    $response->assertStatus(200);

    // Assert form contains correct field names
    $response->assertSee('name="min_stock"', false);
    $response->assertSee('name="max_stock"', false);
    $response->assertSee('name="unit"', false);
    $response->assertSee('name="add_quantity"', false);

    // Assert old field names are not present
    $response->assertDontSee('name="current_stock"', false);
    $response->assertDontSee('name="minimum_stock"', false);
    $response->assertDontSee('name="maximum_stock"', false);
});