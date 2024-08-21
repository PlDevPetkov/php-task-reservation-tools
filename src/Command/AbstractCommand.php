<?php

namespace App\Command;

use App\Entity\CommandsLogs;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @class  AbstractCommand
 * @package App\Command
 */
abstract class AbstractCommand extends Command
{
    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * @var InputInterface
     */
    protected $input;

    /**
     * @var OutputInterface
     */
    protected $output;

    /**
     * @var CommandsLogs|null
     */
    protected $lastExecutedCommand;

    /**
     * @var bool
     */
    private $trace;

    /**
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
    }

    /**
     * @return void
     */
    protected function configure(): void
    {
        $this->addOption(
            'trace',
            null,
            InputOption::VALUE_NONE,
            'Trace command execution'
        );

        $this->configureCommand();
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $commandName = $this->getName();
        $this->input = $input;
        $this->output = $output;
        $this->trace = $input->getOption('trace');
        $this->lastExecutedCommand = $this->entityManager
            ->getRepository(CommandsLogs::class)
            ->findOneBy(['name' => $commandName], ['id' => 'DESC']);

        $startTime = new \DateTime();
        $this->trace("Command $commandName execution started");
        $this->trace(sprintf("Start time: %s", $startTime->format('Y-m-d H:i:s')));

        $result = $this->doExecute($input, $output);

        $endTime = new \DateTime();
        $this->trace(sprintf("End time: %s", $endTime->format('Y-m-d H:i:s')));

        $duration = $endTime->getTimestamp() - $startTime->getTimestamp();
        $this->trace(sprintf("Duration: %s", $duration));

        $commandsLog = new CommandsLogs();
        $commandsLog->setName($commandName);
        $commandsLog->setStartedAt($startTime);
        $commandsLog->setFinishedAt($endTime);
        $commandsLog->setStatus($result);

        $arguments = $input->getArguments();
        if (array_key_exists('command', $arguments)) {
            unset($arguments['command']);
        }
        $commandsLog->setParameters(json_encode($arguments));

        $this->entityManager->persist($commandsLog);
        $this->entityManager->flush();

        return $result;
    }

    /**
     * @param string $message
     * @return void
     */
    protected function trace(string $message)
    {
        if ($this->trace) {
            $this->output->writeln($message);
        }
    }

    /**
     * @return int
     */
    abstract protected function doExecute(): int;

    /**
     * @return void
     */
    abstract protected function configureCommand(): void;
}
