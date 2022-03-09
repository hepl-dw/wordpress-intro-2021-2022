<?php

class PrimaryMenuItem
{
    protected $post;

    public $url;
    public $label;
    public $title;
    public $subitems = [];

    public function __construct($post)
    {
        $this->post = $post;

        $this->url = $post->url;
        $this->label = $post->title;
        $this->title = $post->attr_title;
    }

    public function hasSubItems()
    {
        return ! empty($this->subitems);
    }

    public function isSubItem()
    {
        return boolval($this->getParentId());
    }

    public function getParentId()
    {
        return $this->post->menu_item_parent;
    }

    public function isParentFor(PrimaryMenuItem $instance)
    {
        return ($this->post->ID == $instance->getParentId());
    }

    public function addSubItem(PrimaryMenuItem $instance)
    {
        $this->subitems[] = $instance;
    }

    public function getBemClasses($base)
    {
        $icon = get_field('icon', $this->post);
        $modifiers = [];

        if($this->isCurrent()) {
            $modifiers[] = 'current';
        }

        if($this->post->type === 'custom') {
            $modifiers[] = 'url';
        }

        if($icon) {
            $modifiers[] = $icon;
        }

        $value = $base;

        foreach($modifiers as $modifier) {
            $value .= ' ' . $base . '--' . $modifier;
        }

        return $value;
    }

    public function isCurrent()
    {
        return ($_SERVER['REQUEST_URI'] == parse_url($this->post->url, PHP_URL_PATH));
    }
}
