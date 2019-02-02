<?php

namespace App\Controller;

use App\Entity\Collection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class CollectionController extends AbstractController
{
    /**
     * @Route("/collections", name="collections")
     */
    public function _index()
    {
        $repositoryCollection = $this->getDoctrine()->getRepository(Collection::class);

        $collections =  $repositoryCollection->findAll();
        return $this->render('collection/index.html.twig', [
            'collections' => $collections,
        ]);
    }

    /**
     * @Route("/collection/{slug}", name="collection")
     */
    public function index($slug)
    {

        $repositoryCollection = $this->getDoctrine()->getRepository(Collection::class);

        /** @var Collection $collection */
        $collection =  $repositoryCollection->findOneBy(['id'=>$slug]);
        return $this->render('collection/collection.html.twig', [
            'collection' => $collection,
        ]);
    }

    public function partial()
    {

        $repositoryCollection = $this->getDoctrine()->getRepository(Collection::class);

        /** @var Collection $collection */
        $collection =  $repositoryCollection->findAll();


        return $this->render('partials/collection.html.twig',['collections'=>$collection]);
    }

}
