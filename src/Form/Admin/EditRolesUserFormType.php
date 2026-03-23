<?php

namespace App\Form\Admin;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class EditRolesUserFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder

            ->add('roles', ChoiceType::class, [
                'choices' => [
                    'Rôle utilisateur' => 'ROLE_USER',
                    'Rôle administrateur' => 'ROLE_ADMIN',
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez renseigner au moins un rôle',
                    ]),
                ],
                'expanded' => false,
                'multiple' => true,
            ])

        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
