<?php

namespace App\Form;

use App\Entity\Comment;
use App\Entity\Post;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CommentAdminType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('commentMessage')
            ->add('commentDateCreated', null, [
                'widget' => 'single_text'
            ])
            ->add('commentPublished')
            ->add('post', EntityType::class, [
                'class' => Post::class,
                'choice_label' => 'postTitle',
            ])
            ->add('user', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'username',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Comment::class,
        ]);
    }
}
