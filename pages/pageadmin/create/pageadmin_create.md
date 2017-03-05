---
title: 'PageAdmin Create (PluginRepo)'
cache_enable: false
access:
  site.login: true
template: form
parent: '/readings'
pagefrontmatter:
    template: item
    title: My new Blog post
    taxonomy:
        category: blog
        tag: [journal, guest]
form:
    name: pageadmin_create
    fields:
      - name: pageslug
        type: text
        id: pageslug
        placeholder: "unique-slug_3-48"
        label: 'Unique Identifier (slug)'
        validate:
          required: true
          message: You must have a unique identifier (slug) of 3 to 16 characters. Letters, numbers and "-" and "_"
          pattern: '^[a-z0-9_-]{3,48}$'
  
      - name: title
        label: 'Item Title'
        placeholder: 'Enter your page title here'
        autocomplete: true
        type: text
        validate:
            required: true
            
      - name: referrer
        type: hidden
        default: ByPlugin.referrer

      - name: pagetoedit
        type: hidden
        default: ByPlugin.pagetoedit
      - name: honeypot
        type: honeypot
        
    buttons:
      - type: submit
        value: Create It
        classes: null

    process:
      - jacpageadmin_create: true
---

Enter a Unique Page Identifier (slug) and a Title for the new Item.
