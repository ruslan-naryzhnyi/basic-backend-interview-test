<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction()
    {
        return new JsonResponse(['hello' => 'world']);
    }

    /**
     * @Route("/neo/hazardous", name="app_neo_hazardous")
     *
     * @return JsonResponse
     */
    public function hazardousAction()
    {
        $asteroidRepo = $this->getDoctrine()->getRepository('AppBundle:Asteroid');

        $asteroids = $asteroidRepo->findHazardous(true);

        return new JsonResponse($asteroids);
    }

    /**
     * @Route("/neo/fastest", name="app_neo_fastest")
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function fastestAction(Request $request)
    {
        $asteroidRepo = $this->getDoctrine()->getRepository('AppBundle:Asteroid');

        $isHazardous = filter_var($request->get('hazardous'), FILTER_VALIDATE_BOOLEAN);

        $asteroids = $asteroidRepo->findFastedAsteroid($isHazardous);

        return new JsonResponse($asteroids);
    }
}
