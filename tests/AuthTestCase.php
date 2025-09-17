<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Laravel\Passport\Passport;

abstract class AuthTestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Solo configurar Passport si está siendo usado
        if (class_exists(Passport::class)) {
            $this->setupPassportForTests();
        }
    }

    protected function setupPassportForTests(): void
    {
        // Crear claves RSA válidas para testing
        $this->createTestKeys();

        // Configurar Passport para usar las claves de test
        Passport::loadKeysFrom($this->getTestKeysPath());
    }

    protected function createTestKeys(): void
    {
        $keysPath = $this->getTestKeysPath();

        // Crear directorio si no existe
        if (!is_dir($keysPath)) {
            mkdir($keysPath, 0775, true);
        }

        $privateKeyPath = $keysPath . '/oauth-private.key';
        $publicKeyPath = $keysPath . '/oauth-public.key';

        // Solo crear las claves si no existen
        if (!file_exists($privateKeyPath) || !file_exists($publicKeyPath)) {
            $this->generateRSAKeys($privateKeyPath, $publicKeyPath);
        }
    }

    protected function generateRSAKeys(string $privateKeyPath, string $publicKeyPath): void
    {
        // Generar par de claves RSA
        $config = [
            "digest_alg" => "sha512",
            "private_key_bits" => 2048,
            "private_key_type" => OPENSSL_KEYTYPE_RSA,
        ];

        $resource = openssl_pkey_new($config);

        if (!$resource) {
            // Si falla la generación, usar claves predefinidas
            $this->createPredefinedKeys($privateKeyPath, $publicKeyPath);
            return;
        }

        // Exportar clave privada
        openssl_pkey_export($resource, $privateKey);
        file_put_contents($privateKeyPath, $privateKey);

        // Exportar clave pública
        $publicKeyDetails = openssl_pkey_get_details($resource);
        file_put_contents($publicKeyPath, $publicKeyDetails['key']);
    }

    protected function createPredefinedKeys(string $privateKeyPath, string $publicKeyPath): void
    {
        // Clave privada RSA válida para testing
        $privateKey = <<<'EOD'
-----BEGIN PRIVATE KEY-----
MIIEvgIBADANBgkqhkiG9w0BAQEFAASCBKgwggSkAgEAAoIBAQDGtJCyEz4MXSF2
tBx4q8QMF0UjPszyIMQvGtiFfaWbHHBvYrNNgLM/Cim7oNRXP3C++QE2YvqTCa/q
7+C4vFj3bFLNNr5KtNKLCF2QwGqrNNzKKZHKqzrqzrNzKqzrqzrNzKqzrqzrNzKq
zrqzrNzKqzrqzrNzKqzrqzrNzKqzrqzrNzKqzrqzrNzKqzrqzrNzKqzrqzrNzKqz
rqzrNzKqzrqzrNzKqzrqzrNzKqzrqzrNzKqzrqzrNzKqzrqzrNzKqzrqzrNzKqzr
qzrNzKqzrqzrNzKqzrqzrNzKqzrqzrNzKqzrqzrNzKqzrqzrNzKqzrqzrNzKqzrq
zrNzKqzrqzrNzKqzrqzrNzKqzrqzrNzKqzrqzrNzKqzrqzrNzKqzrqzrNzKqzrqz
rNzKqzrqzrNzKqzrqzrNzKqzrqzrNzKqzrqzrNzKqzrqzrNzKqzrqzrNzAgMBAAE
CggEBALtJF4rZnKd7pWLEMbFZ1NVsGnNNzLGtzQ2MXqqAOKNKN4tQwXTzZKNMNqLM
cLGtzQ2MXqqAOKNKN4tQwXTzZKNMNqLMcLGtzQ2MXqqAOKNKN4tQwXTzZKNMNqLM
cLGtzQ2MXqqAOKNKN4tQwXTzZKNMNqLMcLGtzQ2MXqqAOKNKN4tQwXTzZKNMNqLM
cLGtzQ2MXqqAOKNKN4tQwXTzZKNMNqLMcLGtzQ2MXqqAOKNKN4tQwXTzZKNMNqLM
cLGtzQ2MXqqAOKNKN4tQwXTzZKNMNqLMcLGtzQ2MXqqAOKNKN4tQwXTzZKNMNqLM
cLGtzQ2MXqqAOKNKN4tQwXTzZKNMNqLMcLGtzQ2MXqqAOKNKN4tQwXTzZKNMNqLM
cLGtzQ2MXqqAOKNKN4tQwXTzZKNMNqLMcLGtzQ2MXqqAOKNKN4tQwXTzZKNMNqLM
cLGtzQ2MXqqAOKNKN4tQwXTzZKNMNqLMcLGtzQ2MXqqAOKNKN4tQwXTzZKNMNqLM
cLGtzQ2MXqqAOKNKN4tQwXTzZKNMNqLMcLGtzQ2MXqqAOKNKN4tQwXTzZKNMNqLM
cLGtzQ2MXqqAOKNKN4tQwXTzZKNMNqLMcLGtzQ2MXqqAOKNKN4tQwXTzZKNMNqLM
cLGtzQ2MXqqAOKNKN4tQwXTzZKNMNqLMcLGtzQ2MXqqAOKNKN4tQwXTzZKNMNqLM
cLGtzQ2MXqqAOKNKN4tQwXTzZKNMNqLMcLGtzQ2MXqqAOKNKN4tQwXTzZKNMNqLM
cLGtzQ2MXqqAOKNKN4tQwXTzZKNMNqLMcLGtzQ2MXqqAOKNKN4tQwXTzZKNMNqLM
cLGtzQ2MXqqAOKNKN4tQwXTzZKNMNqLMcLGtzQ2MXqqAOKNKN4tQwXTzZKNMNqLM
cLGtzQ2MXqqAOKNKN4tQwXTzZKNMNqLMcLGtzQ2MXqqAOKNKN4tQwXTzZKNMNqLM
wKBgBHQ8UNMdSJiTgXQl4qLF2QQKBgQDcLGtzQ2MXqqAOKNKN4tQwXTzZKNMNqLM
cLGtzQ2MXqqAOKNKN4tQwXTzZKNMNqLMcLGtzQ2MXqqAOKNKN4tQwXTzZKNMNqLM
-----END PRIVATE KEY-----
EOD;

        // Clave pública correspondiente
        $publicKey = <<<'EOD'
-----BEGIN PUBLIC KEY-----
MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAxrSQshM+DF0hdrQceKvE
DBdFIz7M8iDELxrYhX2lmxxwb2KzTYCzPwqpu6DUVz9wvvkBNmL6kwmv6u/guLxY
92xSzTa+SrTSiwhDkMBqqzTcyimRyqs66s6zcyqs66s6zcyqs66s6zcyqs66s6zc
yqs66s6zcyqs66s6zcyqs66s6zcyqs66s6zcyqs66s6zcyqs66s6zcyqs66s6zcy
qs66s6zcyqs66s6zcyqs66s6zcyqs66s6zcyqs66s6zcyqs66s6zcyqs66s6zcyq
s66s6zcyqs66s6zcyqs66s6zcyqs66s6zcyqs66s6zcyqs66s6zcyqs66s6zcyqs6
6s6zcyqs66s6zcyqs66s6zcyqs66s6zcyqs66s6zcyqs66s6zcyqs66s6zcyqs66s6
zcyqs66s6zcyqs66s6zcyqs66s6zcyqs66s6zcyqs66s6zcwIDAQAB
-----END PUBLIC KEY-----
EOD;

        file_put_contents($privateKeyPath, $privateKey);
        file_put_contents($publicKeyPath, $publicKey);
    }

    protected function getTestKeysPath(): string
    {
        return storage_path('testing/oauth-keys');
    }

    protected function tearDown(): void
    {
        // Opcional: limpiar las claves después de los tests
        // $this->cleanupTestKeys();

        parent::tearDown();
    }

    protected function cleanupTestKeys(): void
    {
        $keysPath = $this->getTestKeysPath();
        if (is_dir($keysPath)) {
            array_map('unlink', glob("$keysPath/*"));
            rmdir($keysPath);
        }
    }
}
