<?php

namespace App\Controller;

use App\Entity\Categories;
use App\Entity\Series;
use App\Form\CategorieType;
use App\Form\SerieType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;

class MainController extends AbstractController
{
    /**
     * @Route("/", name="index")
     */
    public function index(Request $request, EntityManagerInterface $entityManager)
    {
        $categorie = new Categories();

        $categoriesRepository = $this->getDoctrine()
                                ->getRepository(Categories::class)
                                ->findAll();

        $series = $this->getDoctrine()
                        ->getRepository(Series::class)   
                        ->findAll();                     

        $form = $this->createForm(CategorieType::class, $categorie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()){

            $categorie = $form->getData();

            $entityManager->persist($categorie);
            $entityManager->flush();

            $this->redirectToRoute('index');
        }

        return $this->render('main/index.html.twig', [
            'categories' => $categoriesRepository,
            'addCategorie' => $form->createView(),
            'series' => $series,
        ]);
    }

    /**
     * @Route("/series", name="series")
     */
    public function series(Request $request, EntityManagerInterface $entityManager)
    {   
        $serie = new Series();

        $seriesRepository = $this->getDoctrine()
                                ->getRepository(Series::class)
                                ->findAll();
        
        $categories = $this->getDoctrine()
                            ->getRepository(Categories::class)
                            ->findAll();

        if (!is_null($serie->getAffiche())) {
            $serie->setAffiche(new File($this->getParameter('upload_files').'/'.$serie->getAffiche()));
        }

        $formSerie = $this->createForm(SerieType::class, $serie);
        $formSerie->handleRequest($request);

        if ($formSerie->isSubmitted() && $formSerie->isValid()){

            $serie = $formSerie->getData();

            $categorie = $this->getDoctrine()
                    ->getRepository(Categories::class)
                    ->find($request->request->get('categorie'));

            $affiche = $serie->getAffiche();
            $afficheName = md5(uniqid()).'.'.$affiche->guessExtension();
            $affiche->move($this->getParameter('upload_files') ,
            $afficheName);
            $serie ->setAffiche($afficheName);

            $serie->setCategorie($categorie);

            $entityManager->persist($serie);
            $entityManager->flush();

            $this->redirectToRoute('series');
        }

        return $this->render('main/series.html.twig', [
            'series' => $seriesRepository,
            'addSeries' => $formSerie->createView(),
            'categories' => $categories
        ]);
    }

    /**
     * @Route("/serie/fiche/{id}", name="showSerie")
     */

    public function showSerie($id, Request $request, EntityManagerInterface $entityManager)
    {   
        $singleSerie = $this->getDoctrine()
                            ->getRepository(Series::class)
                            ->find($id);
               
        
        $categories = $this->getDoctrine()
                    ->getRepository(Categories::class)
                    ->findAll();

        $form = $this->createForm(SerieType::class, $singleSerie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()){

            $singleSerie = $form->getData();

            $entityManager->persist($singleSerie);
            $entityManager->flush();

            $this->redirectToRoute('series');
        }

        return $this->render('main/singleSerie.html.twig', [
            'serie' => $singleSerie,
            'updateSerie' => $form->createView(),
            'categories' => $categories
        ]);
    }

    /**
     * @Route("/serie/remove/{id}", name="removeSerie")
     */
    public function removeSerie($id, EntityManagerInterface $entityManager){
        $serie = $this->getDoctrine()->getRepository(Series::class)->find($id);

        $entityManager->remove($serie);
        $entityManager->flush();

        return $this->redirectToRoute('series');
    }

    /**
     * @Route("/categorie/{id}", name="categorie")
     */
    public function categorie($id, Request $request, EntityManagerInterface $entityManager)
    {   
        $categorie = $this->getDoctrine()
                            ->getRepository(Categories::class)
                            ->find($id);
               

        $form = $this->createForm(CategorieType::class, $categorie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()){

            $categorie = $form->getData();

            $entityManager->persist($categorie);
            $entityManager->flush();

            $this->redirectToRoute('index');
        }

        return $this->render('main/categorie.html.twig', [
            'categorie' => $categorie,
            'updateCategorie' => $form->createView(),
        ]);
    }

    /**
     * @Route("/categorie/remove/{id}", name="removeCategorie")
     */
    public function removeCategorie($id, EntityManagerInterface $entityManager){
        $categorie = $this->getDoctrine()->getRepository(Categories::class)->find($id);

        $entityManager->remove($categorie);
        $entityManager->flush();

        return $this->redirectToRoute('index');
    }

}
