<?php

namespace Tests\Feature\Cart;

use App\Services\CartMergeService;
use App\Models\Product;
use App\Models\User;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Tests\TestCase;

class CartFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_add_action_queues_cookie(): void
    {
        $product = Product::create([
            'name' => 'Guest Product',
            'slug' => 'guest-product',
            'description' => 'Test product',
            'price_cents' => 1200,
            'stock' => 50,
            'image_url' => null,
            'is_active' => true,
        ]);

        $this->withoutMiddleware(EncryptCookies::class);

        $addResponse = $this->from('/')
            ->post('/cart/items', [
                'product_id' => $product->id,
                'qty' => 3,
            ]);

        $addResponse->assertRedirect('/');
        $addResponse->assertCookie('guest_cart_v1');
    }

    public function test_authenticated_cart_persists_in_database(): void
    {
        $user = User::factory()->create();

        $product = Product::create([
            'name' => 'Auth Product',
            'slug' => 'auth-product',
            'description' => 'Test product',
            'price_cents' => 500,
            'stock' => 200,
            'image_url' => null,
            'is_active' => true,
        ]);

        $this->actingAs($user)
            ->from('/')
            ->post('/cart/items', [
                'product_id' => $product->id,
                'qty' => 10,
            ])
            ->assertRedirect('/');

        $this->actingAs($user)
            ->from('/')
            ->post('/cart/items', [
                'product_id' => $product->id,
                'qty' => 10,
            ])
            ->assertRedirect('/');

        $this->assertDatabaseHas('carts', [
            'user_id' => $user->id,
        ]);

        $cartId = (int) \DB::table('carts')->where('user_id', $user->id)->value('id');

        $this->assertDatabaseHas('cart_items', [
            'cart_id' => $cartId,
            'product_id' => $product->id,
            'quantity' => 20,
        ]);
    }

    public function test_add_action_rejects_more_than_ten_units_at_once(): void
    {
        $product = Product::create([
            'name' => 'Limit Product',
            'slug' => 'limit-product',
            'description' => 'Test product',
            'price_cents' => 1000,
            'stock' => 100,
            'image_url' => null,
            'is_active' => true,
        ]);

        $response = $this->from('/')
            ->post('/cart/items', [
                'product_id' => $product->id,
                'qty' => 11,
            ]);

        $response->assertRedirect('/');
        $response->assertSessionHasErrors('qty');
    }

    public function test_merge_service_merges_guest_cookie_items_into_database_cart_and_clears_cookie(): void
    {
        $this->withoutMiddleware(EncryptCookies::class);

        $password = 'password-123';
        $user = User::factory()->create([
            'password' => bcrypt($password),
        ]);

        $product = Product::create([
            'name' => 'Merge Product '.Str::random(5),
            'slug' => 'merge-product-'.Str::lower(Str::random(8)),
            'description' => 'Test product',
            'price_cents' => 300,
            'stock' => 50,
            'image_url' => null,
            'is_active' => true,
        ]);

        $this->actingAs($user)
            ->post('/cart/items', [
                'product_id' => $product->id,
                'qty' => 4,
            ]);

        Auth::logout();

        $request = Request::create('/');
        $request->cookies->set('guest_cart_v1', json_encode([(string) $product->id => 6]));
        app()->instance('request', $request);

        $summary = app(CartMergeService::class)->mergeGuestCartIntoUser($user->id);

        $this->assertSame(1, $summary['merged_count']);
        $this->assertTrue($summary['final_guest_cleared']);

        $cartId = (int) \DB::table('carts')->where('user_id', $user->id)->value('id');

        $this->assertDatabaseHas('cart_items', [
            'cart_id' => $cartId,
            'product_id' => $product->id,
            'quantity' => 10,
        ]);
    }

    public function test_login_route_triggers_guest_cart_merge_integration(): void
    {
        $this->withoutMiddleware(EncryptCookies::class);

        $password = 'password-123';
        $user = User::factory()->create([
            'password' => bcrypt($password),
        ]);

        $product = Product::create([
            'name' => 'Login Merge Product '.Str::random(5),
            'slug' => 'login-merge-product-'.Str::lower(Str::random(8)),
            'description' => 'Test product',
            'price_cents' => 400,
            'stock' => 100,
            'image_url' => null,
            'is_active' => true,
        ]);

        // Existing DB cart quantity for the user before login.
        $this->actingAs($user)
            ->post('/cart/items', [
                'product_id' => $product->id,
                'qty' => 4,
            ])
            ->assertRedirect('/');

        Auth::logout();

        // Guest has additional quantity in cookie before login.
        $guestCookie = json_encode([(string) $product->id => 6]);

        $loginResponse = $this->withUnencryptedCookie('guest_cart_v1', $guestCookie)
            ->post('/login', [
                'email' => $user->email,
                'password' => $password,
            ]);

        $loginResponse->assertRedirect('/dashboard');

        $cartId = (int) \DB::table('carts')->where('user_id', $user->id)->value('id');

        $this->assertDatabaseHas('cart_items', [
            'cart_id' => $cartId,
            'product_id' => $product->id,
            'quantity' => 10,
        ]);

        $loginResponse->assertCookieExpired('guest_cart_v1');
    }

    public function test_login_route_merge_caps_overlapping_quantities_by_stock(): void
    {
        $this->withoutMiddleware(EncryptCookies::class);

        $password = 'password-123';
        $user = User::factory()->create([
            'password' => bcrypt($password),
        ]);

        $product = Product::create([
            'name' => 'Stock Cap Product '.Str::random(5),
            'slug' => 'stock-cap-product-'.Str::lower(Str::random(8)),
            'description' => 'Test product',
            'price_cents' => 750,
            'stock' => 12,
            'image_url' => null,
            'is_active' => true,
        ]);

        // Existing DB cart quantity before login.
        $this->actingAs($user)
            ->post('/cart/items', [
                'product_id' => $product->id,
                'qty' => 8,
            ])
            ->assertRedirect('/');

        Auth::logout();

        // Guest has overlapping quantity that would exceed stock when merged.
        $guestCookie = json_encode([(string) $product->id => 10]);

        $loginResponse = $this->withUnencryptedCookie('guest_cart_v1', $guestCookie)
            ->post('/login', [
                'email' => $user->email,
                'password' => $password,
            ]);

        $loginResponse->assertRedirect('/dashboard');

        $cartId = (int) \DB::table('carts')->where('user_id', $user->id)->value('id');

        $this->assertDatabaseHas('cart_items', [
            'cart_id' => $cartId,
            'product_id' => $product->id,
            'quantity' => 12,
        ]);

        $loginResponse->assertCookieExpired('guest_cart_v1');
    }
}
