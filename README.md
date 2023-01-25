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

Diff uses a WordPress theme called "Interconnection." It is designed by hang Do Thi Duc and follows the Wikimedia Design (https://design.wikimedia.org/style-guide/) and branding (https://meta.wikimedia.org/wiki/Brand) style guides. Development of Interconnection is handled in [the theme's own repository, wikimedia/interconnection-wordpress-theme](https://github.com/wikimedia/interconnection-wordpress-theme).

Diff uses the following plugins:

* Polylang for translation
* PublishPress for editoral workflow
* wpDiscuz for comments
* Co-Authors Plus for authorship
* Fieldmanager to assist media attribution
* The Events Calendar for event coordination
* Diff customizations for small tweaks to the editing interface to help new folks

## Installation

Most dependencies for the diff.wikimedia.org site are managed using [Composer](https://getcomposer.org/). After cloning the repository, run

```
composer install
```

to pull down the plugins and themes necessary to run the Diff site.

To work on the Interconnection theme specifically, you will want to replace the Composer source code package with the actual theme source:

```sh
# Remove the original checkout
rm -rf themes/interconnection
# Re-install using the actual repository source
composer install wikimedia/interconnection-wordpress-theme --prefer-source
```

## Theme Development

1. Create a PR branch off of the `main` branch in the `wikimedia/interconnection-wordpress-theme` repo.
2. For testing your changes, merge the new PR branch into the `develop` branch; the theme will build to the `release-develop` branch automatically.
3. Switch to the `wpcomvip/wikimedia-blog-wikimedia-org` repo and checkout into the `develop` branch.
4. Open the `composer.json` file in the `wpcomvip/wikimedia-blog-wikimedia-org` repo.
5. Under the `require` section, update the `"wikimedia/interconnection-wordpress-theme": "dev-release"` line to use the `release-develop` branch of the theme like this:
   ```json
   "wikimedia/interconnection-wordpress-theme": "dev-release-develop"
   ```
6. Run `composer update wikimedia/interconnection-wordpress-theme --prefer-source` to update the theme in the `composer.lock` file.
7. Commit the `composer.json` and `composer.lock` file changes on the `develop` branch; this step will trigger the development build on VIP.
8. Test your changes on the dev site here: https://blog-wikimedia-org-develop.go-vip.net/.

## Diff Blocks
There is a custom plugin in [client-mu-plugins/diff-blocks](https://github.com/wpcomvip/wikimedia-blog-wikimedia-org/tree/production/client-mu-plugins/diff-blocks) which exposes Diff-specific [Block Editor](https://wordpress.org/documentation/article/wordpress-block-editor/) blocks and customizations.

Run `npm install` to enable the frontend asset build process. The project currently requires Node v14; if you use [nvm](https://github.com/nvm-sh/nvm), you can run `nvm use` (or `nvm install v14`) in the theme directory to set the correct version.

In the project root directory (the same folder as this README), run

```sh
nvm use
npm install
npm run build
```

These commands will generate the CSS and JS assets in `client-mu-plugins/diff-blocks/` necessary to use that Diff Blocks plugin.

Run `npm install` to enable the frontend asset build process. The theme currently requires Node v14; if you use [nvm](https://github.com/nvm-sh/nvm), you can run `nvm use` (or `nvm install v14`) in the theme directory to set the correct version.

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
To update plugins, first run

```
composer update
```

to install available updates for Composer-managed plugins.

You will see [version ranges listed for each dependency in `composer.json`](https://github.com/wpcomvip/wikimedia-blog-wikimedia-org/blob/production/composer.json#L89-L105)—for major version upgrades, you will need to change this number and re-run `composer update plugin/name` to install the newer version. Minor version upgrades should be able to be installed automatically as long as the version constraint for that plugin is sufficiently flexible. As an example, a version preceded by a carat `^` will permit all subsequent minor and patch releases to be installed, but will require manual editing to upgrade to a new major version.

A small number of plugins are still managed manually, specifically the `wpdiscuz` extensions and the Polylang Pro multilingual tools. These must be downloaded manually from the relevant plugin vendor. To upgrade a manual plugin, delete the copy in your local environment, replace it with the folder downloaded from the plugin vendor, and use `git` to commit the changed files.

After committing all relevant plugin updates, test the site locally as detailed above in "Testing" before pushing your changes to the staging site. Pay special attention to any major plugin version updates: major version updates sometimes alter key functionality, so it is always wise to review the plugin's changelog to check for breaking changes. Also be sure to check your environment's PHP logs to validate that none of the plugin updates cause warnings or fatal errors while browsing the site.
