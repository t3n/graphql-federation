<?php

declare(strict_types=1);

namespace t3n\GraphQL\ApolloFederation\Resolver;

trait ServiceQueryTrait
{
    public function _service(): bool
    {
        return true;
    }
}
