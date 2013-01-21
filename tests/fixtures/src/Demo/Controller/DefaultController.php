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
     * @Route("/form/horizontal-layout")
     * @Template("DemoBundle:Default:horizontal-layout.html.twig")
     */
    public function formHorizontalLayoutAction()
    {
        return array(
            'form'=>$this->getForm()->createView()
        );
    }
    
    public function getForm()
    {
        
        return $this->createForm(new FormType());
    }
}
