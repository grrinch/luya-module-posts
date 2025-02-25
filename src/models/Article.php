<?php

namespace luya\posts\models;

use luya\Exception;
use Yii;
use luya\helpers\{Html, Inflector, Json, Url};
use luya\admin\helpers\I18n;
use luya\admin\aws\TaggableActiveWindow;
use luya\admin\ngrest\base\NgRestModel;
use luya\admin\traits\SoftDeleteTrait;
use luya\admin\traits\TaggableTrait;
use luya\posts\admin\Module;
use luya\posts\models\{AutopostConfig,Autopost,AutopostQueueJob};

/**
 * This is the model class for table "posts_article".
 *
 * @property integer $id
 * @property boolean $is_draft
 * @property boolean $with_autopost
 * @property string $title
 * @property string $text
 * @property string $author
 * @property integer $cat_id
 * @property string $image_id
 * @property string $image_list
 * @property string $file_list
 * @property integer $create_user_id
 * @property integer $update_user_id
 * @property integer $timestamp_create
 * @property integer $timestamp_update
 * @property integer $timestamp_display_from
 * @property integer $timestamp_display_until
 * @property integer $is_deleted
 * @property integer $is_display_limit
 * @property string $teaser_text
 * @property string $detailUrl Return the link to the detail url of a posts item.
 * @author Basil Suter <basil@nadar.io>
 */
class Article extends NgRestModel
{
    use SoftDeleteTrait, TaggableTrait;

    public $i18n = ['title', 'text', 'teaser_text', 'image_list'];

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'posts_article';
    }

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        $this->on(self::EVENT_BEFORE_INSERT, [$this, 'eventBeforeInsert']);
        $this->on(self::EVENT_BEFORE_UPDATE, [$this, 'eventBeforeUpdate']);
        $this->on(self::EVENT_AFTER_INSERT, [$this, 'checkAutopostTrigger']);
        $this->on(self::EVENT_AFTER_UPDATE, [$this, 'checkAutopostTrigger']);
    }

    public function eventBeforeUpdate()
    {
        $this->update_user_id = Yii::$app->adminuser->getId();
        $this->timestamp_update = time();
    }

    public function eventBeforeInsert($event)
    {
        $this->create_user_id = Yii::$app->adminuser->getId();
        $this->update_user_id = Yii::$app->adminuser->getId();
        $this->timestamp_update = time();
        if (empty($this->timestamp_create)) {
            $this->timestamp_create = time();
        }
        if (empty($this->timestamp_display_from)) {
            $this->timestamp_display_from = time();
        }
    }

    public function checkAutopostTrigger()
    {
        if ($this->with_autopost && ! $this->is_draft) {
            if ($this->getAutoposts()->count()) {
                return;
            }
            Yii::$app->postsautopost->queuePostJobs($this);
        } else if (! $this->with_autopost) {
            foreach (AutopostQueueJob::find()->pending()->forArticle($this->id)->all() as $job) {
                $job->delete();
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['title', 'text'], 'required'],
            [['title', 'text', 'image_list', 'file_list', 'teaser_text', 'author'], 'string'],
            [['cat_id', 'create_user_id', 'update_user_id', 'timestamp_create', 'timestamp_update', 'timestamp_display_from', 'timestamp_display_until'], 'integer'],
            [['is_deleted', 'is_display_limit', 'with_autopost', 'is_draft'], 'boolean'],
            [['is_draft'], 'default', 'value' => true],
            [['image_id'], 'safe'],
            ['with_autopost', 'validateAutopostConfigs', 'skipOnError' => true],
        ];
    }

    public function validateAutopostConfigs($attribute, $params, $validator)
    {
        if (! $this->with_autopost) {
            return;
        }
        $autopostConfig = AutopostConfig::find()->all();
        if (empty($autopostConfig)) {
            $validator->addError($this, $attribute, Module::t('article_autopost_no_configs'));
        }
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'title' => Module::t('article_title'),
            'text' => Module::t('article_text'),
            'author' => Module::t('Author'),
            'teaser_text' => Module::t('teaser_text'),
            'cat_id' => Module::t('article_cat_id'),
            'image_id' => Module::t('article_image_id'),
            'timestamp_create' => Module::t('article_timestamp_create'),
            'timestamp_display_from' => Module::t('article_timestamp_display_from'),
            'timestamp_display_until' => Module::t('article_timestamp_display_until'),
            'is_display_limit' => Module::t('article_is_display_limit'),
            'image_list' => Module::t('article_image_list'),
            'file_list' => Module::t('article_file_list'),
            'with_autopost' => Module::t('article_autopost'),
            'is_draft' => Module::t('article_is_draft'),
        ];
    }

    /**
     * @inheritdoc
     */
    public function ngRestAttributeTypes()
    {
        return [
            'title' => 'text',
            'teaser_text' => ['textarea', 'markdown' => true],
            'text' => [
                'class' => 'luya\posts\admin\plugins\WysiwygPlugin',
            ],
            'author' => 'raw',
            'with_autopost' => [
                'class' => 'luya\posts\admin\plugins\ToggleStatus',
                'interactive' => false,
            ],
            'is_draft' => [
                'class' => 'luya\posts\admin\plugins\ToggleStatus',
                'initValue' => 1,
                'interactive' => false,
                'falseIcon' => '',
                'trueIcon' => 'edit',
            ],
            'image_id' => 'image',
            'timestamp_create' => 'datetime',
            'timestamp_display_from' => 'date',
            'timestamp_display_until' => 'date',
            'is_display_limit' => 'toggleStatus',
            'image_list' => 'imageArray',
            'file_list' => 'fileArray',
            'cat_id' => ['selectModel', 'modelClass' => Cat::className(), 'valueField' => 'id', 'labelField' => 'title']
        ];
    }

    /**
     *
     * @return string
     */
    public function getDetailUrl()
    {
        return Url::toRoute(['/posts/default/detail', 'id' => $this->id, 'title' => Inflector::slug($this->title)]);
    }

    /**
     * @return string
     */
    public function getDetailAbsoluteUrl()
    {
        return Url::toRoute(['/posts/default/detail', 'id' => $this->id, 'title' => Inflector::slug($this->title)], true);
    }

    /**
     * @return string
     */
    public function getDetailI18nAbsoluteUrl($lang = null)
    {
        $urlManager = Yii::$app->urlManager;
        $appComposition = Yii::$app->composition;
        $composition = Yii::createObject([
            'class' => \luya\web\Composition::class,
            'hidden' => $appComposition->hidden,
            'hideDefaultPrefixOnly' => $appComposition->hideDefaultPrefixOnly,
            'default' => $appComposition->default,
        ]);
        $composition['langShortCode'] = $lang;
        $slug = Inflector::slug(I18n::decodeFindActive($this->title, '', $lang));
        $path = $urlManager->internalCreateUrl(['/posts/default/detail', 'id' => $this->id, 'title' => $slug], $composition);
        return Url::to($path, true);
    }


    /**
     * Get image object.
     *
     * @return \luya\admin\image\Item|boolean
     */
    public function getImage()
    {
        return Yii::$app->storage->getImage($this->image_id);
    }

    /**
     * @inheritdoc
     */
    public static function ngRestApiEndpoint()
    {
        return 'api-posts-article';
    }

    /**
     * @inheritdoc
     */
    public function ngRestAttributeGroups()
    {
        return [
            [['timestamp_create', 'timestamp_display_from', 'is_display_limit', 'timestamp_display_until'], 'Time', 'collapsed'],
            [['image_id', 'image_list', 'file_list'], 'Media'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function ngRestScopes()
    {
        return [
            [['list'], ['cat_id', 'title', 'is_draft', 'with_autopost', 'timestamp_create', 'image_id']],
            [['create', 'update'], ['cat_id', 'title', 'author', 'teaser_text', 'with_autopost', 'is_draft', 'text', 'timestamp_create', 'timestamp_display_from', 'is_display_limit', 'timestamp_display_until', 'image_id', 'image_list', 'file_list']],
            [['delete'], true],
        ];
    }

    /**
     * @inheritdoc
     */
    public function ngRestActiveWindows()
    {
        return [];
        // return [
        //     ['class' => TaggableActiveWindow::class],
        // ];
    }

    /**
     *
     * @param false|int $limit
     * @return Article
     */
    public static function getAvailable($limit = false)
    {
        $q = self::find()
            ->andWhere('is_draft = :is_draft', ['is_draft' => false])
            ->andWhere('timestamp_display_from <= :time', ['time' => time()])
            ->orderBy('timestamp_display_from DESC');

        if ($limit) {
            $q->limit($limit);
        }

        $articles = $q->all();

        // filter if display time is limited
        foreach ($articles as $key => $article) {
            if ($article->is_display_limit) {
                if ($article->timestamp_display_until <= time()) {
                    unset($articles[$key]);
                }
            }
        }

        return $articles;
    }

    /**
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCat()
    {
        return $this->hasOne(Cat::class, ['id' => 'cat_id']);
    }

    public function getAutoposts()
    {
        return $this->hasMany(Autopost::class, ['article_id' => 'id']);
    }

    /**
     * The cat name short getter.
     *
     * @return string
     */
    public function getCategoryName()
    {
        return $this->cat->title;
    }

    public function getAuthor() {
        $_default = [
            'name' => 'Tommy Husky',
            'bio' => "<p>I LOVE 😍 hamsters!! Always have loved and will always love them :)</p>",
            'avatar' => 'https://huskyhamster.com/storage/tommy_df6c6970.jpg'
        ];

        $author = [];

        try {
            //$_a = Json::decode(Html::decode($this->author), true);
            $_a = Json::decode($this->author, true);

            if(is_array($_a)) {
                if(!empty($_a['name'])) {
                    $author['name'] = $_a['name'];
                }
                else {
                    $author['name'] = $_default['name'];
                }


                if(!empty($_a['bio'])) {
                    $author['bio'] = $_a['bio'];
                }
                else {
                    $author['bio'] = $_default['bio'];
                }

                if(!empty($_a['avatar'])) {
                    $author['avatar'] = $_a['avatar'];
                }
                else {
                    $author['avatar'] = $_default['avatar'];
                }
            }
            else {
                $author = $_default;
            }
        } catch(Exception $e) {
            $author = $_default;
        }

        return $author;
    }
}