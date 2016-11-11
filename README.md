Flow context for kahlan specs/tests - allows nice BDD way of developing Flow app
================================================================================

__this package is under heavy developement and not stable currently__

Install
-------

    composer require --dev ttree/flow-kahlan dev-master
    
Usage
-----

You need to create a configuration for Kahlan ``kahlan-config.php``::

```php
use Ttree\FlowKahlan\Env;

Env::bootstrap($this);
```

You can get sample spec form this [gist](https://gist.github.com/dfeyer/1213b93b7f1e38107dd4ad8dc79e7736).

Run
---

    kahlan --reporter=verbose --config=kahlan-config.php -d Packages/Framework/TYPO3.Eel --spec=Packages/Framework/TYPO3.Eel/Tests/Spec

Acknowledgments
---------------

Development sponsored by [ttree ltd - neos solution provider](http://ttree.ch).

License
-------

Licensed under MIT, see [LICENSE](LICENSE)
