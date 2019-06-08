<?php

namespace App\Form;

use App\Entity\Ad;
use App\Form\ImageType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

class AdType extends AbstractType
{

    /**
     * Permet d'avoir la configuration de base d'un champ
     *
     * @param string $label
     * @param string $placeholder
     * @param array $options
     * @return array
     */
    private function getConfiguration($label, $placeholder, $options = [])
    {
        return array_merge([
            'label' => $label,
            'attr' => [
                'placeholder' => $placeholder
            ]
        ], $options);
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextType::class,$this->getConfiguration("Titre de l'annonce","Choisissez le titre de votre annonce"))
            ->add('slug', TextType::class,$this->getConfiguration("URL de l'annonce","Choisissez l'url de votre annonce",['required' => false]))
            ->add('coverImage', UrlType::class,$this->getConfiguration("URL de l'image principale","Choisissez l'url de votre image"))
            ->add('introduction', TextType::class,$this->getConfiguration("Introduction de l'annonce","Choisissez l'introduction de votre annonce"))
            ->add('content', TextareaType::class,$this->getConfiguration("Contenu de l'annonce","Choisissez le contenu de votre annonce"))
            ->add('rooms', IntegerType::class,$this->getConfiguration("Nombre de chambres","Choisissez le nombre de chambres"))
            ->add('price', MoneyType::class,$this->getConfiguration("Prix par nuit","Choisissez le prix pour une nuit"))
            ->add('images', CollectionType::class,[
                'entry_type' => ImageType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Ad::class,
        ]);
    }
}
