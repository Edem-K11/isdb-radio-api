<?php

namespace Tests\Feature\Admin;

use App\Models\Category;
use App\Models\Episode;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PanelSmokeTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['is_admin' => true]);
    }

    public function test_non_admins_cannot_access_the_panel(): void
    {
        $this->actingAs(User::factory()->create(['is_admin' => false]))
            ->get('/admin')
            ->assertForbidden();
    }

    public function test_admin_can_open_every_back_office_page(): void
    {
        $episode = Episode::factory()->create();
        $category = Category::factory()->create();

        $this->actingAs($this->admin());

        $this->get('/admin')->assertOk();
        $this->get('/admin/episodes')->assertOk();
        $this->get('/admin/episodes/create')->assertOk();
        $this->get("/admin/episodes/{$episode->getKey()}/edit")->assertOk();
        $this->get('/admin/categories')->assertOk();
        $this->get("/admin/categories/{$category->getKey()}/edit")->assertOk();
        $this->get('/admin/stream-settings')->assertOk();
        $this->get('/admin/app-settings')->assertOk();
    }
}
