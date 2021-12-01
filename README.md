# wp-customizations
A repository to house WordPress customizations.


## [duplicate_as_draft.php](functions/duplicate_as_draft.php)
###### Description:
Adds links to the page and post lists of the WP Admin panel that allows an administrator to duplicate a page or post completely. The only property not duplicated is the ID (for obvious reasons). The slug is modified to append the "-backup" to the duplicate's slug. This should prevent WordPress from automatically changing the slug on the original.

###### Installation
Simply copy the file's code (except the very first and last lines) and paste it at the bottom of the functions.php file located in your wp-includes directory. don't copy the line that reads `<?php`, or the line that ends the file `?>` as they are only present to make github's color-coding work properly.
