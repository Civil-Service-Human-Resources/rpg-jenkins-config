# RPG Must use plugins / extensions 

## RPG Careers WP Utils Plugin

Plugin providing various helper functions to supoprt the CMS - sits in mu-plugins folder so automatically run.


## Careers WP Snippet Plugin

Plugin to allow for management of bespoke HTML snippets.  Snippet can be created in WP admin and then a shortcode is used on pages to pull that through:

```
[rpg_snippet tagcode="406"]
```

## Notes
php file is deplyed to mu-plugins folder under wp-content.  Becomes a must use plugin which cannot be altered in anyway in the WP backend.
