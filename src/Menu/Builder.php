<?php
// src/Menu/Builder.php

// see http://symfony.com/doc/current/bundles/KnpMenuBundle/index.html
namespace App\Menu;

use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RouterInterface;

use Symfony\Contracts\Translation\TranslatorInterface;

class Builder
{
    private $factory;
    private $translator;
    private $requestStack;
    private $router;

    /**
     * @param FactoryInterface $factory
     * @param TranslatorInterface $translator
     * @param RequestStack $requestStack
     * @param Router $router
     *
     * Add any other dependency you need
     */
    public function __construct(FactoryInterface $factory,
                                TranslatorInterface $translator,
                                RequestStack $requestStack,
                                RouterInterface $router)
    {
        $this->factory = $factory;
        $this->translator = $translator;
        $this->requestStack = $requestStack;
        $this->router = $router;
    }

    /*
    public function createTopMenu(array $options): ItemInterface
    {
        $menu = $this->factory->createItem('root');
        if (array_key_exists('position', $options) && 'footer' == $options['position']) {
            $menu->setChildrenAttributes([ 'id' => 'menu-top-footer', 'class' => 'small' ]);
        }
        else {
            $menu->setChildrenAttributes([ 'id' => 'menu-top', 'class' => 'list-inline' ]);
        }

        // add menu items
        $menu->addChild('contact', [
                'label' => $this->translator->trans('Contact'), 'route' => 'contact',
            ])
            ->setAttribute('class', 'list-inline-item');

        $menu->addChild('terms', [
                'label' => $this->translator->trans('Terms and Conditions'), 'route' => 'terms',
            ])
            ->setAttribute('class', 'list-inline-item');

        return $menu;
    }
    */

    public function createMainMenu(array $options): ItemInterface
    {
        $menu = $this->factory->createItem('main', [
            'childrenAttributes' => [
                'class' => 'navbar-nav justify-content-end flex-grow-1 pe-3',
            ],
        ]);

        // add menu items
        $menu->addChild('library', [
            'label' => $this->translator->trans('Bibliothek'),
            'route' => 'library',
            'attributes' => [
                'class' => 'nav-item',
            ],
            'linkAttributes' => [
                'class' => 'nav-link',
            ],
        ]);

        $menu->addChild('person-index', [
            'label' => $this->translator->trans('Autor:innen'),
            'route' => 'person-index',
            'attributes' => [
                'class' => 'nav-item',
            ],
            'linkAttributes' => [
                'class' => 'nav-link',
            ],
        ]);

        $menu->addChild('history', [
            'label' => $this->translator->trans('Geschichte'),
            'route' => 'history',
            'attributes' => [
                'class' => 'nav-item',
            ],
            'linkAttributes' => [
                'class' => 'nav-link',
            ],
        ]);

        $menu->addChild('_about', [
            'label' => $this->translator->trans('Über'),
            'uri' => '#',
            'attributes' => [
                'class' => 'nav-item',
            ],
            'linkAttributes' => [
                'class' => 'nav-link dropdown-toggle',
                'dropdown' => true,
                'role' => 'button',
                'data-toggle' => 'dropdown',
                'aria-expanded' => 'false',
            ],
            'childrenAttributes' => [
                'class' => 'dropdown-menu dropdown-menu-right',
            ],
        ]);

        $menu['_about']
            ->addChild('about-project', [
                'label' => $this->translator->trans('Projekt'),
                'route' => 'about-project',
                'linkAttributes' => [
                    'class' => 'dropdown-item',
                ],
            ]);

        $menu['_about']
            ->addChild('about-related', [
                'label' => $this->translator->trans('Weitere Initiativen'),
                'route' => 'about-related',
                'linkAttributes' => [
                    'class' => 'dropdown-item',
                ],
            ]);

        return $menu;
    }
}
