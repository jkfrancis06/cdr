<?php

namespace App\Form;

use App\Entity\CPerson;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\ResetType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;

class CPersonType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            /*->add('c_number',TextType::class, [
                'label' => 'Numero de telephone',
                'constraints' => [
                    new NotBlank(),
                    new Length(array(
                        'min'        => 7,
                        'max'        => 7,
                        'minMessage' => 'Le numéro doit contenir 7 chiffres',
                        'maxMessage' => 'Le numéro doit contenir 7 chiffres'
                    )),
                        new Regex('/^[347][0-9]{6}$/','Le numéro est incorrect')
                ]
            ]) */
            ->add('c_file_name',FileType::class, [
                'label' => 'Fichier CDR (CSV file)',

                // unmapped means that this field is not associated to any entity property
                'mapped' => false,

                // make it optional so you don't have to re-upload the  file
                // every time you edit details
                'required' => false,

                // unmapped fields can't define their validation using annotations
                // in the associated entity, so you can use the PHP constraint classes
                'constraints' => [
                    new File([
                        'maxSize' => '20M',
                        'mimeTypes' => [
                            'text/plain'
                        ],
                        'mimeTypesMessage' => "Veuillez uploader un fichier .csv valide",
                        'maxSizeMessage' => "Taille maximum de 20M"
                    ])
                ],
            ])

            ->add('c_image_name',FileType::class, [
                'label' => ' Photo du sujet',

                // unmapped means that this field is not associated to any entity property
                'mapped' => false,

                // make it optional so you don't have to re-upload the  file
                // every time you edit details
                'required' => false,

                // unmapped fields can't define their validation using annotations
                // in the associated entity, so you can use the PHP constraint classes
                'constraints' => [
                    new File([
                        'maxSize' => '512k',
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/png',
                            'image/gif',
                            'image/x-ms-bmp'
                        ],
                        'mimeTypesMessage' => "Veuillez uploader un fichier image valide",
                        'maxSizeMessage' => "Taille maximum de 1M"
                    ])
                ],
            ])
            ->add('submit', SubmitType::class, ['label' => 'Enregistrer'])
            ->add('cancel', ResetType::class, ['label' => 'Annuler'])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => CPerson::class,
            'allow_extra_fields' => true,
        ]);
    }
}
