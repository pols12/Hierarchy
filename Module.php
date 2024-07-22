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

    public function onBootstrap(MvcEvent $event)
    {
        parent::onBootstrap($event);
    
        $acl = $this->getServiceLocator()->get('Omeka\Acl');
    
        // Allow all users to access public hierarchies
        $acl->allow(
            null,
            ['Hierarchy\Entity\Hierarchy',
            'Hierarchy\Entity\HierarchyGrouping',
            'Hierarchy\Api\Adapter\HierarchyGroupingAdapter',
            'Hierarchy\Api\Adapter\HierarchyAdapter',
            ],
        );
        $acl->allow(null, 'Hierarchy\Controller\Site\Index', 'hierarchy');
    }

    public function install(ServiceLocatorInterface $serviceLocator)
    {
        $connection = $serviceLocator->get('Omeka\Connection');
        $connection->exec('CREATE TABLE hierarchy_grouping (id INT AUTO_INCREMENT NOT NULL, item_set_id INT DEFAULT NULL, hierarchy_id INT NOT NULL, parent_grouping INT DEFAULT NULL, `label` VARCHAR(255) DEFAULT NULL, position INT NOT NULL, INDEX IDX_DCDE57FF960278D7 (item_set_id), INDEX IDX_DCDE57FF582A8328 (hierarchy_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB;');
        $connection->exec('CREATE TABLE hierarchy (id INT AUTO_INCREMENT NOT NULL, `label` VARCHAR(255) DEFAULT NULL, position INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB;');
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
    }

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
        $globalSettings->set('hierarchy_show_label', $params['hierarchy_show_label']);
        $globalSettings->set('hierarchy_group_resources', $params['hierarchy_group_resources']);
        $globalSettings->set('hierarchy_show_count', $params['hierarchy_show_count']);
    }

    // Add relevant hierarchy nested lists to item admin display sidebar
    public function addItemAdminHierarchies(Event $event)
    {
        $this->view = $event->getTarget();
        $api = $this->getServiceLocator()->get('Omeka\ApiManager');
        if ($this->view->item->itemSets()) {
            echo '<div class="meta-group">';
            echo '<h4>' . $this->view->translate('Hierarchies') . '</h4>';

            // Get order for printing item's sets from position on hierarchy page
            $itemSetOrder = array_filter($api->search('hierarchy_grouping', ['sort_by' => 'position'], ['returnScalar' => 'item_set'])->getContent());
            $itemSets = array_replace(array_flip($itemSetOrder), $this->view->item->itemSets());

            foreach ($itemSets as $currentItemSet) {
                if (is_numeric($currentItemSet)) {
                    continue;
                }
                $groupings = $api->search('hierarchy_grouping', ['item_set' => $currentItemSet->id(), 'sort_by' => 'position'])->getContent();
                $this->view->hierarchyHelper()->buildNestedList($groupings, $currentItemSet, $this->view->item);
            }
            echo '</div>';
        }
    }

    // Add relevant hierarchy nested lists to item set admin display sidebar
    public function addItemSetAdminHierarchies(Event $event)
    {
        $this->view = $event->getTarget();
        $api = $this->getServiceLocator()->get('Omeka\ApiManager');
        $groupings = $api->search('hierarchy_grouping', ['item_set' => $this->view->resource->id(), 'sort_by' => 'position'])->getContent();

        echo '<div class="meta-group">';
        echo '<h4>' . $this->view->translate('Hierarchies') . '</h4>';
        $this->view->hierarchyHelper()->buildNestedList($groupings, $this->view->resource, $this->view->item);
        echo '</div>';
    }
}
