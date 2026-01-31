<?php

namespace App\DataFixtures;

use App\Entity\CronTaskRun;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class CronTaskRunFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $now = new \DateTimeImmutable();
        $commands = [
            'app:auth-log:purge',
            'app:cache:cleanup',
            'app:stats:aggregate',
            'app:maintenance:ping',
        ];

        for ($i = 0; $i < 100; ++$i) {
            $run = new CronTaskRun();
            $command = $commands[array_rand($commands)];
            $run->setCommand($command);

            $secondsAgo = random_int(0, 60 * 60 * 24 * 120);
            $startedAt = $now->sub(new \DateInterval('PT'.$secondsAgo.'S'));
            $run->setStartedAt($startedAt);

            $statusRoll = random_int(1, 100);
            if ($statusRoll <= 75) {
                $status = CronTaskRun::STATUS_SUCCESS;
            } elseif ($statusRoll <= 90) {
                $status = CronTaskRun::STATUS_FAILED;
            } else {
                $status = CronTaskRun::STATUS_RUNNING;
            }

            $run->setStatus($status);

            if (CronTaskRun::STATUS_RUNNING === $status) {
                $run->setSummary('Execution en cours.');
                $run->setContext([
                    'trigger' => 'cron',
                    'host' => 'cron-01',
                ]);
                $manager->persist($run);
                continue;
            }

            $durationMs = random_int(120, 8500);
            $durationSeconds = (int) ceil($durationMs / 1000);
            $finishedAt = $startedAt->add(new \DateInterval('PT'.$durationSeconds.'S'));

            $run->setFinishedAt($finishedAt);
            $run->setDurationMs($durationMs);
            $run->setExitCode(CronTaskRun::STATUS_SUCCESS === $status ? 0 : 1);

            if ('app:auth-log:purge' === $command) {
                $days = random_int(180, 365);
                $deleted = CronTaskRun::STATUS_SUCCESS === $status ? random_int(5, 1400) : 0;
                $cutoff = $startedAt->modify(sprintf('-%d days', $days));
                $summary = CronTaskRun::STATUS_SUCCESS === $status
                    ? sprintf('%d entrees supprimees (anterieures a %s).', $deleted, $cutoff->format('Y-m-d'))
                    : 'Erreur lors de la purge des journaux.';
                $run->setSummary($summary);
                $run->setOutput(CronTaskRun::STATUS_SUCCESS === $status ? $summary : null);
                $run->setError(CronTaskRun::STATUS_FAILED === $status ? 'Database timeout during purge.' : null);
                $run->setContext([
                    'days' => $days,
                    'dry_run' => false,
                    'trigger' => 'cron',
                    'host' => 'cron-01',
                ]);
            } else {
                $summary = CronTaskRun::STATUS_SUCCESS === $status
                    ? 'Tache executee avec succes.'
                    : 'Echec lors de l\'execution de la tache.';
                $run->setSummary($summary);
                $run->setOutput(CronTaskRun::STATUS_SUCCESS === $status ? $summary : null);
                $run->setError(CronTaskRun::STATUS_FAILED === $status ? 'Unexpected failure in task runner.' : null);
                $run->setContext([
                    'trigger' => 'cron',
                    'host' => 'cron-01',
                ]);
            }

            $manager->persist($run);
        }

        $manager->flush();
    }
}
