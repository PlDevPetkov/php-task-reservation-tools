<?php

namespace App\Pos\Providers;

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
     * @return array
     * @throws \Exception
     */
    protected function retrieveOrders($fromDate): array
    {
        $xmlResult = $this->config['should_use_dummy_data']
            ? $this->getDummyData()
            : $this->retrieveXmlRequest($fromDate);

        $orders = new \SimpleXMLElement($xmlResult);
        $mappedOrders = [];

        foreach ($orders->Visit as $visit) {
            $visitId = (int) $visit->attributes()->VisitID;

            foreach ($visit->Orders->Order as $order) {
                $orderObj = new \stdClass();
                $orderObj->provider_id = (int) $order['OrderID'];
                $orderObj->reservation_id = $visitId;
                $orderObj->attributes = json_encode($order->attributes());

                $mappedOrders[] = $orderObj;
            }
        }

        return $mappedOrders;
    }

    /**
     * @param \DateTime|null $fromDate
     * @return string
     * @throws \Exception
     */
    private function retrieveXmlRequest($fromDate)
    {
        // TODO: Implement proper filtering
        $xml = sprintf('
<?xml version="1.0" encoding="windows-1251"?>
<RK7Query>
 <RK7Command CMD="GetOrderList" ><Visit><Orders><Order createTime="%s"></Order></Orders></Visit></RK7Command>
</RK7Query>
', $fromDate->format('Y-m-d H:i:s'));

        $host = $this->config['host'];
        $user = $this->config['user'];
        $password = $this->config['password'];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $host);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // For self-signed certificates
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: text/xml'));
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
        curl_setopt($ch, CURLOPT_USERPWD, "$user:$password");

        $response = (string) curl_exec($ch);

        if (curl_errno($ch)) {
            throw new \Exception('Curl error: ' . curl_error($ch));
        }

        if (empty($response)) {
            throw new \Exception('Empty response');
        }

        curl_close($ch);

        return $response;
    }

    /**
     * @return string
     */
    private function getDummyData()
    {
        return file_get_contents(__DIR__ . '/../../../data/pos/rkeeper/rk7queryresult.xml');
    }
}
