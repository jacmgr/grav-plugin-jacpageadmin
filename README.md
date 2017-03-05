# Grav Markdown Notices Plugin

The **jacpageadmin plugin** for [Grav](http://github.com/getgrav/grav) allows 
frontend page administration for CRUD like operations.  

You can:

* Edit an existing page
* Create a New Page
* VIew Source of Page content

You Cannot:

* Change filenames
* Delete Pages
* Move or Copy Pages.

# Installation

Download the Zip.

Put it in plugin folder etc......

Copy the plugins /pages/pageadmin to your user/pages/pageadmin


# Configuration

Don't use this plugin if you can't figure this out....


# PAGES AND ROUTES

# TEMPLATES

No templates are loaded by the plugin.  
This path is not loaded to twig by the plugin.

The template partial included here is a sample for you to use or copy in your own themes. No point to edit it here since it is not available to grav.

````
{#  FIND OUT HOW TO MAKE THIS USE JAC PLUGIN ROUTES  #}
{% set editPageForm = '/pageadmin/edit' %}
{% set createPageForm = '/pageadmin/create' %}   
{% set sourcePageForm = '/pageadmin/source' %} 
{% if page.link == '/' %}
  {% set actionPagePath = "/" ~ page.slug %}
{% else %}
  {% set actionPagePath = uri.path %}
{% endif %}
{# Display Menu based on logged in status #}
{% if grav.user.username and grav.user.authenticated %}
<ul id="editpage">
  <li ><a  href="{{base_url_absolute}}{{sourcePageForm}}?p={{ actionPagePath }}">Page Source</a></li>
  <li ><a  href="{{base_url_absolute}}{{editPageForm}}?p={{ actionPagePath }}">Edit</a></li>
  <li ><a  href="{{base_url_absolute}}{{createPageForm}}?p={{ actionPagePath }}">Create</a></li>
</ul>
{% else %}
<ul id="editpage">
  <li ><a  href="{{base_url_absolute}}{{pagesourcePageForm}}?p={{ actionPagePath }}">Page Source</a></li>
</ul>
{% endif %}
````
