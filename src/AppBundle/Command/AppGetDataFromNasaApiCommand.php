<?php

namespace AppBundle\Command;

use AppBundle\Entity\Asteroid;
use Doctrine\ORM\EntityManager;
use GuzzleHttp\Client;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Serializer\Encoder\JsonDecode;
use Symfony\Component\Serializer\Encoder\JsonEncoder;

class AppGetDataFromNasaApiCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('app:get-data-from-nasa-api')
            ->setDescription('...')
            ->addArgument('argument', InputArgument::OPTIONAL, 'Argument description')
            ->addOption('option', null, InputOption::VALUE_NONE, 'Option description');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $container = $this->getContainer();
        $argument = $input->getArgument('argument');

        if ($input->getOption('option')) {
            // ...
        }

        $apiUrl = $container->getParameter('api.nasa.gov.rest.url');
        $apiKey = $container->getParameter('api.nasa.gov.key');

        // create our http client (Guzzle)
        $client = new Client();

        $datetime = new \DateTime();
        $dateTo = $datetime->format('Y-m-d');
        $datetime->modify('-2 day');
        $dateFrom = $datetime->format('Y-m-d');

        $data = array(
            'start_date' => $dateFrom,
            'end_date' => $dateTo,
            'api_key' => $apiKey,
        );

        $response = $client->request('GET', $apiUrl, ['query' => $data]);

        $result = $this->saveToDatabase($response->getBody()->getContents());

        if ($result) {
            $output->writeln('success');
        } else {
            $output->writeln('error');
        }
    }

    /**
     * @param $contents
     * @return bool
     */
    private function saveToDatabase($contents)
    {
        $container = $this->getContainer();
        /** @var EntityManager $em */
        $em = $container->get('doctrine')->getManager();

        $jsonDecode = new JsonDecode();
        $content = $jsonDecode->decode($contents, JsonEncoder::FORMAT);

        if (!empty($content->near_earth_objects)) {
            foreach ($content->near_earth_objects as $keyDate => $date) {
                foreach ($date as $key => $item) {
                    $asteroid = new Asteroid();
                    $asteroid->setDate(new \DateTime($keyDate));
                    $asteroid->setReference($item->neo_reference_id);
                    $asteroid->setName($item->name);
                    $asteroid->setSpeed($item->close_approach_data[0]->relative_velocity->kilometers_per_hour);
                    $asteroid->setIsHazardous($item->is_potentially_hazardous_asteroid);

                    $em->persist($asteroid);
                    $em->flush($asteroid);
                }
            }

            return true;
        }

        return false;
    }

}
