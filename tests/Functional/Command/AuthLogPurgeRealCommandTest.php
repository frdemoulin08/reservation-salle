<?php

namespace App\Tests\Functional\Command;

use App\Entity\CronTaskRun;
use App\Repository\CronTaskRunRepository;
use App\Tests\Functional\DatabaseWebTestCase;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class AuthLogPurgeRealCommandTest extends DatabaseWebTestCase
{
    public function testPurgeCommandWithRealDeletionCreatesSuccessRun(): void
    {
        $container = self::getContainer();
        $repository = $container->get(CronTaskRunRepository::class);
        $before = $repository->count([]);

        $application = new Application(self::$kernel);
        $command = $application->find('app:auth-log:purge');
        $tester = new CommandTester($command);
        $tester->execute(['--days' => 1]);

        $after = $repository->count([]);
        self::assertSame($before + 1, $after);

        $latest = $repository->findOneBy([], ['id' => 'DESC']);
        self::assertNotNull($latest);
        self::assertSame(CronTaskRun::STATUS_SUCCESS, $latest->getStatus());
        self::assertSame('app:auth-log:purge', $latest->getCommand());
    }
}
