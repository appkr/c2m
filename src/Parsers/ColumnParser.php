<?php

namespace Appkr\Importer\Parsers;

class ColumnParser
{
    /**
     * The parsed columns.
     *
     * @var array
     */
    private $columns = [];

    /**
     * Parse the command line columns.
     * Ex: titie,description
     *
     * @param  string $columns
     * @return array
     */
    public function parse($columns)
    {
        return $this->splitIntoFields($columns);
    }

    /**
     * Add a field to the schema array.
     *
     * @param  array $field
     * @return $this
     */
    private function addField($field)
    {
        $this->columns[] = $field;

        return $this;
    }

    /**
     * Get an array of fields from the given schema.
     *
     * @param  string $schema
     * @return array
     */
    private function splitIntoFields($schema)
    {
        return preg_split('/,\s?(?![^()]*\))/', $schema);
    }

    /**
     * Get the segments of the schema field.
     *
     * @param  string $field
     * @return array
     */
    private function parseSegments($field)
    {
        $segments = explode(':', $field);
        $name = array_shift($segments);
        $type = array_shift($segments);
        $arguments = [];
        $options = $this->parseOptions($segments);

        // Do we have arguments being used here?
        // Like: string(100)
        if (preg_match('/(.+?)\(([^)]+)\)/', $type, $matches)) {
            $type = $matches[1];
            $arguments = explode(',', $matches[2]);
        }

        return compact('name', 'type', 'arguments', 'options');
    }

    /**
     * Parse any given options into something usable.
     *
     * @param  array $options
     * @return array
     */
    private function parseOptions($options)
    {
        if (empty($options)) return [];

        foreach ($options as $option) {
            if (str_contains($option, '(')) {
                preg_match('/([a-z]+)\(([^\)]+)\)/i', $option, $matches);
                $results[$matches[1]] = $matches[2];
            } else {
                $results[$option] = true;
            }
        }

        return $results;
    }
}