<?php

namespace App\Tests\Trait;

use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\DataFixtures\Loader;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Psr\Container\ContainerInterface;

trait FixtureAwareTrait
{
    private ?ORMExecutor $fixtureExecutor = null;
    private ?Loader $fixtureLoader = null;

    protected function addFixture(FixtureInterface|string $fixture): void
    {
        if (is_string($fixture)) {
            $fixture = static::getContainer()->get($fixture);
        }

        // On garantit à l'analyseur statique et à PHP que $fixture est bien une FixtureInterface
        if (!$fixture instanceof FixtureInterface) {
            throw new \InvalidArgumentException(sprintf('The service "%s" must implement Doctrine\\Common\\DataFixtures\\FixtureInterface.', get_debug_type($fixture)));
        }

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
            $container = static::getContainer();

            $this->fixtureLoader = new class($container) extends Loader {
                public function __construct(private ContainerInterface $container)
                {
                }

                protected function createFixture(string $class): FixtureInterface
                {
                    $fixture = $this->container->get($class);

                    if (!$fixture instanceof FixtureInterface) {
                        throw new \InvalidArgumentException(sprintf('The class "%s" must implement Doctrine\\Common\\DataFixtures\\FixtureInterface.', $class));
                    }

                    return $fixture;
                }
            };
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
