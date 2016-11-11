Flow context for kahlan specs/tests - allows nice BDD way of developing Flow app
================================================================================

Usage
-----

You need to create a configuration for Kahlan ``kahlan-config.php``::

```php
use Ttree\FlowKahlan\Env;

Env::bootstrap($this);
```

Run
---

    kahlan --reporter=verbose --config=kahlan-config.php -d Packages/Framework/TYPO3.Eel --spec=Packages/Framework/TYPO3.Eel/Tests/Spec

Acknowledgments
---------------

Development sponsored by [ttree ltd - neos solution provider](http://ttree.ch).

License
-------

Licensed under MIT, see [LICENSE](LICENSE)
