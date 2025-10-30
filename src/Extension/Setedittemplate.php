<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  System.setedittemplate
 *
 * @copyright   (C) 2024 R2H BV. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\System\Setedittemplate\Extension;

use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\Event\SubscriberInterface;
use Joomla\CMS\Event\Module\AfterModuleListEvent;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Plugin to set template style when editing articles in frontend
 *
 * @since  1.0.0
 */
final class Setedittemplate extends CMSPlugin implements SubscriberInterface
{
    /**
     * Application object.
     *
     * @var    CMSApplicationInterface
     * @since  1.0.0
     */
    protected $app;

    /**
     * Affects constructor behavior. If true, language files will be loaded automatically.
     *
     * @var    boolean
     * @since  1.0.0
     */
    protected $autoloadLanguage = true;


    /**
     * Returns the events to which the plugin wants to subscribe.
     *
     * @return  array  The events to subscribe to
     *
     * @since   1.0.0
     */
    public static function getSubscribedEvents(): array
    {
        return [
            'onAfterRoute' => 'onAfterRoute',
            'onBeforeRender' => 'onBeforeRender',
            'onAfterModuleList' => 'onAfterModuleList',
        ];
    }

    /**
     * Event triggered after the framework has routed the application.
     *
     * @return  void
     *
     * @since   1.0.0
     */
    public function onAfterRoute(): void
    {
        // Only target the frontend site
        if (!$this->app->isClient('site')) {
            return;
        }

        // Check if we're in the correct editing context
        if (!$this->isEditingContext()) {
            return;
        }

        // Get the plugin parameter for template style
        $templateStyleId = $this->params->get('template_style', 0);

        // Set the template style in the Application input object
        $input = $this->app->getInput();
        $input->set('templateStyle', $templateStyleId);
    }

    /**
     * Listener for the `onBeforeRender` event
     *
     * @return  void
     *
     * @since   1.0
     */
    public function onBeforeRender()
    {
        // Only target the frontend site
        if (!$this->app->isClient('site')) {
            return;
        }

        if ($this->params->get('debug', 0)) {
            // Get the application input object
            $input = $this->app->getInput();

            // Get the current Component view and layout
            $component = $input->getCmd('option');
            $view = $input->getCmd('view');
            $layout = $input->getCmd('layout');

            // Create a Bootstrap alert with debug information
            $debugHtml = '<div class="alert alert-info" role="alert" style="margin: 10px; z-index: 9999; position: relative;">
                <h4 class="alert-heading"><i class="fa fa-info-circle"></i> Set Edit Template Debug Info</h4>
                <p><strong>Component:</strong> ' . htmlspecialchars($component ?: '(empty)') . '<br>
                <strong>View:</strong> ' . htmlspecialchars($view ?: '(empty)') . '<br>
                <strong>Layout:</strong> ' . htmlspecialchars($layout ?: '(empty)') . '</p>
                <hr>
                <p class="mb-0"><small>Use these values in your plugin configuration to target this page.</small></p>
                <p class="mb-0"><small>To remove this message go to <strong>Plugins -> System -> Set Edit Template</strong> and disable the show info option.</small></p>
            </div>';

            echo $debugHtml;
        }

        $removeOnTemplate   = $this->params->get('disable_modules_on_template', 0);
        $currentTemplateId  = $this->app->getTemplate(true)->id;
        $templateStyleId    = $this->params->get('template_style', 0);

        if (
            $this->isEditingContext() ||
            ($removeOnTemplate && ((int) $templateStyleId === (int) $currentTemplateId))
        ) {
            /** @var \Joomla\CMS\WebAsset\WebAssetManager $wa */
            $wa = $this->app->getDocument()->getWebAssetManager();

            $styling = $this->params->get('editpage_styling', '');
            if (!empty($styling)) {
                $style = <<<CSS
            $styling
            CSS;

                $wa->addInlineStyle($style, ['name' => 'plg_system_setedittemplate.editpage_styling']);
            }
        }
    }

    /**
     * Called after the list of modules for a position is built.
     *
     * @param   AfterModuleListEvent  $event  The event object
     *
     * @return  void
     * @since   1.0.0
     */
    public function onAfterModuleList(AfterModuleListEvent $event): void
    {
        // Only target the frontend site
        if (!$this->app->isClient('site')) {
            return;
        }

        $currentTemplateId  = $this->app->getTemplate(true)->id;
        $templateStyleId    = $this->params->get('template_style', 0);
        $modulesToRemove    = $this->params->get('disable_modules', null);
        $removeOnTemplate   = $this->params->get('disable_modules_on_template', 0);

        if (empty($modulesToRemove)) {
            return;
        }

        if (
            $this->isEditingContext() ||
            ($removeOnTemplate && ((int) $templateStyleId === (int) $currentTemplateId))
        ) {
            // Get the modules array from the event
            $modules = &$event->getArgument('modules');

            // Convert to integers for comparison
            $moduleIdsToRemove = array_map('intval', (array) $modulesToRemove);

            // Remove modules whose IDs are in the removal list
            foreach ($modules as $key => $module) {
                if (isset($module->id) && in_array((int) $module->id, $moduleIdsToRemove, true)) {
                    unset($modules[$key]);
                }
            }

            // Re-index the array to maintain proper indexing
            $modules = array_values($modules);

            // Update the event with the modified modules array
            $event->setArgument('modules', $modules);
        }
    }

    /**
     * Check if we're in an article editing context
     *
     * @return  boolean
     * @since   1.0.0
     */
    private function isEditingContext(): bool
    {
        // Get the application input object
        $input = $this->app->getInput();

        $componentsSet = $this->params->get('components', '');
        $viewsSet = $this->params->get('views', '');
        $layoutsSet = $this->params->get('layouts', '');

        // If all context parameters are empty, return false
        if (empty($componentsSet) && empty($viewsSet) && empty($layoutsSet)) {
            return false;
        }

        // Get the current Component view and layout
        $component = $input->getCmd('option');
        $view = $input->getCmd('view');
        $layout = $input->getCmd('layout');

        // If custom components are configured, check against them - ALL must match
        if (!empty($componentsSet)) {
            $allowedComponents = array_map('trim', explode(',', $componentsSet));
            if (!in_array($component, $allowedComponents, true)) {
                return false;
            }
        }

        // If custom views are configured, check against them - ALL must match
        if (!empty($viewsSet)) {
            $allowedViews = array_map('trim', explode(',', $viewsSet));
            if (!in_array($view, $allowedViews, true)) {
                return false;
            }
        }

        // If custom layouts are configured, check against them - ALL must match
        if (!empty($layoutsSet)) {
            $allowedLayouts = array_map('trim', explode(',', $layoutsSet));
            if (!in_array($layout, $allowedLayouts, true)) {
                return false;
            }
        }

        return true;
    }
}
