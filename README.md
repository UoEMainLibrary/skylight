
[![Alt text](https://raw.github.com/skylightui/skylight/master/theme/default/images/skylight.jpg)](http://skylightui.org/)

Current version: 2.22 (03-07-2020)

Skylight is a customised framework based on PHP. You might want to use docker
for development which is located in the `skylight-local/docker/` folder.

Dependencies:

|  Dependency | | Current version | | Latest stable version | Note |
|-----------:|:---------------|---|:----------------------|---|:---|
| CodeIgniter | [![Github](https://github.com/favicon.ico)](https://github.com/bcit-ci/CodeIgniter/tree/3.1-stable) | 3.1 | [![Github](https://github.com/favicon.ico)](https://github.com/codeigniter4/CodeIgniter4) | [4.0.4](https://codeigniter.com/user_guide/intro/index.html) | Upgrading 3->4 requires refactoring, see [here](https://codeigniter.com/user_guide/installation/upgrade_4xx.html). |
| Colorbox    | | 1.3.17.2 |       | 1.6.4                |      |
| Bootstrap   | | 3.3.5    |       | 4.5.2                       | 
| jQuery      | | 2 >      |       | 3.5.1                       | There are 3 versions installed
| jwPlayer    | | 5.7.1896  | [![Github](https://github.com/favicon.ico)](https://github.com/jwplayer/jwplayer)     | 7.8.0 |       |
| SWFObject   | | 1.5 | | 2.2 | |
| Fancybox |  | 2.1.4 | [![Github](https://github.com/favicon.ico)](https://github.com/fancyapps/fancybox) | 3.5.7 | |
| FontAwesome | | 4.7.0 | [![Github](https://github.com/favicon.ico)](https://github.com/FortAwesome/Font-Awesome)  | 5.14 | There are 2 version installed, also v. 4.40
| ImagesLoaded | | 4.1.1 | [![Github](https://github.com/favicon.ico)](https://github.com/desandro/imagesloaded)  | 4.1.4 
| Isotope | | 3.0.1 | [![Github](https://github.com/favicon.ico)](https://github.com/metafizzy/isotope)  | 3.0.5
| Masonry | | 4.0.0 | [![Github](https://github.com/favicon.ico)](https://github.com/desandro/masonry)  | 4.2.2
| Modernizr | | 1.7 | [![Github](https://github.com/favicon.ico)](https://github.com/Modernizr/Modernizr) | 3.11.2 
| OpenSeaDragon | | 2.1.0 | [![Github](https://github.com/favicon.ico)](https://github.com/openseadragon/openseadragon) | 2.4.2
| TinyMCE | | 3.4.5 | [![Github](https://github.com/favicon.ico)](https://github.com/tinymce/tinymce)  | 5.0.10


\* as of 17th August 2020

Installation
------------
 * Clone from github: https://github.com/skylightui/skylight.git
 * Copy `application/config/skylight-sample.php` to `application/config/skylight.php`
 * Copy `.htaccess-sample` to `.htaccess`
 * Edit `.htaccess` and adjust `RewriteBase` as necessary (e.g. to /skylight/ or / if installed in the web server's root)
 * Enable Discovery on your DSpace server: https://wiki.duraspace.org/display/DSDOC3x/Discovery#Discovery-EnablingDiscovery

Todo
----
* Upgrade to Codeigniter 4
* Upgrade JS dependencies

Who
---
Originally developed by the Digital Development Team at The University of Auckland Library:
 * Stuart Lewis (S) - University of Edinburgh
 * Kim Shepherd (K) - University of Auckland
 * Yin Yin Latt (Y) - University of Auckland

Additional contributors:
 * Jared Watts - University of Auckland
 * Scott Renton - University of Edinburgh
 * Hrafn Malmquist - University of Edinburgh
 * Sebastian Lange - University of Edinburgh