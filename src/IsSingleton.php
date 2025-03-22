<?php

namespace djfhe\PHPStanTypescriptTransformer;

trait IsSingleton
{
    private static ?self $instance = null;

    public static function instance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }
} 