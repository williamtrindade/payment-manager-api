<?php

namespace App\DTOs;

readonly class CardDTO
{
    public function __construct(
        public string $number,
        public string $cvv,
    ) {}

    public static function fromRequest(array $data): self
    {
        return new self(
            number: $data['number'],
            cvv: $data['cvv'],
        );
    }
}
