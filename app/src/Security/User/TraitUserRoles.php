<?php
namespace App\Security\User;

trait TraitUserRoles {

    private function extractRoles(array $data, string $clientId): array
    {
        $roles = ['ROLE_USER'];

        // Extract realm-level roles
        foreach ($this->getRealmRoles($data) as $role) {
            $roles[] = $this->normalizeRoleName($role);
        }

        // Extract client-specific roles from all clients in resource_access
        foreach($this->getClientRoles($data, $clientId) as $role) {
            $roles[] = $this->normalizeRoleName($role);
        }

        return array_unique($roles);
    }

    private function normalizeRoleName(string $name): string
    {
        // Convert "offline-access" -> "OFFLINE_ACCESS"
        // Convert "evwrit-frontend" -> "EVWRIT_FRONTEND"
        return 'ROLE_' .strtoupper(str_replace('-', '_', $name));
    }

    private function getClientRoles(array $data, string $clientId): array
    {
        $clientRoles = [];

        if (isset($data['resource_access'][$clientId]['roles']) && is_array($data['resource_access'][$clientId]['roles'])) {
            $clientRoles = $data['resource_access'][$clientId]['roles'];
        }

        return $clientRoles;
    }

    private function getRealmRoles(array $data): array
    {
        $realmRoles = [];

        if (isset($data['realm_access']['roles']) && is_array($data['realm_access']['roles'])) {
            $realmRoles = $data['realm_access']['roles'];
        }

        return $realmRoles;
    }
}