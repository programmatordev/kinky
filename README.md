# Kinky

[Inky](https://get.foundation/emails/docs/inky.html) email templating language for [Kirby CMS](https://getkirby.com/).

Converts simple HTML tags into the complex table required for emails. Oh là là!

## Table of Contents

- [TL;DR](#tldr)
- [How it Works](#how-it-works)
- [Usage](#usage)
  - [transformTemplate](#transformtemplate)
  - [email](#email)
- [Cookbook](#cookbook)

## TL;DR

Building HTML emails is painful.

You're stuck using archaic table-based layouts, and you constantly have to fight against inconsistent rendering across outdated email clients (like Outlook).
Modern web standards? Mostly ignored. 

To make life easier, this plugin was created so you can write cleaner code and still get reliable results across all major email clients.

## How it Works

This plugin streamlines HTML email development by integrating a transpiler and inliner workflow built around [Inky](https://get.foundation/emails/docs/inky.html). 

It uses a [transpiler](https://github.com/lorenzo/pinky) that converts [Inky](https://get.foundation/emails/docs/inky.html)'s custom components into table-based HTML, 
ensuring compatibility with legacy email clients. 

After that, it passes the output through a [CSS inliner](https://github.com/tijsverkoyen/CssToInlineStyles)
that merges both [Inky](https://get.foundation/emails/docs/inky.html)'s default styles and any custom CSS into inline styles.

The result is clean, reliable HTML emails that render consistently across major clients
like Outlook, Gmail, and Apple Mail — without giving up modern development convenience.

### Example

Simple usage of Inky base components (it is recommended to include the `body` class to enable responsive columns):

```html
<!-- templates/emails/email.html.php -->

<wrapper class="body">
    <container>
        <row>
            <columns>Kinky</columns>
            <columns>Oh là là</columns>
        </row>
    </container>
</wrapper>
```

Transformed HTML (clean example without the whole document and CSS inlining):

```html
<table class="body wrapper" align="center">
    <tr>
        <td class="wrapper-inner">
            <table align="center" class="container">
                <tbody>
                <tr>
                    <td>
                        <table class="row">
                            <tbody>
                            <tr>
                                <th class="small-12 large-6 first columns">
                                    <table>
                                        <tr>
                                            <th>Kinky</th>
                                        </tr>
                                    </table>
                                </th>
                                <th class="small-12 large-6 last columns">
                                    <table>
                                        <tr>
                                            <th>Oh là là</th>
                                        </tr>
                                    </table>
                                </th>
                            </tr>
                            </tbody>
                        </table>
                    </td>
                </tr>
                </tbody>
            </table>
        </td>
    </tr>
</table>
```

## Usage

A global `kinky()` function is available to create and send emails.

### `transformTemplate`

```php
kinky()->transformTemplate(string $template, array $data = []): string
```

Returns the final transformed HTML from the given `$template`.
Take into account that email templates must be located in the `/site/templates/emails/` directory.

> [!NOTE]
> Check the documentation regarding how emails work in [Kirby](https://getkirby.com/docs/guide/emails).

You can pass data into the template using the `$data` parameter.

This method can be useful to help creating and previewing email templates:

```php
// templates/default.php

// template will be located at /site/templates/emails/notification.html.php
<?= kinky()->transformTemplate('notification', [
    'name' => 'Kinky',
    'text' => 'Oh là là'
]); ?>
```

### `email`

```php
use Kirby\Email\Email;

kinky()->email(mixed $preset = [], array $props = []): Email
```

This method is basically a wrapper around the existing `kirby()->email()` method, so it works the same way.
The only difference is that it transpiles and inlines the Inky components and CSS for you.

```php
// note that the "template" property is required
kinky()->email([
    'template' => 'notification', // required
    'from' => 'from@email.com',
    'to' => 'to@email.com',
    'subject' => 'Kinky Plugin',
    'data' => [
        'name' => 'Kinky',
        'text' => 'Oh là là'
    ]
]);
```

It is basically the sames as doing the following:

```php
kirby()->email([
    'from' => 'from@email.com',
    'to' => 'to@email.com',
    'subject' => 'Kinky Plugin',
    'body' => [
        'html' => kinky()->transformTemplate('notification', [
            'name' => 'Kinky',
            'text' => 'Oh là là'
        ])
    ]
]);
```

> [!NOTE]
> Check the documentation regarding how emails work in [Kirby](https://getkirby.com/docs/guide/emails).

## Cookbook

### Custom CSS

To use your own custom CSS, you can just include a `<style>` element in the template and all selectors will be inlined for you.

Template:

```html
<style>
    p {
        background-color: #000000;
        color: #ffffff;
        font-size: 32px;
    }
    
    .small {
        font-size: 16px;
    }
</style>

<wrapper class="body">
    <p class="text">Kinky <span class="small">Oh là là!</span></p>
</wrapper>
```

Result:

```html
<table class="body wrapper" align="center">
    <tr>
        <td class="wrapper-inner">
            <p style="background-color: #000000; color: #ffffff; font-size: 32px;">
                Kinky <span class="small" style="font-size: 16px;">Oh là là</span>
            </p>
        </td>
    </tr>
</table>
```

## Acknowledgments

Thank you to the authors of these libraries that make this plugin possible:

- [https://github.com/lorenzo/pinky](https://github.com/lorenzo/pinky)
- [https://github.com/tijsverkoyen/CssToInlineStyles](https://github.com/beebmx/kirby-env)

## Contributing

Any form of contribution to improve this library (including requests) will be welcome and appreciated.
Make sure to open a pull request or issue.

## License

This project is licensed under the MIT license.
Please see the [LICENSE](LICENSE) file distributed with this source code for further information regarding copyright and licensing.
