<?php

namespace App\Form;

use App\Entity\Post;
use App\Entity\Categorie;
use App\Repository\CategorieRepository;
use App\Repository\CommentRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Validator\Constraints\File;
use Doctrine\ORM\EntityManagerInterface;
class PostType extends AbstractType
{
    
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title')
            ->add('content')
            
            ->add('file', FileType::class, array('data_class' => null),
            
            ['constraints' => [
                new File([
                    'maxSize' => '10M',
                    'mimeTypes' => [
                        'image/jpeg',
                        'image/png',
                        'image/gif',
                        'application/zip',
                        'application/vnd.rar',
                        'application/mp4',
                        'application/mp3',
                        'audio/mpeg',
                        'audio/mpeg','application/octet-stream'
                    ],
                    'mimeTypesMessage' => 'Please upload a file with a supported format',
                ]),
            ]
            ])
            
        ;
        $builder->get('file')->setRequired(false);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Post::class,
        ]);
    }
}
