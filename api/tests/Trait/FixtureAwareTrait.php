<?php

namespace App\Tests\Trait;

use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\DataFixtures\Loader;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

/**
 * Trait à utiliser dans des classes étendant KernelTestCase ou WebTestCase.
 */
trait FixtureAwareTrait
{
    private ?ORMExecutor $fixtureExecutor = null;
    private ?Loader $fixtureLoader = null;

    protected function addFixture(FixtureInterface $fixture): void
    {
        $this->getFixtureLoader()->addFixture($fixture);
    }

    protected function executeFixtures(): void
    {
        $this->getFixtureExecutor()->execute($this->getFixtureLoader()->getFixtures());
    }

    private function getFixtureExecutor(): ORMExecutor
    {
        if (!$this->fixtureExecutor) {
            $em = $this->getEntityManager();
            $purger = new ORMPurger($em);
            $purger->setPurgeMode(ORMPurger::PURGE_MODE_DELETE);

            $this->fixtureExecutor = new ORMExecutor($em, $purger);
        }

        return $this->fixtureExecutor;
    }

    private function getFixtureLoader(): Loader
    {
        if (!$this->fixtureLoader) {
            $this->fixtureLoader = new Loader();
        }

        return $this->fixtureLoader;
    }

    protected function getEntityManager(): EntityManagerInterface
    {
        /** @var \Doctrine\Persistence\ManagerRegistry $doctrine */
        $doctrine = static::getContainer()->get('doctrine');
        /** @var EntityManagerInterface $entityManager */
        $entityManager = $doctrine->getManager();

        return $entityManager;
    }

    /**
     * @template T of object
     *
     * @param class-string<T> $entityClass
     *
     * @return EntityRepository<T>
     */
    protected function getRepository(string $entityClass): EntityRepository
    {
        return $this->getEntityManager()->getRepository($entityClass);
    }
}
