<?php

declare(strict_types=1);

namespace t3n\GraphQL\ApolloFederation\Resolver;

use t3n\GraphQL\ApolloFederation\GraphQLTypeAwareInterface;
use t3n\GraphQL\ResolverInterface;

/**
 * Resolver that resolves the _Entity union.
 */
class EntityResolver implements ResolverInterface
{
    /**
     * Resolves the _Entity union type. The type could be either be an
     * array or a object. If it's an object it has to implement the
     * GraphQLTypeAwareInterface
     *
     * @param mixed $entity
     *
     * @return mixed
     */
    public function __resolveType($entity)
    {
        if (is_array($entity) && isset($entity['__typename'])) {
            return $entity['__typename'];
        }

        if (is_object($entity) && $entity instanceof GraphQLTypeAwareInterface) {
            return $entity->getGraphQLType();
        }

        return null;
    }
}
