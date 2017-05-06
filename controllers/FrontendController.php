<?php
/**
 */

namespace execut\pages\controllers;


use execut\actions\Action;
use execut\actions\action\adapter\Delete;
use execut\actions\action\adapter\Edit;
use execut\actions\action\adapter\GridView;
use execut\crud\fields\Field;
use execut\pages\action\adapter\ShowPage;
use execut\pages\models\Page;
use execut\navigation\Behavior;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use yii\web\ErrorAction;

class FrontendController extends Controller
{
    public function actions()
    {
        return ArrayHelper::merge(parent::actions(), [
            'index' => [
                'class' => Action::class,
                'adapter' => [
                    'class' => ShowPage::class,
                ],
                'view' => 'index',
            ],
            'error' => [
                'class' => ErrorAction::class,
            ],
        ]); // TODO: Change the autogenerated stub
    }
}