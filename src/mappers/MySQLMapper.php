<?php

namespace Avocado\MysqlDriver;

use Avocado\AvocadoORM\Attributes\Relations\JoinColumn;
use Avocado\AvocadoORM\Attributes\Relations\OneToMany;
use Avocado\AvocadoORM\Mappers\Mapper;
use Avocado\ORM\Attributes\Field;
use Avocado\ORM\Attributes\Id;
use Avocado\ORM\AvocadoModel;
use Avocado\ORM\AvocadoModelException;
use Avocado\Utils\AnnotationUtils;
use Avocado\Utils\Arrays;
use ReflectionClass;
use ReflectionEnum;
use ReflectionException;
use ReflectionObject;
use stdClass;

class MySQLMapper implements Mapper {

    /**
     * @param AvocadoModel $model
     * @param object $entity
     * @return object
     * @throws AvocadoModelException
     * @throws ReflectionException
     */
    public function entityToObject(AvocadoModel $model, object $entity): object {
        $modelReflection = new ReflectionClass($model->getModel());
        $modelProperties = $modelReflection->getProperties();

        $instance = $modelReflection->newInstanceWithoutConstructor();
        $instanceReflection = new ReflectionObject($instance);

        foreach ($modelProperties as $modelProperty) {
            $field = $modelProperty->getAttributes(Field::class)[0] ?? null;
            $primaryKey = $modelProperty->getAttributes(Id::class)[0] ?? null;
            $modelPropertyName = $modelProperty->getName();
            $entityPropertyName = $this->getEntityPropertyName($modelProperty, $field, $primaryKey);
            $entityPropertyValue = $entity->{$entityPropertyName} ?? null;

            if ($entityPropertyValue === null) {
                continue;
            }

            $instanceProperty = $instanceReflection->getProperty($modelPropertyName);

            if ($model->isPropertyIsEnum($modelPropertyName)) {
                $this->parseEnum($modelProperty,
                    $entityPropertyValue,
                    $instanceProperty,
                    $instance,
                    $entityPropertyName,
                    $model);
            } else {
                $instanceProperty->setValue($instance, $entityPropertyValue);
            }
        }

        return $instance;
    }

    /**
     * @param \ReflectionProperty $modelProperty
     * @param \ReflectionAttribute|null $field
     * @param \ReflectionAttribute|null $primaryKey
     * @return mixed|string
     */
    private function getEntityPropertyName(\ReflectionProperty $modelProperty, ?\ReflectionAttribute $field, ?\ReflectionAttribute $primaryKey): mixed {
        $entityPropertyName = $modelProperty->getName();

        if (($field && empty($field->getArguments())) || ($primaryKey && empty($primaryKey->getArguments()))) {
            $entityPropertyName = $modelProperty->getName();
        } else if ($field && !empty($field->getArguments())) {
            $entityPropertyName = $field->getArguments()[0];
        } else if ($primaryKey && !empty($primaryKey->getArguments())) {
            $entityPropertyName = $primaryKey->getArguments()[0];
        }
        return $entityPropertyName;
    }

    /**
     * @param \ReflectionProperty $modelProperty
     * @param mixed $entityPropertyValue
     * @param \ReflectionProperty $instanceProperty
     * @param object|string $instance
     * @param mixed $entityPropertyName
     * @param AvocadoModel $model
     * @return void
     * @throws AvocadoModelException
     * @throws ReflectionException
     */
    public function parseEnum(\ReflectionProperty $modelProperty, mixed $entityPropertyValue, \ReflectionProperty $instanceProperty, object|string $instance, mixed $entityPropertyName, AvocadoModel $model): void {
        $enumPropertyReflection = new ReflectionEnum($modelProperty->getType()->getName());

        foreach ($enumPropertyReflection->getCases() as $case) {
            if ($case->getBackingValue() === $entityPropertyValue) {
                $instanceProperty->setValue($instance, $case->getValue());
                break;
            }
        }

        if (!$instanceProperty->isInitialized($instance)) {
            $message = sprintf("`%s` enum property on `%s` model do not have `%s` type.",
                $entityPropertyName,
                $model->getModel(),
                $entityPropertyValue);

            throw new AvocadoModelException($message);
        }
    }
}