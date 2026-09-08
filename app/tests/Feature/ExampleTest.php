<?php

namespace Tests\Feature;

use App\Models\Book;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic test to make sure the public catalog loads.
     *
     * @return void
     */
    public function test_the_catalog_homepage_renders()
    {
        Book::factory()->create(['title' => 'The Art of Laravel', 'author' => 'Jane Doe']);

        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('The Art of Laravel');
        $response->assertSee('Innovatech Library');
    }
}
