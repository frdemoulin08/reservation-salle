<?php

namespace App\Service;

use App\Entity\CronTaskRun;
use Doctrine\ORM\EntityManagerInterface;

class CronTaskTracker
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function start(string $command, array $context = []): CronTaskRun
    {
        $run = new CronTaskRun();
        $run->setCommand($command);
        $run->setStatus(CronTaskRun::STATUS_RUNNING);
        if ([] !== $context) {
            $run->setContext($context);
        }

        $this->entityManager->persist($run);
        $this->entityManager->flush();

        return $run;
    }

    public function finishSuccess(
        CronTaskRun $run,
        ?string $summary = null,
        ?string $output = null,
        ?int $exitCode = 0,
        ?int $durationMs = null,
    ): void {
        $this->finish($run, CronTaskRun::STATUS_SUCCESS, $summary, $output, null, $exitCode, $durationMs);
    }

    public function finishFailure(
        CronTaskRun $run,
        \Throwable $exception,
        ?string $summary = null,
        ?string $output = null,
        ?int $exitCode = 1,
        ?int $durationMs = null,
    ): void {
        $error = trim($exception->getMessage()."\n".$exception->getTraceAsString());
        $this->finish($run, CronTaskRun::STATUS_FAILED, $summary, $output, $error, $exitCode, $durationMs);
    }

    private function finish(
        CronTaskRun $run,
        string $status,
        ?string $summary,
        ?string $output,
        ?string $error,
        ?int $exitCode,
        ?int $durationMs,
    ): void {
        $run->setStatus($status);
        $run->setFinishedAt(new \DateTimeImmutable());
        $run->setExitCode($exitCode);
        $run->setDurationMs($durationMs);
        $run->setSummary($this->truncate($summary, 255));
        $run->setOutput($this->truncate($output, 4000));
        $run->setError($this->truncate($error, 4000));

        $this->entityManager->flush();
    }

    private function truncate(?string $value, int $maxLength): ?string
    {
        if (null === $value) {
            return null;
        }

        if (mb_strlen($value) <= $maxLength) {
            return $value;
        }

        return mb_substr($value, 0, $maxLength - 3).'...';
    }
}
