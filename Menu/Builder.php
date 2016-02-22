<?php

/*
 * This file is part of the AdminBundle package.
 *
 * (c) Muhammad Surya Ihsanuddin <surya.kejawen@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfonian\Indonesia\AdminBundle\Menu;

use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;
use Symfonian\Indonesia\AdminBundle\Annotation\Crud;
use Symfonian\Indonesia\AdminBundle\Controller\UserController;
use Symfonian\Indonesia\AdminBundle\Extractor\ClassExtractor;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\Router;
use Symfony\Component\Security\Core\Authorization\AuthorizationChecker;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * @author Muhammad Surya Ihsanuddin <surya.kejawen@gmail.com>
 */
class Builder
{
    /**
     * @var Router
     */
    protected $router;

    /**
     * @var ClassExtractor
     */
    protected $extractor;

    /**
     * @var \Symfony\Component\Translation\TranslatorInterface
     */
    protected $translator;

    /**
     * @var \Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface
     */
    protected $authorizationChecker;

    protected $translationDomain;

    /**
     * @param Router               $router
     * @param ClassExtractor       $extractor
     * @param TranslatorInterface  $translator
     * @param AuthorizationChecker $authorizationChecker
     * @param string               $translationDomain
     */
    public function __construct(Router $router, ClassExtractor $extractor, TranslatorInterface $translator, AuthorizationChecker $authorizationChecker, $translationDomain)
    {
        $this->router = $router;
        $this->extractor = $extractor;
        $this->translator = $translator;
        $this->translationDomain = $translationDomain;
        $this->authorizationChecker = $authorizationChecker;
    }

    /**
     * @param FactoryInterface $factory
     * @param array            $options
     *
     * @return ItemInterface
     */
    public function mainMenu(FactoryInterface $factory, array $options)
    {
        $menu = $factory->createItem('root', array(
            'childrenAttributes' => array(
                'class' => 'sidebar-menu',
            ),
        ));

        $this->addMenu($menu, 'home', 'menu.dashboard');
        $this->addMenu($menu, 'home', 'menu.profile');
        $menu['menu.profile']->setChildrenAttribute('class', 'treeview-menu');
        $this->addMenu($menu['menu.profile'], 'symfonian_indonesia_admin_profile_profile', 'menu.profile', false);
        $this->addMenu($menu['menu.profile'], 'symfonian_indonesia_admin_profile_changepassword', 'menu.user.change_password', false);

        if ($this->authorizationChecker->isGranted('ROLE_SUPER_ADMIN')) {
            $this->addUserMenu($menu);
        }

        $this->generateMenu($menu);

        return $menu;
    }

    private function addUserMenu(ItemInterface $menu)
    {
        $this->addMenu($menu, 'symfonian_indonesia_admin_user_list', 'menu.user.title');
    }

    private function addMenu(ItemInterface $menu, $route, $name, $label = true, $icon = 'fa-bars')
    {
        if ($label) {
            $html = sprintf('<i class="fa %s"></i> <span>%s</span><i class="fa fa-angle-double-left pull-right"></i></a>', $icon, $this->translator->trans($name, array(), $this->translationDomain));
        } else {
            $html = $this->translator->trans($name, array(), $this->translationDomain);
        }

        $menu->addChild($name, array(
            'route' => $route,
            'label' => $html,
            'extras' => array('safe_label' => true),
            'attributes' => array(
                'class' => 'treeview',
            ),
        ));
    }

    private function generateMenu(ItemInterface $menu)
    {
        $routeCollection = $this->router->getRouteCollection()->all();
        $matches = array_filter($routeCollection, function (Route $route) {
            if (preg_match('/\/list\//', $route->getPath())) {
                return true;
            }

            return false;
        });

        $extractor = $this->extractor;
        $router = $this->router;
        $menus = array_map(function (Route $route) use ($router, $extractor) {
            if ($temp = $route->getDefault('_controller')) {
                $controller = explode('::', $temp);

                $annotations = $extractor->extract(new \ReflectionClass($controller[0]));
                foreach ($annotations as $annotation) {
                    if ($annotation instanceof Crud && !$annotation instanceof UserController) {
                        $entity = new \ReflectionClass($annotation->getEntityClass());

                        return array(
                            'icon' => $annotation->getMenuIcon(),
                            'name' => $entity->getShortName(),
                        );
                    }
                }
            }

            return null;
        }, $matches);

        foreach ($menus as $key => $value) {
            if ($value) {
                $this->addMenu($menu, $key, $value['name'], $value['icon']);
            }
        }
    }
}
