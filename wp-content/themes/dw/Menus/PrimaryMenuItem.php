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
}
