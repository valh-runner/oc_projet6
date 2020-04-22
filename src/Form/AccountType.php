<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Image;

class AccountType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('accountPicture', FileType::class, [
                'mapped' => false, // to not try to store the file in the database
                'required' => false, // to not re-upload the file at every edit for update
                'constraints' => [
                    new Image([
                        'maxSize' => '1024k',
                        'maxSizeMessage' => 'Le fichier image est trop lourd (1024Ko maximum)',
                        'mimeTypes' => [
                            'image/jpg',
                            'image/jpeg',
                            'image/png',
                        ],
                        'mimeTypesMessage' => 'Merci d\'envoyer un fichier image jpeg, jpg ou png valide',
                    ])
                ],
            ])
            ->add('pictureDeletionState', HiddenType::class, [
                'mapped' => false, // to not try to store the file in the database
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
