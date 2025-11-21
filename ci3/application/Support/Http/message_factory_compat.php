<?php

declare(strict_types=1);

namespace Http\Message;

use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;

// phpcs:disable PSR1.Classes.ClassDeclaration.MultipleClasses
if (!interface_exists('Http\\Message\\RequestFactory')) {
    /**
     * @deprecated Use \Psr\Http\Message\RequestFactoryInterface instead.
     */
    interface RequestFactory extends RequestFactoryInterface
    {
    }
}

if (!interface_exists('Http\\Message\\ResponseFactory')) {
    /**
     * @deprecated Use \Psr\Http\Message\ResponseFactoryInterface instead.
     */
    interface ResponseFactory extends ResponseFactoryInterface
    {
    }
}

if (!interface_exists('Http\\Message\\StreamFactory')) {
    /**
     * @deprecated Use \Psr\Http\Message\StreamFactoryInterface instead.
     */
    interface StreamFactory extends StreamFactoryInterface
    {
    }
}

if (!interface_exists('Http\\Message\\UriFactory')) {
    /**
     * @deprecated Use \Psr\Http\Message\UriFactoryInterface instead.
     */
    interface UriFactory extends UriFactoryInterface
    {
    }
}

if (!interface_exists('Http\\Message\\MessageFactory')) {
    /**
     * @deprecated Use PSR-17 interfaces instead.
     */
    interface MessageFactory extends RequestFactory, ResponseFactory
    {
    }
}
// phpcs:enable PSR1.Classes.ClassDeclaration.MultipleClasses
