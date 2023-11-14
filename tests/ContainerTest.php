<?php

namespace tests;

use Closure;
use Container\Container;
use Container\Exceptions\CouldNotResolveAbstraction;
use PHPUnit\Framework\TestCase;
use Psr\Container\NotFoundExceptionInterface;

class ContainerTest extends TestCase
{

    public static function singletonsTestProviders(): array
    {
        return [
            ['service', fn () => new TestService()],
            [TestService::class, fn () => new TestService()],
            ['arbitrary string', fn () => new TestService()]
        ];
    }

    public static function abstractionErrorProvider(): array
    {
        return [
            [TestInterfaceTwo::class],
            [TestAbstraction::class]
        ];
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->container = Container::getInstance();
    }

    public function test_it_allows_registering_services_using_closures(): void
    {
        $this->container->register('service', fn() => new TestService);

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
        Container::getInstance()->register('service', fn() => new TestService);
        $this->assertInstanceOf(TestService::class, Container::getInstance()->get('service'));
    }

    public function test_it_preserves_the_container_instance(): void
    {
        $firstInstance = Container::getInstance();
        $secondInstance = Container::getInstance();

        $this->assertSame($firstInstance, $secondInstance);
    }

    public function test_fetches_unregistered_services(): void
    {
        $this->assertInstanceOf(ORM::class, Container::getInstance()->get(ORM::class));
    }

    public function test_throws_an_exception_on_non_existant_service(): void
    {
        $this->expectException(NotFoundExceptionInterface::class);
        Container::getInstance()->get(NonExistantClass::class);
    }

    public function test_it_injects_dependencies(): void
    {
        $user = Container::getInstance()->get(User::class);

        $this->assertInstanceOf(ORM::class, $user->ORM);
    }

    public function test_it_injects_multiple_dependencies(): void
    {
        $user = Container::getInstance()->get(UserBuilder::class);

        $this->assertInstanceOf(ORM::class, $user->orm);
        $this->assertInstanceOf(TestService::class, $user->testService);
    }

    public function test_it_injects_nested_dependencies(): void
    {
        $user = Container::getInstance()->get(CreateUserAccount::class);

        $this->assertInstanceOf(User::class, $user->user);
        $this->assertInstanceOf(TestService::class, $user->testService);
    }

    /**
     * @dataProvider singletonsTestProviders
     */
    public function test_it_allows_us_to_register_singletons(string $key, Closure $service ): void
    {
        $this->container->singleton($key, $service);

        $this->assertSame(
          $this->container->get($key),
          $this->container->get($key)
        );
    }

    public function test_it_binds_implementations_to_interfaces(): void
    {
        $this->container->register(TestInterface::class, Implementation::class);
        $this->assertInstanceOf(Implementation::class, $this->container->get(TestInterface::class));
    }

    public function test_it_can_bind_implementations_to_interfaces_as_singletons(): void
    {
        $this->container->singleton(TestInterface::class, Implementation::class);
        $this->assertInstanceOf(Implementation::class, $this->container->get(TestInterface::class));
    }

    /**
     * @dataProvider abstractionErrorProvider
     */
    public function test_it_throws_an_exception_on_interface_instantiation(string $abstraction): void
    {
        $this->expectException(CouldNotResolveAbstraction::class);
        $this->expectExceptionMessage("Could not resolve interface or abstract class");
        $this->container->get($abstraction);
    }

    public function test_it_allows_closures_to_access_the_container(): void
    {
        $this->container->register('arbitrary_string_', fn (Container $container) => new User($container->get(ORM::class)));

        $this->assertInstanceOf(User::class, $this->container->get('arbitrary_string_'));
        $this->assertInstanceOf(ORM::class, $this->container->get('arbitrary_string_')->ORM);
    }

}
abstract class TestAbstraction {}
class TestService
{

}
class User
{
    public ORM $ORM;

    public function __construct(ORM $orm)
    {
        $this->ORM = $orm;
    }
}

class ORM
{
    public function __construct()
    {
    }
}

class UserBuilder
{
    public function __construct(
        public TestService $testService,
        public ORM         $orm
    )
    {
    }
}

class CreateUserAccount
{
    public function __construct(
        public User        $user,
        public TestService $testService,
    )
    {
    }
}

interface TestInterface {}
interface TestInterfaceTwo {}

class Implementation implements TestInterface {}

class ImplementationConsumer
{
    public function __construct(
        public TestInterface $interface,
    ){}
}