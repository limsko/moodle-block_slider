<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * English strings for block_slider.
 *
 * @package     block_slider
 * @author      Kamil Łuczak <kamil@limsko.pl>
 * @copyright   (C) 2015-2026 Kamil Łuczak (https://limsko.pl)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['accentcolor'] = 'Accent colour';
$string['accentcolor_help'] = 'Colour of the arrows, pagination dots and focus outlines in the #rrggbb format, for example #0f6cbf.';
$string['addslide'] = 'Add slide';
$string['alttext'] = 'Alternative text';
$string['alttext_help'] = 'Describes the image for people using screen readers. Leave empty to use the slide title. If the image is purely decorative and the slide has no title, it will be announced as decorative.';
$string['arrows'] = 'Arrows';
$string['arrows_desc'] = 'Show previous and next buttons';
$string['aspectcustom'] = 'Custom proportions';
$string['aspectheight'] = 'Height';
$string['aspectratio'] = 'Image proportions';
$string['aspectratio_auto'] = 'Natural image size';
$string['aspectratio_custom'] = 'Custom';
$string['aspectratio_help'] = 'Proportions of the slide frame (width : height). All slides keep the same height, so the page does not jump while they change. "Natural image size" uses the height of each image.';
$string['aspectwidth'] = 'Width';
$string['autoplay'] = 'Autoplay';
$string['autoplay_desc'] = 'Change slides automatically';
$string['autoplay_help'] = 'Slides change automatically. Autoplay stops while the pointer is over the slider or the keyboard focus is inside it, and it never starts for people who prefer reduced motion in their system settings.';
$string['backtopage'] = 'Back to the page';
$string['behaviour'] = 'Behaviour';
$string['buttontext'] = 'Button label';
$string['buttontext_help'] = 'When set and captions are shown, the link opens from a button on the slide instead of the whole slide being a link.';
$string['buttonx'] = 'Button: {$a}';
$string['captionalign'] = 'Text alignment';
$string['captionalign_center'] = 'Centre';
$string['captionalign_end'] = 'Right';
$string['captionalign_start'] = 'Left';
$string['captionbg'] = 'Background colour';
$string['captionbg_accent'] = 'Accent colour, light text';
$string['captionbg_dark'] = 'Dark, light text';
$string['captionbg_help'] = 'Colour of the shade, box or panel behind the caption. The text colour is chosen to stay readable: light on dark and accent backgrounds, dark on light ones.';
$string['captionbg_light'] = 'Light, dark text';
$string['captionhover'] = 'Show captions on hover';
$string['captionhover_desc'] = 'Hide captions until the pointer is over the slide (touch screens always show them)';
$string['captionopacity'] = 'Background opacity';
$string['captionopacity_help'] = 'Higher opacity improves readability of the text, lower shows more of the image.';
$string['captionposition'] = 'Caption position';
$string['captionposition_bottom'] = 'Bottom';
$string['captionposition_center'] = 'Middle';
$string['captionposition_top'] = 'Top';
$string['captions'] = 'Captions';
$string['captionsize'] = 'Text size';
$string['captionsize_large'] = 'Large';
$string['captionsize_medium'] = 'Medium';
$string['captionsize_small'] = 'Small';
$string['captionsize_xlarge'] = 'Very large';
$string['captionstyle'] = 'Caption layout';
$string['captionstyle_below'] = 'Below the image';
$string['captionstyle_box'] = 'Box on the image';
$string['captionstyle_gradient'] = 'Shade on the image';
$string['captionstyle_help'] = 'Where and how the caption is shown. "Next to the image" displays the slide as a card with the image on the left and the text panel on the right (stacked on narrow slides). Captions below and next to the image never cover it.';
$string['captionstyle_plain'] = 'Text on the image (no background)';
$string['captionstyle_side'] = 'Next to the image';
$string['captionstyle_solid'] = 'Full background on the image';
$string['captionwidth'] = 'Caption width';
$string['captionwidth_help'] = 'Maximum width of the caption text in relation to the slide.';
$string['carousel'] = 'carousel';
$string['choosepage'] = 'Choose slide to display';
$string['configtitle'] = 'Block title';
$string['configtitle_help'] = 'Shown as the block header. Leave empty to hide the header when the page is not being edited.';
$string['confirmdelete'] = 'Are you sure you want to delete this slide? Its images will be deleted as well.';
$string['content'] = 'Content';
$string['controls'] = 'Controls';
$string['controlsposition'] = 'Controls position';
$string['controlsposition_bottom'] = 'Bar below the slider';
$string['controlsposition_help'] = 'The bar below the slider never covers images and captions. Controls on the image save space, captions then keep a margin for the arrows.';
$string['controlsposition_overlay'] = 'On the image';
$string['copylink'] = 'Copy link';
$string['customaccent'] = 'Own colour';
$string['customaccent_desc'] = 'Use a custom colour instead of the theme colour';
$string['deleteslide'] = 'Delete slide';
$string['description'] = 'Description';
$string['device'] = 'Show on';
$string['device_all'] = 'All devices';
$string['device_desktop'] = 'Larger screens only';
$string['device_help'] = 'Hide the block on narrow (below 768 px) or on wider screens. The block is always visible while editing the page.';
$string['device_mobile'] = 'Narrow screens only';
$string['dots'] = 'Pagination';
$string['dots_desc'] = 'Show pagination dots below the slider';
$string['editslide'] = 'Edit slide';
$string['effect'] = 'Transition';
$string['effect_fade'] = 'Fade';
$string['effect_help'] = 'Slide moves the slides horizontally and can show several slides at once. Fade shows one slide at a time.';
$string['effect_slide'] = 'Slide';
$string['error_color'] = 'Enter the colour in the #rrggbb format.';
$string['error_interval'] = 'The value must be at least {$a} ms.';
$string['error_link'] = 'Enter a full address starting with http:// or https://, or a path on this site starting with /.';
$string['error_positive'] = 'Enter a positive number.';
$string['filtercode'] = 'To show this slider in any text, use the code:';
$string['frameless'] = 'Without frame';
$string['frameless_desc'] = 'Remove the block border, background and padding';
$string['gap'] = 'Space between slides';
$string['gotoslide'] = 'Go to slide {$a}';
$string['image'] = 'Image';
$string['image_help'] = 'Main slide image. For the best quality use an image at least as wide as the slider and with the proportions set in the block settings.';
$string['imagefit'] = 'Image fit';
$string['imagefit_contain'] = 'Show the whole image';
$string['imagefit_cover'] = 'Fill the frame (crop edges)';
$string['imagefit_help'] = 'How images with other proportions than the frame are shown.';
$string['interval'] = 'Slide duration (ms)';
$string['interval_help'] = 'How long each slide is displayed before the next one, in milliseconds (1000 ms = 1 s).';
$string['layout'] = 'Layout';
$string['link'] = 'Link';
$string['link_help'] = 'Full address (https://...) or a path on this site, for example /course/view.php?id=2.';
$string['linkcopied'] = 'Link copied to clipboard';
$string['loop'] = 'Loop';
$string['loop_desc'] = 'After the last slide go back to the first one';
$string['manageslides'] = 'Manage slides';
$string['maxwidth'] = 'Maximum width (px)';
$string['maxwidth_help'] = 'Limits the slider width. 0 means full width of the region.';
$string['mobileaspectratio'] = 'Proportions on narrow screens';
$string['mobileaspectratio_help'] = 'Proportions used below 768 px, for example a taller frame for images prepared for phones.';
$string['mobileaspectratio_same'] = 'Same as on larger screens';
$string['mobileimage'] = 'Image for narrow screens';
$string['mobileimage_help'] = 'Optional image shown below 768 px instead of the main image, for example with larger text or another crop.';
$string['newwindow'] = 'New window';
$string['newwindow_desc'] = 'Open the link in a new window';
$string['nextslide'] = 'Next slide';
$string['noimage'] = 'No image';
$string['noslides'] = 'There are no slides yet. Add the first slide.';
$string['notitle'] = 'No title';
$string['openlink'] = 'Open link';
$string['pause'] = 'Pause slideshow';
$string['pausebutton'] = 'Pause button';
$string['pausebutton_desc'] = 'Show a button to pause and resume autoplay';
$string['pausebutton_help'] = 'A visible pause button is required for automatically changing content by accessibility guidelines (WCAG 2.2.2).';
$string['pauseonhover'] = 'Pause on hover';
$string['pauseonhover_desc'] = 'Pause autoplay while the pointer is over the slider';
$string['play'] = 'Play slideshow';
$string['pluginname'] = 'Slider';
$string['previousslide'] = 'Previous slide';
$string['privacy:metadata:block'] = 'The slider block stores its settings in the blocks subsystem.';
$string['privacy:metadata:block_slider_slides'] = 'Slides of the slider block. Sliders added to the user\'s dashboard belong to that user.';
$string['privacy:metadata:block_slider_slides:alttext'] = 'Alternative text of the slide image.';
$string['privacy:metadata:block_slider_slides:buttontext'] = 'Label of the slide button.';
$string['privacy:metadata:block_slider_slides:description'] = 'Slide description.';
$string['privacy:metadata:block_slider_slides:link'] = 'Slide link.';
$string['privacy:metadata:block_slider_slides:subtitle'] = 'Slide subtitle.';
$string['privacy:metadata:block_slider_slides:timemodified'] = 'Time the slide was last modified.';
$string['privacy:metadata:block_slider_slides:title'] = 'Slide title.';
$string['privacy:metadata:core_files'] = 'Slide images are stored in the file system.';
$string['reorderhint'] = 'Drag slides to change their order.';
$string['rounded'] = 'Rounded corners';
$string['showcaptions'] = 'Show captions';
$string['showcaptions_desc'] = 'Show title, subtitle, description and button of the slide';
$string['showcaptions_help'] = 'Captions placed on the image get a dark shade or box to keep the text readable on any image.';
$string['sizepx'] = '{$a} px';
$string['slide'] = 'slide';
$string['slidedeleted'] = 'The slide has been deleted.';
$string['slidenumber'] = 'Slide {$a}';
$string['slider:addinstance'] = 'Add a new slider block';
$string['slider:manage'] = 'Manage slides of the slider block';
$string['slider:myaddinstance'] = 'Add a new slider block to the Dashboard';
$string['sliderlabel'] = 'Slider';
$string['slides'] = 'Slides';
$string['slidesaved'] = 'The slide has been saved.';
$string['slidestoshow'] = 'Slides visible at once';
$string['slidestoshow_help'] = 'Number of slides visible side by side. In narrow regions the number is reduced automatically, so that each slide stays at least about 220 px wide.';
$string['slidetitle'] = 'Title';
$string['slidexofy'] = '{$a->index} of {$a->total}';
$string['speed'] = 'Transition speed';
$string['speed_help'] = 'Duration of the transition between slides. People who prefer reduced motion always see slides change without animation.';
$string['speed_none'] = 'No animation';
$string['speedms'] = '{$a} ms';
$string['subtitle'] = 'Subtitle';
$string['swipe'] = 'Swipe';
$string['swipe_desc'] = 'Change slides by swiping on touch screens or dragging with the mouse';
$string['visibility'] = 'Visibility';
$string['visible_desc'] = 'Show this slide in the slider';
