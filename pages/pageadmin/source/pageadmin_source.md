---
login_redirect_here: true
cache_enable: false
title: PageAdmin Source (PluginRepo) 
template: form
process:
    twig: true
form:
    name: pageadmin_source
    fields:
      - name: pagetextsource
        type: textarea
        placeholder: 'Main Content here...'
        id: simplemdeSource
        disabled: true
        rows: 15
        readonly: true
        default: ByPlugin.htmlentitiespagetext
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

      - name: submit2
        type: submit
        value: Submit
            
    buttons:
      -   type: submit
          value: Return
          classes: "button primary"

    process:
      - jacpageadmin_source: true
---
## <a href="{{base_url_relative}}{{pageedit.route}}">{{ pageedit.title }}</a> | <a class="button small" href="{{base_url_relative}}/pageadmin/edit?p={{pageedit.route}}">Edit</a>
<!-- 
if use markdown for the link above, it bunges the not-desired base url into it, so use HTML
mark down: [{{ pageedit.title }}]( {{base_url_relative}}{{pageedit.route}} )
-->
<!---  The actual form is built by the form plugin -->
<style>
textarea#simplemdeSource { width: 100%; height: 100%}
</style>