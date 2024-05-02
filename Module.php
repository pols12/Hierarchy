<?php
namespace Hierarchy;

use Omeka\Module\AbstractModule;
use Omeka\Entity\Item;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Laminas\View\Renderer\PhpRenderer;
use Laminas\Mvc\Controller\AbstractController;
use Laminas\EventManager\SharedEventManagerInterface;
use Laminas\Form\Fieldset;
use Laminas\Mvc\MvcEvent;
use Laminas\EventManager\Event;
use Hierarchy\Form\ConfigForm;

class Module extends AbstractModule
{
    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    public function install(ServiceLocatorInterface $serviceLocator)
    {
        $connection = $serviceLocator->get('Omeka\Connection');
        $connection->exec('CREATE TABLE hierarchy_grouping (id INT AUTO_INCREMENT NOT NULL, item_set_id INT DEFAULT NULL, hierarchy_id INT NOT NULL, parent_grouping INT DEFAULT NULL, `label` VARCHAR(255) DEFAULT NULL, position INT NOT NULL, INDEX IDX_DCDE57FF960278D7 (item_set_id), INDEX IDX_DCDE57FF582A8328 (hierarchy_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB;');
        $connection->exec('CREATE TABLE hierarchy (id INT AUTO_INCREMENT NOT NULL, `label` VARCHAR(255) NOT NULL, position INT NOT NULL, UNIQUE INDEX UNIQ_FA7A28AEEA750E8 (`label`), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB;');
        $connection->exec('ALTER TABLE hierarchy_grouping ADD CONSTRAINT FK_DCDE57FF960278D7 FOREIGN KEY (item_set_id) REFERENCES item_set (id) ON DELETE CASCADE;');
        $connection->exec('ALTER TABLE hierarchy_grouping ADD CONSTRAINT FK_DCDE57FF582A8328 FOREIGN KEY (hierarchy_id) REFERENCES hierarchy (id) ON DELETE CASCADE;');
    }
    
    public function uninstall(ServiceLocatorInterface $serviceLocator)
    {
        $connection = $serviceLocator->get('Omeka\Connection');
        $connection->exec('ALTER TABLE hierarchy_grouping DROP FOREIGN KEY FK_DCDE57FF960278D7');
        $connection->exec('ALTER TABLE hierarchy_grouping DROP FOREIGN KEY FK_DCDE57FF582A8328');
        $connection->exec('DROP TABLE hierarchy');
        $connection->exec('DROP TABLE hierarchy_grouping');
    }

    public function attachListeners(SharedEventManagerInterface $sharedEventManager)
    {
        $sharedEventManager->attach(
            'Omeka\Controller\Admin\Item',
            'view.show.sidebar',
            [$this, 'addItemAdminHierarchies']
        );

        $sharedEventManager->attach(
            'Omeka\Controller\Admin\ItemSet',
            'view.show.sidebar',
            [$this, 'addItemSetAdminHierarchies']
        );

        $sharedEventManager->attach(
            'Omeka\Controller\Site\Item',
            'view.show.after',
            [$this, 'addSiteHierarchies']
        );
    }

    /**
     * Get this module's configuration form.
     *
     * @param ViewModel $view
     * @return string
     */
    public function getConfigForm(PhpRenderer $renderer)
    {
        $formElementManager = $this->getServiceLocator()->get('FormElementManager');
        $form = $formElementManager->get(ConfigForm::class);
        $html = $renderer->formCollection($form, false);
        return $html;
    }

    public function handleConfigForm(AbstractController $controller)
    {
        $params = $controller->params()->fromPost();
        $globalSettings = $this->getServiceLocator()->get('Omeka\Settings');
        $globalSettings->set('hierarchy_show_all_groupings', $params['hierarchy_show_all_groupings']);
        $globalSettings->set('hierarchy_show_label', $params['hierarchy_show_label']);
    }

    // Add relevant hierarchy nested lists to item admin display sidebar
    public function addItemAdminHierarchies(Event $event)
    {
        $view = $event->getTarget();
        $api = $this->getServiceLocator()->get('Omeka\ApiManager');
        if ($view->item->itemSets()) {
            echo '<div class="meta-group">';
            echo '<h4>' . $view->translate('Hierarchies') . '</h4>';

            // Get order for printing item's sets from position on hierarchy page
            $itemSetOrder = array_filter($api->search('hierarchy_grouping', ['sort_by' => 'position'], ['returnScalar' => 'item_set'])->getContent());
            $itemSets = array_replace(array_flip($itemSetOrder), $view->item->itemSets());

            foreach ($itemSets as $currentItemSet) {
                if (is_numeric($currentItemSet)) {
                    continue;
                }
                $groupings = $api->search('hierarchy_grouping', ['item_set' => $currentItemSet->id(), 'sort_by' => 'position'])->getContent();
                $this->buildNestedList($groupings, $currentItemSet, $view->item);
            }
            echo '</div>';
        }
    }

    // Add relevant hierarchy nested lists to item set admin display sidebar
    public function addItemSetAdminHierarchies(Event $event)
    {
        $view = $event->getTarget();
        $api = $this->getServiceLocator()->get('Omeka\ApiManager');
        $groupings = $api->search('hierarchy_grouping', ['item_set' => $view->resource->id(), 'sort_by' => 'position'])->getContent();

        echo '<div class="meta-group">';
        echo '<h4>' . $view->translate('Hierarchies') . '</h4>';
        $this->buildNestedList($groupings, $view->resource, $view->item);
        echo '</div>';
    }

    // Add relevant hierarchy nested lists to site item show page
    public function addSiteHierarchies(Event $event)
    {
        $view = $event->getTarget();
        $api = $this->getServiceLocator()->get('Omeka\ApiManager');
        if ($view->item->itemSets()) {
            echo '<dl class="hierarchies">';
            echo '<div class="property">';
            echo '<dt>' . $view->translate('Hierarchies') . '</dt>';

            // Get order for printing item's sets from position on Hierarchy page
            $itemSetOrder = array_filter($api->search('hierarchy_grouping', ['sort_by' => 'position'], ['returnScalar' => 'item_set'])->getContent());
            $itemSets = array_replace(array_flip($itemSetOrder), $view->item->itemSets());

            foreach ($itemSets as $currentItemSet) {
                if (is_numeric($currentItemSet)) {
                    continue;
                }
                $groupings = $api->search('hierarchy_grouping', ['item_set' => $currentItemSet->id(), 'sort_by' => 'position'])->getContent();
                $this->buildNestedList($groupings, $currentItemSet, $view->item);
            }
            echo '</div></dl>';
        }
    }

    protected function buildNestedList(array $groupings, $currentItemSet, $item)
    {
        $api = $this->getServiceLocator()->get('Omeka\ApiManager');
        $globalSettings = $this->getServiceLocator()->get('Omeka\Settings');
        static $printedGroupings = [];
        static $itemSetCounter = 0;
        $itemSetCounter++;
        $iterate = function ($groupings) use ($api, $globalSettings, $currentItemSet, $item, &$itemSetCounter, &$iterate, &$allGroupings, &$printedGroupings, &$currentHierarchy, &$childCount) {
            foreach ($groupings as $key => $grouping) {
                // Continue if grouping has already been printed
                if (isset($printedGroupings) && in_array($grouping, $printedGroupings)) {
                    continue;
                }

                if ($currentHierarchy != $grouping->getHierarchy()) {
                    // Close HTML list and value if previoius hierarchy or itemSet iteration
                    if (isset($currentHierarchy) || $itemSetCounter > 1) {
                        echo '</ul></dd>';
                    }
                    $currentHierarchy = $grouping->getHierarchy();
                    echo '<dd class="value"><ul>';
                    // Show label if hierarchy_show_label checked in config
                    if ($globalSettings->get('hierarchy_show_label')) {
                        echo '<dt>' . $currentHierarchy->getLabel() . '</dt>';
                    }
                    $allGroupings = $api->search('hierarchy_grouping', ['hierarchy' => $currentHierarchy, 'sort_by' => 'position'])->getContent();
                    // If hierarchy_show_all_groupings checked in config, iterate through all groupings
                    if ($globalSettings->get('hierarchy_show_all_groupings')) {
                        $iterate($allGroupings, $currentItemSet);
                        continue;
                    }
                }

                if ($grouping->getParentGrouping() != 0) {
                    // $iterate through any groupings with current grouping as child
                    $parentArray = array_filter($allGroupings, function($parent) use($grouping) {
                        return $parent->id() == $grouping->getParentGrouping();
                    });
                    if (count($parentArray) > 0) {
                        $iterate($parentArray, $currentItemSet);
                        continue;
                    }
                }

                try {
                    $itemSet = $api->read('item_sets', $grouping->getItemSet())->getContent();
                } catch (\Exception $e) {
                    // Print groupings without assigned itemSet
                    $itemSet = null;
                    echo '<li>' . $grouping->getLabel() . '</li>';
                }

                if (!is_null($itemSet)) {
                    $itemSetArray = isset($item) ? $item->itemSets() : array($currentItemSet);
                    foreach ($itemSetArray as $itemItemSet) {
                        $itemSetIDArray[] = $itemItemSet->id();
                    }
                    // Bold groupings with current itemSet assigned
                    if (in_array($grouping->getItemSet()->getId(), $itemSetIDArray)) {
                        echo '<li><b>' . $itemSet->link($grouping->getLabel()) . '</b></li>';
                    } else {
                        echo '<li>' . $itemSet->link($grouping->getLabel()) . '</li>';
                    }
                }

                // Return any groupings with current grouping as parent
                $childArray = array_filter($allGroupings, function($child) use($grouping) {
                    return $child->getParentGrouping() == $grouping->id();
                });

                // Remove already printed groupings from $allGroupings array
                $allGroupings = array_filter($allGroupings, function($child) use($grouping) {
                    return $child->id() != $grouping->id();
                });

                $printedGroupings[] = $grouping;

                if (count($childArray) > 0) {
                    // Handle multidimensional hierarchies by saving/retrieving previous state
                    $prevChildArray = $childArray ?: [];
                    $childCount = count($childArray);
                    echo '<ul>';
                    $iterate($childArray, $currentItemSet);
                    echo '</ul>';
                    $childArray = $prevChildArray;
                    continue;
                } elseif ($childCount >= 1) {
                    // Keep other variables the same if iterating 'sibling'
                    $childCount--;
                    continue;
                }
            }
        };
        $iterate($groupings, $currentItemSet);
    }
}
