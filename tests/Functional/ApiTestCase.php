<?php

namespace App\Tests\Functional;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Zenstruck\Browser\HttpOptions;
use Zenstruck\Browser\KernelBrowser;
use Zenstruck\Browser\Test\HasBrowser;

abstract class ApiTestCase extends KernelTestCase
{
    private ?EntityManagerInterface $entityManager = null;

    use HasBrowser{
        browser as baseKernelBrowser;
    }

    protected string $baseUrl = '/api';

    protected function browser(array $options = [], array $server = []): KernelBrowser
    {
        return $this->baseKernelBrowser($options, $server)
            ->setDefaultHttpOptions(
                HttpOptions::create()->withHeader('Accept', 'application/ld+json')
            );
    }

    protected function getEntityManager(): EntityManagerInterface
    {
        if ($this->entityManager === null) {
            self::bootKernel();
            $this->entityManager = self::$kernel->getContainer()->get('doctrine.orm.entity_manager');
        }
        return $this->entityManager;
    }

    protected function getRepository(string $className): EntityRepository
    {
        return $this->getEntityManager()->getRepository($className);
    }
}