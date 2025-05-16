<?php

namespace App\Tests\Command;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

class CalculateFeesCommandTest extends KernelTestCase
{
    public function testExampleInput(): void
    {
        self::bootKernel();

        /** @var \Symfony\Component\HttpKernel\KernelInterface $kernel */
        $kernel = self::$kernel;
        $application = new Application($kernel);

        $command = $application->find('app:calculate-fees');

        $tester = new CommandTester($command);
        $tester->execute(['file' => __DIR__ . '/input.csv']);

        $output = $tester->getDisplay();


        $this->assertTrue(strpos($output, "0.60\n3.00\n0.00\n") !== false);
    }
}