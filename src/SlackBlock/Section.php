<?php

namespace Rocket\SlackBlock;

use Rocket\SlackBlockInterface;
use Exception;

class Section implements SlackBlockInterface
{
    /** @var string */
    private $section_type = null;

    /** @var string */
    private $type = null;

    /** @var string */
    private $text = null;

    /** @var SectionField[] */
    private $fields = [];

    /**
     * @param $type
     * @param $text
     *
     * @return Section
     */
    public static function text($type, $text)
    {
        $instance = new static();
        $instance->section_type = 'text';
        $instance->type = $type;
        $instance->text = $text;

        return $instance;
    }

    /**
     * @return Section
     */
    public static function fields()
    {
        $instance = new static();
        $instance->section_type = 'fields';

        return $instance;
    }

    /**
     * @param SectionField $field
     *
     * @return $this
     * @throws Exception
     */
    public function addField($field)
    {
        if ($this->section_type !== 'fields') {
            throw new Exception('');
        }

        $this->fields[] = $field;

        return $this;
    }

    /**
     * @return array
     */
    public function build()
    {
        if ($this->section_type === 'text') {
            return [
                'type' => 'section',
                'text' => [
                    'type' => $this->type,
                    'text' => $this->text
                ]
            ];
        }

        if ($this->section_type === 'fields') {
            $array = [];
            foreach ($this->fields as $field) {
                $array[] = $field->build();
            }

            return [
                'type' => 'section',
                'fields' => $array
            ];
        }
    }
}
