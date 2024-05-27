=== DeliveryFrom ===
Contributors: krunoslavris
Tags: delivery, tracking, label, print, woo commerce
Requires at least: 6.0.0
Tested up to: 6.4.2
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
DeliveryFrom is the Base plugin which provides access to different label printing modules based on your location.

== Description ==

DeliveryFrom is a simple and user-friendly WooCommerce base plugin which enables you to print customized courier labels.
With a single click, you’ll be printing labels while simultaneously initiating the arrival of courier, as well as the Email and SMS services.
This plugin with selected module eliminates the need to manually complete selected courier forms; simply go to the order details page, select the menu, and click “Print Label”. Labels will be instantly generated and printed, and deliveries can be made.
Currently available courier modules and supported countries:
* GLS CE (Croatia, Slovenia, Serbia, Romania, Czech, Slovakia, Hungary)
* GLSShipIt (Germany, Austria)
* DPD (Croatia, Slovenia)
* BoxNow (Croatia)
* Paket24 (Croatia)
A note: Although this plugin is free, modules are subscription based.

== Installation ==

To install the plugin you can use the wordpress repository by going to you Admin dashboard -> Plugins and simply by searching after clicking Add new.
You can also download the zip file and upload it instead of going into the Wordpress repository.
To add a courier, you will need to purchase a licence. Go to [deliveryfrom.shop](https://deliveryfrom.shop) and follow steps.

== Frequently Asked Questions ==

Is Deliveryfrom plugin free?
The base plugin is free, but the courier modules are subscription based. You can find out more by visiting our website [deliveryfrom.shop](https://deliveryfrom.shop).
How can I install and activate modules?
Prerequisite:
* You need to have an account with your chosen courier (GLS, DPD...) including a username and password
* You need to have a courier ID
You can follow a tutorial on our page that describes the process of installing and activating the product.

== Changelog ==

Version 1.0
Released in April 2024, no changes made yet

== Upgrade Notice ==

No upgrades yet, installed plugins will be notified of the upgrade as soon as it comes out.

== Arbitrary section ==

There are no special requirements for the plugin, everything you need to know about installation is on https://deliveryfrom.shop

== Screenshots ==

1. Base module activation page - /deliveryfrom/assets/glsCe_activation.webp
2. Account and courier info page (each module has its own) - /deliveryfrom/assets/glsCe_courier_info.webp

== 3rd party services ==

1. Google Maps API - We use google maps API for fetching the locations of pickup services for the companies customer selected as their delivery service
2. License API - License API is our API that handles the licensing of the delivery plugin add-ons based on the customer choices if they want other services and their domains