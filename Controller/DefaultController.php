<?php

namespace StarterKit\CrudBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {
        return $this->render('StarterKitCrudBundle:Default:index.html.twig');
    }
}
