# Bitbucket Auto Deploy #

A quick and easy way to set up automatic deployments from your Bitbucket repository.

Based on '[Automatic Bitbucket Deploy](https://bitbucket.org/lilliputten/automatic-bitbucket-deploy/)' by Igor Lilliputten (v.151005.001 (0.0.2))

Based on '[Automated git deployment](http://jonathannicol.com/blog/2013/11/19/automated-git-deployments-from-bitbucket/)' script by Jonathan Nicoal

* [Learn Markdown](https://bitbucket.org/tutorials/markdowndemo)

## Set up ##

TODO: Finish readme

### Basic ###

* Edit `config.php` and set your `$REPOSITORIES_PATH`, `$PROJECTS_PATH`, `$USER_NAME`, and `$PROJECTS`
* Create a folder called "deploy" (or whatever) in the public website directory of the domain to which you want to deploy. This folder will need to contain:
    * `bitbucket-hook.php`
    * `bitbucket.php`
    * `log.php`
    * your modified `config.php`
    * your log file (default: `bitbucket.log`)
    * `index.html`
    * TODO: .htaccess (?)
* For each watched repository on Bitbucket, create a webhook and point it towards your `bitbucket-hook.php` file,
    ie: `https://www.yourdomain.com/deploy/bitbucket-hook.php`
    Make sure that your webhook is triggered on 'push'.
    To edit webhooks, go to the repository's Settings > Webhooks

* Deployment instructions


### Options ###
TODO: this, for now just peruse `config.php`
Edit the `$CONFIG` in `config.php`, which is an associative array with the following options:


### Examples ###
* TODO: this, for now just peruse `config.php`

## Contact ##
