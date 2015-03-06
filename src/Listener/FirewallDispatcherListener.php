<?php

/**
 * This file is part of the xPheRe\FirewallDispatcher package
 *
 * (c) Berny Cantos <be@rny.cc>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace xPheRe\FirewallDispatcher\Listener;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Security\Http\Firewall\ListenerInterface;

/**
 * Class FirewallListener
 *
 * Dispatch an event every time a firewall is entered
 *
 * @author Berny Cantos <be@rny.cc>
 */
class FirewallDispatcherListener implements ListenerInterface
{
    /**
     * @var string
     *
     * Name of the firewall
     */
    protected $firewallName;

    /**
     * @var EventDispatcherInterface
     *
     * Event dispatcher
     */
    protected $eventDispatcher;

    /**
     * Construct
     *
     * @param string                   $firewallName    Name of the firewall
     * @param EventDispatcherInterface $eventDispatcher Dispatcher
     */
    public function __construct(
        $firewallName,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->eventDispatcher = $eventDispatcher;
        $this->firewallName = $firewallName;
    }

    /**
     * This interface must be implemented by firewall listeners.
     *
     * @param GetResponseEvent $event Event
     */
    public function handle(GetResponseEvent $event)
    {
        $this
            ->eventDispatcher
            ->dispatch('security.enter_firewall.' . $this->firewallName);
    }
}
