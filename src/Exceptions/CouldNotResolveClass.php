<?php

namespace Container\Exceptions;

use Psr\Container\NotFoundExceptionInterface;
use RuntimeException;

class CouldNotResolveClass extends RuntimeException implements NotFoundExceptionInterface
{
}