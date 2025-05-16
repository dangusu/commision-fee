<?php

namespace App\Command;

use App\Service\ComissionCalculator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CalculateFeesCommand extends Command
{
    protected static $defaultName = 'app:calculate-fees';

    /** @var ComissionCalculator */
    private $calculator;

    public function __construct(ComissionCalculator $calculator)
    {
        parent::__construct();
        $this->calculator = $calculator;
    }

    protected function configure(): void
    {
        $this
            ->addArgument('file', InputArgument::REQUIRED, 'CSV file path');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $file = $input->getArgument('file');
        $handle = fopen($file, 'r');
        $history = [];

        while (($data = fgetcsv($handle)) !== false) {
            [$date, $userId, $userType, $opType, $amt, $cur] = $data;
            $fee = $this->calculator->calculate(
                $userType,
                $opType,
                (float)$amt,
                $cur,
                $history[$userId] ?? []
            );

            $history[$userId][] = [
                'date' => $date,
                'amount' => (float)$amt,
                'type' => $opType,
                'currency' => $cur,
            ];

            $output->writeln(number_format($fee, 2, '.', ''));
        }

        return Command::SUCCESS;
    }
}