## 企业微信 API

* ### Python Example (python3)

* install
```bash
git clone git@github.com:supertaodi/workweixin.git
cd workweixin/python
python setup.py install
```

* python console
```python
>>> from workweixin import Weixin
>>> weixin = Weixin()
>>> help(weixin)
```


* ### PHP Example

* _php 接口功能代码不是很完整, 有几个功能没有写_

* install
```bash
git clone git@github.com:supertaodi/workweixin.git
cd workweixin/php
composer install
```

* code
```php
<?php

include 'weixin.php';

$info = get_user_info('userId');
print_r($info);
```     
