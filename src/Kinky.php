<?php

namespace ProgrammatorDev\Kinky;

use Kirby\Cms\App;
use Kirby\Email\Email;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Filesystem\F;
use Pinky;
use TijsVerkoyen\CssToInlineStyles\CssToInlineStyles;

class Kinky
{
    private App $kirby;

    private CssToInlineStyles $cssInliner;

    private static ?self $instance = null;

    public function __construct()
    {
        $this->kirby = kirby();
        $this->cssInliner = new CssToInlineStyles();
    }

    /**
     * @throws \DOMException
     * @throws InvalidArgumentException
     */
    public function email(mixed $preset = [], array $props = []): Email
    {
        // find in which variable props are and build them for kinky
        if (is_array($preset)) {
            $preset = $this->buildEmailProps($preset);
        }
        else {
            $props = $this->buildEmailProps($props);
        }

        return $this->kirby->email($preset, $props);
    }

    /**
     * @throws \DOMException
     * @throws InvalidArgumentException
     */
    private function buildEmailProps(array $props): array
    {
        // try to find data and template properties if they exist
        // these will be used for the transform
        $data = $props['data'] ?? [];
        $template = $props['template'] ?? null;

        // kinky requires a template to be set
        if ($template === null) {
            throw new InvalidArgumentException('The property "template" is required');
        }

        // set the body with the transpiled template
        // important: do not run transformTemplate directly in props, or the email will be triggered twice for some reason
        $bodyHtml = $this->transformTemplate($template, $data);
        $props['body']['html'] = $bodyHtml;

        // template property must be removed from props at this point,
        // otherwise kirby will ignore the body property
        unset($props['template']);

        return $props;
    }

    /**
     * @throws \DOMException
     */
    public function transformTemplate(string $name, array $data = []): string
    {
        $template = $this->kirby->template(sprintf('emails/%s', $name));
        $html = $template->render($data);

        return $this->transformHtml($html);
    }

    /**
     * @throws \DOMException
     */
    private function transformHtml(string $html): string
    {
        // transpile Inky template
        $document = Pinky\transformString($html);

        // create a Content-Type <meta> element so charset is detected correctly when inlining CSS
        // https://github.com/tijsverkoyen/CssToInlineStyles?tab=readme-ov-file#known-issues
        $metaContentTypeElement = $document->createElement('meta');
        $metaContentTypeElement->setAttribute('http-equiv', 'Content-Type');
        $metaContentTypeElement->setAttribute('content', 'text/html; charset=utf-8');

        // create viewport <meta> element
        $metaViewportElement = $document->createElement('meta');
        $metaViewportElement->setAttribute('name', 'viewport');
        $metaViewportElement->setAttribute('content', 'width=device-width');

        // get Inky base CSS to inject in HTML
        $inkyCss = F::read(__DIR__ . '/../assets/styles/foundation-emails.css');
        // create a <style> element with Inky base CSS
        $styleElement = $document->createElement('style', sprintf("\n%s", $inkyCss));

        // prepend the <meta> and <style> elements to the document
        // this may seem a little bit weird to add <head> specific elements directly in the document,
        // but the transpiler and inliner will automatically wrap <meta> and <style> elements in a <head> element,
        // so there is no need to do it ourselves, and opens the possibility to include a custom <style> directly in the template
        $document->documentElement->prepend($metaContentTypeElement, $metaViewportElement, $styleElement);

        // inline Inky base CSS (for best results in Gmail and Outlook)
        return $this->cssInliner->convert($document->saveHTML());
    }

    public static function instance(): self
    {
        if (self::$instance !== null) {
            return self::$instance;
        }

        self::$instance = new self();

        return self::$instance;
    }
}
