<?php

namespace App\Form;

use App\Entity\SupervisorAffiliation;
use App\Form\Fields\EndDateType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EndSupervisorAffiliationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('endedAt', EndDateType::class, [
                'data' => new \DateTime(),
                'required' => true,
                'help' => null,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => SupervisorAffiliation::class,
        ]);
    }
}
