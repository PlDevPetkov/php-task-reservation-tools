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
     * @var OutputInterface
     */
    protected $output;

    /**
     * @var bool
     */
    protected $trace;

    /**
     * @var string|null
     */
    private $context;

    /**
     * @var string
     */
    private $result = '';

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
        $this->output = $output;
        $this->trace = $input->getOption('trace');

        $startTime = new \DateTime();
        $commandResult = $this->doExecute($input, $output);
        $endTime = new \DateTime();

        $commandsLog = new CommandsLogs();
        $commandsLog->setName($commandName);
        $commandsLog->setContext($this->context);
        $commandsLog->setStartedAt($startTime);
        $commandsLog->setFinishedAt($endTime);
        $commandsLog->setStatus($commandResult);
        $commandsLog->setResult($this->result);

        $arguments = $input->getArguments();
        if (array_key_exists('command', $arguments)) {
            unset($arguments['command']);
        }
        $commandsLog->setParameters(json_encode($arguments));

        $this->entityManager->persist($commandsLog);
        $this->entityManager->flush();

        return $commandResult;
    }

    /**
     * @return CommandsLogs|object|null
     */
    protected function retrieveLastSuccessCommand()
    {
        return $this->entityManager
            ->getRepository(CommandsLogs::class)
            ->findOneBy(
                [
                    'name' => $this->getName(),
                    'context' => $this->context,
                    'status' => Command::SUCCESS
                ],
                ['id' => 'DESC']
            );
    }

    /**
     * @param string $message
     * @return void
     */
    protected function log(string $message)
    {
        if ($this->trace) {
            $this->output->writeln($message);
        }
    }

    /**
     * @param array $result
     * @return void
     */
    protected function setResult(array $result)
    {
        $this->result = json_encode($result);
    }

    /**
     * @param string $context
     * @return void
     */
    protected function setContext(string $context)
    {
        $this->context = $context;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    abstract protected function doExecute(InputInterface $input, OutputInterface $output): int;

    /**
     * @return void
     */
    abstract protected function configureCommand(): void;
}
