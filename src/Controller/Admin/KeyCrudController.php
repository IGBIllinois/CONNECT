<?php
/*
 * Copyright (c) 2022 University of Illinois Board of Trustees.
 * All rights reserved.
 */

namespace App\Controller\Admin;

use App\Entity\Key;
use App\Repository\RoomRepository;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class KeyCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Key::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Key')
            ->setEntityLabelInPlural('Keys')
            ->setDefaultSort(['name' => 'ASC']);
    }

    public function configureActions(Actions $actions): Actions
    {
        return parent::configureActions($actions)
            ->add(Crud::PAGE_INDEX, Action::DETAIL);
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('name'),
            TextField::new('description'),
            AssociationField::new('rooms')->setFormTypeOptions([
                'query_builder' => function (RoomRepository $roomRepository) {
                    return $roomRepository->createFormQueryBuilder();
                }
            ])->setTemplatePath('admin/association_comma_separated.html.twig'),
            DateField::new('startedAt')->onlyOnForms(),
            DateField::new('endedAt')->onlyOnForms(),
            AssociationField::new('keyAffiliations')->onlyOnDetail()->setLabel('Key Assignments')->setTemplatePath(
                'admin/historical_association_comma_separated.html.twig'
            )->setCustomOptions([
                'linkField' => 'person',
                'linkCrudFqcn' => PersonCrudController::class,
                'labelField' => 'person',
            ]), // todo only show current? sort?
        ];
    }
}
