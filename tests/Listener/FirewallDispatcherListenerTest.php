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

use PHPUnit_Framework_TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;

/**
 * Class FirewallDispatcherListenerTest
 *
 * @author Berny Cantos <be@rny.cc>
 */
class FirewallDispatcherListenerTest extends PHPUnit_Framework_TestCase
{
    public function test_it_dispatches_enter_firewall_event_on_handle()
    {
        /**
         * @var $dispatcher ObjectProphecy|EventDispatcherInterface
         */
        $dispatcher = $this->prophesize(EventDispatcherInterface::class);
        $dispatcher
            ->dispatch('security.enter_firewall.admin_area', Argument::any())
            ->shouldBeCalled();

        $listener = new FirewallDispatcherListener('admin_area', $dispatcher->reveal());

        $event = $this->prophesize(GetResponseEvent::class);
        $listener->handle($event->reveal());
    }
}
