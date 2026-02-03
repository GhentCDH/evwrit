<?php

namespace App\Security\User;

use IDCI\Bundle\KeycloakSecurityBundle\Provider\KeycloakProvider;
use IDCI\Bundle\KeycloakSecurityBundle\Security\User\KeycloakBearerUser;
use IDCI\Bundle\KeycloakSecurityBundle\Security\User\KeycloakBearerUserProviderInterface;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use KnpU\OAuth2ClientBundle\Client\OAuth2ClientInterface;
use KnpU\OAuth2ClientBundle\Security\User\OAuthUserProvider;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\TokenNotFoundException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

/**
 * Custom Keycloak Bearer User Provider that introspects tokens and extracts roles.
 *
 * Unlike the default KeycloakBearerUserProvider, this provider merges both realm-level roles
 * and client-specific roles from all clients in the resource_access claim, providing a unified
 * role set for Symfony's security system.
 */


class CustomKeycloakBearerUserProvider extends OAuthUserProvider implements KeycloakBearerUserProviderInterface
{
    public function __construct(
        private ClientRegistry $clientRegistry,
        private HttpClientInterface $httpClient,
        private mixed $sslVerification,
        private LoggerInterface $logger,
        private CacheInterface $cache,
        private readonly bool $debug = false,
    ) {
    }

    public function loadUserByIdentifier(string $accessToken): UserInterface
    {
        $cacheKey = 'keycloak_token_' . hash('sha256', $accessToken);

        $this->debug && $this->logger->debug('Loading user by identifier. Trying cache first.', [
            'cache_key' => $cacheKey,
        ]);

        return $this->cache->get($cacheKey, function (ItemInterface $item) use ($accessToken) {

            $this->debug && $this->logger->debug('Cache miss. Authentication requires introspecting bearer token.', [
                'access_token' => $accessToken,
            ]);

            // only trust the cache if the token is valid, otherwise we might cache invalid tokens
            $jwt = $this->introspectToken($accessToken);

            // create the user from the token payload
            $bearerUser = $this->createUserFromJwt($jwt);
            $bearerUser->setAccessToken($accessToken);

            // check expiry from token payload if available
            $verifiedToken = $bearerUser->getAccessToken();
            if ($jwt['exp'] ?? null) {
                $now = time();
                $expiresAt = $jwt['exp'];
                if ($expiresAt > $now) {
                    $item->expiresAfter($expiresAt - $now);
                }
            } else {
                $item->expiresAfter(300);
            }

            return $bearerUser;
        });
    }

    public function createUserFromJwt(array $jwt): UserInterface
    {
        // Extract roles from both realm and all clients
        $roles = $this->extractRoles($jwt);
        $this->debug && $this->logger->debug('Extracted roles from token', [
            'roles' => $roles,
        ]);

        $this->debug && $this->logger->info('User loaded from token introspection', [
            'username' => $jwt['username'],
            'realm_roles' => $jwt['realm_access']['roles'] ?? [],
            'client_roles' => $this->getClientRoles($jwt),
            'symfony_roles' => $roles,
        ]);

        // Create and return the KeycloakBearerUser
        return (new KeycloakBearerUser($jwt['username'], $roles))
            ->setClientId($jwt['client_id'])
            ->setFirstName($jwt['given_name'] ?? null)
            ->setLastName($jwt['family_name'] ?? null)
            ->setEmail($jwt['email'] ?? null)
            ->setEmailVerified($jwt['email_verified'] ?? false);
    }

    public function introspectToken(string $accessToken): array
    {
        $provider = $this->getKeycloakClient()->getOAuth2Provider();

        // Ensure the provider is an instance of KeycloakProvider
        if (!$provider instanceof KeycloakProvider) {
            throw new \RuntimeException(sprintf('The OAuth2 client provider must be an instance of %s', KeycloakProvider::class));
        }

        // Introspect the token
        $this->debug && $this->logger->debug('Introspecting token with Keycloak', [
            'introspection_url' => $provider->getTokenIntrospectionUrl(),
        ]);
        $response = $this->httpClient->request(Request::METHOD_POST, $provider->getTokenIntrospectionUrl(), [
            'body' => [
                'client_id' => $provider->getClientId(),
                'client_secret' => $provider->getClientSecret(),
                'token' => $accessToken,
            ],
            'verify_host' => $this->sslVerification,
            'verify_peer' => $this->sslVerification,
        ]);
        $jwt = json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);

        $this->debug && $this->logger->debug('Token introspection response', [
            'response' => $jwt,
        ]);

        // Check if the token is active
        if (!$jwt['active']) {
            $this->debug && $this->logger->debug('Token is not active');
            throw new TokenNotFoundException('The token does not exist or is not valid anymore');
        }

        return $jwt;
    }

    private function extractRoles(array $jwt): array
    {
        // Convert "Editor" -> "ROLE_EDITOR"
        // Convert "offline-access" -> "ROLE_OFFLINE_ACCESS"

        // Start with a default role
        $roles = ['ROLE_USER'];

        // Extract realm-level roles
        foreach ($this->getRealmRoles($jwt) as $role) {
            $roles[] = 'ROLE_' . $this->normalizeRoleName($role);
        }

        // Extract client-specific roles from all clients in resource_access
        foreach($this->getClientRoles($jwt) as $client => $clientRoles) {
            foreach ($clientRoles as $role) {
                $roles[] = 'ROLE_' . $this->normalizeRoleName($role);
            }
        }

        return array_unique($roles);
    }

    private function normalizeRoleName(string $name): string
    {
        // Convert "offline-access" -> "OFFLINE_ACCESS"
        // Convert "evwrit-frontend" -> "EVWRIT_FRONTEND"
        return strtoupper(str_replace('-', '_', $name));
    }

    private function getClientRoles(array $jwt): array
    {
        $clientRoles = [];

        if (isset($jwt['resource_access']) && is_array($jwt['resource_access'])) {
            foreach ($jwt['resource_access'] as $clientId => $access) {
                if (isset($access['roles']) && is_array($access['roles'])) {
                    $clientRoles[$clientId] = $access['roles'];
                }
            }
        }

        return $clientRoles;
    }

    private function getRealmRoles(array $jwt): array
    {
        return $jwt['realm_access']['roles'] ?? [];
    }

    public function refreshUser(UserInterface $user): UserInterface
    {
        if (!$user instanceof KeycloakBearerUser) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', get_class($user)));
        }

        $user = $this->loadUserByIdentifier($user->getAccessToken());

        if (!$user) {
            throw new UserNotFoundException();
        }

        return $user;
    }

    public function supportsClass($class): bool
    {
        return KeycloakBearerUser::class === $class;
    }

    protected function getKeycloakClient(): OAuth2ClientInterface
    {
        return $this->clientRegistry->getClient('keycloak');
    }
}
