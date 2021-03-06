<?php

namespace Demo\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Demo\Form\FormType;

class DefaultController extends Controller
{
    /**
     * @Route("/")
     * @Template()
     */
    public function indexAction()
    {
        return $this->render('DemoBundle:Default:index.html.twig');
    }
    
    /**
     * @Route("/error_page")
     * @Template()
     */
    public function errorAction()
    {
        throw new \Exception("Hello World");
    }
    
    public function redirectAction()
    {
        return $this->redirect('http://www.example.com');
    }
}
