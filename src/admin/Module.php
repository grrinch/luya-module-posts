<?php

namespace luya\posts\admin;

use yii\base\InvalidConfigException;
use luya\admin\components\AdminMenuBuilder;

/**
 * Posts Admin Module.
 *
 * @author Basil Suter <basil@nadar.io>
 */
final class Module extends \luya\admin\base\Module
{
    public $wysiwygOptions = [];

    public $fbAppId;

    public $fbAppSecret;

    public $encryptStoredTokens = true;

    public $encryptTokensSecret = 'CHANGE ME';
    
    public $apis = [
        'api-posts-article' => 'luya\posts\admin\apis\ArticleController',
        'api-posts-cat' => 'luya\posts\admin\apis\CatController',
        'api-posts-autopostconfig' => 'luya\posts\admin\apis\AutopostConfigController',
        'api-posts-wysiwygconfig' => 'luya\posts\admin\controllers\WysiwygConfigController',
    ];

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        $s =& $this->encryptTokensSecret;
        if ($this->encryptStoredTokens && (empty($s) || 'CHANGE ME' === $s)) {
            throw new InvalidConfigException('Please, change the `encryptTokensSecret` property value if you need token storage encryption (which is preffered). Otherwise, set `encryptStoredTokens` property to false to disable encryption');
        }
    }

    /**
     * @inheritdoc
     */
    public function getMenu()
    {
        return (new AdminMenuBuilder($this))
            ->node('posts', 'local_library')
                ->group('posts_administrate')
                    ->itemApi('article', 'postsadmin/article/index', 'edit', 'api-posts-article')
                    ->itemApi('cat', 'postsadmin/cat/index', 'bookmark_border', 'api-posts-cat')
                    ->itemApi('autopost_config', 'postsadmin/autopost-config/index', 'tune', 'api-posts-autopostconfig');
    }
    /**
     * @inheritdoc
     */
    public function registerComponents()
    {
        return [
            'postsautopost' => [
                'class' => 'luya\posts\admin\components\Autopost',
            ],
        ];
    }

    public static function onLoad()
    {
        self::registerTranslation('postsadmin', '@postsadmin/messages', [
            'postsadmin' => 'postsadmin.php',
        ]);
    }
    
    /**
     * Translat posts messages.
     *
     * @param string $message
     * @param array $params
     * @return string
     */
    public static function t($message, array $params = [])
    {
        return parent::baseT('postsadmin', $message, $params);
    }

    /**
     * @inheritdoc
     */
    public function getAdminAssets() {
        return [
            'luya\posts\admin\assets\WysiwygAsset',
            'luya\posts\admin\assets\AutopostConfigAsset',
        ];
    }

    public function getJsTranslationMessages()
    {
        return [
            'js_autopost_config_fb_login_fail', 'js_autopost_config_label_renew_token',
        ];
    }
}
