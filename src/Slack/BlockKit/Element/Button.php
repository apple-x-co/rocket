<?php

namespace Rocket\Slack\BlockKit\Element;

class Button implements ElementInterface
{
    /** @var PlainText */
    private $text;

    /** @var string */
    private $action_id;

    /** @var string|null */
    private $url;

    /** @var string|null */
    private $value;

    /** @var null|'primary'|'danger' */
    private $style;

    /**
     * @param PlainText $text
     * @param string    $action_id
     */
    public function __construct(PlainText $text, $action_id)
    {
        $this->text = $text;
        $this->action_id = $action_id;
    }

    /**
     * @param string|null $url
     *
     * @return $this
     */
    public function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }

    /**
     * @param string|null $value
     *
     * @return $this
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * @return $this
     */
    public function setStyleDefault()
    {
        $this->style = null;

        return $this;
    }

    /**
     * @return $this
     */
    public function setStylePrimary()
    {
        $this->style = 'primary';

        return $this;
    }

    /**
     * @return $this
     */
    public function setStyleDanger()
    {
        $this->style = 'danger';

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function toArray()
    {
        $array = [
            'type' => 'button',
            'text' => $this->text->toArray(),
            'action_id' => $this->action_id,
        ];

        if ($this->style !== null) {
            $array['style'] = $this->style;
        }

        if ($this->url !== null) {
            $array['url'] = $this->url;
        }

        if ($this->value !== null) {
            $array['value'] = $this->value;
        }

        return $array;
    }
}
