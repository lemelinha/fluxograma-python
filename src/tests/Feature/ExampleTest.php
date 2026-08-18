<?php

test('o endpoint de health responde com sucesso', function () {
    $response = $this->get('/up');

    $response->assertStatus(200);
});
