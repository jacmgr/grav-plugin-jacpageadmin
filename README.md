# Grav jac PageAdmin Plugin

![jac PageAdmin Plugin](assets/jacpageadmin_thumbnail.png)

The [**jacpageadmin plugin**](https://github.com/jacmgr/grav-plugin-jacpageadmin) for [Grav](http://github.com/getgrav/grav) allows frontend page administration for CRUD like operations on your pages.  

The plugin can:

* Edit an existing page
* Create a New Page
* View Source of Page content

The plugin cannot (yet):

* Delete Pages
* Change Page Filenames
* Move or Copy Pages.

## Preface

This is simply a "Proof of Concept" plugin.  I am not an expert in how grav works. I am not an expert in php.  I am simply a hobbyist programmer doing these things for my own enjoyment.  I have no clients, customers, or work related to this programming.  Every now and then I maintain my personal blog and change up the software behind it. 

I will not be maintaining the code as an official grav plugin.  Use it at your own risk. I simply hope that some real programmers can see the Proof of Concept and maybe spur them on to provide another real plugin we all can use instead of this one!

I don't know anything about git and version control and pushes and pulls, so I won't be doing anything like that.  I just periodically upload my files to this repository.

This plugin is inspired by some other front end editing plugins.  One allowed you to edit an existing page and nothing else.[Bluetzinn's editable](https://github.com/bleutzinn/grav-plugin-editable)  The other allows you to create a new page and nothing else.([Bluetzinn's add-page-by-form](https://github.com/bleutzinn/grav-plugin-add-page-by-form))  In any case, both of them helped me understand a lot about [Grav](http://github.com/getgrav/grav).  I was looking for more of an all in one pageadmin function that I could control with permissions and templates.

# Installation

You really should know something about installing grav plugins if you are going to try this plugin.  So no blah blah blah blah instructions!  If you don;t know how to do it, you should not be trying out **jacpageadmin**.

* Download the Zip.
* Put it in plugin folder etc...... under `plugins\jacpageadmin`
* Copy the plugins `/pages/pageadmin` to your `user/pages/pageadmin`  [i can't get it to work with the pages location in the plugin structure; so move the pages to your actual pages structure]
* Add some links in your templates to use the CRUD pages provided. The routes provided by the plugin are:
 
  * /pageadmin/source?p=/RouteToThePageToActOn
  * /pageadmin/edit?p=/RouteToThePageToActOn
  * /pageadmin/create?p=/RouteToThePageToActOn

## Dependencies
Requires the `grav login plugin` as well as the typical Error and Problems and Debugbar plugins.  Maybe others, not sure, see if you get error messages for dependencies and let me know.

I tested this with standard installs of SKELETONS for antimatter and pinpress skeletons and added the login plugins and debugbar.

##Verify your install

Try typing the route `/pageadmin/source?p=/` and you should get a source code display of your HOME page. If you don;t something not working!

##Verify User Account permissions.

To use the edit route `/pageadmin/edit?p=/` and create page route `/pageadmin/create?p=/` you need to be logged into **The Front End**, not the grav admin.  The user acount should have the following yaml in their username.md account file.

~~~~
access:
  site:
    login: true
    front-end: true     #for editable plug in
    editor: true        #for jaccms
~~~~

For example; you can try this user [on my test site](http://jhinline.com/grav/pin01/) with username: demouser and paswrd: DemoUser33

~~~~
email: demodummy@dummy.net
fullname: 'Demo User'
title: Editor
state: enabled
access:
  admin:
    login: false
    super: false
  site:
    login: true
    front-end: true     #for editable plug in
    editor: true        #for jaccms
hashed_password: $2y$10.........
~~~~

# Updating

None really planned. I've taken it as far as my talent and skill can go. It takes me 10 hours to do what most of you programmers do in 30 minutes. Watch the repo if you want.
  
# USAGE

The pageadmin pages are simple grav front end forms. They are activated by navigating to the form with a URL GET parameter of the page that you want to act on.

The links are made by inserting them in your PAGE templates. For example in your `templates\yourtheme\item.html.twig` or one of its embedded templates you can put something like this.

~~~
<a  href="{{base_url_absolute}}/pageadmin/source?p={{ uri.path }}">Page Source</a>
<a  href="{{base_url_absolute}}/pageadmin/edit?p={{ uri.path }}">Edit</a>
<a  href="{{base_url_absolute}}/pageadmin/create?p={{ uri.path }}">Create</a>
~~~

The plugin does include a template folder where you can see what I actually used in my sites.  The polugin itself ignores the plugin template folder.  (Mostly because when I tried to use it, I could not get it to work properly).

# TEMPLATES

No templates are loaded by the plugin.  
The plugin template path is not loaded to twig by the plugin.

The template partial included here is a sample for you to use or copy in your own themes. No point to edit it in the plugin folder since it is not used.  This is what I put in my theme folder in a template named `partials\jacpageadmin_menu.html.twig`.

~~~~
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
~~~~

Then in my theme template `item.html.twig` I modified like below

~~~~
.
.
.
<div class="span9" id="content" role="main">
  <div class="main section" id="main">
    <div class="widget Blog" id="Blog1">
	  {# The next line is the added line for jacpageadmin links #}
      {% include 'partials/jacpageadmin_menu.html.twig' %}
      {% include 'partials/blog_item.html.twig' with {'truncate':false,'big_header':true} %}
    </div>
  </div>
</div>
.
.
.
~~~~

# THE FORM PAGES

I tried to put the defualt pages in the plugin pages folder as default pages.  It kind of worked, the code is in the plugin to do that. But I kept haveing strange form posting issues.  I gave up.  Put the form pages in your own pages floder as described above and it all works.  I disabled the function in the plugin to load the pages from the plugin.


##CREATE PAGE

A new page is created as a sibling to the page you are on when you click a create link.  It will take its location in the pages system based on the sibling.

You will be asked to enter the unique identifier (which becomes the folder name and page route) and the title.  Once you enter that, the page file is created in the file system and you are redirected to the EDIT page for that file.

##READ PAGE

Simply displays the raw content in a text box for the current page.

## UPDATE/EDIT PAGE
The edit page provides a text area with the entire page file loaded, including frontmatter and content. I guess that is almost similar to "expert mode" in the official grav admin.  In **jacpageadmin** you edit all the header frontmatter and page content in the same text area.  In my case use, the only real header information should be the title and maybe an author and date publiushed.  This editor is not meant for editting complicated pages that are using the page as a controller or view with lots of yanl frontmatter.

That means:
* To change a pages title, you edit the frontmatter directly to change the `title` attribute.
* Any header frontmatter you want, you would have to manually type into the files frontmatter area. Such as:
 * author
 * date
 * slug
 * template
 
To minimize USER entered yaml, i use the folder wide yaml controlled by the site admin.  This is described in the grav documentation.

## DELETE

Not implemented. Not planned to implement yet.

# DEVELOPMENT NOTES

## FORM DEFAULTS

A big problem I had was setting default text values into the front end forms field.  Apparently there is NO WAY TO DO THAT from your own plugins using form plugin functionality.  I posted a question (enhancement) about that to the [grav-plugin-form](https://github.com/getgrav/grav-plugin-form/issues/123) repository.

My work around was to 
OK. Would be a nice enhancement!  I have a workaround for my current case.

In the form definition use a default attribute like this:

~~~~
  - name: summary
      type: text
      default: ByPlugin.summary    #any unique word or phrase as a placeholder.
~~~~
Then in plugin 
~~~~
    public function onOutputGenerated()
    {
        $DV = some function or process to get desired default value
        $this->grav->output = str_replace('ByPlugin.summary', $DV, $this->grav->output);  
     }
~~~~

Probably only works on text/textareas, but that is all i needed for jacpageadmin.