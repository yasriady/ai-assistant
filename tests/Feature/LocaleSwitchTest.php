<?php

namespace Tests\Feature;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class LocaleSwitchTest extends TestCase
{
    #[Test]
    public function guest_can_switch_locale_to_english(): void
    {
        $this->from(route('login'))
            ->get(route('locale.switch', 'en'))
            ->assertRedirect(route('login'));

        $this->assertSame('en', session('locale'));

        $this->get(route('login'))
            ->assertOk()
            ->assertSee('Sign in', false);
    }

    #[Test]
    public function guest_can_switch_locale_to_indonesian(): void
    {
        $this->withSession(['locale' => 'en'])
            ->from(route('login'))
            ->get(route('locale.switch', 'id'))
            ->assertRedirect(route('login'));

        $this->assertSame('id', session('locale'));

        $this->get(route('login'))
            ->assertOk()
            ->assertSee('Masuk', false);
    }

    #[Test]
    public function unsupported_locale_returns_not_found(): void
    {
        $this->get('/locale/fr')->assertNotFound();
    }
}
