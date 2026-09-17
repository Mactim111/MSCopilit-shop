<?php

namespace App\Exceptions;

use RuntimeException;

class FavoriteLimitExceededException extends RuntimeException
{
    public function __construct(public readonly int $limit = 200)
    {
        parent::__construct('Ваш список избранных товаров заполнен!');
    }
}
