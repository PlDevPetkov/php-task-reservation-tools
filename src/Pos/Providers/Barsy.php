<?php

namespace App\Pos\Providers;

use Lukanet\BarsyApiClient\BarsyApiClient;
use Lukanet\BarsyApiClient\BarsyApiData;
use Lukanet\BarsyApiClient\Exceptions as LukanetExceptions;
use Lukanet\BarsyApiClient\Reservations\Data\ReservationsListFiltersData;
use Lukanet\BarsyApiClient\Reservations\Reservations;

/**
 * @class Barsy
 * @package App\Pos\Providers
 */
class Barsy extends AbstractProvider
{
    const BARSY_PROVIDER_NAME = 'barsy';

    /**
     * @return string
     */
    public function getName(): string
    {
        return self::BARSY_PROVIDER_NAME;
    }

    /**
     * @param \DateTime|null $fromDate
     * @return array
     * @throws LukanetExceptions\BarsyApiClientFault
     * @throws LukanetExceptions\BarsyApiClientMessage
     */
    protected function retrieveOrders($fromDate): array
    {
        $bapi = new BarsyApiClient(
            $this->config['host'],
            $this->config['user'],
            $this->config['password']
        );

        $filters = BarsyApiData::factory(ReservationsListFiltersData::class);
        if ($fromDate) {
            $filters->create_date = [$fromDate->format('Y-m-d H:i:s')];
        }

        $reservationsModel = new Reservations($bapi);
        $reservations = $reservationsModel->getlist($filters, [], 0, 10000, '');

        foreach ($reservations as $reservation) {
            $reservation->provider_id = $reservation->barsy_id;
        }

        return $reservations;
    }
}
