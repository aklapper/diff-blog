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
`npm run`                  | See a list of all available npm commands
`npm run compile:css`      | Build the sass files into a single CSS file
`npm run watch:css`        | Monitor sass files for changes and automatically rebuild
`npm run lint:scss`        | Check the sass code for errors
`npm run lint:js`          | Check the JS files for errors
`composer lint:php`        | Check theme PHP files for errors

## Testing
When making changes to the site, test that the design and functionality works locally before pushing changes to the `develop` branch for staging verification, and that all key site functions still work as expected. This is a partial list of tests and checks you may want to perform:

- Does the homepage look correct at a variety of screen sizes?
- Does an article page look correct at a variety of screen sizes?
- Does the [calendar](https://diff.wikimedia.org/calendar/) look and function correctly at a variety of screen sizes?
- Does search work?
- Does the language picker work on a multilingual post? ([example](https://diff.wikimedia.org/es/2022/03/17/reservate-la-fecha-y-ayudanos-a-crear-la-wikimania-2022/))
- Do photo credits appear on a post (right above the footer)?
- Can you add multiple authors (via Co-Authors Plus), and do the bylines appear correctly?
- Can you schedule a post (PublishPress) to be published in the future, and does it send an email when the publish time has passed? (This may be difficult to test locally, depending on your environment)

If the local environment passes the majority of the above checks, you may merge your code into `develop` for testing on the remote [staging environment](https://blog-wikimedia-org-develop.go-vip.net/).

## Plugin updates
To update plugins, it is useful to have a local environment which supports [WP_CLI](https://wp-cli.org/) such as [VVV](https://varyingvagrantvagrants.org/) or [Chassis](https://docs.chassis.io/en/latest/). SSH into your virtual machine (for example by using `vagrant ssh`), and use this command to upgrade all plugins automatically:

```
wp plugin update --all
```

WP_CLI will upgrade any plugins which are available through the WordPress plugin directory, and then output status like this:
```
+-------------------------------------+-------------+-------------+---------+
| name                                | old_version | new_version | status  |
+-------------------------------------+-------------+-------------+---------+
| co-authors-plus                     | 3.4.8       | 3.5.1       | Updated |
| polylang                            | 2.8.4       | 3.2.2       | Updated |
| publishpress                        | 3.7.0       | 3.7.1       | Updated |
| wikipedia-preview                   | 1.2.0       | 1.3.0       | Updated |
| wpdiscuz                            | 7.2.2       | 7.3.17      | Updated |
| wpdiscuz-comment-search             | 7.0.3       | 7.0.4       | Error   |
| wpdiscuz-report-flagging            | 7.0.4       | 7.0.10      | Error   |
| wpdiscuz-subscribe-manager          | 7.0.2       | 7.0.4       | Error   |
| wpdiscuz-syntax-highlighter         | 1.0.2       | 1.0.3       | Error   |
| wpdiscuz-user-comment-mentioning    | 7.0.6       | 7.1.5       | Error   |
| wpdiscuz-widgets                    | 7.0.6       | 7.1.3       | Error   |
+-------------------------------------+-------------+-------------+---------+
Error: Only updated 5 of 11 plugins.
```
(Note that the wpDiscuz plugin addons did not install. These modules are paid addons to wpDiscuz, and are not available from the WP directory. You may need to contact GVector support to get the files necessary to upgrade these plugins.)

On a new git branch, add and commit each plugin separately, _e.g._:

```
git add plugins/co-authors-plus
git commit -m "Upgrade co-authors-plus to 3.5.1 (was 3.4.8)"
```

After committing all relevant plugin updates, test the site locally as detailed above in "Testing" before pushing your changes to the staging site. Pay special attention to any major plugin version updates, such as the v2 to v3 change for Polylang in this example: major version updates sometimes alter key functionality, so it is always wise to review the plugin's changelog to check for breaking changes. Also be sure to check your environment's PHP logs to validate that none of the plugin updates cause warnings or fatal errors while browsing the site.
