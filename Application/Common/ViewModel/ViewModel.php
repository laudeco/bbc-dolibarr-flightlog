<?php


namespace FlightLog\Application\Common\ViewModel;


abstract class ViewModel
{

    /**
     * @param array $values
     * @param string $prefix
     *
     * @return static
     */
    public static function fromArray(array $values, string $prefix = '')
    {
        $self = new static();

        foreach ($values as $column => $value) {
            if ($value === null) {
                continue;
            }

            if (!empty($prefix) && strrpos($column, $prefix . '_', -strlen($column)) !== false) {
                $column = str_replace($prefix . '_', '', $column);
            }

            $property = str_replace('_', '', lcfirst(ucwords($column, '_')));
            $method = 'set' . ucfirst($property);

            if (!method_exists($self, $method)) {
                continue;
            }

            $targetMethod = new \ReflectionMethod($self, $method);
            if ($targetMethod->isPublic()) {
                $self->$method(self::sanitize($targetMethod, $value));
            }
        }

        return $self;
    }

    /**
     * @param \ReflectionMethod $targetMethod
     * @param mixed $value
     *
     * @return bool|float|int|string
     */
    private static function sanitize(\ReflectionMethod $targetMethod, $value = null)
    {
        /** @var \ReflectionNamedType $valueType */
        $valueType = $targetMethod->getParameters()[0]->getType();

        if ($valueType === null) {
            return $value;
        }

        switch ($valueType->getName()) {
            case 'array':
            case 'string':
            case 'object':
                return $value;
                break;
            case 'int':
                return intval($value);
                break;
            case 'float':
                return floatval($value);
                break;
            case 'bool':
                return boolval($value);
                break;
            case 'DateTimeImmutable':
                return \DateTimeImmutable::createFromFormat('Y-m-d', $value);
                break;
            default:
                throw new \InvalidArgumentException(sprintf('Unsupported type %s given.', $valueType->getName()));
        }
    }

}