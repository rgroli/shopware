<?php
namespace  Shopware\Tests\Mink\Page\Emotion;

use Shopware\Tests\Mink\Element\Emotion\ArticleBox;
use Shopware\Tests\Mink\Element\Emotion\FilterGroup;
use SensioLabs\Behat\PageObjectExtension\PageObject\Page;
use Shopware\Tests\Mink\Helper;
use Shopware\Tests\Mink\HelperSelectorInterface;

class Listing extends Page implements HelperSelectorInterface
{
    /**
     * @var string $basePath
     */
    protected $basePath = '/listing/index/sCategory/{sCategory}';

    /**
     * @var string $path
     */
    protected $path = '';

    /**
     * @inheritdoc
     */
    public function getCssSelectors()
    {
        return [
            'viewTable' => 'a.table-view.active',
            'viewList' => 'a.list-view.active',
            'active' => '.active',
            'filterCloseLinks' => 'div.filter_properties > div > div.slideContainer > ul > li.close > a',
            'listingBox' => 'div.listing'
        ];
    }

    /**
     * @inheritdoc
     */
    public function getNamedSelectors()
    {
        return [
            'moreProducts' => ['de' => 'Weitere Artikel in dieser Kategorie', 'en' => 'More articles in this category']
        ];
    }

    /**
     * Opens the listing page
     * @param array $params
     * @param bool $autoPage
     */
    public function openListing(array $params, $autoPage = true)
    {
        $parameters = array_merge(
            ['sCategory' => 3],
            ($autoPage) ? ['sPage' => 1] : [],
            Helper::convertTableHashToArray($params, 'parameter')
        );

        $categoryId = array_shift($parameters);
        $this->path = $this->basePath . '?' . http_build_query($parameters);
        $parameters['sCategory'] = $categoryId;

        $this->open($parameters);
    }

    /**
     * Verify if we're on an expected page. Throw an exception if not.
     * @throws \Exception
     */
    public function verifyPage()
    {
        if (Helper::hasNamedLink($this, 'moreProducts')) {
            return;
        }

        $errors = [];

        if (!$this->hasSelect('n')) {
            $errors[] = '- There is no "article per page" select!';
        }

        if (!$this->hasSelect('o')) {
            $errors[] = '- There is no "order" select!';
        }

        if (!$errors) {
            return;
        }

        $message = ['You are not on a listing:'];
        $message = array_merge($message, $errors);
        $message[] = 'Current URL: ' . $this->getSession()->getCurrentUrl();
        Helper::throwException($message);
    }

    /**
     * Sets the article filter
     * @param FilterGroup $filterGroups
     * @param array $properties
     * @throws \Exception
     */
    public function filter(FilterGroup $filterGroups, array $properties)
    {
        $this->resetFilters();
        $this->setFilters($filterGroups, $properties);
    }

    /**
     * Resets all filters
     */
    protected function resetFilters()
    {
        $elements = Helper::findAllOfElements($this, ['filterCloseLinks'], false);

        if (empty($elements['filterCloseLinks'])) {
            return;
        }

        $closeLinks = array_reverse($elements['filterCloseLinks']);
        foreach ($closeLinks as $closeLink) {
            $closeLink->click();
        }
    }

    /**
     * Sets the filters
     * @param FilterGroup $filterGroups
     * @param array $properties
     * @throws \Exception
     */
    protected function setFilters(FilterGroup $filterGroups, array $properties)
    {
        foreach ($properties as $property) {
            $found = false;

            foreach ($filterGroups as $filterGroup) {
                $filterGroupName = rtrim($filterGroup->getText(), ' +');

                if ($filterGroupName === $property['filter']) {
                    $found = true;
                    $success = $filterGroup->setProperty($property['value']);

                    if (!$success) {
                        $message = sprintf('The value "%s" was not found for filter "%s"!', $property['value'],
                            $property['filter']);
                        Helper::throwException($message);
                    }

                    break;
                }
            }

            if (!$found) {
                $message = sprintf('The filter "%s" was not found!', $property['filter']);
                Helper::throwException($message);
            }
        }
    }

    /**
     * Checks the view method of the listing. Only $view has to be active
     * @param string $view
     */
    public function checkView($view)
    {
        $elements = array_filter(Helper::findElements($this, ['viewTable', 'viewList'], false));

        if (key($elements) !== $view) {
            $message = sprintf('"%s" is active! (should be "%s")', key($elements), $view);
            Helper::throwException($message);
        }
    }

    /**
     * Checks, whether an article is in the listing or not, is $negation is true, it checks whether an article is NOT in the listing
     * @param string $name
     * @param bool $negation
     */
    public function checkListing($name, $negation = false)
    {
        $result = $this->isArticleInListing($name);

        if ($negation) {
            $result = !$result;
        }

        if (!$result) {
            $message = sprintf(
                'The article "%s" is%s in the listing, but should%s.',
                $name,
                ($negation) ? '' : ' not',
                ($negation) ? ' not' : ''
            );
            Helper::throwException([$message]);
        }
    }

    /**
     * Checks, if a product is in the listing
     * @param string $name
     * @return bool
     */
    private function isArticleInListing($name)
    {
        $elements = Helper::findElements($this, ['listingBox']);
        $listingBox = $elements['listingBox'];

        return $listingBox->hasLink($name);
    }

    /**
     * @param ArticleBox $articleBox
     * @param array $properties
     * @throws \Exception
     */
    public function checkArticleBox(ArticleBox $articleBox, array $properties)
    {
        $properties = Helper::floatArray($properties, ['price']);
        $result = Helper::assertElementProperties($articleBox, $properties);

        if ($result === true) {
            return;
        }

        $message = sprintf(
            'The %s is "%s" (should be "%s")',
            $result['key'],
            $result['value'],
            $result['value2']
        );

        Helper::throwException($message);
    }
}
