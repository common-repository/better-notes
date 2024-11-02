=== Better Notes ===
Contributors: jchristopher
Donate link: http://mondaybynoon.com/donate/
Tags: post, page, post, posts, pages, note, notes, text, WYSIWYG
Requires at least: 2.9.2
Tested up to: 2.9.2
Stable tag: 1.0.1b

**THIS IS A BETA VERSION - Currently in development**

Dynamically append sortable rich text Notes to your Pages and your Posts

== Description ==

Dynamically append sortable rich text Notes to your Pages and your Posts

== Installation ==

1. Download the plugin and extract the files
1. Upload `better-notes` to your `~/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Update your templates where applicable (see **Usage**)

== Frequently Asked Questions ==

Coming soon

== Screenshots ==

1. Dynamically add/edit/remove/sort additional TinyMCE (WYSIWYG) formatted Notes to your Posts and Pages.

== Changelog ==

= 1.0.1b =
* Added screenshot

= 1.0b =
* Complete rewrite of Post Notes

== Usage ==

After installing Better Notes, you will need to update your template files in order to pull the data to the front end.

To pull all Better Notes for a Post or Page, fire `betternotes_get_betternotes()`. There is one optional parameter which can force a Post ID if `betternotes_get_betternotes()` is fired outside The Loop. If used inside The Loop, all Better Notes will be pulled for the current Post or Page.

Firing `betternotes_get_betternotes()` returns an array consisting of all available Better Notes. Currently each Better Note has 1 piece of data available:

* **copy** - The Better Note copy

Here is a basic implementation:

`<?php 
  $betternotes = betternotes_get_betternotes();
  $total_betternotes = count($betternotes);
  if( $total_betternotes > 0 )
  {
    echo '<div class="betternote">';
    for ($i=0; $i < $total_betternotes; $i++)
    {
      echo $betternotes[$i]['copy'];
    }
    echo '</div>';
  }
?>`