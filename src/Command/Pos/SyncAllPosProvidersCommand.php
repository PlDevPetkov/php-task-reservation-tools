<?php

namespace App\Command\Pos;

use App\Command\AbstractCommand;
use App\Pos\PosFactory;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\ExceptionInterface;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

#[AsCommand(name: 'app:sync-all-pos-providers')]
class SyncAllPosProvidersCommand extends AbstractCommand
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
        $this->setDescription('Trigger synchronization of all POS Providers');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function doExecute(InputInterface $input, OutputInterface $output): int
    {
        $command = $this->getApplication()->find('app:sync-provider');
        $status = Command::SUCCESS;
        $result = [];

        foreach ($this->posFactory->getProvidersNames() as $providerName) {
            try {
                $providerSyncCommandInput = new ArrayInput([
                    'providerName' => $providerName,
                    '--trace' => $this->trace
                ]);

                $isSyncSuccessful = $command->run($providerSyncCommandInput, $output);
                $result[$providerName] = $isSyncSuccessful === Command::SUCCESS;
            } catch (\Exception|ExceptionInterface $e) {
                $this->log(sprintf('Failed to synchronize orders from %s', $providerName));
                $result[$providerName] = false;
                $status = Command::FAILURE;
            }
        }

        $this->setResult($result);
        return $status;
    }
}
