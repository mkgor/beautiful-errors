<?php


namespace BeautifulErrors;


use Highlighter\Theme\DefaultThemes\Minimalistic;

class HighlighterTheme extends Minimalistic
{
    public function getLineHighlightBgColor()
    {
        return 'none';
    }

    public function getLineNumberHighlightedBgColor()
    {
        return 'bg_red';
    }
}