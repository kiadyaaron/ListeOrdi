<?php

namespace App\Controller;

use App\Entity\ListeOrdi;
use App\Form\ListeOrdiForm;
use App\Repository\ListeOrdiRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/liste')]
final class ListeOrdiController extends AbstractController
{
    #[Route(name: 'app_liste_ordi_index', methods: ['GET'])]
public function index(Request $request, ListeOrdiRepository $listeOrdiRepository): Response
{
    $term = $request->query->get('q');
    $listeOrdis = $term ? $listeOrdiRepository->search($term) : $listeOrdiRepository->findAll();

    return $this->render('liste_ordi/index.html.twig', [
        'liste_ordis' => $listeOrdis,
        'query' => $term,
    ]);
}

    #[Route('/new', name: 'app_liste_ordi_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $listeOrdi = new ListeOrdi();
        $form = $this->createForm(ListeOrdiForm::class, $listeOrdi);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->calculateFields($listeOrdi);
            $entityManager->persist($listeOrdi);
            $entityManager->flush();

            return $this->redirectToRoute('app_liste_ordi_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('liste_ordi/new.html.twig', [
            'liste_ordi' => $listeOrdi,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_liste_ordi_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, ListeOrdi $listeOrdi, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ListeOrdiForm::class, $listeOrdi);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->calculateFields($listeOrdi);
            $entityManager->flush();

            return $this->redirectToRoute('app_liste_ordi_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('liste_ordi/edit.html.twig', [
            'liste_ordi' => $listeOrdi,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_liste_ordi_show', methods: ['GET'])]
    public function show(ListeOrdi $listeOrdi): Response
    {
        return $this->render('liste_ordi/show.html.twig', [
            'liste_ordi' => $listeOrdi,
        ]);
    }

    #[Route('/{id}', name: 'app_liste_ordi_delete', methods: ['POST'])]
    public function delete(Request $request, ListeOrdi $listeOrdi, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $listeOrdi->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($listeOrdi);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_liste_ordi_index', [], Response::HTTP_SEE_OTHER);
    }

    private function calculateFields(ListeOrdi $listeOrdi): void
    {
        $prixUnitaire = $listeOrdi->getPrixUnitaire();
        $coutJournalier = $listeOrdi->getCoutJournalierFixe();
        $dateDotation = $listeOrdi->getDateFirstDotation();

        if ($prixUnitaire > 0 && $coutJournalier > 0 && $dateDotation !== null) {
            $nbJoursFixe = intdiv($prixUnitaire, $coutJournalier);
            $dateFinAmort = (clone $dateDotation)->modify("+$nbJoursFixe days");

            $today = new \DateTime();
            $interval = $today->diff($dateFinAmort);
            $nbJoursRestants = ($dateFinAmort >= $today) ? $interval->days : 0;

            $prixAmort = $nbJoursRestants * $coutJournalier;

            $listeOrdi->setNbJourFixe($nbJoursFixe);
            $listeOrdi->setDateFinAmort($dateFinAmort);
            $listeOrdi->setNbJoursRestants($nbJoursRestants);
            $listeOrdi->setPrixAmort($prixAmort);
        }
    }
}
