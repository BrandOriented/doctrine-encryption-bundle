<?php
declare(strict_types=1);
/*
 * This file is part of the BrandOriented package.
 *
 * (c) Brand Oriented sp. z o.o.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @author Dominik Labudzinski <dominik@labudzinski.com>
 * @name Chacha20poly1305.php - 22-05-2018 10:03
 */

namespace BrandOriented\Encryption\Subscriber;

use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Annotations\Reader;
use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;
use Doctrine\ORM\Mapping\Embedded;
use BrandOriented\Encryption\Annotation\Encrypted;
use BrandOriented\Encryption\Bridge\Bridge;

/**
 * Class DoctrineEncryptionSubscriber
 * @package BrandOriented\Encryption\Subscriber
 */
class DoctrineEncryptionSubscriber implements EventSubscriber
{
    /**
     * @var Bridge
     */
    private $bridge;

    /**
     * @var Reader
     */
    private $annReader;

    /**
     * DoctrineEncryptionSubscriber constructor.
     * @param Reader $reader
     * @param Bridge $bridge
     */
    public function __construct(Reader $reader, Bridge $bridge)
    {
        $this->annReader = $reader;
        $this->bridge = $bridge;
    }

    /**
     * @param LifecycleEventArgs $args
     * @throws \ReflectionException
     */
    public function prePersist(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        $this->processFields($entity);
    }

    /**
     * @param PreUpdateEventArgs $args
     * @throws \ReflectionException
     */
    public function preUpdate(PreUpdateEventArgs $args)
    {
        $entity = $args->getEntity();
        $this->processFields($entity);
    }

    /**
     * @param LifecycleEventArgs $args
     * @throws \ReflectionException
     */
    public function postLoad(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        $this->processFields($entity);
    }

    /**
     * @return array
     */
    public function getSubscribedEvents(): array
    {
        return array(
            Events::prePersist,
            Events::preUpdate,
            Events::postLoad
        );
    }

    /**
     * Encrypt entity fields
     *
     * @param Object $entity doctrine entity
     *
     * @return object|null
     * @throws \ReflectionException
     */
    public function processFields($entity)
    {
        if (strstr(get_class($entity), "Proxies")) {
            $realClass = ClassUtils::getClass($entity);
        } else {
            $realClass = get_class($entity);
        }

        //Get ReflectionClass of our entity
        $reflectionClass = new \ReflectionClass($realClass);
        $properties = $this->getClassProperties($realClass);

        //Foreach property in the reflection class
        foreach ($properties as $refProperty) {
            if ($this->annReader->getPropertyAnnotation($refProperty, Embedded::class)) {
                $this->handleEmbeddedAnnotation($entity, $refProperty);
                continue;
            }

            $methodName = ucfirst($refProperty->getName());

            /**
             * If contains the Encrypt tag, lets encrypt that property
             */
            if ($this->annReader->getPropertyAnnotation($refProperty, Encrypted::class)) {
                /**
                 * If it is public lets not use the getter/setter
                 */
                if ($refProperty->isPublic()) {
                    $propName = $refProperty->getName();
                    $entity->$propName = $this->bridge->encrypt($refProperty->getValue());
                } else {
                    //If private or protected check if there is an getter/setter for the property, based on the $methodName
                    if ($reflectionClass->hasMethod($getter = 'get' . $methodName) && $reflectionClass->hasMethod($setter = 'set' . $methodName)) {
                        //Get the information (value) of the property
                        try {
                            $getInformation = $entity->$getter();
                        } catch (\Exception $e) {
                            $getInformation = null;
                        }

                        if (!is_null($getInformation) && !empty($getInformation)) {
                            $suffix = $this->bridge->getEncryptor()->getSuffix();

                            $start = strlen($suffix) * -1;
                            if (substr($entity->$getter(), $start) !== $suffix) {
                                $currentPropValue = $this->bridge->encrypt($entity->$getter());
                                $entity->$setter($currentPropValue);
                            }
                        }

                    }
                }
            }
        }

        return $entity;
    }

    /**
     * @param $entity
     * @param $embeddedProperty
     * @throws \ReflectionException
     */
    private function handleEmbeddedAnnotation($entity, $embeddedProperty)
    {
        $reflectionClass = new \ReflectionClass($entity);
        $propName = $embeddedProperty->getName();
        $methodName = ucfirst($propName);

        $embeddedEntity = null;

        if ($embeddedProperty->isPublic()) {
            $embeddedEntity = $embeddedProperty->getValue();
        } else {
            if ($reflectionClass->hasMethod($getter = 'get' . $methodName) && $reflectionClass->hasMethod('set' . $methodName)) {

                //Get the information (value) of the property
                try {
                    $embeddedEntity = $entity->$getter();
                } catch (\Exception $e) {
                    $embeddedEntity = null;
                }
            }
        }
        if ($embeddedEntity) {
            $this->processFields($embeddedEntity);
        }
    }

    /**
     * Recursive function to get an associative array of class properties
     * including inherited ones from extended classes
     *
     * @param string $className Class name
     *
     * @return array
     * @throws \ReflectionException
     */
    public function getClassProperties(string $className): array
    {
        $reflectionClass = new \ReflectionClass($className);
        $properties = $reflectionClass->getProperties();
        $propertiesArray = array();

        foreach ($properties as $property) {
            $propertyName = $property->getName();
            $propertiesArray[$propertyName] = $property;
        }

        if ($parentClass = $reflectionClass->getParentClass()) {
            $parentPropertiesArray = $this->getClassProperties($parentClass->getName());
            if (count($parentPropertiesArray) > 0)
                $propertiesArray = array_merge($parentPropertiesArray, $propertiesArray);
        }

        return $propertiesArray;
    }
}

