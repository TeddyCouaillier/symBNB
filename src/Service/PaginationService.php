<?php

namespace App\Service;

use Twig\Environment;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\HttpFoundation\RequestStack;

class PaginationService {
    private $entityClass;
    private $limit = 10;
    private $currentPage = 1;
    private $manager;
    private $twig;
    private $route;
    private $templatePath;

    public function __construct(ObjectManager $manager, Environment $twig, RequestStack $request, $templatePath)
    {
        $this->manager      = $manager;
        $this->twig         = $twig;
        $this->route        = $request->getCurrentRequest()->attributes->get('_route');
        $this->templatePath = $templatePath;
    }

    public function display()
    {
        $this->twig->display($this->templatePath, [
            'page'  => $this->currentPage,
            'pages' => $this->getPages(),
            'route' => $this->route
        ]);
    }

    public function getData()
    {
        if(empty($this->entityClass))
            throw new \Exception("Vous n'avez pas spécifié l'entité de la pagination");
        
        $offset = $this->currentPage * $this->limit - $this->limit;
        $repo   = $this->manager->getRepository($this->entityClass);
        $data   = $repo->findBy([], [], $this->limit, $offset);

        return $data;
    }

    public function setRoute($route)
    {
        $this->route = $route;

        return $this;
    }

    public function getRoute()
    {
        return $this->route;
    }

    public function setTemplatePath($templatePath)
    {
        $this->templatePath = $templatePath;

        return $this;
    }

    public function getTemplatePath()
    {
        return $this->templatePath;
    }

    public function getPages()
    {
        if(empty($this->entityClass))
            throw new \Exception("Vous n'avez pas spécifié l'entité de la pagination");
    
        $repo  = $this->manager->getRepository($this->entityClass);
        $total = count($repo->findAll());

        return ceil($total / $this->limit);
    }

    public function setPage($page)
    {
        $this->currentPage = $page;

        return $this;
    }

    public function getPage()
    {
        return $this->currentPage;
    }

    public function setLimit($limit)
    {
        $this->limit = $limit;

        return $this;
    }

    public function getLimit()
    {
        return $this->limit;
    }

    public function setEntityClass($entityClass) 
    {
        $this->entityClass = $entityClass;

        return $this;
    }

    public function getEntityClass()
    {
        return $this->entityClass;
    }
}