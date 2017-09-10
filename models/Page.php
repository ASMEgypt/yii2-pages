<?php

namespace execut\pages\models;

use execut\crudFields\Behavior;
use execut\crudFields\BehaviorStub;
use execut\crudFields\fields\Action;
use execut\crudFields\fields\Boolean;
use execut\crudFields\fields\Date;
use execut\crudFields\fields\Editor;
use execut\crudFields\fields\HasOneRelation;
use execut\crudFields\fields\HasOneSelect2;
use execut\crudFields\fields\Id;
use execut\crudFields\ModelsHelperTrait;
use \execut\pages\models\base\Page as BasePage;
use lhs\Yii2SaveRelationsBehavior\SaveRelationsBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "pages_pages".
 */
class Page extends BasePage
{
    const MODEL_NAME = '{n,plural,=0{Pages} =1{Page} other{Pages}}';
    use BehaviorStub, ModelsHelperTrait;
//    public $vsSeoKeywords = [];
    public function behaviors()
    {
        return ArrayHelper::merge(
            parent::behaviors(),
            [
                'seoKeywords' => [
                    'class' => SaveRelationsBehavior::class,
                    'relations' => [
                        'seoKeywords'
                    ],
                ],
                'fields' => [
                    'class' => Behavior::class,
                    'plugins' => \yii::$app->getModule('pages')->getPageFieldsPlugins(),
                    'fields' => $this->getStandardFields(null, [
                        [
                            'class' => HasOneSelect2::class,
                            'attribute' => 'pages_page_id',
                            'relation' => 'pagesPage',
                            'url' => [
                                '/pages/backend'
                            ],
                        ],
                    ])
                ],
                [
                    'class' => TimestampBehavior::className(),
                    'createdAtAttribute' => 'created',
                    'updatedAtAttribute' => 'updated',
                    'value' => new Expression('NOW()'),
                ],
                # custom behaviors
            ]
        );
    }

    protected static $pagesCache = [];
    public static function getCache($id) {
        if (isset(self::$pagesCache[$id])) {
            return self::$pagesCache[$id];
        }
    }

    public static function setCache($model) {
        return self::$pagesCache[$model->id] = $model;
    }

    public static function getNavigationPages($id) {
        $result = [];
        if (!($page = self::getCache($id))) {
            $query = self::find()->andWhere(['id' => $id])->withParents()->isVisible();
            $page = $query->one();
            self::setCache($page);
        }

        if (!$page) {
            return [];
        }

        do {
            $currentPage = $page->getNavigationPage();
            $result[] = $currentPage;
            if (count($result) == 1) {
                self::initCurrentNavigationPage($currentPage, $page);
            }
        } while ($page = $page->pagesPage);

        return array_reverse($result);
    }

    /**
     * @TODO Very bad
     *
     * @param $navigationPage
     * @param $pageModel
     */
    public static function initCurrentNavigationPage($navigationPage, $pageModel) {
        \yii::$app->getModule('pages')->initCurrentNavigationPage($navigationPage, $pageModel);
    }

    public function getNavigationPage() {
        $page = new \execut\pages\navigation\Page();
        $checkedAttributes = [
            'name',
            'keywords',
            'title',
            'description',
            'header',
            'text',
        ];

        $page->setUrl($this->getUrl());
        foreach ($checkedAttributes as $attribute) {
            if (!empty($this->$attribute)) {
                $setter = 'set' . ucfirst($attribute);
                $page->$setter($this->$attribute);
            }
        }

        $page->setTime(strtotime($this->getLastTime()));
        $page->model = $this;

        return $page;
    }

    public function getUrl() {
        return [
            '/pages/frontend',
            'id' => $this->id,
        ];
    }

    public function attributeLabels()
    {
        return ArrayHelper::merge(parent::attributeLabels(), [
            'pages_page_id' => 'Parent page',
        ]); // TODO: Change the autogenerated stub
    }

    public function getLastTime() {
        if ($this->updated) {
            return $this->updated;
        }

        return $this->created;
    }

    public function getVsSeoKeywords() {
        return $this->getRelation('vsSeoKeywords');
    }

    public function getSeoKeywords() {
        return $this->getRelation('seoKeywords');
    }
}
