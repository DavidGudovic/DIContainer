<?php

namespace Container\Exceptions;

use Psr\Container\ContainerExceptionInterface;
use RuntimeException;

class CouldNotResolveAbstraction extends RuntimeException implements ContainerExceptionInterface
{
    protected $message = "Could not resolve interface or abstract class";
}