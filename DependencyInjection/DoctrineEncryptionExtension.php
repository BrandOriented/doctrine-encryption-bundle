<?php
/**
 * Created by PhpStorm.
 * User: matthewthomas
 * Date: 12/11/2017
 * Time: 16:04
 */

namespace BrandOriented\Encryption\DependencyInjection;

use BrandOriented\Encryption\Encryptor\EncryptorInterface;
use BrandOriented\Encryption\Encryptor\OpenSSL;
use Encryptor\Chacha20poly1305;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * Class DoctrineEncryptionExtension
 * @package BrandOriented\Encryption\DependencyInjection
 */
class DoctrineEncryptionExtension extends Extension
{
    /**
     * @inheritdoc
     * @throws \ReflectionException
     * @throws \Exception
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $this->validateConfiguration($config);
        $this->setDefaults($config);

        $container->setParameter('doctrine_encryption.class', $config[Configuration::ENCRYPTOR_CLASS]);
        $container->setParameter('doctrine_encryption.iv', $config[Configuration::ENCRYPTOR_IV]);
        $container->setParameter('doctrine_encryption.key', $config[Configuration::KEY]);
        $container->setParameter('doctrine_encryption.method', $config[Configuration::ENCRYPTOR_METHOD]);
        $container->setParameter('doctrine_encryption.suffix', $config[Configuration::ENCRYPTOR_SUFFIX]);


        $definition = new Definition($config[Configuration::ENCRYPTOR_CLASS], [
            $container->getParameter('doctrine_encryption.key'),
            $container->getParameter('doctrine_encryption.suffix')

        ]);
        $container->setDefinition('doctrine_encryption.encryptor', $definition);

        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yml');
    }

    /**
     * @inheritdoc
     */
    public function getAlias(): string
    {
        return Configuration::ROOT;
    }

    /**
     * Validate the configuration
     *
     * @param array $config
     */
    private function validateConfiguration(array $config)
    {
        if (!isset($config[Configuration::KEY])) {
            throw new \RunTimeException('A KEY must be specified for DoctrineEncryptionBundle.');
        }

        if(!isset($config[Configuration::ENCRYPTOR_SUFFIX])) {
            throw new \RuntimeException('An encryption Suffix must be specified for DoctrineEncryptionBundle.');
        }
    }

    /**
     * Checks the config and set defaults if none are applied
     *
     * @param array $config
     * @throws \ReflectionException
     */
    private function setDefaults(array &$config)
    {
        if (!isset($config[Configuration::ENCRYPTOR_METHOD])) {
            $config[Configuration::ENCRYPTOR_METHOD] = 'AES-256-CBC';
        }

        if (!isset($config[Configuration::ENCRYPTOR_CLASS])) {
            $config[Configuration::ENCRYPTOR_CLASS] = Chacha20poly1305::class;
        } else {
            $refClass = new \ReflectionClass($config[Configuration::ENCRYPTOR_CLASS]);
            if (!$refClass->implementsInterface(EncryptorInterface::class)) {
                throw new \RuntimeException('Encryptor must implements interface ' . EncryptorInterface::class);
            }
        }
    }
}
