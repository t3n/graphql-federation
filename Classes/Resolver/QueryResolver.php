<?php

declare(strict_types=1);

namespace t3n\GraphQL\ApolloFederation\Resolver;

use t3n\GraphQL\ResolverInterface;

class QueryResolver implements ResolverInterface
{
    public function _service(): bool
    {
        return true;
    }
}
