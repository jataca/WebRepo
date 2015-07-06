=== Artiss Relative Date ===
Contributors: dartiss
Donate link: http://artiss.co.uk/donate
Tags: ago, date, days, hours, relative, minutes, months, pages, posts, seconds, time, years
Requires at least: 2.5
Tested up to: 3.4.2
Stable tag: 1.2

Display a relative date (e.g. "4 days ago")

== Description ==

Relative dates are where, instead of showing the exact date of a post, you can say it was posted "4 days ago." Tomorrow, this message would say "5 days ago." This plugin, originally named WP Relative Date, has been designed to be called from your theme to display these relative dates as required.

It is based on code from a number of sites (none of which can be proven to be the originator!) but has been improved and converted to work with WordPress.

All relative dates are shown up to 2 depths and are not designed to be 100% accurate (due to rounding), but rather to give the reader an idea as to how old something is.

The dates will be shown in one the following formats, depending on relevance...

* Years, months
* Years, weeks
* Months, weeks
* Months, days
* Weeks, days
* Days, hours
* Hours, minutes
* Minutes, seconds
* Seconds

Some examples...

Something that is 1 years and 2 months and 3 weeks old will be output as "1 years, 2 months".
Something that is 1 years and 0 months and 3 weeks old will be output as "1 years, 3 weeks".

To use, you simply call either `get_relative_date` or `relative_date` - the first will return the relative date and the second will output it.

No parameters are required and if none are specified then it will simply create a relative date for the current post or page based on how old it is. However, you can specify as the first or second parameter a Unix format date (more on that in a minute). If one is supplied then the difference will be between that and the current date. If a second date is specified then the output will be the difference between the 2 dates.

For more information and advanced options please read the "Other Notes" tab.

**For help with this plugin, or simply to comment or get in touch, please read the appropriate section in "Other Notes" for details. This plugin, and all support, is supplied for free, but [donations](http://artiss.co.uk/donate "Donate") are always welcome.**

== Using the Functions ==

All dates specified must be in Unix format. In most cases you'll probably use this plugin to display a post time. That is returned by the code `get_the_time()`. By specifying `get_the_time('U')` instead, it will return it in Unix format. Don't forget, however, that if you don't specify a date then the post time will be assumed anyway. None-the-less, we'll use this date as an example.

So, to return the relative date instead you'd specify `get_relative_date( get_the_time( 'U' ) )`. Put that together along with an appropriate script to check that the plugin is active, you might have something like this in your theme...

`if ( function_exists( 'get_relative_date' ) ) {
    echo 'Created ' . get_relative_date( get_the_time( 'U' ) ) . ' ago.';
}`

If you need to convert other dates/times to Unix format, you may want to look at the PHP commands [`date`](http://php.net/manual/en/function.date.php "date") and [`strtotime`](http://www.php.net/manual/en/function.strtotime.php "strtotime").

== Additional Parameters ==

There is another parameter that you can use, if you wish to override the default divider (which is a comma). For example...

`get_relative_date( get_the_time( 'U' ) )`

Might produce a result of `3 days, 4 hours ago`. Whereas...

`get_relative_date( get_the_time( 'U' ), ' and ' )`

Would produce a result of `3 days and 4 hours ago`. Note that you need to specify any spaces as well around the divider that you need.

By default, all output has a maximum depth of 2 values - for example, `3 days, 4 hours ago`. If you specify a number of 1 or 2 as a parameter, however, you can control this depth. A depth of 1 with the previous example would output `3 days ago`.

With regard to the sequence of the parameters, it doesn't matter - you can specify the parameters in any order (up to 2 dates, 1 divider and 1 depth).

== Adding to Your Theme ==

If you wish to change your theme so that it used a relative date then you'll need to modify your theme files, for example `single.php`. Most themes will use `get_the_date`, `get_the_time`, `the_date` or `the_time` to output dates on a blog.

As an example, my theme has the following...

`the_time( get_option( 'date_format' ) );`

This is getting the default WordPress data format and then outputting the current posts' created date in this format. To convert to a relative date, you'd replace this with...

`relative_date( get_the_time( 'U' ) )`

In fact, leaving the parameter out will produce the same result, as this plugin will assume the date of the current plugin if nothing is specified. In this case, we're passing to the plugin the date of the current post in Unix format (which is the required format for this plugin).

== Licence ==

This WordPress plugin is licensed under the [GPLv2 (or later)](http://wordpress.org/about/gpl/ "GNU General Public License").

== Support ==

All of my plugins are supported via [my website](http://www.artiss.co.uk "Artiss.co.uk").

Please feel free to visit the site for plugin updates and development news - either visit the site regularly, follow [my news feed](http://www.artiss.co.uk/feed "RSS News Feed") or [follow me on Twitter](http://www.twitter.com/artiss_tech "Artiss.co.uk on Twitter") (@artiss_tech).

For problems, suggestions or enhancements for this plugin, there is [a dedicated page](http://www.artiss.co.uk/wp-relative-date "WP Relative Date") and [a forum](http://www.artiss.co.uk/forum "WordPress Plugins Forum"). The dedicated page will also list any known issues and planned enhancements.

== Installation ==

1. Upload the entire `wp-relative-date` folder to your wp-content/plugins/ directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. That's it, you're done - you just need to add the function call to your theme!

== Frequently Asked Questions ==

= Which version of PHP does this plugin work with? =

It has been tested and been found valid from PHP 4 upwards.

Please note, however, that the minimum for WordPress is now PHP 5.2.4. Even though this plugin supports a lower version, I am not coding specifically to achieve this - therefore this minimum may change in the future.

== Changelog ==

= 1.2 =
* Maintenance: Split code into separate files within an `includes` folder. Have a single root file that includes all the relevant code
* Maintenance: Re-written README
* Enhancement: Added new depth parameter
* Enhancement: Added internationalisation

= 1.1.1 =
* Maintenance: Removed dashboard widget

= 1.1 =
* Bug: Now calculates current time properly - gets server time in GMT and applies GMT difference, as specified in user's WP options
* Enhancement: New parameter added that allows user to change the divider used in output
* Enhancement: User does not now need to pass any dates to the function - in this case the relative date for the current post/page will be returned
* Enhancement: Renamed functions that user does not specify to avoid clashes with other plugins
* Enhancement: Allow parameters to be specified in any order
* Enhancement: Added useful links to plugin page
* Maintenance: Renamed to Artiss Relative Date and brought code up to current standards
  
= 1.0 =
* Initial release

== Upgrade Notice ==

= 1.2 =
* Upgrade to add new features and add internationalisation

= 1.1 =
* Upgrade to remove the dashboard widget

= 1.1 =
* Upgrade to fix a bug in the time difference calculation and to add new functionality

= 1.0 =
* Initial release