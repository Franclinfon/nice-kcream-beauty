<?php

namespace App\Controller\Admin;

use App\Entity\Order;
use App\Entity\OrderStatusHistory;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class OrderCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Order::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Commande')
            ->setEntityLabelInPlural('Commandes')
            ->setDefaultSort(['createdAt' => 'DESC'])
            ->setPageTitle('index', 'Gestion des commandes')
            ->setPageTitle('detail', fn (Order $order) => 'Commande ' . $order->getNumeroCommande());
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->onlyOnIndex(),

            TextField::new('numeroCommande', 'N° commande'),

            AssociationField::new('client', 'Client'),

            ChoiceField::new('statut', 'Statut')
                ->setChoices([
                    'En attente de paiement' => 'en_attente_paiement',
                    'Payée'                  => 'payee',
                    'En préparation'         => 'en_preparation',
                    'Expédiée'               => 'expediee',
                    'Livrée'                 => 'livree',
                    'Annulée'                => 'annulee',
                ])
                ->renderAsBadges([
                    'en_attente_paiement' => 'warning',
                    'payee'               => 'info',
                    'en_preparation'      => 'primary',
                    'expediee'            => 'success',
                    'livree'              => 'success',
                    'annulee'             => 'danger',
                ]),

            MoneyField::new('montantTotal', 'Total')
                ->setCurrency('EUR')
                ->onlyOnIndex(),

            TextField::new('deliveryMethod', 'Livraison')
                ->formatValue(fn ($value) => $value === 'colissimo' ? '🚚 Colissimo' : '🏪 Retrait boutique')
                ->onlyOnDetail(),

            TextField::new('livraisonNom', 'Nom livraison')->onlyOnDetail(),
            TextField::new('livraisonRue', 'Adresse')->onlyOnDetail(),
            TextField::new('livraisonCodePostal', 'Code postal')->onlyOnDetail(),
            TextField::new('livraisonVille', 'Ville')->onlyOnDetail(),
            TextField::new('livraisonEmail', 'Email client')->onlyOnDetail(),
            TextField::new('livraisonTelephone', 'Téléphone')->onlyOnDetail(),

            DateTimeField::new('createdAt', 'Date')
                ->setFormat('dd/MM/yyyy HH:mm'),
        ];
    }

    public function configureActions(Actions $actions): Actions
    {
        $updateStatus = Action::new('updateStatus', 'Changer le statut', 'fa fa-edit')
            ->linkToRoute('admin_order_update_status', fn (Order $order) => ['id' => $order->getId()])
            ->addCssClass('btn btn-primary btn-sm');

        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->add(Crud::PAGE_DETAIL, $updateStatus)
            ->remove(Crud::PAGE_INDEX, Action::NEW)
            ->remove(Crud::PAGE_INDEX, Action::EDIT)
            ->remove(Crud::PAGE_DETAIL, Action::EDIT)
            ->remove(Crud::PAGE_INDEX, Action::DELETE);
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(ChoiceFilter::new('statut')->setChoices([
                'En attente de paiement' => 'en_attente_paiement',
                'Payée'                  => 'payee',
                'En préparation'         => 'en_preparation',
                'Expédiée'               => 'expediee',
                'Livrée'                 => 'livree',
                'Annulée'                => 'annulee',
            ]));
    }

    #[Route('/admin/order/{id}/update-status', name: 'admin_order_update_status')]
    public function updateStatus(
        int $id,
        Request $request,
        EntityManagerInterface $entityManager,
        AdminUrlGenerator $adminUrlGenerator,
    ): Response {
        $order = $entityManager->getRepository(Order::class)->find($id);

        if (!$order) {
            throw $this->createNotFoundException('Commande introuvable.');
        }

        $statuts = [
            'en_attente_paiement' => 'En attente de paiement',
            'payee'               => 'Payée',
            'en_preparation'      => 'En préparation',
            'expediee'            => 'Expédiée',
            'livree'              => 'Livrée',
            'annulee'             => 'Annulée',
        ];

        if ($request->isMethod('POST')) {
            $newStatut = $request->request->get('statut');
            $commentaire = $request->request->get('commentaire', '');

            if (isset($statuts[$newStatut]) && $newStatut !== $order->getStatut()) {
                $order->setStatut($newStatut);

                $history = new OrderStatusHistory();
                $history->setCommande($order);
                $history->setStatut($newStatut);
                $history->setDate(new \DateTimeImmutable());
                $history->setCommentaire($commentaire ?: 'Statut mis à jour par l\'administrateur.');
                $entityManager->persist($history);
                $entityManager->flush();

                $this->addFlash('success', 'Statut mis à jour : ' . $statuts[$newStatut]);
            }

            $url = $adminUrlGenerator
                ->setController(OrderCrudController::class)
                ->setAction(Action::DETAIL)
                ->setEntityId($order->getId())
                ->generateUrl();

            return $this->redirect($url);
        }

        return $this->render('admin/order_update_status.html.twig', [
            'order'  => $order,
            'statuts' => $statuts,
        ]);
    }
}