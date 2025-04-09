<?php

namespace ProgrammatorDev\KirbyInky;

use Kirby\Template\Template;
use Pinky;

class KirbyInky
{
    public static function transformTemplate(string $name, array $data = []): string
    {
        $template = self::getTemplate($name);
        $html = $template->render($data);

        return Pinky\transformString($html)->saveHTML();
    }

    private static function getTemplate(string $name): Template
    {
        return kirby()->template(sprintf('emails/%s', $name));
    }
}
