<?php

test('the named login route renders successfully', function () {
    $response = $this->get(route('login'));

    $response->assertStatus(200);
});
