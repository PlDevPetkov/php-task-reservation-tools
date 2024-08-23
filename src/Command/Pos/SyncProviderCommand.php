<?php

namespace App\Command\Pos;

use App\Command\AbstractCommand;
use App\Pos\PosFactory;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

#[AsCommand(name: 'app:sync-provider')]
class SyncProviderCommand extends AbstractCommand
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
    ) {
        parent::__construct($entityManager);
        $this->posFactory = $posFactory;
    }

    /**
     * @return void
     */
    protected function configureCommand(): void
    {
        $this->setDescription('Synchronize orders from specific POS Provider')
            ->addArgument(
                'providerName',
                InputArgument::REQUIRED,
                'The name of the provider'
            );
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function doExecute(InputInterface $input, OutputInterface $output): int
    {
        $providerName = $input->getArgument('providerName');
        $this->setContext($providerName);

        $lastSuccessCommandStaredAt = null;
        $lastSuccessCommand = $this->retrieveLastSuccessCommand();

        if ($lastSuccessCommand) {
            $lastSuccessCommandStaredAt = $lastSuccessCommand->getFinishedAt();
            $this->log(sprintf('Synced from %s', $lastSuccessCommandStaredAt->format('Y-m-d H:i:s')));
        }

        $status = Command::SUCCESS;
        $result = ['synced_orders_count' => 0];
        try {
            $provider = $this->posFactory->getProviderByName($providerName);
            $syncedOrdersCount = $provider->synchronizeOrders($lastSuccessCommandStaredAt);

            $this->log(sprintf(
                'Synchronized %d orders from %s',
                $syncedOrdersCount,
                $providerName
            ));

            $result['synced_orders_count'] = $syncedOrdersCount;
        } catch (\Exception $e) {
            $this->log(sprintf('Failed to synchronize orders from %s', $providerName));
            $status = Command::FAILURE;
        }

        $this->setResult($result);
        return $status;
    }
}
