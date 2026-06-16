<?php

namespace App\Controller\Front;

use App\Entity\Address;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
final class AccountController extends AbstractController
{
    #[Route('/mon-compte', name: 'app_front_account_index')]
    public function index(): Response
    {
        return $this->render('front/account/index.html.twig');
    }

    #[Route('/mon-compte/adresses', name: 'app_front_account_addresses')]
    public function addresses(): Response
    {
        $user = $this->getUser();

        return $this->render('front/account/addresses.html.twig', [
            'addresses' => $user->getAddresses(),
        ]);
    }

    #[Route('/mon-compte/adresses/nouvelle', name: 'app_front_account_address_new')]
    public function newAddress(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        $address = new Address();
        $address->setClient($user);

        $form = $this->createFormBuilder($address)
            ->add('label')
            ->add('rue')
            ->add('complement', null, ['required' => false])
            ->add('codePostal', null, ['label' => 'Code postal'])
            ->add('ville')
            ->add('pays')
            ->add('isDefault', null, ['label' => 'Adresse par défaut', 'required' => false])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($address);
            $entityManager->flush();

            $this->addFlash('success', 'Adresse ajoutée avec succès.');

            return $this->redirectToRoute('app_front_account_addresses');
        }

        return $this->render('front/account/address_form.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/mon-compte/adresses/{id}/supprimer', name: 'app_front_account_address_delete', methods: ['POST'])]
    public function deleteAddress(int $id, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        $address = $entityManager->getRepository(Address::class)->find($id);

        if ($address && $address->getClient() === $user) {
            $entityManager->remove($address);
            $entityManager->flush();
            $this->addFlash('success', 'Adresse supprimée.');
        }

        return $this->redirectToRoute('app_front_account_addresses');
    }
}