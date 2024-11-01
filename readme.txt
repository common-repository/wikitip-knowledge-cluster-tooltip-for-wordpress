=== Plugin Name ===
Contributors: rvencu
Donate link: 
Tags: tooltip, dictionary, glossary, thesaurus, wikitip, jQuery, JSON, AJAX, multisite, terms, definitions
Requires at least: 3.1
Tested up to: 5.5
Stable tag: 1.5

This plugin integrates a tooltip dictionary/glossary/thesaurus with terms definitions stored at wikitip.info. Users may create their own clusters for free.

== Description ==

This plugin integrates a tooltip dictionary with terms definitions stored at wikitip.info. Wikitip.info is a WordPress Multisite installation available for subscription of subdomains
that are named Knowledge Clusters. All post tags are making the terms dictionary, post content is the definition of the term. More than one subdomain/sites can be used and
they can get custom hierarchy in the network. Several hierarchies can coexist. The plugin can tap into any hierarcy's node and will provide terms from that node and all it's descendants.

1. Ability to connect to own or public dictionaries for free at wikitip.info
1. Ability to subscribe to specialty dictionaries at wikitip.info
1. Ability to use the tooltip only on specific pages (see options)
1. Ability to disable the tooltip for specific pages via metabox in edit screen
1. Communication with wikitip.info is optimized no matter how big is the content of the page or the size of the dictionary of terms
1. Analytics data sent to wikitip.info for site owners
1. Ability to define multiple glossaries/dictionaries on the same installation. Just define and use different categories to group definitions in glossaries. Users have the ability to enable or disable glossaries they like to use

**Other features**

1. Multisite compatible

== Installation ==

1. Upload `wikitip.zip` to the `/wp-content/plugins/` directory
1. Unzip the archive
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Fill up the required option settings
1. Adjust the css file to fit your website look and feel
1. Build your own dictionary at wikitip.info and connect to it

== Frequently Asked Questions ==

= How do I use this plugin? =
Please go to <a href="http://wikitip.info/subscribe/">http://wikitip.info</a> and apply for an account. Please mention if you need your own cluster or you want to use an existing one. 
If you already have a dictionary we will help importing it in our system.

= What is the cost for using this technology? =
Usually if you use your own cluster there is no cost involved. However we plan to host premium features so we can cover our own expenses with hosting of
wikitip.info system. If you plan to upload a broad dictionary with a lot of terms definitions this will require some paid subscription since more resources must be allocated.

= I have my own glossary but I am concerned to import it to your system since it is my private property =
We respect the copyright of user's information. Please read our terms of service, you only need to give us enough rights to host the information and to promote it.
If you like you can choose to monetize your glossary since it can be used by other website owners via paid subscriptions. Or if you like you can release all the information
to the public domain then everyone can access it for free.

= I installed and configured the plugin and get the terms underlined but I see no tooltip when I put the mouse over them? =
There is an issue with the z-index value of your tooltip. Please go to the options menu and experiment with values such as `9999`

= How do I determine what container to use? =
A container is a HTML element that contain the text you want to check against the knowledge cluster (glossary). If your cluster is small in size 
and specialized in some field perhaps it is a good idea to use as container the entire content of your post. Look in source code of your blog page
and find what element holds your content. For Twenty Eleven theme this is `div.entry-content`. However if you have a broad knowledge cluster such as a dictionary
then explaining every word in the content may not make sense. In this case you can decide for a method to announce the terms you want explained and a good
idea is to emphasize them at the edit time. Just select the terms and format them as italic. WordPress editor will surround the terms inside a `<em>` tag and
you can simply use `em` as container.

= Is the access to the definitions secure? =
We took measures to prevent unauthorized access to the terms definitions. Therefore only eligible subscribers can have access to certain knowledge clusters and only
for the duration of their subscription.


== Screenshots ==

1. Administration interface
1. Usage demo

== Changelog ==

= 1.5 =
1. fixed the user panel

= 1.4.1 =
1. removed scriptaculous script that breaks javascript on backend

= 1.4 =
1. cleanup code

= 1.3 =
1. updated compatibility

= 1.2 =
1. now the server is setup with SSL
1. server upgraded with newest igbinary code, trie objects regeneration is required before usage

= 1.1 =
1. various fixes
1. compatibility with WP 4.3

= 1.0.3 =
1. jQuery compatibility updated

= 1.0.2 =
1. fixed a bug where multiple custom fields with the same name were created via the post edit metabox.
1. fixed language matching

= 1.0.1 =
1. fixed a small jQuery bug introduced in version 1.0 that prevented the load of definitions
1. added controls in admin panel to generate trie objects at server side. Trie generation is necessary after new definitions are added or modified by addition of new tags
1. added option to ignore cluster's language, meaning you can use definitions in different language than the target website language
1. other minor improvements in the admin area

= 1.0 =
1. Added smart sorting algorithm for returned definitions, useful when integrating multiple dictionaries. The sorting score weights can be edited in the backend
1. Now frontend filtering by dictionaries / glossaries / categories does not require to reload the page

= 0.1.6 =
1. fixed a small jQuery bug introduced in version 0.1.5 that prevents user control panel to be expanded

= 0.1.5 =
1. Fixed a broken link to the category of term definition
1. Added tooltip pagination option
1. Now the plugin matches the page's language against cluster's language. If the language is not available at the cluster's side, an error message is sent back.
Supports single language sites as well as multilanguage sites with qTranslate or WPML plugin. WPML pro plugin is used at server side to define multilanguage clusters.
Please note that current version (2.4.3) of WPML has some bug generating slow SQL queries if used into a big size cluster.
1. Added optional user-level controls with storage of settings in cookies
1. Ability to filter definitions by glossaries stored as categories at the server side when user controls are enabled


= 0.1.4 =
1. added new algorithm for terms matching. Now matching is possible independent of language, for instance western languages may be combined with Chinese Simplified
characters and words where no spaces or punctuation is used. Algorithm performance: 145000 terms matched in 1.2 seconds, 1050000 terms matched in less than 6 seconds.
1. added terms inflexions (admin tools to add inflexions at clusters side will become available soon)
1. expressions options are phased out since the new algorithm includes this option by default

= 0.1.3 =
1. further optimizations to improve speed: use of cache to store dictionary objects. Reduced number of connections to the terms server via modified PHP proxy.
1. added option to the clusters side to declare it as broad dictionary (>20000 terms) or small dictionary. For small dictionary brute force detection is automatically
used to improve expression match.
1. added option to use words clusters only or to add expressions as well. Works with broad dictionaries only

= 0.1.2 =
1. z-index still got some issues, there is a new option added in the administration screen to manually test different settings, depending on your theme.
Some themes accept the default `auto` setting, others require to input a specific number such as `9999`. Accepts numbers, `auto` or `inherit`.
1. fixed an issue in pages with multiple occurences of the container (such as loops where the content container repeats for the number of displayed posts).
The issue was that when the found terms were marked up all the containers were filled with the content of the first one.
1. added more usage information in the FAQ section
1. added descriptions to plugin admin options
1. speed optimization on the server side now match the page content against more than 100K terms in less than 3 seconds

= 0.1.1 =
1. moved tooltip to z-index `9999` to avoid problems with some themes
1. added localization for tooltip, now tooltip is displayed in the cluster's main language
1. fixed tooltip effect bug
1. added option to identify only terms with specified minimum length

= 0.1.0 =
Incipient version

== Plugin TO DO list ==

This is our current to do list. Feel free to <a href="http://wikitip.info/forums/forum/developers/">suggest more features in the forums</a>.

1. insert adsense snippets within the tooltip definitions. An algorithm will rotate our adsense codes with customer's codes for all free plans.
Paid plans will use 100% adsense codes of the customer.
1. identify cluster owner in admin interface and restrict the trie object creation to this owner alone
