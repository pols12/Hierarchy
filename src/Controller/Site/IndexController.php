<?php
namespace Hierarchy\Controller\Site;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;

class IndexController extends AbstractActionController
{
    public function hierarchyAction()
    {
        // $writer = new \Laminas\Log\Writer\Stream('logs/application.log');
        // $logger = new \Laminas\Log\Logger();
        // $logger->addWriter($writer);
        // $logger->info($this->params('grouping-id'));
        $view = new ViewModel;
        $site = $this->currentSite();

        // Retrieve grouping given in URL
        $grouping = $this->params('grouping-id') ? $this->api()->read('hierarchy_grouping', $this->params('grouping-id'))->getContent() : '';
        $groupingItemSet = $grouping->getItemSet();
        
        // Retrieve items assigned to groupings
        $query = $this->params()->fromQuery();
        $query['item_set_id'] = $groupingItemSet->id();
        $response = $this->api()->search('items', $query);
        $this->paginator($response->getTotalResults());
        $items = $response->getContent();
        $siteSlug = $this->params('site-slug');
        
        $view->setVariable('hierarchyGrouping', $grouping);
        $view->setVariable('itemSet', $groupingItemSet);
        $view->setVariable('items', $items);
        $view->setVariable('siteSlug', $siteSlug);
        return $view;
    }
}
