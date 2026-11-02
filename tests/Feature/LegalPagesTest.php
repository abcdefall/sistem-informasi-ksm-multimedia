<?php

test('privacy and terms pages are available to guests and link to each other', function () {
    $this->assertGuest();

    $this->get(route('privacy'))
        ->assertOk()
        ->assertSee('Kebijakan Privasi')
        ->assertSee(route('terms'), false)
        ->assertSee(route('privacy'), false);

    $this->get(route('terms'))
        ->assertOk()
        ->assertSee('Syarat &amp; Ketentuan', false)
        ->assertSee(route('privacy'), false)
        ->assertSee(route('terms'), false);
});
