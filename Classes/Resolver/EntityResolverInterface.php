<?php

declare(strict_types=1);

namespace t3n\GraphQL\ApolloFederation\Resolver;

use t3n\GraphQL\ResolverInterface;

interface EntityResolverInterface extends ResolverInterface
{
    public function __resolveType(): string;

    /**
     * This method actually has to resolve your object/array that represents your entity
     *
     * @param mixed[] $variables those variables are passed down via the representation array
     */
    public function __resolveEntity(array $variables);
}
