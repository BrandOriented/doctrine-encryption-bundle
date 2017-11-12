<?php
/**
 * Created by PhpStorm.
 * User: matthewthomas
 * Date: 12/11/2017
 * Time: 16:04
 */

namespace Matt9mg\Encryption\DependencyInjection;


use Matt9mg\Encryption\Encryptor\OpenSSL;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * Class DoctrineEncryptionExtension
 * @package Matt9mg\Encryption\DependencyInjection
 */
class DoctrineEncryptionExtension extends Extension
{
    /**
     * @inheritdoc
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        if(!isset($config[Configuration::KEY])) {
            throw new \RunTimeException('A key must be specified for Matt9mgDoctrineEncryptionBundle.');
        }

        if(!isset($config[Configuration::ENCRYPTOR_METHOD])) {
            $config[Configuration::ENCRYPTOR_METHOD] = 'AES-256-CBC';
        }

        if(!isset($config[Configuration::ENCRYPTOR_CLASS])) {
            $config[Configuration::ENCRYPTOR_CLASS] = OpenSSL::class;
        }

        $container->setParameter('matt9mg_doctrine_encryption.encryptor_class', $config[Configuration::ENCRYPTOR_CLASS]);
        $container->setParameter('matt9mg_doctrine_encryption.encryptor_method', $config[Configuration::ENCRYPTOR_METHOD]);
        $container->setParameter('matt9mg_doctrine_encryption.key', $config[Configuration::KEY]);
    }

    public function getAlias()
    {
        return 'matt9mg_doctrine_encryption';
    }
}