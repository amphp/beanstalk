---
title: Miscellaneous
permalink: /misc
---

* Table of Contents
{:toc}

## Get System Stats

To see what stats are available for the system, checkout the [System](classes/system) class page.

```php
$beanstalk = new Amp\Beanstalk\BeanstalkClient("tcp://127.0.0.1:11300");

$stats = $beanstalk->getSystemStats();
```

## Close the Connection

To manually close the connection to the server.

```php
$beanstalk = new Amp\Beanstalk\BeanstalkClient("tcp://127.0.0.1:11300");

$beanstalk->quit();
```
