# new_drupal9_projects

This repository contains a script to identify new Drupal 9 projects. The script works off of 
https://drupal.org/files/releases.tsv which is a regularly updated automated list of new releases.

With Drupal 9, we cannot tell from the release number anymore if a release is compatible or
not. A 8.x-4.2 release may be the first Drupal 9 compatible release of a project. So we take
the release, check it out of git and check the main info file, if it is Drupal 9 compatible.

However, that the release we found is Drupal 9 compatible does not mean that was the first one.
So we also look at the list of all previously created project tags that may represent possibly
Drupal 9 compatible releases and check those out one by one as well. Then we verify if either
of them were already Drupal 9 compatbile. If they were, then we categorize them as such.

At the end we get four lists of projects:

- Newly Drupal 9 compatible releases.
- Releases that are Drupal 9 compatible but not a first in the project.
- Releases that could have been Drupal 9 compatible but are not.
- Releases that could not even have been Drupal 9 compatible (7.x, 6.x, etc).

I created the script to support data collection for http://hojtsy.hu/drupalcares-9 and may
use for other purposes in the future. Feedback welcome!
