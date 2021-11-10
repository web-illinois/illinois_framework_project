# Illinois Framework Project

This is a [Composer](https://getcomposer.org/)-based installer for the [Illinois Framework Drupal distribution](https://github.com/web-illinois/illinois_framework_profile). It is intended to be used on the UIUC cPanel instance at https://web.illinois.edu. For more information about the Illinois Framework project, please check out the visit the [Illinois Framework Drupal distribution repository](https://github.com/web-illinois/illinois_framework_profile).

## Prerequisites

* A fresh cPanel account on https://web.illinois.edu
* Github [personal access token](https://docs.github.com/en/github/authenticating-to-github/keeping-your-account-and-data-secure/creating-a-personal-access-token) that is [enabled for SSO](https://docs.github.com/en/github/authenticating-to-github/authenticating-with-saml-single-sign-on/authorizing-a-personal-access-token-for-use-with-saml-single-sign-on)
  * Save your token somewhere safe. You will need it to run the composer command that installs your site below.

## Creating a cPanel site in web.illinois.edu

* From the cPanel dashboard, open up Terminal (or SSH into your site if you prefer)
* Run the below command from your home (~) directory:

```
composer create-project --remove-vcs --no-dev --repository="{\"url\": \"https://github.com/web-illinois/illinois_framework_project.git\", \"type\": \"vcs\"}" web-illinois/illinois_framework_project:1.x-dev illinois_framework
```

* Congrats! You should now have a Illinois Framework Drupal site!
* _Be sure to take note of the admin password displayed at the end of the script_

## Extra information:

* The files for your site are stored in the ~/illinois_framework directory
* Files uploaded to the site are stored in ~/illinois_framework/docroot/sites/default/files
* The MySQL database username and password is stored in ~/.my.cnf
* If you lose/forget your admin password, you can reset it with drush using the command `drush upwd admin "NEWPASSWORD"`
* You can set up Shibboleth authentication to your Illinois Framework site using the instructions below

## Updating your site

Security updates are regularly relased for Drupal and its modules, so it's vital to keep your site updated. To update your site, open the terminal for your site, `cd` to the directory `~/illinois_framework`, and run the following commands:

```bash
composer update --with-all-dependencies --no-dev -o
drush updb -y; drush cr; drush ccr; drush config-distro-update -y
```

## Setting up Shibboleth authentication within your Illinois Framework Drupal site
Instructions for adding Shibboleth are [in the wiki](https://github.com/web-illinois/illinois_framework_project/wiki/Setting-up-Shibboleth-authentication-within-your-Illinois-Framework-Drupal-site).

## Extending the Illinois Framework

If you would like to extend the Illinois Framework with additional [modules](https://www.drupal.org/project/project_module) or [themes](https://www.drupal.org/project/project_theme), you need to use composer to add them to your site.  

| Task                                            | Composer                                          |
|-------------------------------------------------|---------------------------------------------------|
| Installing a contrib project (latest version)   | ```composer require drupal/PROJECT```             |
| Installing a contrib project (specific version) | ```composer require drupal/PROJECT:1.0.0-beta3``` |
| Updating a single contrib project               | ```composer update drupal/PROJECT```              |

### Drush and Drupal Console

[Drush](https://www.drush.org/) is installed and available for your Framework site at `~/illinois_framework/vendor/drush/drush/drush`.

[Drupal Console](https://drupalconsole.com/docs/en/about/what-is-the-drupal-console) is installed and available for your Framework site at `~/illinois_framework/vendor/bin/drupal`.

You can add the below alias commands to your `~/.bashrc` to keep from having to type the whole path each time:

```bash
alias drush='$HOME/illinois_framework/vendor/drush/drush/drush'
alias drupal='$HOME/illinois_framework/vendor/bin/drupal'
```

## Source Control
If you peek at the ```.gitignore```, you'll see that certain directories, including all directories containing contributed projects, are excluded from source control. In a Composer-based project like this one, **you SHOULD NOT commit your installed dependencies to source control**.

When you set up the project, Composer will create a file called ```composer.lock```, which is a list of which dependencies were installed, and in which versions. **Commit ```composer.lock``` to source control!** Then, when your colleagues want to spin up their own copies of the project, all they'll have to do is run ```composer install```, which will install the correct versions of everything in ```composer.lock```.

## How do I update Drupal core?
It's counterintuitive, but **don't add `drupal/core` to your project's composer.json!** The Illinois Framework manages Drupal core for you, so adding a direct dependency on Drupal core is likely to cause problems for you in the future.
