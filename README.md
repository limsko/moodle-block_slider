moodle-block_slider
======================

Description:
------------
**Slider block**

This block creates a slideshow of images.

It should work with all bootstrap based themes.

**Installation:**
Install using Moodle backend panel as described on https://docs.moodle.org/35/en/Installing_plugins

Download, extract, and upload the "slider" folder into moodle/blocks/

Supported Moodle versions:
--------------------------
I have tested plugin on clean install of Moodle 3.1 - 3.8

Version history:
----------------
0.2.1

- fixed bug #11 - Auto-play running when disabled under configuration
- fixed bug #10 - Pagination button stay visible when disabled under configuration
- small improvements


0.2.0

- multiple instances on single page can be added
- each slide is configurable
- each slide has optional: title, desc, href
- added support for Moodle 3.6.x, 3.7.x, 3.8.x
- bugfixes


0.1.0

- First release
0.1.1
- fixed wrong risks in db/access
- fixed PHP notice when trying to get not yet set config property
- deleted unnecessary functions from code
- used moodle_url::make_file_url() to get file list instead of SQL
- removed font-awesome - using Moodle core theme icons to navigate forward/backward
+ added option to disable auto-play
+ tested and working on Moodle 2.9
0.1.2
+ added support for Moodle 3.0
+ now allowed multiple instances of block
0.1.3
+ plugin is supported by Moodle 3.1, 3.2, 3.3, 3.4, 3.5
+ now using AMD format Javascript Modules
0.1.4
+ fixed polish translation
+ added help for setting width and height