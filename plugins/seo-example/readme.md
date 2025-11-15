# SEO Meta Enhancer Example Plugin

This plugin demonstrates how to extend the SEO Toolkit using hooks.

## Features
- Cleans content before SEO analysis
- Adds custom metrics to analysis results
- Enhances generated meta tags with Twitter cards

## Installation
1. Copy the `seo-example` folder to your `plugins/` directory
2. The plugin will automatically register with the CMS

## Hook Implementations
- `before_seo_analysis`: Removes HTML comments from content
- `after_seo_analysis`: Adds heading structure analysis
- `seo_meta_generation`: Adds Twitter card meta tags

## Customization
Edit the `SEOMetaEnhancer.php` file to:
- Add new cleaning rules
- Include additional metrics
- Support other meta tag formats