# Illinois-Framework-Project

## Creating a development site in web.illinois.edu

* Start by creating a new web hosting account on http://web.illinois.edu/, or using an alredy created account that is empty
* On the cPanel dashboard, open up Terminal (or SSH into your site if you prefer)
* Create the two following scripts in your home (~) folder:

_site-build.sh_
```bash
#!/bin/bash
COMPOSER_MEMORY_LIMIT=-1 composer create-project --repository https://fwpackages.web.illinois.edu/ atlas-web/illinois-framework-project:dev-master my-fw-project
ln -s ~/my-fw-project/vendor ~/vendor
ln -s ~/my-fw-project/docroot/.* ~/public_html/
ln -s ~/my-fw-project/docroot/* ~/public_html/

# Install Drupal
~/vendor/drush/drush/drush site:install illinois_framework --yes --db-url=$DB_URL --site-name=IllinoisFramework
```

_site-remove.sh_
```bash
#!/bin/bash
rm ~/public_html/*
rm ~/public_html/.*
rm ~/vendor
chmod 775 ~/my-fw-project/docroot/sites/default/
rm -R -f ~/my-fw-project
```

Be sure to `chmod 770` the scripts so that you can run them.

After running `site-build.sh`, you should be able to browse to your site and start the Drupal installation process. I recommend using the SQLite DB option for simplicity and speed.

Once your site is installed, you should be able to modify the template files via the Terminal or SSH by browsing to `cd ~/my-fw-project/docroot/themes/contrib/illinois-framework-theme/`. To commit your changes to the [Illinois-Framework-Theme](https://github.com/ATLAS-Illinois/Illinois-Framework-Theme) repository, you'll want to follow the steps at https://help.github.com/en/github/authenticating-to-github/authorizing-an-ssh-key-for-use-with-saml-single-sign-on.

To rebuild your cPanel site from scratch, you can run the below command from your home directory:

```bash
./site-remove.sh; ./site-build.sh
```
