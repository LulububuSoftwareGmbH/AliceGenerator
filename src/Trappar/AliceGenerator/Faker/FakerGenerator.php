<?php

namespace Trappar\AliceGenerator\Faker;

class FakerGenerator
{
    /**
     * @param array<mixed> $arguments
     */
    public static function generate(string $fakerName, array $arguments): string
    {
        $arguments = self::handleArray($arguments);

        return "<$fakerName($arguments)>";
    }

    /**
     * @param mixed $value
     * @return mixed
     */
    private static function handleType($value)
    {
        switch (gettype($value)) {
            case 'array':
                return '[' . self::handleArray($value) . ']';
            case 'string':
                return '"' . $value . '"';
            case 'boolean':
                return ($value) ? 'true' : 'false';
            case 'NULL':
                return 'null';
            default:
                return $value;
        }
    }

    /**
     * @param array<mixed> $array
     */
    private static function handleArray(array $array): string
    {
        return implode(', ', array_map(['self', 'handleType'], $array));
    }
}