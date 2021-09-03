# Illinois Framework Project

This is a [Composer](https://getcomposer.org/)-based installer for the [Illinois Framework Drupal distribution](https://github.com/web-illinois/illinois_framework_profile). For more information about the Illinois Framework project, please check out the visit the [Illinois Framework Drupal distribution repository](https://github.com/web-illinois/illinois_framework_profile).

## Installation

If you're comfortable with using Composer and have a hosting environment already, you can get started with the Illinois Framework with the command:

```
$ composer create-project --remove-vcs --repository="{\"url\": \"https://github.com/web-illinois/illinois_framework_project.git\", \"type\": \"vcs\"}" web-illinois/illinois_framework_project:^1.0 MY_PROJECT
```

It's possible Composer will run out of memory if PHP is configured to limit the amount of PHP memory available. To get around that, try prepending `COMPOSER_MEMORY_LIMIT=-1` to the above command:

```
$ COMPOSER_MEMORY_LIMIT=-1 composer create-project --remove-vcs --repository="{\"url\": \"https://github.com/web-illinois/illinois_framework_project.git\", \"type\": \"vcs\"}" web-illinois/illinois_framework_project:^1.0 MY_PROJECT
```

### Creating a cPanel site in web.illinois.edu

* Start by creating a new web hosting account on http://web.illinois.edu/, or using an already created account that is empty
* On the cPanel dashboard, open up Terminal (or SSH into your site if you prefer)
* Create one of the two the `site-build.sh` scripts below in your home (~) folder:

Option 1: _site-build.sh_ using MySQL as your database
> Optionally creates a new database and database user for you in your cPanel instance. If you have precreated your database, answer n to "Create Database?"
> Substitue your cPanel account name for "CPANELUSER" for the database name and username when prompted
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

Option 2: _site-build.sh_ using SQLite as your database
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

* Optional - create a `site-remove.sh` script that will delete your site. This is useful when developing the framework, but not when developing a site that relies on the framework.

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

* Run `site-build.sh`, and *be sure to take note of the admin password generated at the end of the script*.
* You should have a new Illinois Framework site available at your cPanel address.

## Setting up Shibboleth authentication within your cPanel web.illinois.edu site

* Open up a cPanel Terminal session or SSH into your site
* In your project folder `~/my-fw-project`, run the command
```bash
composer config repositories.simplesamlphp '{"type": "path", "url": "/var/simplesamlphp-1.18"}'
```
* SimpleSAMLPHP is already set up and configured in cPanel on web.illinois.edu. The above command tells composer where to find it.
* Next, run the below command to fetch the Drupal module [simpleSAMLphp Authentication](https://www.drupal.org/project/simplesamlphp_auth)
```bash
composer require drupal/simplesamlphp_auth:^3.2 -W
```
* Log into your framework site as an administrator
* From the admin toolbar, click on "Extend"
* Search for "Simplesaml" in the list of modules
* Click the box next to "SimpleSAMLphp Authentication"
* Click the "Install" button at the bottom of the page

![simplesaml-install](https://user-images.githubusercontent.com/56594946/132043539-74833b8b-9d2f-499c-8b35-c09e674db21c.PNG)

* After installing the module, go to the configuration page by going to Configuration->People->SimpleSAMLphp Auth Settings
* Click on the "User info and synching" tab
* Set the first two fields to "uid" ("SimpleSAMLphp attribute to be used as unique identifier for the user" and "SimpleSAMLphp attribute to be used as username for the user"
* Check "Automatically enable SAML authentication for existing users upon successful login" on the same page and click "Save Configuration"

![simplesaml-config1](https://user-images.githubusercontent.com/56594946/132044290-3bb9e81d-82cf-41cf-91f5-1770351705e4.PNG)

* Click on the "Local Authentication" tab
* Uncheck "Allow SAML users to set Drupal passwords" and click "Save Configuration"

![simplesaml-config2](https://user-images.githubusercontent.com/56594946/132044492-07bb5f09-e8f3-4d91-ac77-a241e20855ff.PNG)

* Click on the "Basic Settings" tab
* Check "Activate authentication via SimpleSAMLphp"
* Change "Federated Log In Link Display Name" to "University of Illinois Login" and click "Save Configuration"

![simplesaml-config3](https://user-images.githubusercontent.com/56594946/132044734-e8b5158a-d168-485f-afb7-d25cce2bbe4e.PNG)

You should now be able to authenticate using the UIUC Shibboleth login system! If you go to /user/login for your site, you should see a "University of Illinois Login" button. Clicking on that will take you to the Shibboleth login page.

![simplesaml-config4](https://user-images.githubusercontent.com/56594946/132045163-aa51f1b3-4bbb-4439-b778-98ac133e39ff.PNG)

With the above configuration, any valid user with a NetID will be able to log into your site and automatically create an account. That user will not have any additional permissions. To give that user additional permissions, you will need to find them on the People admin page and assign them a role like administrator or editor.

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
