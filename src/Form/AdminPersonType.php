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
use TeiEditionBundle\Entity\Person;

class AdminPersonType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('familyName', TextType::class, [
                'label' => 'Family Name',
                'required' => true,
            ])
            ->add('givenName', TextType::class, [
                'label' => 'Given Name',
                'required' => false,
            ])
            ->add('additionalName', TextareaType::class, [
                'label' => 'Additional Name(s)',
                'required' => false,
                'attr' => [
                    'rows' => 2,
                ],
            ])
            ->add('gender', ChoiceType::class, [
                'label' => 'Gender',
                'multiple' => false,
                'required' => false,
                'choices' => [ 'Female' => 'F', 'Male' => 'M' ],
            ])
            ->add('gnd', TextType::class, [
                'label' => 'GND',
                'required' => false,
            ])
            ->add('birthDate', TextType::class, [
                'label' => 'Date of Birth',
                'required' => false,
            ])
            ->add('deathDate', TextType::class, [
                'label' => 'Date of Death',
                'required' => false,
            ])
            // ->add('nationality')
            ->add('url', UrlType::class, [
                'label' => 'URL',
                'required' => false,
            ])
            ->add('description_de', TextareaType::class, [
                'label' => 'Description (German)',
                'required' => false,
                'getter' => function (Person $person, FormInterface $form): ?string {
                    return $person->getDescriptionLocalized('de');
                },
                'setter' => function (Person &$person, ?string $val, FormInterface $form): void {
                    $locale = 'de';

                    // $person->setDescriptionLocalized($locale, $val) is missing - emulate
                    $description = $person->getDescription();

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

                    $person->setDescription($description);
                },
            ]);
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => \TeiEditionBundle\Entity\Person::class,
        ]);
    }
}
