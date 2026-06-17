<?php

namespace App\Form;

use App\Entity\Vigilance;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class VigilanceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('definition')
        ;

        if ($options['picto_upload_mode']) {
            $builder->add('pictoFile', FileType::class, [
                'mapped' => false,
                'required' => false,
                'label' => 'Picto (format png)',
                'attr' => [
                    'accept' => '.png,image/png',
                ],
                'constraints' => [
                    new File([
                        'mimeTypes' => ['image/png'],
                        'mimeTypesMessage' => 'Le fichier doit être au format PNG.',
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
            'data_class' => Vigilance::class,
            'picto_upload_mode' => false,
        ]);
    }
}
