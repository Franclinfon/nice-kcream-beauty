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
            ->add('rue', null, [
                'label' => 'Adresse',
                'attr' => [
                    'placeholder' => 'Ex : 1 rue Léon Pavot',
                    'class' => 'form-control',
                ],
            ])
            ->add('complement', null, [
                'label' => 'Complément d\'adresse (optionnel)',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Ex : Bât. B, Appt 12',
                    'class' => 'form-control',
                ],
            ])
            ->add('codePostal', null, [
                'label' => 'Code postal',
                'attr' => [
                    'placeholder' => 'Ex : 49100',
                    'class' => 'form-control',
                ],
            ])
            ->add('ville', null, [
                'label' => 'Ville',
                'attr' => [
                    'placeholder' => 'Ex : Angers',
                    'class' => 'form-control',
                ],
            ])
            ->add('pays', null, [
                'label' => 'Pays',
                'attr' => [
                    'placeholder' => 'Ex : France',
                    'class' => 'form-control',
                ],
            ])
            ->add('isDefault', null, [
                'label' => 'Définir comme adresse par défaut',
                'required' => false,
                'attr' => ['class' => 'form-check-input'],
            ])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // On utilise l'adresse comme libellé par défaut
            $address->setLabel($address->getRue());
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