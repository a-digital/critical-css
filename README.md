# Critical CSS plugin for Craft CMS 3.x

Generates Critical CSS without the need for SSH access.

<img src="src/icon.svg" width="250px" alt="Logo" title="Logo">

## Requirements

This plugin requires Craft CMS 3.0.0-beta.23 or later.

## Installation

To install the plugin, follow these instructions.

1. Open your terminal and go to your Craft project:

        cd /path/to/project

2. Then tell Composer to load the plugin:

        composer require adigital/critical-css

3. In the Control Panel, go to Settings → Plugins and click the “Install” button for Critical CSS.

## Critical CSS Overview

This plugin is designed for Craft 3 users who don't have SSH access, or those who can't install Gulp onto their server. We would still recommend using Gulp to generate Critical CSS if you can but this tool has been made so that nobody gets left out of the party.

## Configuring Critical CSS

Use the plugin settings page to enter your urls and templates.

## Using Critical CSS

Go to the CP section and generate your Critical CSS. You will then need to add the following to your templates:

### This goes into the template
```
{% block criticalCss %}
	<style>
		{{ source('folder/template_critical.min.css', ignore_missing = true) }}
	</style>
{% endblock %}
```
Please make you that you exchange folder/template for your template path used in the plugin settings. e.g. blog/entry would be: blog/entry_critical.min.css

### This goes into your layout
```
{% set critical = block('criticalCss') %}
{% if critical is defined and critical is not empty %}
	{{ critical|raw }}
{% endif %}
```
This code sits within the head before your stylesheets are called. You can then also add `{% if critical is not empty %}rel="preload" as="style"{% else %}rel="stylesheet"{% endif %}` to your stylesheet tags and within your JS you will need to do the following:
```
$("link[rel='preload']").each(function(){
	$(this).clone().attr("rel", "stylesheet").appendTo("head");
});
```

Please note: this is just one example of how you can get critical css up and running. There are other ways of implementing this also which can be found in various blog posts. We are just providing you with the generation of the file, how you implement it is completely up to you!

## Critical CSS Roadmap

Some things to do, and ideas for potential features:

* Fix generate all functionality and uncomment the button for this.

Brought to you by [A Digital](https://adigital.agency)