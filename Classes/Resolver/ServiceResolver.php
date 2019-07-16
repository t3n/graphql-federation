<?php

declare(strict_types=1);

namespace t3n\GraphQL\ApolloFederation\Resolver;

use Neos\Cache\Frontend\VariableFrontend;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Exception;
use Neos\Flow\Mvc\ActionRequest;
use Neos\Utility\Files;
use t3n\GraphQL\Context;
use t3n\GraphQL\ResolverInterface;
use t3n\GraphQL\Service\SchemaService;

class ServiceResolver implements ResolverInterface
{
    /**
     * @Flow\Inject()
     *
     * @var SchemaService
     */
    protected $schemaService;

    /**
     * @Flow\Inject
     *
     * @var VariableFrontend
     */
    protected $schemaCache;

    /**
     * @param null $_
     * @param mixed[] $args
     */
    public function sdl($_, array $args, Context $context): string
    {
        /** @var ActionRequest $request */
        $request = $context->getRequest();
        $endpoint = $request->getArgument('endpoint');

        if ($this->schemaCache->has($endpoint . '-sdl')) {
            return $this->schemaCache->get($endpoint . '-sdl');
        }

        $configuration = $this->schemaService->getEndpointConfiguration($endpoint);
        $sdl = '';

        foreach ($configuration['schemas'] as $name => $schemaConfiguration) {
            if (strpos($name, 'federation') !== 0) {
                if (substr($schemaConfiguration['typeDefs'], 0, 11) === 'resource://') {
                    $sdl .= Files::getFileContents($schemaConfiguration['typeDefs']);
                    if ($schemaConfiguration['typeDefs'] === false) {
                        throw new Exception(sprintf('File "%s" does not exist', $schemaConfiguration['typeDefs']));
                    }
                } else {
                    $sdl .= $schemaConfiguration['typeDefs'];
                }
            }
        }

        $this->schemaCache->set($endpoint . '-sdl', $sdl);

        return $sdl;
    }
}
