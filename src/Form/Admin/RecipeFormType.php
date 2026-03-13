<?php

namespace App\Form\Admin;

use App\Entity\Category;
use App\Entity\Ingredient;
use App\Entity\Recipe;
use App\Entity\Tag;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Vich\UploaderBundle\Form\Type\VichImageType;

class RecipeFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class)
            ->add('category', EntityType::class, [
                'class' => Category::class,
                'choice_label' => 'name',
                'placeholder' => 'Sélectionner une catégorie',
            ])
            ->add('tags', EntityType::class, [
                'class' => Tag::class,
                'choice_label' => 'name',
                'expanded' => false,
                'multiple' => true,
            ])
            ->add('ingredients', EntityType::class, [
                'class' => Ingredient::class,
                'choice_label' => 'name',
                'expanded' => false,
                'multiple' => true,
            ])
            ->add('description', TextType::class)
            ->add('keywords', TextType::class)
            ->add('imageFile', VichImageType::class, [
                // 'required' => true,
                'required' => false,

                // 'allow_delete' => true,
                'allow_delete' => false,
                'delete_label' => 'Supprimer l\'image actuelle?',
                // pour télécharger l'image
                // 'download_label' => false,
                'download_label' => true,
                'download_uri' => false,

                'image_uri' => false,

                'imagine_pattern' => false,
                'asset_helper' => false,
            ])
            ->add('content', TextareaType::class)
            // ->add('user', EntityType::class, [
            //     'class' => User::class,
            //     'choice_label' => 'id',
            // ])

        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Recipe::class,
        ]);
    }
}
