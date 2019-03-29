<?php

namespace luya\posts\admin\render;

use luya\helpers\Json;

class RenderArticleCrudView extends \luya\admin\ngrest\render\RenderCrudView
{
    /**
     * Need to run custom service
     * 
     * @TODO move config into `getAngularControllerConfig` method
     * @inheritdoc
     */
    public function registerAngularControllerScript()
    {
        $config = [
            'apiListQueryString' => $this->context->apiQueryString('list'),
            'apiUpdateQueryString' => $this->context->apiQueryString('update'),
            'apiEndpoint' => $this->context->getApiEndpoint(),
            'list' => $this->context->getFields('list'),
            'create' => $this->context->getFields('create'),
            'update' => $this->context->getFields('update'),
            'ngrestConfigHash' => $this->context->getConfig()->getHash(),
            'activeWindowCallbackUrl' => $this->context->getApiEndpoint('active-window-callback'),
            'activeWindowRenderUrl' =>  $this->context->getApiEndpoint('active-window-render'),
            'pk' => $this->context->getConfig()->getPrimaryKey(),
            'inline' => $this->context->getIsInline(),
            'modelSelection' => $this->context->getModelSelection(),
            'orderBy' => $this->context->getOrderBy(),
            'tableName' => $this->context->getConfig()->getTableName(),
            'groupBy' => $this->context->getConfig()->getGroupByField() ? 1 : 0,
            'groupByField' => $this->context->getConfig()->getGroupByField() ? $this->context->getConfig()->getGroupByField() : '0',
            'groupByExpanded' => $this->context->getConfig()->getGroupByExpanded(),
            'filter' => '0',
            'fullSearchContainer' => false,
            'minLengthWarning' => false,
            'saveCallback' => $this->context->getConfig()->getOption('saveCallback') ? new JsExpression($this->context->getConfig()->getOption('saveCallback')) : false,
            'relationCall' => $this->context->getRelationCall(),
            'relations' => $this->context->getConfig()->getRelations(),
        ];
        
        $client = 'zaa.bootstrap.register("'.$this->context->config->getHash().'", ["$scope", "$controller", "autopostQueueWorker", function($scope, $controller, autopostQueueWorker) {
			$.extend(this, $controller("CrudController", { $scope : $scope }));
			$scope.config = '.Json::htmlEncode($config).'
            autopostQueueWorker.run();
	    }]);';
        
        $this->registerJs($client, self::POS_BEGIN);
    }
}

