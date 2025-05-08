<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var common\models\Hotels $model */

$this->title = 'Create Hotels';
$this->params['breadcrumbs'][] = ['label' => 'Hotels', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="hotels-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
