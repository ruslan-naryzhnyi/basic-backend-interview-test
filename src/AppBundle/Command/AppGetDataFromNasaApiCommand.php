<?php

namespace AppBundle\Command;

use AppBundle\Entity\Asteroid;
use Doctrine\ORM\EntityManager;
use GuzzleHttp\Client;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Serializer\Encoder\JsonDecode;
use Symfony\Component\Serializer\Encoder\JsonEncoder;

class AppGetDataFromNasaApiCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('app:get-data-from-nasa-api');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $container = $this->getContainer();

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
            'end_date'   => $dateTo,
            'api_key'    => $apiKey,
        );

        $response = $client->request('GET', $apiUrl, ['query' => $data]);

        $jsonDecode = new JsonDecode();
        $content = $jsonDecode->decode($response->getBody()->getContents(), JsonEncoder::FORMAT, [
            'json_decode_associative' => true,
        ]);

        $result = $this->saveToDatabase($content);

        if ($result) {
            $output->writeln('success');
        } else {
            $output->writeln('error');
        }
    }

    /**
     * @param $content
     *
     * @return bool
     */
    private function saveToDatabase($content)
    {
        $container = $this->getContainer();
        /** @var EntityManager $em */
        $em = $container->get('doctrine')->getManager();

        if (!empty($content['near_earth_objects'])) {
            foreach ($content['near_earth_objects'] as $keyDate => $date) {
                foreach ($date as $key => $item) {
                    $asteroid = new Asteroid();
                    $asteroid->setDate(new \DateTime($keyDate));
                    $asteroid->setReference($item['neo_reference_id']);
                    $asteroid->setName($item['name']);
                    $asteroid->setSpeed($item['close_approach_data'][0]['relative_velocity']['kilometers_per_hour']);
                    $asteroid->setIsHazardous($item['is_potentially_hazardous_asteroid']);

                    // If this script was called every day,
                    // it might be worth making a validation for the existence of this record in the database
                    // The database already has indexes that verify the uniqueness of the record
                    $em->persist($asteroid);
                    $em->flush($asteroid);
                }
            }

            return true;
        }

        return false;
    }

}
