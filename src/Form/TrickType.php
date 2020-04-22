<?php

namespace App\Form;

use App\Entity\Category;
use App\Entity\Trick;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Image;
use Symfony\Component\Validator\Constraints\NotBlank;

class TrickType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name')
            ->add('description')
            ->add('categoryType', ChoiceType::class, [
                'choices'  => [
                    'Catégorie existante' => 1,
                    'Nouvelle catégorie' => 2
                ],
                'mapped' => false,
                'expanded' => true
            ])
            ->add('existantCategory', EntityType::class, [
                'class' => Category::class, // choices from this entity
                'choice_label' => 'name', // Entity property as the visible option string
                'mapped' => false,
                'required' => false,
                'placeholder' => 'SELECTIONNEZ',
                'constraints' => [
                    new NotBlank(['groups' => ['existCat']]),
                ],
            ])
            ->add('newCategory', TextType::class, [
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new NotBlank(['groups' => ['newCat']]),
                ],
            ])
            ->add('pictures', CollectionType::class, [
                'entry_type' => PictureType::class,
                'entry_options' => ['label' => false],
                'allow_add' => true,
                'by_reference' => false,
                'allow_delete' => true
            ])
            ->add('videos', CollectionType::class, [
                'entry_type' => VideoType::class,
                'entry_options' => ['label' => false],
                'allow_add' => true,
                'by_reference' => false,
                'allow_delete' => true
            ])
            ->add('featuredPicture', FileType::class, [
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
            ->add('featuredPictureDeletionState', HiddenType::class, [
                'mapped' => false, // to not try to store the file in the database
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Trick::class,
            'validation_groups' => function (FormInterface $form) {

                $catTypeValue = $form->get('categoryType')->getData();

                if ($catTypeValue == 1) {
                    return ['Default', 'existCat'];
                }
                elseif ($catTypeValue == 2) {
                    return ['Default', 'newCat'];
                }
                
                return ['Default', 'existCat'];
            },
        ]);
    }
}
