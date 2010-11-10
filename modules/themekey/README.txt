*****************************************************************************
                              T H E M E K E Y
*****************************************************************************
Name: themekey module
Author: Thilo Wawrzik <drupal at profix898 dot de>
Drupal: 6.x
*****************************************************************************
DESCRIPTION:

ThemeKey is meant to be a generic theme switching module. It
allows you to switch the theme for different paths and based
on object properties (e.g. node field values). It can also be
easily extended to support additional paths or properties as
exposed by other modules.

Documentation for users and developers is very sparse at the
moment. I hope to complete the docs in the next few weeks.
Thanks for your patience :)

*****************************************************************************
INSTALLATION:

1. Place whole themekey folder into your Drupal modules/ or better
   sites/x/modules/ directory.

2. Enable the themekey module by navigating to
     administer > modules

3. Bring up themekey configuration screens by navigating to
     administer > settings > themekey

*****************************************************************************
THEMEKEY UI:

How to use ThemeKey UI on node forms ...

- Go to admin/settings/themekey/settings/ui to make theme options available
  on node forms, and check off the content types you want to enable the
  options for
- Go to admin/settings/themekey/settings and enable the 'node:nid' property

*****************************************************************************
FOR DEVELOPERS:

HOOK_themekey_properties()
  Attributes
    Key:    namespace:property
    Value:  array()
            - description => Readable name of property (required)
            - multiple    => TRUE/FALSE (optional)
              (does an object, e.g. a node, can have more than one of this property values)
            - weight      => weighting callback (optional)
              (if multiple values are possible, the weighting callback is required for priority)
            - path        => Path to property value on an object (optional)

  Maps
    Key:    none (indexed)
    Value:  array()
            - src       => Source property path (required)
            - dst       => Destination property path (required)
            - callback  => Mapping callback (required)

HOOK_themekey_global()
  Global properties
    Key:    namespace:property
    Value:  property value

HOOK_themekey_paths()
  Paths
    Key:    none (indexed)
    Value:  array()
            - path      => Router path to register (required)
            - callbacks => Load (and/or match) callback (required)
              (the callback function can set the 'theme' element in $item directly)
              Callback arguments:
              - $item:    array of elements associated with the path/callback
              - $params:  array of parameters available for load callback
              
*****************************************************************************
