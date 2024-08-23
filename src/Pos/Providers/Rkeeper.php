<?php

namespace App\Pos\Providers;

use App\Pos\Order;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

/**
 * @class Rkeeper
 * @package App\Pos\Providers
 */
class Rkeeper extends AbstractProvider
{
    const RKEEPER_PROVIDER_NAME = 'rkeeper';

    /**
     * @return string
     */
    public function getName(): string
    {
        return self::RKEEPER_PROVIDER_NAME;
    }

    /**
     * @param \DateTime|null $fromDate
     * @return Order[]
     * @throws \Exception|TransportExceptionInterface
     */
    protected function retrieveOrders($fromDate): array
    {
        $xmlResult = (bool) $this->config['should_use_dummy_data']
            ? $this->getDummyData()
            : $this->retrieveXmlRequest($fromDate);

        $orders = new \SimpleXMLElement($xmlResult);

        $mappedOrders = [];
        foreach ($orders->Visit as $visit) {
            $visitId = (int) $visit->attributes()->VisitID;

            foreach ($visit->Orders->Order as $order) {
                $orderObj = (new Order())
                    ->setProviderId((int) $order['OrderID'])
                    ->setReservationId($visitId)
                    ->setOrderDetails($order->attributes());

                $mappedOrders[] = $orderObj;
            }
        }

        return $mappedOrders;
    }

    /**
     * @param \DateTime|null $fromDate
     * @return string
     * @throws \Exception
     * @throws ClientExceptionInterface|TransportExceptionInterface
     */
    private function retrieveXmlRequest($fromDate): string
    {
        $xml = sprintf(
            '<?xml version="1.0" encoding="windows-1251"?><RK7Query><RK7Command CMD="GetOrderList" ><Visit><Orders><Order createTime="%s"></Order></Orders></Visit></RK7Command></RK7Query>',
            $fromDate->format('Y-m-d H:i:s')
        );

        $httpClient = HttpClient::create([
            'auth_basic' => [
                $this->config['user'],
                $this->config['password']
            ]
        ]);

        $response = $httpClient->request('POST', $this->config['host'], [
            'headers' => ['Content-Type' => 'text/xml'],
            'body' => $xml,
        ]);

        if ($response->getStatusCode() !== 200) {
            throw new \Exception('Request failed with status: ' . $response->getStatusCode());
        }

        $responseContent = $response->getContent();

        if (empty($responseContent)) {
            throw new \Exception('Empty response');
        }

        return $responseContent;
    }

    /**
     * @return string
     */
    private function getDummyData()
    {
        return file_get_contents(__DIR__ . '/../../../data/pos/rkeeper/rk7queryresult.xml');
    }
}
