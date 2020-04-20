<?php

// Fresh release list via https://drupal.org/files/releases.tsv
$releases = file_get_contents('releases.tsv');

$results = [
  'NEW Drupal 9' => [],
  'Already Drupal 9' => [],
  'Not yet' => [],
  'Other' => [],
];

foreach (explode("\n", $releases) as $release) {
  list($d, $name, $version) = explode("\t", $release);
  if (preg_match('!^(\d)\\.x!', $version, $found) && $found[1] != '8') {
    // Skip processing releases that are not 8.x or semantic.
    $results['Other'][] = "$name $version";
    continue;
  }

  // Check out this release.
  print "Processing $name $version\n";
  exec("rm -rf $name && git clone --progress https://git.drupalcode.org/project/$name.git 2> /dev/null && cd $name && git checkout $version 2> /dev/null", $output);

  // If this release was Drupal 9 compatile, we still need to check if this was the first for this project.
  if (is_drupal9_compatible($name)) {
    
    $newly_compatible = TRUE;
    $tags = $release_date = $tag_date = [];

    // Take the date of this checkout so we can compare it to other tags' dates.
    exec("cd $name && git show -s --format=%ci", $release_date);
    
    // Get list of tags in project to iterate over.
    exec('cd ' . $name . ' && git tag', $tags);
    foreach($tags as $tag) {
      if (preg_match('!^(\d)\\.x!', $tag, $found) && $found[1] != '8') {
        // Skip releases that are not 8.x or semantic.
        continue;
      }
      elseif ($tag == $version) {
        // Skip the tag itself that we tested above.
        continue;
      }

      // Get date of this tag, so we can ensure we only compare to older tags.
      exec("cd $name && git checkout $tag 2> /dev/null && git show -s --format=%ci", $tag_date);
      if ((strtotime($release_date[0]) > strtotime($tag_date[0])) && is_drupal9_compatible($name)) {
        $results['Already Drupal 9'][] = "$name $version (already compatible in $tag)";
        $newly_compatible = FALSE;
        break;
      }
    }
    if ($newly_compatible) {
      $results['NEW Drupal 9'][] = "$d $name $version";
    }
  }
  else {
    // This release itself is not yet Drupal 9 compatible.
    $results['Not yet'][] = "$name $version";
  }
  exec("rm -rf $name");
}

var_dump($results);

/**
 * Check info file if a release is Drupal 9 compatible.
 * 
 * @param string $name
 *   Project name.
 */
function is_drupal9_compatible(string $name) {
  $infos = glob($name . '/*.info.yml');
  $compatibility = [];
  foreach($infos as $file) {
    $yaml = file_get_contents($file);
    // Very rough pattern to check compatibility for now.
    if (preg_match('!^core_version_requirement:.+\^9.*$!m', $yaml)) {
      $compatibility[$file] = TRUE;
    }
    else {
      $compatibility[$file] = FALSE;
    }
  }
  // Ideally the project has an info file with the name of the project.
  if (!empty($compatibility[$name . '/' . $name . '.info.yml'])) {
    return TRUE;
  }
  // If that is not the case (eg. 'bugtracker' project has a 'bug_tracker' module),
  // we should order by length of info files and take the shortest. This is what
  // drupal.org does as well for considering compatibility.
  elseif (count($compatibility)) {
    uksort($compatibility, 'lengthsort');
    if (reset($compatibility)) {
      return TRUE;
    }
  }
  return FALSE;
}

/**
 * Custom short function for length sorting.
 */
function lengthsort($a, $b){
  return strlen($a) - strlen($b);
}
