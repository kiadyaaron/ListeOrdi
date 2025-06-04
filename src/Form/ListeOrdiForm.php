<?php

namespace App\Form;

use App\Entity\ListeOrdi;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;

class ListeOrdiForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('DateFirstDotation', Datetype::class,[
                'widget' => 'single_text',
                'label' => 'Date de première dotation',
            ])
            ->add('DaOdoo', Texttype::class,[
                'label' => 'DA Odoo'
            ])
            ->add('PrixUnitaire', Integertype::class,[
                'label' => 'Prix Unitaire',
            ])
            ->add('CoutJournalierFixe', Integertype::class,[
                'label' => 'Côut journalier fixe'
            ])
            ->add('IM', Texttype::class,[
                'label' => 'IM'
            ])
            ->add('Detenteur', Texttype::class,[
                'label' => 'Détenteur'
            ])
            ->add('Fonction', Texttype::class,[
                'label' => 'Fonction'
            ])
            ->add('Marque', Texttype::class,[
                'label' => 'Marque'
            ])
            ->add('NumSerie', Texttype::class,[
                'label' => 'Numéro de série'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ListeOrdi::class,
        ]);
    }
}
