<?php

declare(strict_types=1);

namespace t3n\GraphQL\ApolloFederation\Resolver;

use GraphQL\Error\Error;
use Neos\Flow\Annotations as Flow;

trait EntitiesQueryTrait
{
    /**
     * @Flow\Inject
     *
     * @var \Neos\Flow\Reflection\ReflectionService
     */
    protected $reflectionService;

    /**
     * @Flow\Inject
     *
     * @var \Neos\Flow\ObjectManagement\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var string[]|null
     */
    protected $entityClasses = null;

    /**
     * Resolves all entity representations. Each representation will call it's Resolver "__resolveEntity" method
     * to actually resolve the entity.
     *
     * @see https://www.apollographql.com/docs/apollo-server/federation/federation-spec/#query_entities
     *
     * @param null $_
     * @param mixed[] $variables
     *
     * @return mixed[]
     */
    public function _entities($_, array $variables): array
    {
        $entities = [];

        if ($this->entityClasses === null) {
            $allImplementations = $this->reflectionService->getAllImplementationClassNamesForInterface(EntityResolverInterface::class);

            $this->entityClasses = [];

            foreach ($allImplementations as $entityClass) {
                $resolver = $this->objectManager->get($entityClass);
                $this->entityClasses[$resolver->__resolveType()] = $resolver;
            }
        }

        foreach ($variables['representations'] as $representation) {
            if (! isset($this->entityClasses[$representation['__typename']])) {
                throw new Error(sprintf('No Resolver for type %s could be found', $representation['__typename']));
            }

            /** @var EntityResolverInterface $resolver */
            $resolver = $this->entityClasses[$representation['__typename']];
            $entities[] = $resolver->__resolveEntity($representation);
        }

        return $entities;
    }
}
