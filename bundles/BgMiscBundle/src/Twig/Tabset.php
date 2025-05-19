<?php

/**
 * Created by PhpStorm.
 * User: Benoît Guchet
 * Date: 31/10/2019
 * Time: 14:22
 */

namespace Bg\MiscBundle\Twig;

use Twig\Environment;

class Tabset
{
    protected $twigEnv;
    protected $twigContext;
    protected $request;
    protected $tabs;
    protected $tabsInfos;
    protected $default;
    protected $templateTabName;
    protected $templateTab;
    protected $parentTab;
    protected $activeTabId;

    public function __construct(Environment $env, array $context, Tab $parentTab = null)
    {
        $this->parentTab = $parentTab;
        $this->twigEnv = $env;
        $this->twigContext = $context;
        $this->request = $this->twigContext['app']->getRequest();
    }

    public function init($tabsInfos, $default = null, $templateTabName = null, $activeTabId = null)
    {
        $tabsInfos = array_filter($tabsInfos);
        $this->tabsInfos = $tabsInfos;
        $this->default = $default;
        $this->activeTabId = $activeTabId;
        $this->templateTabName = $templateTabName;
        $this->tabs = [];

        foreach ($tabsInfos as $id => $tabInfos) {
            $tab = new Tab($tabInfos, $id, $this);
            $this->tabs[$id] = $tab;
        }
    }

    public function getTwigEnv()
    {
        return $this->twigEnv;
    }

    public function getTwigContext()
    {
        return $this->twigContext;
    }

    public function getTemplateTab()
    {
        if (!$this->templateTabName)
            return null;
        if (!$this->templateTab)
            $this->templateTab = new Tab($this->templateTabName, null, $this);
        return $this->templateTab;
    }

    public function getTabs()
    {
        return $this->tabs;
    }

    public function getTabIds()
    {
        return array_keys($this->getTabs());
    }

    public function getAllTabIdsRecursive() {
        $ids = array_keys($this->getTabs());

        foreach ($this->tabs as $tab) {
            $subtabset = $tab->getSubtabset();
            if ($subtabset)
                $ids = array_merge($ids, $subtabset->getAllTabIdsRecursive());
        }

        return $ids;
    }

    public function getDefaultTabId()
    {
        $allIds = $this->getTabIds();
        return ($this->default and in_array($this->default, $allIds))
            ? $this->default : reset($allIds);
    }

    public function getActiveTabId()
    {
        if (!$this->activeTabId) {
            $rootTabset = $this->parentTab ? $this->parentTab->getTabset() : $this;
            $allTabIds = $rootTabset->getAllTabIdsRecursive();
            $allActiveTabIds = explode('/', $this->request->get('tab'));
            $allActiveTabIds[] = $rootTabset->getDefaultTabId();
            $eligibleActiveTabIds = array_values(array_intersect($allActiveTabIds, $allTabIds));

            $this->activeTabId = $eligibleActiveTabIds ? reset($eligibleActiveTabIds) : reset($allActiveTabIds);
        }

        return $this->activeTabId;
    }

    public function getTabByPosition($pos)
    {
        $tabs = $this->getTabs();
        $ids = $this->getTabIds();
        return isset($ids[$pos]) ? $tabs[$ids[$pos]] : null;
    }
}
