<?php

namespace App\Form\Admin;

use App\Entity\Post;
use App\Entity\Section;
use App\Entity\Tag;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\String\Slugger\AsciiSlugger;

class PostType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('postTitle')
            ->add('postDescription')
            ->add('postPublished')
            ->add('sections', EntityType::class, [
                'class' => Section::class,
                'choice_label' => 'sectionTitle',
                'multiple' => true,
                'required' => false
            ])
            ->add('tags', EntityType::class, [
                'class' => Tag::class,
                'choice_label' => 'tagName',
                'multiple' => true,
                'required' => false
            ])
            ->add('user', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'username',
            ])
            ->addEventListener(FormEvents::POST_SUBMIT, $this->postSubmit(...));
        ;
    }

    private function postSubmit(FormEvent $event){
        /**
         * @var Post
         */
        $post = $event->getData();
        if($post->getPostTitle() !== null){
            $slugger = new AsciiSlugger();
            $post->setPostSlug($slugger->slug(strtolower($post->getPostTitle())));
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Post::class,
        ]);
    }
}
