<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use App\Services\InvestmentAnalyzer;

class NbpBestInvestmentCommand extends Command
{

    /**
     * @var InvestmentAnalyzer
     */
    protected $analyzer;
    
    protected function configure()
    {
        $this
            ->setName('nbp:best-investment')
            ->setDescription('Calculate best investment')
            ->addArgument('start', InputArgument::REQUIRED, 'Begin of date range in YYYY-MM-DD format')
            ->addArgument('end', InputArgument::REQUIRED, 'End of date range in YYYY-MM-DD format')
            ->addArgument('value', InputArgument::REQUIRED, 'Investment value');
    }
    
    public function __construct(InvestmentAnalyzer $analyzer)
    {
        $this->analyzer = $analyzer;
        parent::__construct();
    }

    /**
     * 
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $startString = $input->getArgument('start');
        $endString = $input->getArgument('end');
        $value = floatval($input->getArgument('value'));
        $today = new \DateTime();
        $today->setTime(23,59,59);
        /**
         * @var $start \DateTime
         */
        $start = \DateTime::createFromFormat('Y-m-d', $startString);
        /**
         * @var $end \DateTime
         */
        $end = \DateTime::createFromFormat('Y-m-d', $endString);

        if (!is_object($start)) {
            $io->error("Start date invalid");
            
            return 1;
        }

        if (!is_object($end)) {
            $io->error("End date invalid");
            
            return 1;
        }
        
        if ($start->getTimestamp() > $end->getTimestamp()) {
            $io->error("Start must be greater than end");
            
            return 1;
        }
        
        if ($end->getTimestamp() > $today->getTimestamp()) {
            $io->error("End date must be before current date");
            
            return 1;
        }
        $result = $this->analyzer->getBestInvestment($start, $end);
        if ($result['diff'] == 0) {
            $io->error('There is no good investment in this date range');
            return 0;
        }
        $units = floor($value / $result['buy']['value']);
        $buyValue = $units * $result['buy']['value'];
        $sellValue = $units * $result['sell']['value'];
        $earn = $sellValue - $buyValue;
        $ror = ($sellValue - $buyValue) / ($buyValue) * 100;
        
        $io->writeln("Date of purchase: " . $result['buy']['date']);
        $io->writeln("Purchase unit-value: " . $result['buy']['value']);
        $io->writeln("Sale date: " . $result['sell']['date']);
        $io->writeln("Sale unit-value: " . $result['sell']['value']);
        $io->writeln("Buy/Sell difference: " . number_format($result['diff'], 2, '.', ' '));
        $io->writeln("Bought units: " . $units);
        $io->writeln("Purchase value: " . $buyValue);
        $io->writeln("Sale value: " . $sellValue);
        $io->writeln("Earnings: " . number_format($earn, 2, '.', ' '));
        $io->writeln("Rate of Return: " . number_format($ror, 2, '.', ' ') . '%');
    }
}
