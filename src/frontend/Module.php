<?php

namespace luya\posts\frontend;

/**
 * Posts Frontend Module.
 *
 * @author Basil Suter <basil@nadar.io>
 */
final class Module extends \luya\base\Module
{
    /**
     * @var boolean use the application view folder
     */
    public $useAppViewPath = true;

    /**
     * @var array The default order for the article overview in the index action for the posts.
     *
     * In order to read more about activeDataProvider defaultOrder: http://www.yiiframework.com/doc-2.0/yii-data-sort.html#$defaultOrder-detail
     */
    public $articleDefaultOrder = ['timestamp_display_from' => SORT_DESC];

    /**
     * @var integer Default number of pages.
     */
    public $articleDefaultPageSize = 15;

    /**
     * @var array The default order for the category article list in the category action for the posts.
     *
     * In order to read more about activeDataProvider defaultOrder: http://www.yiiframework.com/doc-2.0/yii-data-sort.html#$defaultOrder-detail
     */
    public $categoryArticleDefaultOrder = ['timestamp_display_from' => SORT_DESC];

    /**
     * @var integer Default number of pages.
     */
    public $categoryArticleDefaultPageSize = 15;

    /**
     * @var array
     */
    public $urlRules = [
        ['pattern' => 'posts/<id:\d+>/<title:[a-zA-Z0-9\-]+>/', 'route' => 'posts/default/detail'],
        ['pattern' => 'posts/category/<categoryId:\d+>/', 'route' => 'posts/default/category'],
    ];
}