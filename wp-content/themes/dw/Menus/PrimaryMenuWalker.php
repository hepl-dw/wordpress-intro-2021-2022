<?php

class PrimaryMenuWalker extends Walker_Nav_Menu
{
    function start_el(&$output, $item, $depth=0, $args=null, $id=0)
    {
        $icon = get_field('icon', $item);
        $modifiers = [];

        if($item->current) {
            $modifiers[] = 'current';
        }

        if($item->type === 'custom') {
            $modifiers[] = 'url';
        }

        if($icon) {
            $modifiers[] = $icon;
        }

        $output .= '<li class="' . $this->generateBemClasses('nav__item', $modifiers) . '">';

        $output .= '<a href="' . $item->url . '"'
         . ($item->attr_title ? ' title="' . $item->attr_title . '"' : '')
         . ' class="nav__link">' . $item->title . '</a>';
    }

    function end_el(&$output, $item, $depth=0, $args=null)
    {
        $output .= '</li>';
    }

    function generateBemClasses(string $base, array $modifiers = [])
    {
        // Objectif : "nav__item nav__item--current nav__item--url"

        $value = $base;

        foreach($modifiers as $modifier) {
            $value .= ' ' . $base . '--' . $modifier;
        }

        return $value;
    }
}