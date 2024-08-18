<?php

namespace App\Command;

use App\Pos\PosFactory;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

const COMMAND_NAME = 'app:sync-all-pos';

#[AsCommand(name: COMMAND_NAME)]
class SyncAllPosCommand extends AbstractCommand
{
    /**
     * @var string
     */
    protected static $defaultName = COMMAND_NAME;

    /**
     * @var array
     */
    private $params;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var PosFactory
     */
    private $posFactory;

    /**
     * @param ParameterBagInterface $params
     * @param EntityManagerInterface $entityManager
     * @param PosFactory $posFactory
     */
    public function __construct(
        ParameterBagInterface $params,
        EntityManagerInterface $entityManager,
        PosFactory $posFactory

    ) {
        $this->params = $params;
        $this->entityManager = $entityManager;
        $this->posFactory = $posFactory;
        parent::__construct($entityManager);
    }

    /**
     * @return void
     */
    protected function configureCommand(): void
    {
        $this->setDescription('Synchronize orders from all POS systems');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function doExecute(InputInterface $input, OutputInterface $output): int
    {
        $lastExecutedCommandFinishedAt = null;
        $lastExecutedCommand = $this->getLastExecutedCommand();

        if ($lastExecutedCommand) {
            $lastExecutedCommandFinishedAt = $lastExecutedCommand->getFinishedAt();
        }

        try {
            foreach ($this->posFactory->getProviders() as $provider) {
                $syncedOrdersCount = $provider->synchronizeOrders($lastExecutedCommandFinishedAt);
                $this->trace(sprintf(
                    'Synchronized %d orders from %s',
                    $syncedOrdersCount,
                    $provider->getName()
                ));
            }
        } catch (\Exception $e) {
            $output->writeln($e->getMessage());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
