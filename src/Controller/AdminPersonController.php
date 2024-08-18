<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use TeiEditionBundle\Entity\Person;
use App\Form\AdminPersonType;

#[Route(path: '/admin/person')]
class AdminPersonController extends AbstractController
{
    #[Route(path: '/', name: 'app_admin_person_index', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $people = $entityManager
            ->getRepository(Person::class)
            ->findBy([], [
                'familyName' => 'ASC',
                'givenName' => 'ASC',
            ]);

        return $this->render('Admin/Person/index.html.twig', [
            'people' => $people,
        ]);
    }

    #[Route(path: '/new', name: 'app_admin_person_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $person = new Person();
        $form = $this->createForm(AdminPersonType::class, $person);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($person);
            $entityManager->flush();

            return $this->redirectToRoute('app_admin_person_show', [
                    'id' => $person->getId(),
                ], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('Admin/Person/new.html.twig', [
            'person' => $person,
            'form' => $form,
        ]);
    }

    #[Route(path: '/{id}', name: 'app_admin_person_show', methods: ['GET'])]
    public function show(Person $person): Response
    {
        return $this->render('Admin/Person/show.html.twig', [
            'person' => $person,
        ]);
    }

    #[Route(path: '/{id}/edit', name: 'app_admin_person_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Person $person, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(AdminPersonType::class, $person);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_admin_person_show', [
                    'id' => $person->getId(),
                ], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('Admin/Person/edit.html.twig', [
            'person' => $person,
            'form' => $form,
        ]);
    }

    #[Route(path: '/{id}', name: 'app_admin_person_delete', methods: ['POST'])]
    public function delete(Request $request, Person $person, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$person->getId(), $request->request->get('_token'))) {
            $entityManager->remove($person);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_admin_person_index', [], Response::HTTP_SEE_OTHER);
    }
}
