<?php

namespace App\Form;

use App\Entity\TempsContact;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class TempsContactType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('tempsContact')
        ;

        if ($options['picto_upload_mode']) {
            $builder->add('pictoFile', FileType::class, [
                'mapped' => false,
                'required' => true,
                'label' => 'Picto (format png)',
                'attr' => [
                    'accept' => '.png,image/png',
                ],
                'constraints' => [
                    new File([
                        'mimeTypes' => ['image/png'],
                        'mimeTypesMessage' => 'Le fichier doit etre au format PNG.',
                    ]),
                ],
            ]);
        } else {
            $builder->add('picto');
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => TempsContact::class,
            'picto_upload_mode' => false,
        ]);
    }
}
