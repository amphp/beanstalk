<?php

namespace Amp\Beanstalk\Stats;

trait Initializer {
    public function __construct(array $properties) {
        foreach ($properties as $key => $value) {
            $formattedKey = $this->getPropertyInCamelCase($key);
            if (!property_exists($this, $formattedKey)) {
                continue;
            }
            $this->{$formattedKey} = $value;
        }
    }

    private function getPropertyInCamelCase(string $key): string {
        return lcfirst(str_replace(' ', '', ucwords(str_replace('-', ' ', $key))));
    }
}
