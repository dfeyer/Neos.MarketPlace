Neos Market Place
=================

This package is a prototype for the Extension Repository for the Neos Project. 

The goal is to make available packages and vendors more visible on the main [website](http://www.neos.io) of the Neos Project.

Flow Framework and Neos CMS use composer to handle packages, so this project is a simple frontend on top of Packagist. We
synchronize in a regulary basic the package from Packagist.

Features
--------

- [x] Import / Update packages and versions from Packagist
- [x] Import / Update maintainers from Packagist
- [ ] Handle deleted / abandonned packages
- [x] Basic integration with ElasticSearch
- [ ] ElasticSearch Aggregation support
- [ ] More advanced search configuration
- [x] Listing of packages
- [x] Vendor detail page
- [x] Package detail page
- [ ] Some utility NodeType to show case specific packages in neos.io

Currently the templating is done on top of the [Neos Demo Package](https://github.com/neos/neosdemotypo3org).

Some screenshot from the current implemtentation (*Warning*: we use some fake/static data currently, so ...):

### Search
![Search](https://dl.dropboxusercontent.com/s/hyo09gn9mc9ta3i/2016-03-10%20at%2009.49%202x.png?dl=0)

### Vendor Page
![Vendor](https://dl.dropboxusercontent.com/s/831tjm98xccodrp/2016-03-10%20at%2009.50%202x.png?dl=0)

### Package Page
![Package](https://dl.dropboxusercontent.com/s/9fx1w2c3au649r7/2016-03-10%20at%2009.50%202x%20%281%29.png?dl=0)
 
How to test it ?
----------------
 
- Install the Neos Demo Webiste
- Add this package
- Create a document of type ```Flowpack.SearchPlugin:Search```
- Update this package settings to change the UUID in ```Neos.MarketPlace.repository.identifier```

License
-------

The MIT License (MIT). Please see [LICENSE](LICENSE.txt) for more information.
