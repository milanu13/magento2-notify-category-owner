# Magento 2 - Notify Category Owner
This module allows you to send an email notification to the category owner when the order is placed successfully.

## Features
<ul>
<li>Send an email to category owner when the order is submitted.</li>
<li>Custom transitional email templates can be used from the backend.</li>
<li>Multiple category owners can be set.</li>
</ul>

## Requirements
This plugin supports Magento2.x.x version or higher.

## Installation
To install this module, copy the module folder in the magento_root/app/code directory and run the following commands:
```
php bin/magento module:enable MilanDev_NotifyCatOwner
php bin/magento setup:upgrade
php bin/magento setup:di:compile
```
## Configurations
1 ) To enable/disable, set email template and sender
```
Stores-->Configuration-->Milandev-->Notify Category Owner
```
2 ) To set category owner (multiple email can be set by comma)
```
Catalog-->Categories-->{Category}-->Category Owners
```
## License
Free as in Freedom.