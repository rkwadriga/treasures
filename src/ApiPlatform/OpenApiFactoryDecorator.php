<?php

namespace App\ApiPlatform;

use ApiPlatform\OpenApi\Factory\OpenApiFactoryInterface;
use ApiPlatform\OpenApi\Model\SecurityScheme;
use ApiPlatform\OpenApi\OpenApi;
use ArrayObject;
use Symfony\Component\DependencyInjection\Attribute\AsDecorator;

#[AsDecorator('api_platform.openapi.factory')]
readonly class OpenApiFactoryDecorator implements OpenApiFactoryInterface
{
    public function __construct(
        private OpenApiFactoryInterface $openApiFactory
    ){
    }

    public function __invoke(array $context = []): OpenApi
    {
        $openApi = $this->openApiFactory->__invoke($context);

        $securitySchemes = $openApi->getComponents()->getSecuritySchemes() ?: new ArrayObject();
        $securitySchemes['access_token'] = new SecurityScheme(
            type: 'http',
            scheme: 'bearer',
        );

        return $openApi;
    }
}