# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/)

# Plugin version.php information
```php
// Plugin release number corresponds to the lasest tested Moodle version in which the plugin has been tested.
$plugin->release = '3.5.7'; // [3.5.7]

// Plugin version number corresponds to the latest plugin version.
$plugin->version = 2019010100; // 2019-01-01
```

# How do I make a good changelog?
Guiding Principles
* Changelogs are for humans, not machines.
* There should be an entry for every single version.
* The same types of changes should be grouped.
* The latest version comes first.
* The release date of each version is displayed.

Types of changes
* **Added** for new features.
* **Changed** for changes in existing functionality.
* **Deprecated** for soon-to-be removed features.
* **Removed** for now removed features.
* **Fixed** for any bug fixes.
* **Security** in case of vulnerabilities.

# Version (3.10) - 2020-11-14

#### Added
- Improved code style
- Tested in Moodle 3.10 no issues found.

#### Removed
- Remove `.eslintrc` `Gruntfile.js` and `packages.json` from the project causes Travis issues.

# Version (3.9.0) - 2020-05-09
#### Updated
- Version number no issues found in Moodle 3.9

# Version (3.7.3) - 2019-12-30
#### Added
- Global setting template
- Add capability `block/genericotemp:view`
- Default use pagetypepattern `course-*`
- Add pix icon
- Fix backup

#### Changed
- Cloned the original html block from Moodle 3.7