###INSTALL

* Download Repo/files
* Create config/config.php file
  * There is an example file in the config/ directory
* Create config/search.php file
  * There is an example file in the config/ directory
* Run `composer update` from the main directory

###RUN

To run the program execute the `bin/autoresponder` script.  This will:
* Check the mailboxes specified in the `search.php` file
* Respond with the given email template
* Mark the original email as `ANSWERED`

Probably desirable to run script using the system cron.

###FEATURES

* Can check multiple mailboxes on an account
* Can run multiple searches
  * Each search can be associated with it's own response template
* Can send HTML responses with a TXT backup (optional)

### NOTES

* Config files go in the `/config` directory
* Email Response templates go in the `/template` directory
* Each `template.html` can have an optional `template.txt` file associated with it
* The search criteria and associated template files are defined in the `config/search.php` file