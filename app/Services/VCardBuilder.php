<?php

namespace App\Services;

use Sabre\VObject\Component\VCard;

final class VCardBuilder
{
    public function build(array $data): string
    {
        $lastName = $this->value($data, 'last_name');
        $firstName = $this->value($data, 'first_name');
        $middleName = $this->value($data, 'middle_name');
        $prefix = $this->value($data, 'prefix');
        $suffix = $this->value($data, 'suffix');
        $organization = $this->value($data, 'organization');
        $email = $this->value($data, 'email');

        $formattedName = implode(' ', array_filter(
            [$prefix, $firstName, $middleName, $lastName, $suffix],
            fn (string $part): bool => $part !== ''
        ));

        $vcard = new VCard([
            'FN' => $formattedName ?: ($organization ?: ($email ?: 'Contact')),
            'N' => [$lastName, $firstName, $middleName, $prefix, $suffix],
        ]);

        $this->add($vcard, 'ORG', $organization);
        $this->add($vcard, 'TITLE', $this->value($data, 'vtitle'));
        $this->add($vcard, 'ROLE', $this->value($data, 'role'));
        $this->add($vcard, 'EMAIL', $email);
        $this->add($vcard, 'EMAIL', $this->value($data, 'work_email'), ['TYPE' => 'WORK']);
        $this->add($vcard, 'URL', $this->value($data, 'work_url'), ['TYPE' => 'WORK']);
        $this->add($vcard, 'TEL', $this->value($data, 'home_phone'), ['TYPE' => 'HOME']);
        $this->add($vcard, 'TEL', $this->value($data, 'work_phone'), ['TYPE' => 'WORK']);
        $this->add($vcard, 'TEL', $this->value($data, 'cell_phone'), ['TYPE' => 'CELL']);

        $this->addAddress($vcard, $data, 'home', 'HOME');
        $this->addAddress($vcard, $data, 'work', 'WORK');

        return $vcard->serialize();
    }

    private function add(VCard $vcard, string $property, string $value, array $parameters = []): void
    {
        if ($value !== '') {
            $vcard->add($property, $value, $parameters);
        }
    }

    private function addAddress(VCard $vcard, array $data, string $prefix, string $type): void
    {
        $address = [
            '',
            '',
            $this->value($data, "{$prefix}_address_street"),
            $this->value($data, "{$prefix}_address_city"),
            $this->value($data, "{$prefix}_address_state"),
            $this->value($data, "{$prefix}_address_zip"),
            $this->value($data, "{$prefix}_address_country"),
        ];

        if (array_filter($address, fn (string $part): bool => $part !== '')) {
            $vcard->add('ADR', $address, ['TYPE' => $type]);
        }
    }

    private function value(array $data, string $key): string
    {
        return trim((string) ($data[$key] ?? ''));
    }
}
