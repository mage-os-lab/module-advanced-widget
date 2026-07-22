# MageOS AdvancedWidget Module for Magento

Add configurable multi-row CMS Widgets with image picker component, product picker component, select fields and much more.

---

## Overview

The **AdvancedWidget** module allows you to define multi-row CMS widgets.
These features combined with MageOS_PageBuilderWidget module (that is explicit dependency) make finally possible to develop custom pagebuilder components with own preview and a large set of configurations.
Complex pagebuilder ui components development is no more needed.


## 🚀 Features

> 1) This module let you specify Title separators inside widgets
   ![title section](./doc/title-section_screenshot.png)

> 2) This module let you specify multiple "repeatable" sections where you can specify unlimited rows inside widgets
   ![repeatable section](./doc/repeatable-section_screenshot.png)
>
>> 2.1) Each item field can receive a dedicated tooltip
   ![repeatable section](./doc/repeatable-section-tooltip_screenshot.png)
> 
>> 2.2) Each field is validated and ask to be required for compilation
   ![repeatable section](./doc/repeatable-section-validation_screenshot.png)
>
>> 2.3) Items can be sorted
   ![repeatable section](./doc/repeatable-section-sorter_screenshot.png)
> 
>> 2.4) You can specify whether a field is editable directly in the main box or whether it must be edited in the detail modal.
   ![repeatable section](./doc/repeatable-section-row_screenshot.png)
 
> 3) Row item images fields are available
   ![image field](./doc/image-field_screenshot.png)
   ![image field selection](./doc/image-field-selection_screenshot.png)

> 4) Row item select fields are available
   ![select field](./doc/select-field_screenshot.png)

> 5) Row item product fields are available
   ![product field](./doc/product-field_screenshot.png)
   ![product field selection](./doc/product-field-selection_screenshot.png) 

> 6) Row item wysiwyg and colorpicker fields are also available (see the field reference below)


## 📖 Field reference

Two kinds of fields exist:

1. **Widget parameter fields** — declared in your module's `etc/widget.xml` as `xsi:type="block"` parameters pointing at a renderer class from `MageOS\AdvancedWidget\Block\WidgetField\*`. On the frontend the value arrives as plain block data (`$block->getData('name')`).
2. **Repeatable row fields** — declared in PHP inside a `Rows` subclass; editors can add, sort and delete unlimited rows, each row containing these fields.

### Widget parameter fields

#### Image — `Block\WidgetField\Image`

Renders a text input with a "Choose Image..." button that opens the Magento media gallery browser. Stores the **media-relative path** (e.g. `wysiwyg/hero/image.jpg`) as a plain string.

```xml
<parameter name="image" sort_order="10" required="true" visible="true" xsi:type="block">
    <label translate="true">Image</label>
    <block class="MageOS\AdvancedWidget\Block\WidgetField\Image">
        <data>
            <item name="button" xsi:type="array">
                <item name="open" xsi:type="string">Choose image...</item>
            </item>
        </data>
    </block>
</parameter>
```

On the frontend, resolve the stored path with `$block->getImageUrl('image')` (from `Block\Widgets\AbstractColumns`) — it prefixes the store media base URL when the value isn't already absolute.

#### TextArea — `Block\WidgetField\TextArea`

Plain multi-line text input (no HTML editor). Line breaks are preserved in the stored value.

```xml
<parameter name="description" sort_order="20" required="false" visible="true" xsi:type="block">
    <label translate="true">Description</label>
    <block class="MageOS\AdvancedWidget\Block\WidgetField\TextArea"/>
</parameter>
```

#### WYSIWYG — `Block\WidgetField\Wysiwyg`

Full TinyMCE editor (variables and images enabled, nested widgets disabled). The stored value is HTML — don't `escapeHtml` it on the frontend; sanitize it instead, e.g. with `Block\Widgets\Template::stripTags()`, which ships with a sensible default tag whitelist.

```xml
<parameter name="content" sort_order="30" required="false" visible="true" xsi:type="block">
    <label translate="true">Rich content</label>
    <block class="MageOS\AdvancedWidget\Block\WidgetField\Wysiwyg"/>
</parameter>
```

#### Title separator — `Block\WidgetField\Title`

Purely visual: renders a heading/divider inside the widget form to group fields. It stores no meaningful value — the displayed text comes from the parameter's `<description>`. Don't read this parameter on the frontend.

```xml
<parameter name="section_title" sort_order="40" required="false" visible="true" xsi:type="block">
    <label translate="true"> </label>
    <description translate="true"><![CDATA[<h2>Content</h2><hr/>]]></description>
    <block class="MageOS\AdvancedWidget\Block\WidgetField\Title"/>
</parameter>
```

#### Repeatable rows — `Block\WidgetField\Rows`

The headline feature: unlimited sortable rows, each with its own field set. The parameter **name must start with `repeatable_`** — both the save plugin (`Plugin\SaveRepeatableItems`) and the admin renderer detect repeatable values by that prefix. A widget can have several repeatable parameters, each with its own `Rows` subclass.

```xml
<parameter name="repeatable_items" xsi:type="block" sort_order="50" required="false" visible="true">
    <label translate="true">Items</label>
    <block class="Vendor\Module\Block\Widget\Repeatable\CardItem"/>
</parameter>
```

### Repeatable row fields

Extend `MageOS\AdvancedWidget\Block\WidgetField\Rows` and fill the `$rows` property — an ordered map of `field_key => config`:

```php
class CardItem extends \MageOS\AdvancedWidget\Block\WidgetField\Rows
{
    protected $rows = [
        'title' => [
            'label' => 'Title',
            'type' => 'text',
            'required' => true,
            'preview' => true,
        ],
        'image' => [
            'label' => 'Image',
            'type' => 'image',
            'preview' => true,
        ],
        'style' => [
            'label' => 'Card style',
            'type' => 'select',
            'options' => [
                'light' => 'Light',
                'dark' => 'Dark',
            ],
            'preview' => false,
        ],
        'background' => [
            'label' => 'Background color',
            'type' => 'colorpicker',
            'default' => '#1E88E5',
            'description' => 'Shown as a tooltip next to the field.',
            'preview' => false,
        ],
    ];
}
```

#### Row config keys

| Key | Meaning |
| --- | --- |
| `label` | Admin label for the field |
| `type` | One of the row field types below |
| `required` | Adds admin-side validation on the row |
| `preview` | `true` → editable directly in the collapsed row list; `false` → only in the row's edit modal |
| `description` | Tooltip text next to the field |
| `options` | `value => label` map, `select` type only |
| `default` | Starting color for a `colorpicker` field, e.g. `#FF0000` or `rgba(0,0,0,1)`; falls back to `#FFFFFF` |

#### Row field types

| Type | Admin control | Stored value |
| --- | --- | --- |
| `text` | Single-line text input | Plain string |
| `textarea` | Multi-line text input | Plain string, line breaks preserved |
| `select` | Dropdown built from the `options` map | The selected option value |
| `image` | Media gallery browser button | Media-relative path (resolve with `getImageUrlByPath()`) |
| `product` | Product chooser grid | Product **ID** — see below |
| `wysiwyg` | TinyMCE editor in the row modal | HTML string (same escaping caveats as the WYSIWYG parameter) |
| `colorpicker` | [JSColor](https://jscolor.com/) picker (rgba format); starting color set via the row's `default` key | CSS color string, e.g. `rgba(255,255,255,1)` |

Notes:

- `text` and `textarea` are built in; `select`, `image`, `product`, `wysiwyg` and `colorpicker` are registered as custom field types in `etc/adminhtml/di.xml` (the `customFields` argument of `Block\Adminhtml\Renderer\Repeatable`). Third-party modules can register additional types through the same DI argument — each entry pairs a `type` name with a JS component and a `.phtml` partial.
- **`product`**: the field key must start with `product` — the admin renderer then auto-populates the sibling display keys `<field>_sku`, `<field>_name` and `<field>_image` for the row list. On the frontend, load the product from the stored ID; `ViewModel\ProductData::getProductById()` does this and pre-resolves `mageos_main_image`, `mageos_swatch_image`, `mageos_small_image` and `mageos_thumb_image` URLs on the product.

### Reading values on the frontend

Extend `MageOS\AdvancedWidget\Block\Widgets\AbstractColumns` in your widget block:

```php
foreach ($this->getRepeatableField('repeatable_items') as $item) {
    $title = $item['title'] ?? '';
    $image = $this->getImageUrlByPath($item['image'] ?? '');
    // ...
}
```

Available helpers:

| Method | Purpose |
| --- | --- |
| `getRepeatableField($name)` | Decoded array of row arrays for a repeatable parameter |
| `getRepeatableFieldAsObject($name)` | Same rows as `DataObject`s (`$item->getTitle()`) |
| `getImageUrl($field)` / `getImageUrlByPath($path)` | Resolve a stored media-relative path into a full media URL |
| `getPreparedUrl($url)` | Normalize relative link values against the store base URL |
| `getPreparedDescription($field)` | Replace `\EOL` markers with `<br />` |

 
## 🔧 Installation

1. Install it into your Mage-OS/Magento 2 project with composer:
    ```
    composer require mage-os/module-advanced-widget
    ```

2. Enable module
    ```
    bin/magento module:enable MageOS_AdvancedWidget
    bin/magento setup:upgrade
    ```

## 🤝 Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.


## 📄 License

The MIT License (MIT). Please see [License File](LICENSE) for more information.

### Attribution

This software uses Open Source software. See the [ATTRIBUTION](ATTRIBUTION.md) page for these projects.
