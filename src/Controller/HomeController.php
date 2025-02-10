<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController extends AbstractController
{



    
    #[Route('/home', name: 'home')]
    public function index(): Response
    {
        return $this->render('home/Home.html.twig', [
            'controller_name' => 'HomeController',
        ]);
    }

    #[Route('/services', name: 'services')]
    public function index2(): Response
    {
        return $this->render('services/services.html.twig', [
            'controller_name' => 'HomeController',
        ]);
    }
    #[Route('/doctors', name: 'doctors')]
    public function index3(): Response
    {
        return $this->render('doctors/doctors.html.twig', [
            'controller_name' => 'HomeController',
        ]);
    }

    #[Route('/appointment', name: 'appointment')]
    public function index4(): Response
    {
        return $this->render('appointment/appointment.html.twig', [
            'controller_name' => 'HomeController',
        ]);
    }
    #[Route('/testimonial', name: 'testimonial')]
    public function index5(): Response
    {
        return $this->render('testimonial/testimonial.html.twig', [
            'controller_name' => 'HomeController',
        ]);
    }

    #[Route('/contact', name: 'contact')]
    public function index6(): Response
    {
        return $this->render('contact/contact.html.twig', [
            'controller_name' => 'HomeController',
        ]);}

        #[Route('/dashboard', name: 'dashboard')]
        public function index7(): Response
        {
            return $this->render('dashboard/index.html.twig', [
                'controller_name' => 'HomeController',
            ]);
    }
    #[Route('/dashboard2', name: 'dashboard2')]
    public function index8(): Response
    {
        return $this->render('dashboard2/index.html.twig', [
            'controller_name' => 'HomeController',
        ]);
}



}
