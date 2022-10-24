<p align="center">
  <img src="https://raw.githubusercontent.com/luyadev/luya/master/docs/logo/luya-logo-0.2x.png" alt="LUYA Logo"/>
</p>

# Posts Module

[![LUYA](https://img.shields.io/badge/Powered%20by-LUYA-brightgreen.svg)](https://luya.io)
[![Slack Support](https://img.shields.io/badge/Slack-luyadev-yellowgreen.svg)](https://slack.luya.io/)

The posts module provides standard blog/news functionality, including categories, articles, tags, wysiwyg, social networks integration.

This module is a fork of the [luya news module](https://github.com/luyadev/luya-module-news)

## Stability

**The module is under development**, so no stable version is currently available yet

## Installation

For the installation of modules Composer is required.

```sh
composer require denyadzi/luya-module-posts: ~2.0-dev
```

For multilingual posts, the php *intl* extention is highly recommended to be installed in your system

### Configuration

After installation via Composer include the module to your configuration file within the modules section.

```php
'modules' => [
    // ...
    'posts' => [
    	'class' => 'luya\posts\frontend\Module',
    	'useAppViewPath' => false, // When enabled the views will be looked up in the @app/views folder, otherwise the views shipped with the module will be used.
    ],
    'postsadmin' => [
        'class' => 'luya\posts\admin\Module',
        'wysiwygOptions' => [ /* various tinymce editor options */
            'height' => '800',
            'menubar' => false,
            'relative_urls' => false,
            'remove_script_host' => false,
            'convert_urls' => true,
            'plugins' => 'link paste image imagetools code lists textcolor fullscreen wordcount table',
            'toolbar' => 'undo redo | formatselect | bold underline italic forecolor backcolor image | alignleft aligncenter alignright alignjustify | numlist bullist outdent indent | link unlink | paste pastetext | removeformat | table | code wordcount | fullscreen',
            'imagetools_cors_hosts' => [ 'huskyhamster.com' ],
            'extended_valid_elements' => 'script[language|type|src]',
//                'default_link_target' => '_blank',
            'link_context_toolbar' => true,
            'fullscreen_native' => true,
            'rel_list' => [
                ['title' => 'Internal', 'value' => 'internal'],
                ['title' => 'Internal CTA', 'value' => 'internal-cta'],
                ['title' => 'Full External', 'value' => 'noopener nofollow noreferrer'],
                ['title' => 'Nofollow', 'value' => 'nofollow'],
              ],
            'paste_word_valid_elements' => 'p,b,strong,i,em,h1,h2,h3,h4,h5,h6,a,ul,li,ol,blockquote,cite,table,tr,td,th,tbody,thead',
        ],
    ],
]
```

### Initialization 

After successfully installation and configuration run the migrate, import and setup command to initialize the module in your project.

1.) Migrate your database.

```sh
./vendor/bin/luya migrate
```

2.) Import the module and migrations into your LUYA project.

```sh
./vendor/bin/luya import
```

After adding the persmissions to your group you will be able to edit and add new posts.

## Example Views

As the module will try to render a view for the post overview, here is what this could look like this in a very basic way:

#### views/posts/default/index.php

```php
<?php
use yii\widgets\LinkPager;

/* @var $this \luya\web\View */
/* @var $provider \yii\data\ActiveDataProvider */
?>
<h2>Latest Posts</h2>
<?php foreach($provider->models as $item): ?>
    <?php /* @var $item \luya\posts\models\Article */ ?>
    <pre>
        <?php print_r($item->toArray()); ?>
    </pre>
    <p>
        <a href="<?= $item->detailUrl; ?>">Post Detail Link</a>
    </p>
<?php endforeach; ?>

<?= LinkPager::widget(['pagination' => $provider->pagination]); ?>
```

#### views/posts/default/detail.php

```php
<?php
/* @var $this \luya\web\View */
/* @var $model \luya\posts\models\Article */
?>
<h1><?= $model->title; ?></h1>
<pre>
<?php print_r($model->toArray()); ?>
</pre>
```

The above examples will just dump all the data from the model active records.

