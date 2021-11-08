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

## Updating your site

Security updates are regularly relased for Drupal and its modules, so it's vital to keep your site updated. To update your site, open the terminal for your site, `cd` to the directory `~/illinois_framework`, and run the following commands:

```bash
composer update --with-all-dependencies --no-dev -o
drush updb -y; drush cr; drush ccr; drush config-distro-update -y
```

## Setting up Shibboleth authentication within your Illinois Framework Drupal site

* Open up a cPanel Terminal session or SSH into your site
* In your project directory `~/illinois_framework`, run the command
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
alias drush='/home/illinoisdrupal/my-fw-project/vendor/drush/drush/drush'
alias drupal='/home/illinoisdrupal/my-fw-project/vendor/bin/drupal'
```

## Source Control
If you peek at the ```.gitignore```, you'll see that certain directories, including all directories containing contributed projects, are excluded from source control. In a Composer-based project like this one, **you SHOULD NOT commit your installed dependencies to source control**.

When you set up the project, Composer will create a file called ```composer.lock```, which is a list of which dependencies were installed, and in which versions. **Commit ```composer.lock``` to source control!** Then, when your colleagues want to spin up their own copies of the project, all they'll have to do is run ```composer install```, which will install the correct versions of everything in ```composer.lock```.

## How do I update Drupal core?
It's counterintuitive, but **don't add `drupal/core` to your project's composer.json!** The Illinois Framework manages Drupal core for you, so adding a direct dependency on Drupal core is likely to cause problems for you in the future.
