<?php

namespace Yoga;

/**
 * @method static Formatter service()
 */
class Formatter extends Service {

    public function format($variable, $nestedLevels = 2) {
        return $this->doFormat($variable, $nestedLevels);
    }

    public function formatPath($path) {
        return str_replace('\\', '/', $path);
    }

    /**
     * @param array $linesAndComments - array of arrays like this: ['line', 'comment']
     * @return string[]
     */
    public function mergeLinesAndComments(array $linesAndComments) {
        $maxLength = 0;
        foreach ($linesAndComments as $lineAndComment) {
            $line = $lineAndComment[0];
            $length = strlen($line);
            if ($length > $maxLength) {
                $maxLength = $length;
            }
        }
        $maxLength = min(40, $maxLength);
        $result = [];
        foreach ($linesAndComments as $lineAndComment) {
            $line = $lineAndComment[0];
            $comment = $lineAndComment[1];
            if ($comment) {
                $line = str_pad($line, $maxLength) . ' // ' . $lineAndComment[1];
            }
            $result[] = $line;
        }
        return $result;
    }

    private function doFormat($variable, $nestedLevels, $level = 0) {
        if (is_null($variable)) {
            return 'null';
        }
        if (is_bool($variable)) {
            return ($variable) ? 'true' : 'false';
        }
        if (is_string($variable)) {
            return '\'' . $variable . '\'';
        }
        if (is_integer($variable)) {
            return 'int(' . $variable . ')';
        }
        if (is_float($variable)) {
            $result = sprintf('%.10f', $variable);
            $result = rtrim($result, '0');
            $result = rtrim($result, '.');
            return 'float(' . $result . ')';
        }
        if ($level > $nestedLevels) {
            return '<<' . gettype($variable) . '>>';
        }
        if (is_array($variable)) {
            $count = count($variable);
            $result = 'array(' . $count . ')';
            if ($count) {
                foreach ($variable as $key => $value) {
                    $result .= PHP_EOL .
                        $this->formatPrefix($level + 1) .
                        $this->formatKey($key, ' => ') .
                        $this->doFormat($value, $nestedLevels, $level + 1);
                }
            }
            return $result;
        }
        if ($variable instanceof DateTime) {
            return $variable->formatShort(true, true);
        }
        if (is_object($variable)) {
            static $recursion = [];
            $objectHash = spl_object_hash($variable);
            if (isset($recursion[$objectHash])) {
                return get_class($variable) . ' // recursion';
            }
            $recursion[$objectHash] = true;
            $result = get_class($variable);
            $properties = ReflectionPropertiesReader::service()
                ->getProperties($variable, 0);
            $nonStaticProperties = [];
            foreach ($properties as $property) {
                if ($property->isStatic()) {
                    continue;
                }
                $nonStaticProperties[$property->getName()] = $property->getValue($variable);
            }
            if (count($nonStaticProperties)) {
                foreach ($nonStaticProperties as $key => $value) {
                    $result .= PHP_EOL .
                        $this->formatPrefix($level + 1) .
                        $this->formatKey($key, ': ') .
                        $this->doFormat($value, $nestedLevels, $level + 1);
                }
            }
            unset($recursion[$objectHash]);
            return $result;
        }
        throw new \Exception('Unable to format: ' . var_export($variable, true));
    }

    private function formatPrefix($level) {
        return str_repeat('    ', $level);
    }

    private function formatKey($key, $in_array) {
        $s = $key;
        if ($in_array) {
            $s .= $in_array;
        }
        return $s;
    }

}
