<?php

namespace Step2dev\LazySeoTools\Services;

class JsonLdService
{
    public function __construct(protected SchemaService $schema) {}

    public function generateForPage(array $data): string
    {
        return $this->script($data['schema'] ?? $data['type'] ?? 'webPage', $data);
    }

    public function make(string $type = 'webPage', array $data = []): array
    {
        return $this->schema->make($type, $data);
    }

    public function script(string $type = 'webPage', array $data = []): string
    {
        return '<script type="application/ld+json">'.$this->schema->toJson($this->make($type, $data)).'</script>';
    }
}
