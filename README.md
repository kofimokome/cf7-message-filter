[![Codacy Badge](https://app.codacy.com/project/badge/Grade/9a772c24d22d43d0b69c03f782abdd03)](https://www.codacy.com/gh/kofimokome/cf7-message-filter/dashboard?utm_source=github.com&amp;utm_medium=referral&amp;utm_content=kofimokome/cf7-message-filter&amp;utm_campaign=Badge_Grade)

# Message Filter for Contact Form 7

This is a WordPress plugin that helps prevent a contact form from submitting if it contains words or email marked as
unwanted

Contact Form 7 must be installed and activated before you can use this plugin
<br>

<b>Note: This is just an extension. This plugin is not affiliated with or endorsed by Contact Form 7.</b>

# Security Policy
## Reporting Security Bugs

You can report security bugs through the Patchstack Vulnerability Disclosure Program. The Patchstack team help validate, triage and handle any security vulnerabilities. [Report a security vulnerability.](https://patchstack.com/database/vdp/cf7-message-filter)


## Contribution

The latest codes are found in the `develop` branch. If you would like to contribute, you should use the `develop`
branch. Bear in mind that the codes in the `develop` branch may be unstable. If you are looking for the latest stable
codes, checkout the `master` branch

## Testing

See [TESTING.md](TESTING.md) for instructions on configuring and running the unit and integration tests.

## Development setup

This plugin depends on the Composer package `kofimokome/wordpress-tools` ([WP-Tools on GitHub](https://github.com/kofimokome/WP-Tools)).

After cloning the repository, run:

```bash
composer install
```

Composer will install the package from the GitHub VCS repository and automatically create the `lib/wordpress_tools` symlink pointing to `vendor/kofimokome/wordpress-tools/src`.

If you ever need to recreate the symlink manually:

```bash
composer setup-wordpress-tools
```

### Running `wptools`

Use the Composer script to run the WordPress Tools CLI from the plugin root:

```bash
composer wptools

# Examples
composer wptools -- make:model User
composer wptools -- make:migration create_users_table --table=users
composer wptools -- make:migration add_slug_to_users --table=users --update
```

`lib/wordpress_tools` is gitignored and should never be committed.

## This plugin is used by

1. **[Cameroon Boyo](https://cameroonboyo.com)**
2. **[Luca Ortis](http://lucaortis.com/)**
