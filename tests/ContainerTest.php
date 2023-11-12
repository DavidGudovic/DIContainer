<?php

namespace tests;

use Container\Container;
use PHPUnit\Framework\TestCase;

class ContainerTest extends TestCase
{
    public function test_it_allows_you_to_register_services(): void
    {
        $container = new Container();

        $container->bind('service', fn () => new TestService);

        $this->assertTrue($container->has('service'));
        $this->assertInstanceOf(TestService::class, $container->get('service'));
    }
}

class TestService
{

}