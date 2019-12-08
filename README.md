# delete-comments-matching-blacklist

WordPress Plugin to help moderate spam when you have a huge backlog of comments.

I wrote this when I had a blog that attracted a lot of spam comments. 
I'd let it languish a while, and had a lot of comments to moderate.

To make moderation easier, this tool has two functions.

* Scan all the pending comments for keywords in the spam word blacklist, and delete the matching comments.
* Scan the usernames, urls, and email addresses in messages already marked as spam, and move pending comments that match to spam.

Both are kind of dangerous. I've lost a lot of legit comments due to bugs, and there may be other bugs.

Both tools also help you get through thousands of spammy posts.

To use it, upload the directory into wp-content/plugins/ and then activate the plugin.

Once you're done, deactivate the plugin.

## References

https://plugins.trac.wordpress.org/browser/delete-all-comments/trunk/delete-all-comments.php

https://plugins.trac.wordpress.org/browser/batch-comment-spam-deletion/trunk/batch-comment-spam-deletion.php

https://plugins.trac.wordpress.org/browser/comment-blacklist-updater/trunk/comment-blacklist-updater.php
