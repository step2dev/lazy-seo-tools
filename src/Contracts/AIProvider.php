<?php

namespace Step2dev\LazySeoTools\Contracts;

interface AIProvider
{
    /**
     * @param array<int, array{role: string, content: string}> $messages
     * @return array<string, mixed>|null
     */
    public function chatJson(array $messages): ?array;
}
