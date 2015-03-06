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

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Class FirewallDispatcherPass
 *
 * Add firewall dispatchers to every firewall
 *
 * @author Berny Cantos <be@rny.cc>
 */
class FirewallDispatcherPass implements CompilerPassInterface
{
    /**
     * Attach event listeners to firewalls
     *
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->has('security.firewall.map')) {
            return;
        }

        $definitions = $this->collectFirewallDefinitions($container);

        foreach ($definitions as $name => $firewall) {

            $listener = $this->createFirewallListener($name, $container);
            $this->addListenerToFirewall($listener, $firewall);
        }
    }

    /**
     * Find all firewall context definitions in the firewall map
     *
     * @param ContainerBuilder $container
     *
     * @return Definition[]
     */
    protected function collectFirewallDefinitions(ContainerBuilder $container)
    {
        $keys = array_keys(
            $container
                ->findDefinition('security.firewall.map')
                ->getArgument(1)
        );

        return array_combine(
            array_map(
                function ($key) {
                    return substr($key, 30);
                },
                $keys
            ),

            array_map(
                function ($serviceId) use ($container) {

                    return $container->findDefinition($serviceId);
                },
                $keys
            )
        );
    }

    /**
     * Create a new service definition for a firewall listener
     *
     * @param string           $firewallName
     * @param ContainerBuilder $container
     *
     * @return Reference
     */
    protected function createFirewallListener($firewallName, ContainerBuilder $container)
    {
        $id = 'security.firewall_dispatcher.' . $firewallName;
        $definition = new Definition(
            'xPheRe\FirewallDispatcher\EventListener\FirewallDispatcherListener',
            array(
                $firewallName,
                new Reference('event_dispatcher'),
            )
        );

        $container->setDefinition($id, $definition);

        return new Reference($id);
    }

    /**
     * Add a new listener to a firewall definition
     *
     * @param object     $listener
     * @param Definition $firewallDefinition
     */
    protected function addListenerToFirewall($listener, Definition $firewallDefinition)
    {
        $listeners = $firewallDefinition->getArgument(0);
        $listeners[] = $listener;

        $firewallDefinition->replaceArgument(0, $listeners);
    }
}
