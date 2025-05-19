<?php

/**
 * Created by PhpStorm.
 * User: Benoît Guchet
 * Date: 01/05/2020
 * Time: 11:34
 */

namespace Bg\MiscBundle\Twig;

class Tab
{
    protected $blockset;
    protected $id;
    protected $tabset;
    protected $subtabset;
    protected $tabInfos;

    public function __construct($tabInfos, $id, Tabset $tabset)
    {
        $this->id = $id;
        $this->tabset = $tabset;
        $this->tabInfos = $tabInfos;

        if (!empty($tabInfos['subtabs'])) {
            $this->subtabset = new Tabset($tabset->getTwigEnv(), $tabset->getTwigContext(), $this);
            $this->subtabset->init($tabInfos['subtabs']);
        }

    }

    public function getTabset()
    {
        return $this->tabset;
    }

    public function getSubtabset()
    {
        return $this->subtabset;
    }

    public function isActive()
    {
        return $this->tabset->getActiveTabId() == $this->id;
    }

    public function isOpen()
    {
        return $this->getSubtabset() and ($this->isActive() or $this->hasActiveSubtab());
    }

    public function getBlockset()
    {
        if (!$this->blockset) {
            $tplName = is_array($this->tabInfos) ? $this->tabInfos['template_name'] : $this->tabInfos;
            $tplVars = is_array($this->tabInfos) ? $this->tabInfos['vars'] : [];

            $context = $this->tabset->getTwigContext();
            $context['tab'] = $this;

            $this->blockset = Blockset::build(
                $this->tabset->getTwigEnv(),
                $context,
                $tplName,
                $tplVars
            );
        }

        return $this->blockset;
    }

    public function hasActiveSubtab()
    {
        if ($this->subtabset) {
            foreach ($this->subtabset->getTabs() as $tab) {
                if ($tab->isActive())
                    return true;
            }
        }

        return false;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getPosition()
    {
        $ids = $this->tabset->getTabIds();
        return array_search($this->id, $ids);
    }

    public function getPrevious()
    {
        return $this->tabset->getTabByPosition($this->getPosition() - 1);
    }

    public function getNext()
    {
        return $this->tabset->getTabByPosition($this->getPosition() + 1);
    }

    public function isBeforeActive()
    {
        $ids = $this->tabset->getTabIds();
        $activeId = $this->tabset->getActiveTabId();
        $activePos = array_search($activeId, $ids);

        return $activePos > $this->getPosition();
    }
}
