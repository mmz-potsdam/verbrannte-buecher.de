<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use TeiEditionBundle\Entity\Organization;
use App\Form\AdminOrganizationType;

#[Route(path: '/admin/organization')]
class AdminOrganizationController extends AbstractController
{
    #[Route(path: '/', name: 'app_admin_organization_index', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $results = $entityManager
            ->getRepository(Organization::class)
            ->findBy([], [
                'name' => 'ASC',
            ]);

        return $this->render('Admin/Organization/index.html.twig', [
            'results' => $results,
        ]);
    }

    #[Route(path: '/new', name: 'app_admin_organization_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $organization = new Organization();
        $form = $this->createForm(AdminOrganizationType::class, $organization);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($organization);
            $entityManager->flush();

            return $this->redirectToRoute('app_admin_organization_show', [
                'id' => $organization->getId(),
            ], Response::HTTP_SEE_OTHER);
        }

        return $this->render('Admin/Organization/new.html.twig', [
            'organization' => $organization,
            'form' => $form,
        ]);
    }

    #[Route(path: '/{id}', name: 'app_admin_organization_show', methods: ['GET'])]
    public function show(Organization $organization): Response
    {
        return $this->render('Admin/Organization/show.html.twig', [
            'organization' => $organization,
        ]);
    }

    #[Route(path: '/{id}/edit', name: 'app_admin_organization_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Organization $organization, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(AdminOrganizationType::class, $organization);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_admin_organization_show', [
                'id' => $organization->getId(),
            ], Response::HTTP_SEE_OTHER);
        }

        return $this->render('Admin/Organization/edit.html.twig', [
            'organization' => $organization,
            'form' => $form,
        ]);
    }

    #[Route(path: '/{id}', name: 'app_admin_organization_delete', methods: ['POST'])]
    public function delete(Request $request, Organization $organization, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $organization->getId(), $request->request->get('_token'))) {
            $entityManager->remove($organization);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_admin_organization_index', [], Response::HTTP_SEE_OTHER);
    }
}
