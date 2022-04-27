# Diff
Diff (diff.wikimedia.org) is a blog by and for the Wikimedia volunteer community to connect and share learnings, stories, and ideas from across our movement. 

The maintenance and support of Diff is facilitated by the Communications team at the Foundation with dedicated staff to support the editorial process. We plan to present Diff to all community-facing teams, and we are looking forward to hearing your feedback and suggestions. If you have any questions or ideas for something you'd like to share, please let us know. 


## Reporting issues
Site not working? Have a bug to report? Let us know!

* Create a Phabricator task (https://phabricator.wikimedia.org) and add the #wmf-communications tag
* Leave a note on the project talk page https://meta.wikimedia.org/wiki/Diff_(blog)
* Email the team at diff{{at}}wikimedia.org

## Architecture
Diff is hosted on WordPress VIP. For local development, see this guide. https://wpvip.com/documentation/vip-go/local-vip-go-development-environment/

Diff uses a WordPress theme called "Interconnection" based on \_s (underscores) a popular starter theme framework. It is designed by hang Do Thi Duc and follows the Wikimeida Design (https://design.wikimedia.org/style-guide/)  and branding (https://meta.wikimedia.org/wiki/Brand) style guides. 

Diff uses the following plugins.
* Polylang for translation
* PublishPress for editoral workflow
* wpDiscuz for comments
* Co-Authors Plus for authorship
* Fieldmanager to assist media attribution
* AMP for distribution
* Diff customizations for small tweaks to the editing interface to help new folks

## Theme development
Development occurs primarily within the [themes/interconnection](themes/interconnection/) folder.

Run `composer install` to enable the use of PHPCS for linting theme code.

Run `npm install` to enable the frontend asset build process. The theme currently requires Node v14; if you use [nvm](https://github.com/nvm-sh/nvm), you can run `nvm use` (or `nvm install v14`) in the theme directory to set the correct version.

Useful commands, all usable from within the theme directory:

 Command                   | Description
-------------------------- | --------------------------------------------------------
`npm run compile:css`      | Build the sass files into a single CSS file
`npm run watch:css`        | Monitor sass files for changes and automatically rebuild
`npm run lint:scss`        | Check the sass code for errors
`npm run lint:js`          | Check the JS files for errors
`composer lint:php`        | Check theme PHP files for errors
