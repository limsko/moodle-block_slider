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
 * Simple slider block for Moodle
 *
 * @package   block_slider
 * @copyright 2015 Kamil Łuczak    www.limsko.pl     kamil@limsko.pl
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_slider extends block_base {
    
    public function init() {
        $this->title = get_string('pluginname', 'block_slider');
    }
    // The PHP tag and the curly bracket for the class definition 
    // will only be closed after there is another function added in the next section.
    
    public function get_content() {
        global $DB, $CFG;
        require_once($CFG->libdir . '/filelib.php');
        $this->page->requires->jquery();
        $this->page->requires->js('/blocks/slider/js/jquery.slides.min.js');
        
        
        if ($this->content !== null) {
            return $this->content;
        }
        
        // echo "<pre>";
        // print_r($this->context->id);	
        // echo "</pre>";	
        
        $this->content         =  new stdClass;
        $this->content->text = $this->config->text;
        
        file_rewrite_pluginfile_urls($this->content->text, 'pluginfile.php', $this->context->id, 'block_slider', 'content', NULL);
        
        $table = 'files';
        $select = "component = 'block_slider' AND contextid = '" . $this->context->id ."' AND filename != '.'";
        $fields = 'filename';
        $sort = 'filename';
        $images = $DB->get_records_select($table, $select, NULL, $sort, $fields);
        if (!empty($this->config->navigation)) {
        $this->content->text .= "<style type=\"text/css\">
												@font-face{
												font-family:'FontAwesome';
												src:url('".$CFG->wwwroot."/blocks/slider/fonts/fontawesome-webfont.eot?v=3.0.1');
												src:url('".$CFG->wwwroot."/blocks/slider/fonts/fontawesome-webfont.eot?#iefix&v=3.0.1') format('embedded-opentype'),
												url('".$CFG->wwwroot."/blocks/slider/fonts/fontawesome-webfont.woff?v=3.0.1') format('woff'),
												url('".$CFG->wwwroot."/blocks/slider/fonts/fontawesome-webfont.ttf?v=3.0.1') format('truetype');
												font-weight:normal;
												font-style:normal }
												</style>";
        $this->content->text .= '<link rel="Stylesheet" href="'.$CFG->wwwroot.'/blocks/slider/css/font-awesome.min.css">';
		}	
$this->content->text .= '
<div class="slider">
<div id="slides">';
        
        foreach ($images as $image) {
            
            
            
            $imagefile = $image->filename;
            $url = $CFG->wwwroot . '/pluginfile.php/' . $this->context->id . '/block_slider/content/' . $imagefile;
            
            $this->content->text .= '<img src="' . $url . '" />' ;
        }
        if (!empty($this->config->navigation)) {
            $this->content->text .= '
<a href="#" class="slidesjs-previous slidesjs-navigation"><i class="icon-chevron-left icon-large"></i></a>
<a href="#" class="slidesjs-next slidesjs-navigation"><i class="icon-chevron-right icon-large"></i></a>';
        }
        $this->content->text .= '</div></div>';
        
        
        if (!empty($this->config->text)) {
            
            
            //	print_r($this->config->attachments);
        }
        
        if (!empty($this->config->width) and is_numeric($this->config->width)) {
            
            $width = $this->config->width;
        }
        else {
            $width = 940;
        }
        
        if (!empty($this->config->height) and is_numeric($this->config->height)) {
            $height = $this->config->height;
        }
        else {
            $height = 528;
        }
			
		  if (!empty($this->config->interval) and is_numeric($this->config->interval)) {	
            $interval = $this->config->interval;
        }
        else {
            $interval = 5000;
        }
			
        if (!empty($this->config->effect)) {
            $effect = $this->config->effect;
        }
        else {
            $effect = 'fade';
        }
        
        if (!empty($this->config->pagination)) {
            $pag = 'true';
        }
        else {
            $pag = 'false';
        }
        
        
        $this->content->footer = '
<script type="text/javascript">
$(function() {
    $("#slides").slidesjs( {
        width: '.$width.',
        height: '.$height.',
        navigation: {
            active: false,
            effect: "'.$effect.'",
        }
        ,
        
        pagination: {
            active: '.$pag.',
            // [boolean] Create pagination items.
            // You cannot use your own pagination. Sorry.
            effect: "'.$effect.'",
            // [string] Can be either "slide" or "fade".
        }
        ,
        
        play: {
            active: false,
            // [boolean] Generate the play and stop buttons.
            // You cannot use your own buttons. Sorry.
            effect: "'.$effect.'",
            // [string] Can be either "slide" or "fade".
            interval: '.$interval.',
            // [number] Time spent on each slide in milliseconds.
            auto: true,
            // [boolean] Start playing the slideshow on load.
            swap: false,
            // [boolean] show/hide stop and play buttons
            pauseOnHover: true,
            // [boolean] pause a playing slideshow on hover
            restartDelay: 3000
            // [number] restart delay on inactive slideshow
        }
    }
    );
}
);
</script>	
';

			if(count($images) < 1) {
				$this->content->text = get_string('noimages', 'block_slider');
				$this->content->footer = '';
			}


        return $this->content;
    }
    
    function has_config() {
		return true;
	}
	public function instance_allow_multiple() { 
		return false;
	}
	public function applicable_formats() {
  		return array(
  			'site' => true,
  			'course-view' => true
  		);
	}
    
    public function hide_header() {
	 global $PAGE;
		if($PAGE->user_is_editing()){
        return false;
		} else {
			return true;
		}
    }
}
// Here's the closing bracket for the class definition

