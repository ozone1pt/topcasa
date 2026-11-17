<?php

// Only the keys we override. The Sanctum service provider merges the rest
// (stateful domains, guards, middleware) from the package config.

$minutes = env('SANCTUM_TOKEN_EXPIRATION');

return [
    'expiration' => ($minutes !== null && $minutes !== '') ? (int) $minutes : null,
];
