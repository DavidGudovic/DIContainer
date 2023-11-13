<?php

use Container\Container;
use PHPUnit\Framework\TestCase;

class ContainerTest extends TestCase
{

    protected function setUp(): void
    {
        parent::setUp();
        $this->container = Container::getInstance();
    }
    public function test_it_allows_registering_services_using_closures(): void
    {
        $this->container->register('service', fn () => new TestService);

        $this->assertTrue($this->container->has('service'));
        $this->assertInstanceOf(TestService::class, $this->container->get('service'));
        $this->assertNotSame($this->container->get('service'), $this->container->get('service'));
    }

    public function test_it_allows_registering_services_using_strings()
    {
        $this->container->register('service', 'some-string');
        $this->assertEquals('some-string', $this->container->get('service'));
    }

    public function test_it_persists_services_between_instances(): void
    {
        Container::getInstance()->register('service', fn () => new TestService);
        $this->assertInstanceOf(TestService::class, Container::getInstance()->get('service'));
    }

    public function test_it_preserves_the_container_instance(): void
    {
        $firstInstance = Container::getInstance();
        $secondInstance = Container::getInstance();

        $this->assertSame($firstInstance, $secondInstance);
    }

}

class TestService
{

}