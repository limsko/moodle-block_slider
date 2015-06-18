moodle-block_slider
======================

Description:
------------
#Slider block 

###This block creates a slideshow of images.

It should work with all bootstrap based themes.

**Installation:**
Download, extract, and upload the "slider" folder into moodle/blocks/


Version history:
----------------
0.1.0 
- First release
0.1.1
- fixed wrong risks in db/access
- fixed PHP notice when trying to get not yet set config property
- deleted unnecessary functions from code
- used moodle_url::make_file_url() to get file list instead of SQL
- removed font-awesome - using Moodle core theme icons to navigate forward/backward
+ added option to disable auto-play

Thanks:
-------
thanks to mudrd8mz for so detailed code review