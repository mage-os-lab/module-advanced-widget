# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/en/1.0.0/)
and this project adheres to [Semantic Versioning](http://semver.org/spec/v2.0.0.html).

## 1.2.4
### Added
- Colorpicker repeatable field: configurable starting color via a `default` row config key (`'default' => '#RRGGBB'` or an `rgba(...)` value). When omitted it still falls back to `#FFFFFF`.
### Fixed
- sync correctly colorpicker values sync between preview and modal
- wysiwyg field now shows a limited number of elements on the toolbar

## 1.2.3
### Fixed
- Remove debugging code

## 1.2.2
### Fixed
- Change declaration for properties

## 1.2.1
### Fixed - Added
- Add wysiwyg and color repeatable component types
- Fix fatal error on redeclaration for properties removing protected attribute from $rawResultFactory and $getInsertImageConten. Thanks to @Sental

## 1.2.0 - 2026-04-21
### Fixed
- PHP 8.5 compatibility: cast nullable values to string before passing to `str_replace`, `strpos`, `str_contains`, `preg_match`, `explode`, `urldecode` and similar string functions to avoid deprecation notices and fatal errors
- Cast nullable values to string/int before using as array index (PHP 8.5 null array key deprecation)
- Replace deprecated `strpos` / `strpos(..., 'http') === false` usages with `str_starts_with` / `str_contains`
- Add `declare(strict_types=1)` and missing return type hints to `Controller/Adminhtml/Image/Chooser`
- Clean up redundant `$this->escaper = $escaper ?: ObjectManager::getInstance()->get(...)` fallback in `Controller/Adminhtml/Product/Widget/Chooser` (constructor already enforces non-null Escaper)

### Changed
- Change remaining `private` promoted/regular properties to `protected` for consistent extensibility across all classes

## 1.1.2
### Fixed
- Fix getRowClickCallback() returning null instead of string when massaction is enabled
- Change private constructor properties to protected for extensibility

## 1.1.1
## Fixed
- Unset missing products from repeatable fields during widget configuration rendering

## 1.1.0
## Updated
- Update composer JSON making the module installable for Magento Opensource also

## 1.0.0
### Added
- First Commit, now is possible to define CMS widgets with multiple row fields!
