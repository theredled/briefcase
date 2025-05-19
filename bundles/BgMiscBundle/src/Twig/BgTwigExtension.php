<?php

namespace Bg\MiscBundle\Twig;
use QsGeneralBundle\Doctrine\GroupsCollection;
use QsGeneralBundle\Entity\PlanningEventInterface;
use Symfony\Component\HttpFoundation\UrlHelper;
use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;
use Twig\TwigTest;

/**
 * Created by IntelliJ IDEA.
 * User: Benoît Guchet
 * Date: 02/01/2017
 * Time: 20:35
 */
class BgTwigExtension extends AbstractExtension
{
    protected $rootDir;
    protected $offlineCacheRows = null;
    /** @var UrlHelper  */
    //protected $urlHelper;

    public function __construct($rootDir = null)
    {
        //$this->urlHelper = $urlHelper;
        $this->rootDir = $rootDir;
    }

    public function getName() {
        return 'guche_extension';
    }

    public function getFilters() {
        return [
            new TwigFilter('phone', [$this, 'phoneFilter']),
            new TwigFilter('json_decode', [$this, 'jsonDecode']),
            new TwigFilter('html_attr_list', [$this, 'htmlAttrList'], [
                'is_safe' => ['html'],
            ]),
            new TwigFilter('offline_cache', [$this, 'offlineCache']),
            new TwigFilter('add_get_params', [$this, 'addGetParams']),
            new TwigFilter('asset_uptodate', [$this, 'getAssetUpToDateUrl']),
            new TwigFilter('group', [$this, 'group']),
            new TwigFilter('sort_groups_by_first_child_date', [$this, 'sortGroupsByFirstChildDate']),
            new TwigFilter('rowinate', [$this, 'rowinate']),
            new TwigFilter('paginate', [$this, 'paginate']),
            new TwigFilter('minify', [$this, 'minify']),
            new TwigFilter('ago', [$this, 'ago'], [
                'needs_environment' => true,
                'needs_context' => true,
            ]),
            new TwigFilter('money', [$this, 'moneyFilter'], [
                'is_safe' => ['html'],
            ]),
            new TwigFilter('pdf_asset', [$this, 'pdfAsset']),
            new TwigFilter('to_form_inputs', [$this, 'arrayToFormInputs']),
            new TwigFilter('short_class', [$this, 'shortClass']),
            new TwigFilter('iterator_to_array', [$this, 'iteratorToArray']),
            new TwigFilter('plain_text', [$this, 'plainText'], [
                'is_safe' => ['html'],
            ]),
            new TwigFilter('local_date', [$this, 'dateFilter'], [
                'needs_environment' => true,
                'needs_context' => true,
            ]),
            new TwigFilter('linkify_urls', [$this, 'linkifyUrls'], [
                'is_safe' => ['html'],
                'pre_escape' => 'html',
            ]),
            new TwigFilter('field_label', [$this, 'getFieldLabel'], [
                'needs_context' => true,
            ])
        ];
    }

    public function getTests()
    {
        return [
            new TwigTest('instanceof', [$this, 'isInstanceof']),
            new TwigTest('yes', [$this, 'isYes']),
        ];
    }

    public function getFunctions()
    {
        return [
            /*new TwigFunction('block_is_not_empty', [$this, 'blockIsNotEmpty'], [
                'needs_environment' => true,
                'needs_context' => true,
            ]),*/
            new TwigFunction('find_active_tab', [$this, 'findActiveTab']),
            new TwigFunction('is_active_tab', [$this, 'isActiveTab'], [
                'needs_context' => true,
            ]),
            new TwigFunction('blockset', [$this, 'getBlockset'], [
                'needs_environment' => true,
                'needs_context' => true,
                'is_safe' => ['html'],
            ]),
            new TwigFunction('build_tabset', [$this, 'buildTabset'], [
                'needs_environment' => true,
                'needs_context' => true,
                'is_safe' => ['html'],
            ]),
            new TwigFunction('static_call', [$this, 'staticCall']),
            new TwigFunction('get_class', [$this, 'getClass']),
            new TwigFunction('get_file_content', [$this, 'getFileContent'], [
                'is_safe' => ['html'],
            ]),
            new TwigFunction('base64_image_src', [$this, 'base64ImageSrc'], [
                'needs_context' => true,
            ]),

            //new TwigFunction('constant', [$this, 'constant']),
        ];
    }


    public function jsonDecode($str) {
        return json_decode($str, true);
    }

    public function arrayToFormInputs($fields, $parentName = null, $multipleSelect = false)
    {
        $arr = [];

        foreach ($fields as $name => $val) {
            $inputName = ($parentName ? $parentName.'['.$name.']' : $name);
            $isMultipleSelectValue = (is_array($val) and $multipleSelect and array_values($val) === $val);
            if (is_array($val) and !$isMultipleSelectValue) {
                $subFieldsArr = $this->arrayToFormInputs($val, $inputName);
                $arr = array_merge($arr, $subFieldsArr);
            }
            else
                $arr[$inputName] = $val;
        }

        return $arr;
    }

    public function linkifyUrls($str)
    {
        return preg_replace('/(^|\s)(\w+:\/\/\S+)($|\s)/m', '$1<a href="$2" target="_blank">$2</a>$3', $str);
    }

    public function pdfAsset($uri, $forHtml)
    {
        if (!$forHtml) {
            //$path = realpath($this->getWebDir() . preg_replace('#^([^?]*)\?(.*)$#', '$1', $uri));
            //return 'file:///'.str_replace(' ', '%20', $path);
            $path = preg_replace('#^([^?]*)\?(.*)$#', '$1', $uri);
            return substr(str_replace(' ', '%20', $path), 1); // Supprime le '/' de début. Remplacé par le <base href=""> dans le template pdfFrame.html.twig

        }
        else
            return $uri;
    }

    /**
     * http://userguide.icu-project.org/formatparse/datetime
     */
    public function dateFilter(Environment $env, array $context, $date, $format = null)
    {
        $date = twig_date_converter($env, $date, null);

        $formatter = \IntlDateFormatter::create(
            'fr',
            null,
            null,
            \IntlTimeZone::createTimeZone($date->getTimezone()->getName()),
            \IntlDateFormatter::GREGORIAN,
            $format
        );

        return $formatter->format($date->getTimestamp());
    }

    public function moneyFilter($amount, $short = false)
    {
        if ($amount === null)
            return null;
        elseif ($short) {
            if ($amount > 10000)
                $str = number_format($amount / 1000, $amount > 10000 ? 0 : 1, ',', '&nbsp;').' K€';
            else
                $str = number_format($amount, 0, ',', '&nbsp;').' €';
        }
        else {
            $str = number_format($amount, 2, ',', '&nbsp;').' €';
        }

        return str_replace(' ', '&nbsp;', $str);
    }

    public function getBlockset(Environment $env, array $context, string $path, array $vars = []): Blockset
    {
        $blockset = Blockset::build($env, $context, $path, $vars);
        return $blockset;
    }

    /*public function blockIsNotEmpty(Environment $env, array $context, $name) {
        $a = 1;
    }*/
    public function minify($path)
    {
        return $path;
    }

    /**
     * Groupe une liste selon un champ
     * Ex : [{stuff: 'foo', b: 2}, {stuff: 'foo', b: 1}, {stuff: 'bar', b: 3}]
     *   => |group('getStuff') => [
     *          'foo': {parent: 'foo', list: [{stuff: 'foo', b: 2}, {stuff: 'foo', b: 1}},
     *          'bar': {parent: 'bar', list: [{stuff: 'bar', b: 3}]}
     *      ]
     */
    public function group($list, $separatorFieldMethod, $idPrefix = null)
    {
        $grouped = GroupsCollection::group($list, $separatorFieldMethod, $idPrefix);
        return $grouped;
    }

    public function rowinate($events) {
        //-- Étalement sur un minimum de lignes
        $rows = [];
        /** @var PlanningEventInterface $e */
        foreach ($events as $e) {
            $rowFound = false;
            foreach($rows as &$row) {
                $lastEvent = end($row);
                if ($lastEvent->getEndDate() < $e->getBeginDate()) {
                    $row[] = $e;
                    $rowFound = true;
                    break;
                }
            }
            if (!$rowFound)
                $rows[] = [$e];
        }

        return $rows;
    }

    public function getAssetUpToDateUrl($path)
    {
        $fullPath = realpath($this->getWebDir().$path);
        $path .= '?v='.filemtime($fullPath);
        return $path;
    }

    public function buildTabset(Environment $env, array $context, $tabs, $default = null,
        $templateTabName = null, $activeTabId = null)
    {
        $tabset = new Tabset($env, $context);
        $tabset->init($tabs, $default, $templateTabName, $activeTabId);
        return $tabset;
    }

    protected function getWebDir() {
        return $this->rootDir.'/../web';
    }

    public function htmlAttrList($attributes) {
        $str = '';
        foreach($attributes as $key => $value)
            $str .= $key.'="'.$value.'" ';
        return $str;
    }

    public function paginate($coll, $nbByPage, $separatorFieldMethod = null) {
        $class = is_object($coll) ? get_class($coll) : null;
        $i = 0;
        $pages = [];
        $lastItem = null;
        $currentPage = $class ? new $class : [];;
        $pages[] = &$currentPage;

        foreach ($coll as $item) {
            if ($separatorFieldMethod) {
                //-- Nouveau séparateur (il y a toujours un séparateur en début de page)
                if (!$lastItem or $item->$separatorFieldMethod() != $lastItem->$separatorFieldMethod()) {
                    $i++;
                }
            }

            //-- Nouvel item
            $lastItem = $item;
            $currentPage[] = $item;
            $i++;

            //-- Nouvelle page
            if ($i >= $nbByPage) {
                unset($currentPage);
                $currentPage = $class ? new $class : [];;
                $pages[] = &$currentPage;
                $lastItem = null;
                $i = 0;
            }
        }

        return $pages;
    }

    public function staticCall($class, $function, $args = array()) {
        if (!class_exists($class))
            throw new \Exception('Classe inexistante : '.$class);
        if (!method_exists($class, $function))
            throw new \Exception('Methode inexistante : '.$function);

        return call_user_func_array(array($class, $function), $args);
    }

    public function getClass($entity)
    {
        return get_class($entity);
    }

    public function shortClass($class)
    {
        if (is_object($class))
            $class = get_class($class);

        return (new \ReflectionClass($class))->getShortName();
    }

    /*
     * = instanceof PHP + gère les noms de classes hors namespaces.
     * ex : $b = new \Foo\Bar\Baz();
     * {{ b is instanceof('Baz') }} => 1
     * {{ b is instanceof('BazParent') }} => 1
     */
    public function isInstanceof($var, $class) {
        if (!is_object($var))
            return false;

        foreach ([get_class($var)] + class_parents(get_class($var)) as $var_class) {
            $var_short_class = preg_replace('#^.*\\\\(\w+)$#', '$1', $var_class);
            if ($var_class == $class or $var_short_class == $class)
                return true;
        }

        return false;
    }

    public function isYes($var) {
        return false;
    }

    public function offlineCache($url, $doNothing = false) {
        return $url;
    }

    public function phoneFilter($phone) {
        // remove all spaces from the number
        $phone = str_replace(' ', '', $phone);
        // remove all symbols except + and ()
        $phone = preg_replace('/[^0-9\+()]/', '', $phone);
        // reformat special symbols
        $phone = str_replace('(', ' (', $phone);
        // if the phone is of normal local format
        if(
            strlen($phone) === 10 && strpos($phone, '+') === false &&
            strpos($phone, '(') === false && strpos($phone, ')') === false
        ) {
            // format the phone number by blocks of two numbers
            return(
                substr($phone, 0, 2) . ' ' . substr($phone, 2, 2) . ' ' .
                substr($phone, 4, 2) . ' ' . substr($phone, 6, 2) . ' ' . substr($phone, 8, 2));
        }
        // the phone is in international format
        else {
            // return the phone number, truncate at 31 chars (the world longest possible phone number)
            return substr($phone, 0, 31);
        }
    }

    public function addGetParams($url, array $addParams) {
        return self::staticAddGetParams($url, $addParams);
    }


    static public function staticAddGetParams($url, array $addParams) {
        if (!$url)
            return null;

        $comps = parse_url($url);
        if (isset($comps['query']))
            parse_str($comps['query'], $params);
        else
            $params = [];
        $params = array_merge($params, $addParams);
        $newUrl = (!empty($comps['host'])
                ? $comps['scheme'].'://'.$comps['host'].(!empty($comps['port']) ? ':'.$comps['port'] : '')
                : '').$comps['path']
            .($params ? '?'.http_build_query($params) : '')
            .(!empty($comps['fragment']) ? '#'.$comps['fragment'] : '');
        return $newUrl;
    }

    /**
     * Récupère le contenu d'un fichier CSS et le renvoie en string
     *
     * @param $filename
     * @return string
     */
    public function getFileContent($filename)
    {
        if (is_file($filename) && is_readable($filename)) {
            return file_get_contents($filename);
        }
        else
            throw new \Exception($filename.' n\'est pas lisible');
    }

    public function getFieldLabel(array $context, $fieldName)
    {
        return isset($context['usual_fields'][$fieldName]['label'])
            ? $context['usual_fields'][$fieldName]['label']
            : $fieldName;
    }

    /*public function getFieldLabelsList(array $fieldNames, array $context)
    {
        return isset($context['usual_fields'][$fieldName]['label'])
            ? $context['usual_fields'][$fieldName]['label']
            : $fieldName;
    }*/

    public function ago(Environment $env, array $context, \DateTime $date)
    {
        $now = new \DateTime();
        $delta = $now->diff($date);
        $ago = null;
        /*if ($delta->y > 0)
            $ago = $delta->y.' '.($delta->y > 1 ? 'ans' : 'an');
        elseif ($delta->m > 0)
            $ago = $delta->m.' mois';*/
        if ($delta->y > 0 or $delta->m > 0 or $delta->d > 2)
            return 'Le '.$this->dateFilter($env, $context, $date, 'd MMMM YYYY');
        elseif ($delta->d > 0)
            $ago = $delta->d.' '.($delta->d > 1 ? 'jours' : 'jour');
        elseif ($delta->h > 0)
            $ago = $delta->h.' '.($delta->h > 1 ? 'heures' : 'heure');
        elseif ($delta->i > 0)
            $ago = $delta->i.' '.($delta->i > 1 ? 'minutes' : 'minute');
        else
            return 'À l\'instant';

        return 'Il y a '.$ago;
    }

    public function plainText($html)
    {
        $txt = strip_tags($html);
        $txt = html_entity_decode($txt, ENT_QUOTES);
        return $txt;
    }

    public function base64ImageSrc(array $context,$path, $type)
    {
        $image = file_get_contents($context['web_dir'].'/'.$path);
        $data = base64_encode($image);
        return 'data:image/'.$type.';base64,'.$data;
    }

    public function iteratorToArray(\Iterator $iterator)
    {
        return iterator_to_array($iterator);
    }
}
