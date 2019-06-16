<?php

namespace App\Service;

use Twig\Environment;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\HttpFoundation\RequestStack;

class PaginationService {
    /**
     * Classe utilisé pour la pagination
     *
     * @var Object 
     */
    private $entityClass;

    /**
     * Nombre d'item par page
     *
     * @var Integer 
     */
    private $limit = 10;

    /**
     * Page courrante lors de la pagination
     *
     * @var Integer 
     */
    private $currentPage = 1;

    /**
     * @var ObjectManager 
     */
    private $manager;

    /**
     * Environnement Twig
     * 
     * @var Environment 
     */
    private $twig;

    /**
     * Route spécifique à la pagination (différents liens)
     *
     * @var String
     */
    private $route;

    /**
     * Route du template twig
     *
     * @var String
     */
    private $templatePath;
    
    public function getEntityClass(): ?Object 
    {
        return $this->entityClass;
    }

    public function setEntityClass(Object $entityClass): self
    {
        $this->entityClass = $entityClass;

        return $this;
    }

    public function getLimit(): ?int
    {
        return $this->limit;
    }

    public function setLimit(int $limit): self
    {
        $this->limit = $limit;

        return $this;
    }
    
    public function getPage(): ?int
    {
        return $this->currentPage;
    }

    public function setPage(int $page): self
    {
        $this->currentPage = $page;

        return $this;
    }

    public function getRoute(): ?string
    {
        return $this->route;
    }

    public function setRoute(String $route): self
    {
        $this->route = $route;

        return $this;
    }

    public function getTemplatePath(): ?string
    {
        return $this->templatePath;
    }

    public function setTemplatePath(string $templatePath): self
    {
        $this->templatePath = $templatePath;

        return $this;
    }

    /**
     * Cosntructeur
     * 
     * @param ObjectManager $manager
     * @param Environment $twig
     * @param RequestStack $request
     * @param String $templatePath
     */
    public function __construct(ObjectManager $manager, Environment $twig, RequestStack $request, $templatePath)
    {
        $this->manager      = $manager;
        $this->twig         = $twig;
        $this->route        = $request->getCurrentRequest()->attributes->get('_route');
        $this->templatePath = $templatePath;
    }

    /**
     * Affiche la pagination spécifique dans le twig
     *
     * @return void
     */
    public function display()
    {
        $this->twig->display($this->templatePath, [
            'page'  => $this->currentPage,
            'pages' => $this->getPages(),
            'route' => $this->route
        ]);
    }

    /**
     * Retourne la requête de la pagination suivant la limite et le repository 
     *
     * @return Object liste des items par page
     */
    public function getData()
    {
        if(empty($this->entityClass))
            throw new \Exception("Vous n'avez pas spécifié l'entité de la pagination");
        
        $offset = $this->currentPage * $this->limit - $this->limit;
        $repo   = $this->manager->getRepository($this->entityClass);
        $data   = $repo->findBy([], [], $this->limit, $offset);

        return $data;
    }

    /**
     * Retourne le nombre de page total
     *
     * @return Integer
     */
    public function getPages()
    {
        if(empty($this->entityClass))
            throw new \Exception("Vous n'avez pas spécifié l'entité de la pagination");
    
        $repo  = $this->manager->getRepository($this->entityClass);
        $total = count($repo->findAll());

        return ceil($total / $this->limit);
    }

}