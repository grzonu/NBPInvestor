<?php

namespace App\Services;

use GuzzleHttp\Client;

/**
 * Helper for collect api data
 *
 * @author grzonu
 */
class NbpApi
{

    const MAX_DAYS = 93;

    /**
     * @var Client
     */
    protected $client;
    /**
     * 
     * @param Client $client
     */
    public function __construct(Client $client)
    {
        $this->client = $client;
    }
    /**
     * Download one package of gold values
     * 
     * @param \DateTime $start
     * @param \DateTime $end
     * @return array
     * @throws \Exception
     */
    protected function getValuePack(\DateTime $start, \DateTime $end): array
    {
        $response = $this->client->get('/api/cenyzlota/' . $start->format('Y-m-d') . '/' . $end->format('Y-m-d') . '/');
        if ($response->getStatusCode() != 200) {
            throw new \Exception("Api communication error");
        }
        $data = json_decode($response->getBody(), true);
        return $data;
    }
    
    /**
     * Calculate minimum date 
     * 
     * @param \DateTime $date1
     * @param \DateTime $date2
     * @return \DateTime
     */
    protected function getMinDate(\DateTime $date1, \DateTime $date2): \DateTime
    {
        if ($date1->getTimestamp() > $date2->getTimestamp()) {
            return $date2;
        }
        return $date1;
    }
    
    /**
     * Reindex array to date => value
     * 
     * @param array $input
     * @return array
     */
    protected function reindexData(array $input): array
    {
        $data = [];
        foreach ($input as $row) {
            $data[$row['data']] = $row['cena'];
        }
        return $data;
    }
    
    /**
     * Return full data about gold value in date range
     * 
     * @param \DateTime $start
     * @param \DateTime $end
     * @return array
     */
    public function getGoldValue(\DateTime $start, \DateTime $end): array
    {
        $beginRange = clone $start;
        $returnData = [];
        while ($beginRange->getTimestamp() < $end->getTimestamp()) {
            $endRange = clone $beginRange;
            $endRange->modify('+' . self::MAX_DAYS . ' DAY');
            $endRange = $this->getMinDate($end, $endRange);
            $pack = $this->getValuePack($beginRange, $endRange);
            $returnData = array_merge($returnData, $pack);
            $beginRange->modify('+' . self::MAX_DAYS . ' DAY');
        }
        
        return $this->reindexData($returnData);
    }
}
