<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use TeiEditionBundle\Entity\Organization;

class AdminOrganizationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Name',
                'required' => true,
            ])
            ->add('gnd', TextType::class, [
                'label' => 'GND',
                'required' => false,
            ])
            ->add('url', UrlType::class, [
                'label' => 'URL',
                'required' => false,
            ])
            ->add('description_de', TextareaType::class, [
                'label' => 'Description (German)',
                'required' => false,
                'getter' => function (Organization $organization, FormInterface $form): ?string {
                    return $organization->getDescriptionLocalized('de');
                },
                'setter' => function (Organization &$organization, ?string $val, FormInterface $form): void {
                    $locale = 'de';

                    // $organization->setDescriptionLocalized($locale, $val) is missing - emulate
                    $description = $organization->getDescription();

                    if (!is_null($val)) {
                        $val = trim($val);
                        if ('' === $val) {
                            $val = null;
                        }
                    }

                    if (is_null($val)) {
                        // clear if exists
                        if (is_array($description) && array_key_exists($locale, $description)) {
                            unset($description[$locale]);
                        }
                    }
                    else {
                        // add or set
                        if (is_null($description)) {
                            $description = [];
                        }

                        $description[$locale] = $val;
                    }

                    $organization->setDescription($description);
                },
            ]);
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => \TeiEditionBundle\Entity\Organization::class,
        ]);
    }
}
