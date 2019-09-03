# t3n.GraphQL.ApolloFederation

Sidecar package for [t3n.GraphQL](https://github.com/t3n/graphql) to implement the [Apollo Federation specification](https://www.apollographql.com/docs/apollo-server/federation/federation-spec/) within your GraphQL schema.

Simply install it via composer:

```bash
composer require "t3n/graphql-apollofederation"
```

_Note: This package is still under progress and might change in it's implementation_

## About Apollo Federation

Apollo Federation is an architecture for composing multiple GraphQL services into a single graph. This package provides all functionality to implement the specification to your endpoints so you can use your service with Apollo's tools.

## Adjustments

In order to implement the specification you need do add some small adjustments to your existing schema.
You can see an [example implementation here](https://github.com/t3n/graphql-federation-demo).

### Add Federation Spec schema

1. Add the graphql specification to your schema:

```yaml
t3n:
  GraphQL:
    endpoints:
      'your-endpoint':
        schemas:
          federationSpec: # make sure to start this key with "federation"
            typeDefs: 'resource://t3n.GraphQL.ApolloFederation/Private/GraphQL/federation-schema.graphql'
              resolvers:
                _Entity: 't3n\GraphQL\ApolloFederation\Resolver\EntityResolver'
                _Service: 't3n\GraphQL\ApolloFederation\Resolver\ServiceResolver'
```

2. Add the `ServiceQueryTrait`  to your Query-Resolver

```php
<?php

declare(strict_types=1);

namespace Some\Vendor\Namespace\Resolver;

use t3n\GraphQL\ApolloFederation\Resolver\ServiceQueryTrait;
use t3n\GraphQL\ResolverInterface;

class QueryResolver implements ResolverInterface
{
    use ServiceQueryTrait;

    // [...]
}
```


3. Add a new Entity union to your schema:

You need to make each of your entities part of the `_Entity` union. In order to do so add
a new schema file to your Endpoint that defines the union:

```graphql
# Resources/Private/GraphQL/federation.graphql
union _Entity = User | Product | _allOfYourEntities_
```

```yaml
t3n:
  GraphQL:
    endpoints:
      'your-endpoint':
        schemas:
          federationEntity: # make sure to start this key with "federation"
            typeDefs: 'resource://Your.Package/Private/GraphQL/federation.graphql'
```

4. Add the EntitiesQueryTrait to your query resolver

This step is optional if do not have any entities.

The Federation specification needs a new query `_entities`. Add the trait to all of your QueryResolver:

```php
<?php

declare(strict_types=1);

namespace Some\Vendor\Namespace\Resolver;

use t3n\GraphQL\ApolloFederation\Resolver\EntitiesQueryTrait;
use t3n\GraphQL\ResolverInterface;

class QueryResolver implements ResolverInterface
{
    use EntitiesQueryTrait;

    // [...]
}
```

Also add the `_entities` to your query type:

```graphql
type Query @extends {
  _entities(representations: [_Any!]!): [_Entity]!
}
```

5. Implement EntityResolverInterface

Once the `_entities` query is called the `Entity` union will be resolved. The Apollo Gateway server will send queries like

```graphql
query($representations: [_Any!]!) {
  _entities(representations: $representations) {
    ... on User {
      name
    }
  }
}
```

Depending on the representation input a concrete resolver will be instantiated. All of your Resolvers that resolves
entities must therefore implement the `EntityResolverInterface`:

```php
    # Return the typename of your Entity
    public function __resolveType(): string;

    /**
     * This method actually has to resolve your object/array that represents your entity
     *
     * @param array $variables those variables are passed down via the representation array
     */
    public function __resolveEntity(array $variables);
```

5. Implement GraphQLTypeAwareInterface if needed

All of your data that you actually resolve and return which is part of the `_Entity` union type must be
aware of it's type name. If it's an object you must implement the `GraphQLTypeAwareInterface`. If your data
is an array there must be a key `__typename` on the first level.
