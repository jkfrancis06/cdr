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
use Symfony\Component\Validator\Constraints\All;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;

class XlsxConvertType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder

            ->add('xlsxFiles',FileType::class, [
                'label' => 'Fichier CDR (CSV file)',

                // unmapped means that this field is not associated to any entity property
                'mapped' => false,

                // make it optional so you don't have to re-upload the  file
                // every time you edit details
                'required' => false,

                'multiple' => true,

                // unmapped fields can't define their validation using annotations
                // in the associated entity, so you can use the PHP constraint classes
                'constraints' => [
                    new All([
                        'constraints' => [
                            new File([
                                'maxSize' => '20M',
                                'mimeTypes' => [
                                    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
                                ],
                                'mimeTypesMessage' => "Veuillez uploader un fichier excel valide",
                                'maxSizeMessage' => "Taille maximum de 20M"
                            ])
                        ]
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
        ]);
    }
}
