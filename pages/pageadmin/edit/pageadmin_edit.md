---
login_redirect_here: true
cache_enable: false
title: PageAdmin Edit (PluginRepo) 
template: form
access:
  site.login: true
process:
    twig: true
form:
    name: pageadmin_edit
    fields:
      - name: pageslug
        type: text
        id: pageslug
        default: ByPlugin.pageslug
        placeholder: "unique-slug_3-48"
        label: 'Unique Slug'
        validate:
          required: true
          message: You must have a unique page Slug from 3 to 16 characters. Letters, numbers and "-" and "_"
          pattern: '^[a-z0-9_-]{3,48}$'

      - name: pagetitle
        type: text
        id: pagetitle
        default: ByPlugin.pagetitle
        placeholder: "The Title should be standard text"
        label: 'Formal Page Title'
        validate:
          required: true
          message: You must have a unique page title
          xpattern: '^[a-z0-9_-]{3,16}$'

      - name: pagetext
        type: textarea
        id: simplemde
        placeholder: "Enter Page Content as Markdown"
        default: ByPlugin.pagetext
        autofocus: true
        label: 'Page Content'

      - name: plugin_action
        type: hidden
        default: ByPlugin.plugin_action

      - name: referrer
        type: hidden
        default: ByPlugin.referrer

      - name: pagetoedit
        type: hidden
        default: ByPlugin.pagetoedit

    buttons:
      -   type: submit
          value: Save
          classes: "button primary"
      -   type: reset
          value: Reset (not working)

    process:
      - jacpageadmin_edit: true
---
## <a href="{{base_url_relative}}{{pageedit.route}}">{{ pageedit.title }}</a>
<!-- 
if use markdown for the link above, it bunges the not-desired base url into it, so use HTML
mark down: [{{ pageedit.title }}]( {{base_url_relative}}{{pageedit.route}} )
-->
<!---  The actual form is built by the form plugin -->
