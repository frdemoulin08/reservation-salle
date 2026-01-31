<?php

namespace App\Tests\Functional;

use App\DataFixtures\CronTaskRunFixtures;
use App\DataFixtures\ResetPasswordLogFixtures;
use App\DataFixtures\RoleFixtures;
use App\DataFixtures\SiteFixtures;
use App\DataFixtures\UserFixtures;
use App\Repository\UserRepository;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Loader;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

abstract class DatabaseWebTestCase extends WebTestCase
{
    protected ?KernelBrowser $client = null;

    protected function setUp(): void
    {
        parent::setUp();

        self::ensureKernelShutdown();
        $this->client = self::createClient();

        $entityManager = self::getContainer()->get('doctrine')->getManager();
        $metadata = $entityManager->getMetadataFactory()->getAllMetadata();

        if (!empty($metadata)) {
            $schemaTool = new SchemaTool($entityManager);
            $schemaTool->dropSchema($metadata);
            $schemaTool->createSchema($metadata);
        }

        $loader = new Loader();
        $loader->addFixture(new RoleFixtures());
        $loader->addFixture(new SiteFixtures());

        $passwordHasher = self::getContainer()->get(UserPasswordHasherInterface::class);
        $loader->addFixture(new UserFixtures($passwordHasher));
        $loader->addFixture(new CronTaskRunFixtures());
        $loader->addFixture(new ResetPasswordLogFixtures());

        $executor = new ORMExecutor($entityManager, new ORMPurger());
        $executor->execute($loader->getFixtures());
    }

    protected function loginAsAdmin(): KernelBrowser
    {
        $client = $this->client ?? self::createClient();
        $repository = self::getContainer()->get(UserRepository::class);
        $user = $repository->findOneBy(['email' => 'frederic.demoulin@cd08.fr']);

        self::assertNotNull($user, 'Utilisateur admin introuvable pour les tests.');

        $client->loginUser($user);

        return $client;
    }
}
