<?php

namespace ProgrammatorDev\Kinky;

use Kirby\Filesystem\F;
use Pinky;
use TijsVerkoyen\CssToInlineStyles\CssToInlineStyles;

class Kinky
{
    private CssToInlineStyles $cssInliner;

    private static ?self $instance = null;

    public function __construct(array $options = [])
    {
        $this->cssInliner = new CssToInlineStyles();
    }

    /**
     * @throws \DOMException
     */
    public function transformTemplate(string $name, array $data = []): string
    {
        $template = kirby()->template(sprintf('emails/%s', $name));
        $html = $template->render($data);

        return $this->transformHtml($html);
    }

    /**
     * @throws \DOMException
     */
    private function transformHtml(string $html): string
    {
        // get Inky base style to inject and inline in HTML
        $baseCss = F::read(__DIR__ . '/../assets/styles/foundation-emails.css');

        // transpile Inky template
        $domDocument = Pinky\transformString($html);

        // create Content-Type meta element so charset is detected correctly when inlining CSS
        // https://github.com/tijsverkoyen/CssToInlineStyles?tab=readme-ov-file#known-issues
        $metaContentTypeElement = $domDocument->createElement('meta');
        $metaContentTypeElement->setAttribute('http-equiv', 'Content-Type');
        $metaContentTypeElement->setAttribute('content', 'text/html; charset=utf-8');

        // create style element with Inky base style
        $styleElement = $domDocument->createElement('style', $baseCss);
        $styleElement->setAttribute('type', 'text/css');

        // Pinky transpiler does not create the head element
        // so we have to do it ourselves to append the meta and style elements
        $headElement = $domDocument->createElement('head');
        $headElement->appendChild($metaContentTypeElement);
        $headElement->appendChild($styleElement);

        // prepend the head element to the document
        $domDocument->documentElement->prepend($headElement);

        // transpiled HTML
        $html = $domDocument->saveHTML();
        // inline Inky base style for best results in Gmail and Outlook
        $html = $this->cssInliner->convert($html, $baseCss);

        return $html;
    }

    public static function instance(array $options = []): self
    {
        if (self::$instance !== null) {
            return self::$instance;
        }

        self::$instance = new self($options);

        return self::$instance;
    }
}
