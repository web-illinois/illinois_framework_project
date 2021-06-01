# Illinois Framework Project

This is a [Composer](https://getcomposer.org/)-based installer for the [Illinois Framework Drupal distribution](https://github.com/web-illinois/illinois_framework_profile).

## Installation

If you're comfortable with using Composer and have a hosting environment already, you can get started with the Illinois Framework with the command:

```
$ composer create-project --remove-vcs --repository="{\"url\": \"https://github.com/web-illinois/illinois_framework_project.git\", \"type\": \"vcs\"}" web-illinois/illinois_framework_project:1.x-dev MY_PROJECT
```

It's possible Composer will run out of memory if PHP is configured to limit the amount of PHP memory available. To get around that, try prepending `COMPOSER_MEMORY_LIMIT=-1` to the above command:

```
$ COMPOSER_MEMORY_LIMIT=-1 composer create-project --remove-vcs --repository="{\"url\": \"https://github.com/web-illinois/illinois_framework_project.git\", \"type\": \"vcs\"}" web-illinois/illinois_framework_project:1.x-dev MY_PROJECT
```

### Creating a development site in web.illinois.edu

* Start by creating a new web hosting account on http://web.illinois.edu/, or using an already created account that is empty
* On the cPanel dashboard, open up Terminal (or SSH into your site if you prefer)
* Create the two following scripts in your home (~) folder:

_site-build.sh_ using MySQL as your database
> Optionally creates a new database and database user for you in your cPanel instance. If you have precreated your database, answer n to "Create Database?" 
```bash
#!/bin/bash

COMPOSER_MEMORY_LIMIT=-1 composer create-project --remove-vcs --repository="{\"url\": \"https://github.com/web-illinois/illinois_framework_project.git\", \"type\": \"vcs\"}" web-illinois/illinois_framework_project:1.x-dev my-fw-project
ln -s ~/my-fw-project/vendor ~/vendor
ln -s ~/my-fw-project/docroot/.* ~/public_html/
ln -s ~/my-fw-project/docroot/* ~/public_html/
CREATE="Y"

read -p "Enter your MySQL database name ex: [CPANELUSER_XXX]:" DBNAME;
read -p "Enter your database username ex: [CPANELUSER_XXX]:" DBUSER;
read -p "Enter your database password:" DBPASSWORD;
read -p "Create Database? [Y/n]" CREATE;

if [ $CREATE != n ]; then
#create database
uapi Mysql create_database name=$DBNAME

#create db user
uapi  Mysql create_user name=$DBUSER password=$DBPASSWORD

#add db user privs
uapi Mysql set_privileges_on_database user=$DBUSER database=$DBNAME privileges=SELECT,INSERT,UPDATE,DELETE,CREATE,DROP,INDEX,ALTER,CREATE%20TEMPORARY%20TABLES

fi
# Install Drupal
~/vendor/drush/drush/drush site:install --yes --site-name=IllinoisFramework --db-url="mysql://$DBUSER:$DBPASSWORD@localhost/$DBNAME"
```

_site-build.sh_ using SQLite as your database
```bash
#!/bin/bash
DB_URL=${DB_URL:-sqlite://sites/default/files/.ht.sqlite}

COMPOSER_MEMORY_LIMIT=-1 composer create-project --remove-vcs --repository="{\"url\": \"https://github.com/web-illinois/illinois_framework_project.git\", \"type\": \"vcs\"}" web-illinois/illinois_framework_project:1.x-dev my-fw-project
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

After running `site-build.sh`, you should have a new Illinois Framework site available at your cPanel address. Be sure to note the admin account password that's generated at the end of the script.

Once your site is installed, you should be able to modify the template files via the Terminal or SSH by browsing to `cd ~/my-fw-project/docroot/themes/contrib/illinois_framework_theme/`. To commit your changes to the [Illinois Framework Theme](https://github.com/web-illinois/illinois_framework_theme) repository, you'll want to follow the steps at https://help.github.com/en/github/authenticating-to-github/authorizing-an-ssh-key-for-use-with-saml-single-sign-on.

To rebuild your cPanel site from scratch, you can run the below command from your home directory:

```bash
./site-remove.sh; ./site-build.sh
```

## Maintenance

Updating the Framework and Drupal is done through Composer. Below are some commands that you can use to add and update Drupal modules.

| Task                                            | Composer                                          |
|-------------------------------------------------|---------------------------------------------------|
| Installing a contrib project (latest version)   | ```composer require drupal/PROJECT```             |
| Installing a contrib project (specific version) | ```composer require drupal/PROJECT:1.0.0-beta3``` |
| Updating all contrib projects and Drupal core   | ```composer update```                             |
| Updating a single contrib project               | ```composer update drupal/PROJECT```              |
| Updating Drupal core                            | ```composer update drupal/core```                 |

### Drush and Drupal Console

[Drush](https://www.drush.org/) is installed and available for your Framework site at `~/MY_PROJECT/vendor/drush/drush/drush`.

[Drupal Console](https://drupalconsole.com/docs/en/about/what-is-the-drupal-console) is installed and available for your Framework site at `~/MY_PROJECT/vendor/bin/drupal`.

You can add the below alias commands to your `~/.bashrc` to keep from having to type the whole path each time:

```bash
alias drush='/home/illinoisdrupal/my-fw-project/vendor/drush/drush/drush'
alias drupal='/home/illinoisdrupal/my-fw-project/vendor/bin/drupal'
```

## Source Control
If you peek at the ```.gitignore```, you'll see that certain directories, including all directories containing contributed projects, are excluded from source control. In a Composer-based project like this one, **you SHOULD NOT commit your installed dependencies to source control**.

When you set up the project, Composer will create a file called ```composer.lock```, which is a list of which dependencies were installed, and in which versions. **Commit ```composer.lock``` to source control!** Then, when your colleagues want to spin up their own copies of the project, all they'll have to do is run ```composer install```, which will install the correct versions of everything in ```composer.lock```.

## How do I update Drupal core?
It's counterintuitive, but **don't add `drupal/core` to your project's composer.json!** The Illinois Framework manages Drupal core for you, so adding a direct dependency on Drupal core is likely to cause problems for you in the future.
