<?php

namespace App\Controller;

use App\Entity\Ad;
use App\Form\AdType;
use App\Entity\Image;
use App\Repository\AdRepository;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

class AdController extends AbstractController
{
    /**
     * Affiche la liste des annonces
     * @Route("/ads", name="ad_index")
     * @return Response
     */
    public function index(AdRepository $rep)
    {
        $ads = $rep->findAll();
        
        return $this->render('ad/index.html.twig', [
            'ads' => $ads
        ]);
    }

    /**
     * Permet de créer une annonce
     * @Route("/ads/news", name="ad_create")
     * @return Response
     */
    public function create(Request $request, ObjectManager $manager)
    {
        $ad = new Ad();

        $form = $this->createForm(AdType::class,$ad);

        $form->handleRequest($request);
        
        dump($ad->getImages());
        if($form->isSubmitted() && $form->isValid())
        {   
            foreach($ad->getImages() as $image){
                $image->setAd($ad);
                $manager->persist($image);
            }

            $manager->persist($ad);
            $manager->flush();

            $this->addFlash(
                'success',
                "L'annonce <strong>{$ad->getTitle()}</strong> a bien été enregistrée"
            );

            return $this->redirectToRoute('ad_show',[
                'slug' => $ad->getSlug()
            ]);
        }

        return $this->render('ad/new.html.twig',[
            "form" => $form->createView()
        ]);
    }

    /**
     * Permet de modifier l'annonce
     * @Route("/ads/{slug}/edit", name="ad_edit")
     * @return Response
     */
    public function edit(Ad $ad, Request $request, ObjectManager $manager)
    {
        $form = $this->createForm(AdType::class,$ad);

        $form->handleRequest($request);
        
        if($form->isSubmitted() && $form->isValid())
        {   
            foreach($ad->getImages() as $image){
                $image->setAd($ad);
                $manager->persist($image);
            }

            $manager->persist($ad);
            $manager->flush();

            $this->addFlash(
                'success',
                "L'annonce <strong>{$ad->getTitle()}</strong> a bien été modifiée"
            );

            return $this->redirectToRoute('ad_show',[
                'slug' => $ad->getSlug()
            ]);
        }

        return $this->render('ad/edit.html.twig',[
            'form' => $form->createView(),
            'ad' => $ad
        ]);
    }

    /**
     * Affiche une seule annonce
     * @Route("/ads/{slug}", name="ad_show")
     * @return Response
     */
    public function show(Ad $ad)
    {
        dump($ad);
        return $this->render('ad/show.html.twig', [
            'ad' => $ad
        ]);
    }


}
