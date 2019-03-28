<?php

namespace BlueM;

/**
 * Stateless service which provides serialization of a PHP variable/data structure to a string
 * representation which can be inserted into JavaScript code without needing JSON deserialization.
 */
class JavaScriptSerializer
{
    /**
     * Converts the given PHP variable to a string which is a valid JavaScript variable value
     * equivalent (as far as possible) to the PHP variable
     *
     * @param mixed $var
     *
     * @return string
     * @throws \RuntimeException
     */
    public function serialize($var): string
    {
        return $this->serializeVariable($var);
    }

    /**
     * Returns a string representing a JavaScript value which is equivalent
     * to the PHP variable passed to this function.
     *
     * @param mixed $var
     *
     * @return string
     * @throws \RuntimeException
     */
    private function serializeVariable($var): string
    {
        if (null === $var) {
            return 'null';
        }

        if (is_string($var)) {
            return $this->processString($var);
        }

        if (is_int($var)) {
            return $var;
        }

        if (is_float($var)) {
            return $var;
        }

        if (is_bool($var)) {
            return $var ? 'true' : 'false';
        }

        if (is_array($var)) {

            if (!count($var)) {
                return '[]';
            }

            if (array_keys($var) === range(0, count($var) - 1)) {
                $indexed = true;
                $open = '[';
                $close = ']';
            } else {
                $indexed = false;
                $open = '{';
                $close = '}';
            }

            $items = [];

            foreach ($var as $key => $value) {
                $key = $indexed ? '' : $this->processKey($key).': ';
                $items[] = $key.$this->serializeVariable($value);
            }

            return $open.implode(', ', $items).$close;
        }

        if (is_object($var)) {
            if ($var instanceof \JsonSerializable) {
                $serialized = $var->jsonSerialize();

                return is_string($serialized) ? $serialized : $this->serializeVariable($serialized);
            }

            if (is_callable([$var, 'toArray'])) {
                // There is a callable toArray() method on the object. Use that.
                return $this->serializeVariable($var->toArray());
            }

            if (is_callable([$var, '__toString'])) {
                return (string)$var;
            }

            if ($var instanceof \DateTime) {
                return 'new Date('.($var->format('U.u') * 1000).')';
            }

            throw new \RuntimeException(
                sprintf(
                    'Instance of class %s cannot be serialized (as the class neither '.
                    'implements %s, nor has a public toArray() or __toString() method.',
                    get_class($var),
                    \JsonSerializable::class
                )
            );
        }

        throw new \RuntimeException('Cannot serialize a variable of type '.gettype($var));
    }

    /**
     * @param string $key
     *
     * @return string
     */
    private function processKey($key): string
    {
        return preg_match('/^[a-z_][a-z0-9_]+$/i', $key) ? "$key" : $this->processString($key);
    }

    /**
     * @param string $string
     *
     * @return mixed
     */
    private function processString(string $string): string
    {
        return "'".str_replace(["\r\n", "\n"], '\\n', addcslashes($string, "'\\"))."'";
    }
}
