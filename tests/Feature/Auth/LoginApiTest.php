<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    $throttleKey = Str::transliterate(Str::lower('test@example.com') . '|' . '127.0.0.1');
    RateLimiter::clear($throttleKey);
});


test('user can login with valid credentials via api', function () {
    $user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => bcrypt('password123')
    ]);

    $response = $this->postJson('/api/login', [
        'email' => 'test@example.com',
        'password' => 'password123'
    ]);

    $response->assertStatus(200)
    ->assertJsonStructure([
        'user' => [
            'id',
            'name',
            'email'
        ],
        'token'
    ])
    ->assertJson([
        'user' => [
            'id' => $user->id,
            'name' => $user->name,
            'email' =>$user->email
        ]
    ])
    ;

    expect($response->json('token'))->not->toBeEmpty();

    $this->assertAuthenticated();
});

test('user cannot login with invalid email via api', function () {
    $user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => bcrypt('password123')
    ]);

    $response = $this->postJson('/api/login', [
        'email' => 'wrong@example.com',
        'password' => 'password123'
    ]);

    $response->assertStatus(422)
    ->assertJsonValidationErrors(['email']);
    
    $this->assertGuest();
});

test('user cannot login with invalid password via api', function () {
    $user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => bcrypt('password123')
    ]);

    $response = $this->postJson('/api/login', [
        'email' => 'test@example.com',
        'password' => 'wrongpassword'
    ]);

    $response->assertStatus(422)
    ->assertJsonValidationErrors(['email']);
    
    $this->assertGuest();
});

test('login requires email field', function () {
    
    $response = $this->postJson('/api/login', [
        'password' => 'password123'
    ]);

    $response->assertStatus(422)
    ->assertJsonValidationErrors(['email']);
    
});

test('login requires password field', function () {
    
    $response = $this->postJson('/api/login', [
        'email' => 'test@example.com'
    ]);
    
    $response->assertStatus(422)
    ->assertJsonValidationErrors(['password']);
    
});

test('login requires valid email format', function () {
    
    $response = $this->postJson('/api/login', [
        'email' => 'invalid-email',
        'password' => 'password123'
    ]);
    
    $response->assertStatus(422)
    ->assertJsonValidationErrors(['email']);
    
});

test('login is rate limited after too many attempts', function () {
    $user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => bcrypt('password123')
    ]);

    for ($i = 0; $i < 5; $i++) {
        $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'wrongpassword'
        ]);
    }

    $response = $this->postJson('/api/login', [
        'email' => 'test@example.com',
        'password' => 'wrongpassword'
    ]);

    $response->assertStatus(422)
    ->assertJsonValidationErrors(['email']);

});

test('login works after rate limit expires', function () {
    $user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => bcrypt('password123')
    ]);

    for ($i = 0; $i < 5; $i++) {
        $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'wrongpassword'
        ]);
    }
 
    // Clear the rate limiter for this key
    $throttleKey = Str::transliterate(Str::lower('test@example.com') . '|' . '127.0.0.1');
    RateLimiter::clear($throttleKey);
    
    $response = $this->postJson('/api/login', [
        'email' => 'test@example.com',
        'password' => 'password123'
    ]);

    $response->assertStatus(200)
    ->assertJsonStructure([
        'user' => [
            'id',
            'name',
            'email'
        ],
        'token'
    ]);

    $this->assertAuthenticated();

});

test('login endpoint requires guest middleware', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user, 'sanctum')
    ->postJson('/api/login', [
        'email' => 'test@example.com',
        'password' => 'password123'
    ]);

    $response->assertStatus(302);
});

test('login response contains correct user data structure', function () {
    $user = User::factory()->create([
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'password' => bcrypt('password123')
    ]);

    $response = $this->postJson('/api/login', [
        'email' => 'john@example.com',
        'password' => 'password123'
    ]);

    $response->assertStatus(200)
    ->assertJson([
        'user' => [
            'id' => $user->id,
            'name' => 'John Doe',
            'email' => 'john@example.com'
        ],
        
    ]);

    $responseData = $response->json();
    expect($responseData['user'])->not->toHaveKey('password');
    expect($responseData['user'])->not->toHaveKey('remember_token');
});