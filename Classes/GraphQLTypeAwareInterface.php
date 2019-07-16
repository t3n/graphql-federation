<?php

declare(strict_types=1);

namespace t3n\GraphQL\ApolloFederation;

use t3n\GraphQL\ResolverInterface;

interface GraphQLTypeAwareInterface extends ResolverInterface
{
    public function getGraphQLType(): string;
}
