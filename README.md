# Illinois Framework Project

This is a [Composer](https://getcomposer.org/)-based installer for the [Illinois Framework Drupal distribution](https://github.com/web-illinois/illinois_framework_profile). It is intended to be used on the UIUC cPanel instance at https://web.illinois.edu. For information about what content types and modules are included, please check out the [Illinois Framework Drupal distribution repository](https://github.com/web-illinois/illinois_framework_profile). This distribution is maintained by the [Illinois WIGG-Drupal group](https://webtheme.illinois.edu/about/drupal/). An example site that showcases the features of this distribution can be found at [https://drupal.webtheme.illinois.edu/](https://drupal.webtheme.illinois.edu/).

## Prerequisites

* A fresh cPanel account on https://web.illinois.edu
* Github [personal access token](https://docs.github.com/en/github/authenticating-to-github/keeping-your-account-and-data-secure/creating-a-personal-access-token) that is [enabled for SSO](https://docs.github.com/en/github/authenticating-to-github/authenticating-with-saml-single-sign-on/authorizing-a-personal-access-token-for-use-with-saml-single-sign-on)
  * Save the token string somewhere safe.  You will need it for each new site you install.
  * You will be prompted for it after you run the composer command below that installs your site.  Paste it into the terminal window and hit enter.  You will not see it when pasting.
  * Creating individual keys for each install is advisable but will require more maintenance and record keeping.  If you will be sharing your cPanel account with others you should create a new key.
  * The key you enter will be stored and reused when you run the update script.

## Creating a cPanel site in web.illinois.edu

1. From the cPanel dashboard, open up Terminal (or SSH into your site if you prefer)
2. Run the `composer` command below from your home (~) directory. _Be sure to take note of the admin password displayed at the end of the script._

```
composer create-project --remove-vcs --no-dev --repository="{\"url\": \"https://github.com/web-illinois/illinois_framework_project.git\", \"type\": \"vcs\"}" web-illinois/illinois_framework_project:1.x-dev illinois_framework
```

3. Access the site at _\<your domain prefix\>_.web.illinois.edu
4. Login to your site at _\<your domain prefix\>_.web.illinois.edu/user/login

Congrats! You should now have a Illinois Framework Drupal site!

## Extra information:

* Be sure to add the `drush` alias to your .bashrc file using the [instructions below](#drush-and-drupal-console)
* The files for your site are stored in the ~/illinois_framework directory
* Files uploaded to the site are stored in ~/illinois_framework/docroot/sites/default/files
* The MySQL database username and password is stored in ~/.my.cnf
* If you lose/forget your Drupal admin password, you can reset it with drush using the command `drush upwd admin "NEWPASSWORD"`

## Shibboleth authentication
Instructions for adding Shibboleth to your Illinois Framework site is [in the wiki](https://github.com/web-illinois/illinois_framework_project/wiki/Setting-up-Shibboleth-authentication-within-your-Illinois-Framework-Drupal-site).

## Updating your site

Security updates are regularly relased for Drupal and its modules, so it's vital to keep your site updated. To update your site, open the terminal for your site, `cd` to the directory `~/illinois_framework`, and run the following commands:

```bash
composer update --with-all-dependencies --no-dev -o
drush updb -y; drush cr; drush ccr; drush config-distro-update -y
```

The above commands assume you have a `drush` alias set up already. See below for adding the alias to your site.

### Upgrading to version 2 of the framework

Version 2 of the framework was released 1/12/2022. To upgrade your site from version 1.x to version 2, follow the steps below:

- Make a backup of your site (see below)
- Make the directory `~/illinois_framework/docroot/sites/default` writable by running `chmod 755 ~/illinois_framework/docroot/sites/default`
- Run the `composer update...` and `drush updb...` commands above to update your site
- If you are using Shibboleth to log into your site, [follow the instructions in the wiki](https://github.com/web-illinois/illinois_framework_project/wiki/Setting-up-Shibboleth-authentication-within-your-Illinois-Framework-Drupal-site#login-loop-error---updated-11222) to fix a possible login loop error
- Finally, update your site to version 2 by running `composer require web-illinois/illinois_framework_profile:^2.0 -W` from your `~/illinois_framework` directory

## Backing up your site

It's important to periodically back up your site. The most important part of a Drupal site is the database. The below commands will create a `~/backups` folder and create a database backup of your site. It will also create a backup of your composer.json and composer.lock, which can be useful in restoring a site's code back to a previous state. Replace the *YOUR_DB_NAME* with the name of your database.

```bash
mkdir -p ~/backups
mysqldump YOUR_DB_NAME | gzip > ~/backups/db.$(date +%F.%H%M%S).sql.gz
tar cvf - ~/illinois_framework/composer* | gzip > ~/backups/composer.$(date +%F.%H%M%S).tar.gz
```

## Drush and Drupal Console

[Drush](https://www.drush.org/) is installed and available for your Framework site at `~/illinois_framework/vendor/drush/drush/drush`.

[Drupal Console](https://drupalconsole.com/docs/en/about/what-is-the-drupal-console) is installed and available for your Framework site at `~/illinois_framework/vendor/bin/drupal`.

You can add the below alias commands to your `~/.bashrc` to keep from having to type the whole path each time:

```bash
alias drush='$HOME/illinois_framework/vendor/drush/drush/drush'
alias drupal='$HOME/illinois_framework/vendor/bin/drupal'
```

## Extending the Illinois Framework

If you would like to extend the Illinois Framework with additional [modules](https://www.drupal.org/project/project_module) or [themes](https://www.drupal.org/project/project_theme), you need to use composer to add them to your site.  

| Task                                            | Composer                                          |
|-------------------------------------------------|---------------------------------------------------|
| Installing a contrib project (latest version)   | ```composer require drupal/PROJECT```             |
| Installing a contrib project (specific version) | ```composer require drupal/PROJECT:1.0.0-beta3``` |
| Updating a single contrib project               | ```composer update drupal/PROJECT```              |

## Source Control
You should commit the files in the `~/illinois_framework` directory to source control. You can run `git init` in the `~/illnois_framework` directory to initialize the directory as a git repository. After that, you'll want to commit the files and push them to a remote repository.

If you peek at the ```.gitignore```, you'll see that certain directories, including all directories containing contributed projects, are excluded from source control. In a Composer-based project like this one, **you SHOULD NOT commit your installed dependencies to source control**.

When you set up the project, Composer will create a file called ```composer.lock```, which is a list of which dependencies were installed, and in which versions. **Commit ```composer.lock``` to source control!** Then, when your colleagues want to spin up their own copies of the project, all they'll have to do is run ```composer install```, which will install the correct versions of everything in ```composer.lock```.

## How do I update Drupal core?
It's counterintuitive, but **don't add `drupal/core` to your project's composer.json!** The Illinois Framework manages Drupal core for you, so adding a direct dependency on Drupal core is likely to cause problems for you in the future.

## Developing the Illinois Drupal Framework
Instructions on setting up a local Docker-based development environment are located at [https://github.com/web-illinois/illinois_framework_localdev](https://github.com/web-illinois/illinois_framework_localdev).
