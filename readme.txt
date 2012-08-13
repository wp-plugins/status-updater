=== Facebook Status Updater ===
Contributors: 
Donate link: http://joeldentici.tk/donation/
Tags: share link facebook status update updater profile timeline
Requires at least: 3.2
Tested up to: 3.4.1
Stable tag: 2.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Facebook Status Updater allows you to connect to Facebook through the Graph API and share posts to your/your pages' Timeline when publishing posts.

== Description ==

Facebook Status Updater is a recoded and aesthetically neater version of the Status Updater written by devu.

It has been tested on version 3.4.1 of WordPress only but should work on 3.2 plus and maybe even earlier versions.

This **plugin requires JSON** for communication with Facebook via the Graph API (used for Authentication and Link Sharing).

It uses cURL by default but will try to fall back to sockets if cURL is not installed. The socket fallback only worked on one of the two servers I am currently using to test the plugin when I triggered it. It works flawlessly using cURL on both my servers.

Planned for next release:
* **Twitter Support**
* Fix any bugs people find

**What you need to install**
1. cURL or Sockets and JSON (automatically included in PHP 5.2+)
2. Probably WP 3.2 or greater
3. A Facebook account
4. A Mobile Phone to verify your Facebook account when creating the Facebook App
5. Probably a Facebook Page if you are a business so you aren't posting to your personal timeline.

== Installation ==

1. Install the downloaded zip file to WordPress (Plugins-Add New-Upload)
1. Activate the plugin through the 'Plugins' menu in WordPress.
1. Follow the on screen instructions to set up the app. You will need the information and requirements in the description.

== Frequently asked questions ==

= How Do I Share a Post =

To share a post you must first set up the plugin. After it is set up you must enable at least one of your Timelines (Profile/Pages/Apps). You will then see a meta box when adding/editing a post.

= Why do I need to enable Timelines? Why not just show them all on the meta box? =

I thought that it would be good for security on multi-author blogs/news sites to only allow posting to the Timelines that the Facebook Account Owner wants. This way one time Authors and employees cannot post to your personal Timeline or Cat Fan Page.

= I'm just having a bunch of problems =

Leave me a note of the issue and I'll work to resolve it. This is a new plugin and I have only tested it on two servers and in Google Chrome (which only matters for logging into Facebook to add the App). Give me steps to reproduce your error and leave details (if you get PHP errors with line numbers make sure to include them).

== Screenshots ==

1. This shows the setup page you will be taken to immediately after activating the plugin.
2. You can either login or go back to correct your App ID and App Secret
3. Authorize Facebook. Afterwards click the finish button.
4. This is the screen you see after it is set up. It has instructions below the first postbox now.

== Changelog ==

= 2.0 =
* First version (even though it is 2.0 because it is meant to be a continuation of the original Status Updater)
* Might have bugs. I have solved all the ones that came up for me. Leave notes of bugs.

== Upgrade notice ==

= 2.0 =
First version of the plugin Released.
