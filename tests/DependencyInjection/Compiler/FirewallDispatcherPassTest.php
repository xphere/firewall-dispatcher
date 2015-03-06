<?php

/**
 * This file is part of the xPheRe\FirewallDispatcher package
 *
 * (c) Berny Cantos <be@rny.cc>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace xPheRe\FirewallDispatcher\DependencyInjection\Compiler;

use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractCompilerPassTestCase;
use Symfony\Bundle\SecurityBundle\Security\FirewallContext;
use Symfony\Bundle\SecurityBundle\Security\FirewallMap;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Class FirewallDispatcherPassTest
 *
 * @author Berny Cantos <be@rny.cc>
 */
class FirewallDispatcherPassTest extends AbstractCompilerPassTestCase
{
    /**
     * Register the compiler pass under test, just like you would do inside a bundle's load()
     * method:
     *
     *   $container->addCompilerPass(new MyCompilerPass());
     */
    protected function registerCompilerPass(ContainerBuilder $container)
    {
        $container->addCompilerPass(new FirewallDispatcherPass());
    }

    /**
     * Test with just one firewall
     */
    public function test_it_adds_a_listener_to_firewall()
    {
        $this->setFirewallMap([
            'root',
        ]);

        $this->compile();

        $this->assertContainerBuilderHasServiceDefinitionWithArgument(
            'security.firewall.map.context.root', 0,
            [
                new Reference('security.firewall_dispatcher.root'),
            ]
        );
    }

    /**
     * Test with multiple firewalls
     */
    public function test_it_adds_a_listener_to_every_firewall()
    {
        $this->setFirewallMap([
            'admin_site',
            'public_site',
        ]);

        $this->compile();

        $this->assertContainerBuilderHasServiceDefinitionWithArgument(
            'security.firewall.map.context.admin_site', 0,
            [
                new Reference('security.firewall_dispatcher.admin_site'),
            ]
        );

        $this->assertContainerBuilderHasServiceDefinitionWithArgument(
            'security.firewall.map.context.public_site', 0,
            [
                new Reference('security.firewall_dispatcher.public_site'),
            ]
        );
    }

    /**
     * Register a firewall map with one/multiple firewalls
     *
     * @param string[] $firewalls Names of the firewalls
     *
     * @return Definition
     */
    protected function setFirewallMap(array $firewalls)
    {
        $firewallMap = [];
        foreach ($firewalls as $firewallName) {
            $id = 'security.firewall.map.context.' . $firewallName;
            $this
                ->registerService($id, FirewallContext::class)
                ->addArgument([])
                ->addArgument(null)
            ;
            $firewallMap[$id] = null;
        }

        return $this
            ->registerService('security.firewall.map', FirewallMap::class)
            ->addArgument(new Reference('service_container'))
            ->addArgument($firewallMap)
        ;
    }
}
