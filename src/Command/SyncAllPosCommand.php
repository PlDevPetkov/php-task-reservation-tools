<?php

namespace App\Command;

use App\Pos\PosFactory;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

#[AsCommand(name: 'app:sync-all-pos')]
class SyncAllPosCommand extends AbstractCommand
{
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
    )
    {
        parent::__construct($entityManager);
        $this->posFactory = $posFactory;
    }

    /**
     * @return void
     */
    protected function configureCommand(): void
    {
        $this->setDescription('Synchronize orders from all POS systems');
    }

    /**
     * @return int
     */
    protected function doExecute(): int
    {
        $lastExecutedCommandStaredAt = null;
        if ($this->lastExecutedCommand) {
            $lastExecutedCommandStaredAt = $this->lastExecutedCommand->getStartedAt();
        }

        try {
            foreach ($this->posFactory->getProviders() as $provider) {
                $syncedOrdersCount = $provider->synchronizeOrders($lastExecutedCommandStaredAt);
                $this->trace(sprintf(
                    'Synchronized %d orders from %s',
                    $syncedOrdersCount,
                    $provider->getName()
                ));
            }
        } catch (\Exception $e) {
            $this->output->writeln($e->getMessage());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
