<?php

namespace AltDesign\FleetCommand\DTO;

class UserDTO
{
    public static function make(
        string|int $id,
        string $name,
        string $email,
    ) : self {
        return new self(
            id: $id,
            name: $name,
            email: $email
        );
    }

    private function __construct(
        private string|int $id,
        private string $name,
        private string $email,
    ) {

    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
        ];
    }
}