<?php

namespace App\Form;

use App\Entity\Comment;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

use Symfony\Component\Validator\Constraints\File;
class CommentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('content', TextareaType::class, array('data_class' => null,'label' => false))
            
            ->add('file', FileType::class, array('data_class' => null,'label' => false),
            
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
            'data_class' => Comment::class,
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
            'csrf_token_id'   => 'form_intention',
        ]);
    }
}
