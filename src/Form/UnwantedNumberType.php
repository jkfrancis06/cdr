<?php

namespace App\Form;

use App\Entity\UnwantedNumber;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\RangeType;
use Symfony\Component\Form\Extension\Core\Type\ResetType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Positive;

class UnwantedNumberType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('number',TextType::class, [
                'label' => 'Numero a filtrer',
                'required' => false

            ])
            ->add('char',TextType::class, [
                'label' => 'Contenant les valeurs',
                'mapped' => false,
                'required' => false


            ])
            ->add('supto',IntegerType::class, [
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new Positive(array(
                        'message' => 'Doit etre superieur a 1'
                    ))
                ]

            ])
            ->add('infto',IntegerType::class, [
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new Positive(array(
                        'message' => 'Doit etre superieur a 1'
                    ))
                ]

            ])
            ->add('rangeinf',IntegerType::class, [
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new Positive(array(
                        'message' => 'Doit etre superieur a 1'
                    ))
                ]

            ])
            ->add('rangesup',IntegerType::class, [
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new Positive(array(
                        'message' => 'Doit etre superieur a 1'
                    ))
                ]

            ])
            ->add('description',TextareaType::class,[
                'label' => 'Description',
                'required' => false
            ])
            ->add('submit',SubmitType::class,[
                'label' => 'Ajouter'
            ])
            ->add('cancel',ResetType::class,[
                'label' => 'Annuler'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => UnwantedNumber::class,
        ]);
    }
}
