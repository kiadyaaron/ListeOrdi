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
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

#[Route('/liste')]
final class ListeOrdiController extends AbstractController
{
    #[Route(name: 'app_liste_ordi_index', methods: ['GET'])]
public function index(Request $request, ListeOrdiRepository $listeOrdiRepository): Response
{
    $term = $request->query->get('q');
    $listeOrdis = $term ? $listeOrdiRepository->search($term) : $listeOrdiRepository->findAll();
    $nombreOrdi = $listeOrdiRepository->count([]);

    return $this->render('liste_ordi/index.html.twig', [
        'liste_ordis' => $listeOrdis,
        'query' => $term,
        'nombreOrdi' => $nombreOrdi,
    ]);
}

    #[Route('/export', name: 'app_liste_ordi_export', methods: ['GET'])]
public function export(ListeOrdiRepository $listeOrdiRepository): StreamedResponse
{
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    // En-têtes
    $headers = [
        'Date premier dotation', 'DA Odoo', 'Prix Unitaire', 'Cout Journalier Fixe',
        'Nb Jour Fixe', 'Date Fin Amort', 'Nb Jours Restants', 'Prix Amort',
        'IM', 'Détenteur', 'Fonction', 'Marque', 'NumSerie'
    ];
    $sheet->fromArray($headers, null, 'A1');

    // Données
    $liste = $listeOrdiRepository->findAll();
    $row = 2;

    foreach ($liste as $ordi) {
        $sheet->fromArray([
            $ordi->getDateFirstDotation()?->format('d-m-Y'),
            $ordi->getDaOdoo(),
            $ordi->getPrixUnitaire(),
            $ordi->getCoutJournalierFixe(),
            $ordi->getNbJourFixe(),
            $ordi->getDateFinAmort()?->format('d-m-Y'),
            $ordi->getNbJoursRestants(),
            $ordi->getPrixAmort(),
            $ordi->getIM(),
            $ordi->getDetenteur(),
            $ordi->getFonction(),
            $ordi->getMarque(),
            $ordi->getNumSerie(),
        ], null, 'A' . $row++);
    }

    $writer = new Xlsx($spreadsheet);

    $response = new StreamedResponse(function () use ($writer) {
        $writer->save('php://output');
    });

    $disposition = $response->headers->makeDisposition(
        ResponseHeaderBag::DISPOSITION_ATTACHMENT,
        'liste_ordinateurs.xlsx'
    );

    $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    $response->headers->set('Content-Disposition', $disposition);

    return $response;
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
        }
    }
}
