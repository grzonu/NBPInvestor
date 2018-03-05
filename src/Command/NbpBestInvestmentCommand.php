<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use GuzzleHttp\Client;

class NbpBestInvestmentCommand extends Command {

    /**
     * @var Client 
     */
    protected $client;
    
    protected function configure() {
        $this
                ->setName('nbp:best-investment')
                ->setDescription('Calculate best investment')
                ->addArgument('start', InputArgument::REQUIRED, 'Begin of date range in YYYY-MM-DD format')
                ->addArgument('end', InputArgument::REQUIRED, 'End of date range in YYYY-MM-DD format')
                ->addArgument('value', InputArgument::OPTIONAL, 'Investment value')
        ;
    }
    
    public function __construct(Client $client) {
        $this->client = $client;
        parent::__construct();
    }    
    
    protected function execute(InputInterface $input, OutputInterface $output) {
        $io = new SymfonyStyle($input, $output);
        $startString = $input->getArgument('start');
        $endString = $input->getArgument('end');
        $value = floatval($input->getArgument('value'));
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
        

    }

}
