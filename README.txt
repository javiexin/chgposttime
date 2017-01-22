# chgpostime
Change Post Time extension for phpbb 3.1 and 3.2

Adds the option for a moderator to change the posting date and time of a post.

This is a port of the abandoned 3.0 mod found here: https://www.phpbb.com/community/viewtopic.php?p=12881195

Main features:
* Adds the possibility for a moderator to change the posting date and time of any single post.
* Configuration is done through the MCP, post moderation page, where a new block allows setting the new date and time when the post was made.
* Takes into consideration the change to update topic and forum accordingly.
* Permission based, uses a new moderator permission (both global and forum), "Can change post date/time", that copies the configuration of "Can change poster" in the forum where it is installed.
* Fix issue with moderators in different timezone than server
