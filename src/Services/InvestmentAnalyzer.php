<?php

namespace App\Services;

/**
 * Description of InvestmentAnalyzer
 *
 * @author grzonu
 */
class InvestmentAnalyzer
{

    /**
     *
     * @var NbpApi
     */
    protected $api;

    /**
     * 
     * @param \App\Services\NbpApi $api
     */
    public function __construct(NbpApi $api)
    {
        $this->api = $api;
    }
    
    /**
     * Calculate function extreme
     * 
     * @param array $data
     * @param \App\Services\callable $callback
     * @return array
     */
    protected function getExtreme(array $data, callable $callback): array
    {
        $indexes = array_keys($data);
        $values = array_values($data);
        $extreme = [];
        $i = 1;
        $size = count($values) - 1;
        while ($i < $size) {
            if ($callback($values[$i - 1], $values[$i], $values[$i + 1])) {
                $extreme[] = [
                    'date' => $indexes[$i],
                    'value' => $values[$i]
                ];
            }
            $i++;
        }

        return $extreme;
    }
    
    /**
     * Find biggest difference between local min/max
     * 
     * @param array $bottom
     * @param array $top
     * @return array
     */
    protected function findBestInvestment(array $bottom, array $top): array
    {
        $minIndex = 0;
        $maxIndex = 0;
        $diff = 0;
        foreach ($bottom as $bottomKey => $bottomRow) {
            $bottomVal = $bottomRow['value'];
            foreach ($top as $topKey => $topRow) {
                $topVal = $topRow['value'];
                if ($bottomRow['date'] < $topRow['date']
                    && $topVal - $bottomVal > 0
                    && $topVal - $bottomVal > $diff
                ) {
                    $diff = $topVal - $bottomVal;
                    $minIndex = $bottomKey;
                    $maxIndex = $topKey;
                }
            }
        }
        if ($diff == 0) {
            return [
                'diff' => 0
            ];
        }
        
        return [
            'diff' => $diff,
            'buy' => [
                'date' => $bottom[$minIndex]['date'],
                'value' => $bottom[$minIndex]['value']
            ],
            'sell' => [
                'date' => $top[$maxIndex]['date'],
                'value' => $top[$maxIndex]['value']
            ]
        ];
    }

    /**
     * Calculate the best investment time
     * 
     * @param \DateTime $start
     * @param \DateTime $end
     * @return array
     */
    public function getBestInvestment(\DateTime $start, \DateTime $end): array
    {
        $data = $this->api->getGoldValue($start, $end);
        $bottomExtreme = $this->getExtreme(
            $data,
            function ($prev, $curr, $next) {
                return ($curr < $prev && $curr < $next);
            }
        );
        $topExtreme = $this->getExtreme(
            $data,
            function ($prev, $curr, $next) {
                return ($curr > $prev && $curr > $next);
            }
        );
        return $this->findBestInvestment($bottomExtreme, $topExtreme);
    }
}
