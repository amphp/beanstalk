<?php

namespace Amp\Beanstalk\Stats;

trait Initializer {
    public function __construct(array $properties) {
        foreach ($properties as $key => $value) {
            $key = str_replace('-', '_', $key);
            if (!property_exists($this, $key)) {
                continue;
            }
            $this->{$key} = $value;
        }
    }
}
