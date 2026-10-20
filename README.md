# Slider block for Moodle (block_slider)

Responsive and accessible slideshow block. Each slide has an image (with an optional
image for narrow screens), title, subtitle, description, link and an optional button.

## Features

* Slide or fade transition, one or several slides visible at once.
* Controls in a bar below the slider or on the image: arrows, pagination, pause button.
* Captions on the image (shade, full background, box, text only), below the image or next
  to it as a card. Text size, width, alignment, position, background colour and opacity
  can be set per block. Captions that do not fit on the image move below it automatically.
* Image proportions (presets, custom or natural size), separate proportions for narrow
  screens, image fit, maximum width, gap, rounded corners, block without frame.
* Autoplay with pause on hover and keyboard focus, loop, swipe, keyboard navigation,
  respect for the "reduced motion" preference (WAI-ARIA carousel pattern, WCAG 2.2.2).
* Management page: add, edit, hide and delete slides, change the order by drag and drop
  (also from the keyboard) or with arrows, preview of both images, link copy.
* No external JavaScript libraries.
* Backup and restore, course import, copying with the default Dashboard, privacy API.
* Works with [filter_slider](https://github.com/limsko/moodle-filter_slider):
  use `[SLIDER-<blockid>]` in any text.

## Requirements

| Branch | Moodle |
|---|---|
| `MOODLE_405_STABLE` | 4.2 - 4.5 |
| `MOODLE_503_STABLE` | 5.0 - 5.3 |

Version numbers (`YYYYMMDDXX`) end with 00-49 on `MOODLE_405_STABLE` and 50-99 on `MOODLE_503_STABLE`,
so for the same date the 5.x build is always newer. Release fixes on both branches together.

## Installation

Install from the Moodle plugins directory or copy the code to `blocks/slider`
(`public/blocks/slider` in Moodle 5.1 and later) and visit the notifications page.

Add the "Slider" block to a page, turn editing on and use "Manage slides" in the block.
Display options are in the block settings.

## Upgrading from 0.x

Slides, images and settings of older versions (BX Slider, SlidesJS, Glider.js) are migrated
automatically. Migrated blocks keep the controls on the image, as before.

## Version history

### 1.0.0
* Rewritten block: own lightweight carousel, BX Slider, SlidesJS and jQuery removed.
* New management page with drag and drop, hiding slides and image previews.
* Many new display options (layout, captions, controls, proportions).
* Image for narrow screens, alternative text, button label, link in a new window.
* Backup and restore of slides, privacy API, external function for reordering.
* PHPUnit and Behat tests.
* Database table renamed to `block_slider_slides`.

### 0.4.0
* Responsive BX Slider, image descriptions, captions on hover.

Older history is available in the git log.

## License

GNU GPL v3 or later. (C) 2015-2026 Kamil Łuczak
