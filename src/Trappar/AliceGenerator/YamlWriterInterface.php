<?php

namespace Trappar\AliceGenerator;

interface YamlWriterInterface
{
    /**
     * @param array $data
     */
    public function write(array $data): string;
}