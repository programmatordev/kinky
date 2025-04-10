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
        // get Inky base CSS to inject in HTML
        $inkyCss = F::read(__DIR__ . '/../assets/styles/foundation-emails.css');

        // transpile Inky template
        $domDocument = Pinky\transformString($html);

        // create Content-Type <meta> element so charset is detected correctly when inlining CSS
        // https://github.com/tijsverkoyen/CssToInlineStyles?tab=readme-ov-file#known-issues
        $metaContentTypeElement = $domDocument->createElement('meta');
        $metaContentTypeElement->setAttribute('http-equiv', 'Content-Type');
        $metaContentTypeElement->setAttribute('content', 'text/html; charset=utf-8');

        // create viewport <meta>
        $metaViewportElement = $domDocument->createElement('meta');
        $metaViewportElement->setAttribute('name', 'viewport');
        $metaViewportElement->setAttribute('content', 'width=device-width');

        // create <style> element with Inky base CSS
        $styleElement = $domDocument->createElement('style', sprintf("\n%s", $inkyCss));

        // prepend the <meta> and <style> elements to the document
        // this may seem a little bit quirky to add <head> specific elements directly in the document,
        // but the transpiler and inliner will automatically wrap <meta> and <style> elements in a <head> element,
        // so there is no need to do it ourselves, and opens the possibility to include custom <style>s directly in the template
        $domDocument->documentElement->prepend($metaContentTypeElement, $metaViewportElement, $styleElement);

        // inline Inky base CSS (for best results in Gmail and Outlook)
        return $this->cssInliner->convert($domDocument->saveHTML());
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
