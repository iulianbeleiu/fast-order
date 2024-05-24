import Plugin from 'src/plugin-system/plugin.class';
import DomAccess from 'src/helper/dom-access.helper';
import Debouncer from 'src/helper/debouncer.helper';
import HttpClient from 'src/service/http-client.service';
import DeviceDetection from 'src/helper/device-detection.helper';
import Iterator from 'src/helper/iterator.helper';
import ArrowNavigationHelper from 'src/helper/arrow-navigation.helper';

export default class ArticleSearchPlugin extends Plugin {

    static options = {
        articleSearchSelector: '.js-article-search-form',
        articleSearchInputFieldSelector: 'input[type=search]',
        articleSearchUrlDataAttribute: "data-article-search-url",
        articleSearchResultSelector: '.js-article-search-result',
        articleSearchResultItemSelector: '.js-article-item',
        articleSearchResultArticleLink: '.search-suggest-product-link',

        itemQuantityInputSelector: '.js-quantity',

        searchResultArticleNumberDataAttribute: 'data-article-number',
        searchResultArticleTitleDataAttribute: "data-article-title",
        searchResultArticlePriceFormattedDataAttribute: 'data-article-price-formatted',
        searchResultArticlePriceDataAttribute: 'data-article-price-raw',
        searchResultArticleCurrencySymbolDataAttribute: 'data-currency-symbol',
        itemTotalDataAttribute: 'data-item-total',

        selectedArticleTitleSelector: ".js-selected-article-title",
        selectedArticlePriceSelector: ".js-selected-article-price",

        searchFieldSelector: '.js-search-field',

        totalAmountSelector: '.js-total-amount',

        articleSearchDelay: 250,
        articleSearchMinChars: 3,
    };
    
    init() {
        try {
            this._searchField = DomAccess.querySelector(this.el, this.options.articleSearchInputFieldSelector);
            this._quantityField = DomAccess.querySelector(this.el, this.options.itemQuantityInputSelector);
            this._articleSearchUrl = DomAccess.getAttribute(this.el, this.options.articleSearchUrlDataAttribute);

            this._selectedArticleTitle = DomAccess.querySelector(this.el, this.options.selectedArticleTitleSelector);
            this._selectedArticlePrice = DomAccess.querySelector(this.el, this.options.selectedArticlePriceSelector);
        } catch (e) {
            console.log(e);
            return;
        }

        this._client = new HttpClient();

        // initialize the arrow navigation
        this._navigationHelper = new ArrowNavigationHelper(
            this._searchField,
            this.options.articleSearchResultSelector,
            this.options.articleSearchResultItemSelector,
            true,
        );

        // add click event listener to body
        const event = (DeviceDetection.isTouchDevice()) ? 'touchstart' : 'click';
        document.body.addEventListener(event, this._onBodyClick.bind(this));

        this._registerEvents();
    }

    _registerEvents() {
        this._searchField.addEventListener(
            'input',
            Debouncer.debounce(this._handleInputEvent.bind(this), this.options.articleSearchDelay),
            {
                capture: true,
                passive: true,
            },
        );

        this._quantityField.addEventListener(
            'input',
            this._handleQuantityChange.bind(this)
        );
    }

    _handleInputEvent() {
        const value = this._searchField.value.trim();

        // stop search if minimum input value length has not been reached
        if (value.length < this.options.articleSearchMinChars) {
            // clear search results
            this._clearSuggestResults();
            return;
        }

        this._search(value);
    }

    _search(value) {
        const url = this._articleSearchUrl + encodeURIComponent(value);
        this._client.abort();

        this._client.get(url, (response) => {
            // remove existing search results first
            this._clearSuggestResults();
            this._clearSelectedItem();

            // attach search results to the DOM
            this.el.insertAdjacentHTML('beforeend', response);

            // add event listener for each result to be clicked
            const results = document.querySelectorAll(this.options.articleSearchResultArticleLink);
            Iterator.iterate(results, result => result.addEventListener(
                'click',
                this._handleArticleClickEvent.bind(this)
            ));
        });
    }

    _clearSuggestResults() {
        // reset arrow navigation helper to enable form submit on enter
        this._navigationHelper.resetIterator();

        // remove all result popovers
        const results = document.querySelectorAll(this.options.articleSearchResultSelector);
        Iterator.iterate(results, result => result.remove());

        this.$emitter.publish('clearSuggestResults');
    }

    _onBodyClick(e) {
        // early return if click target is the search result or any of it's children
        if (e.target.closest(this.options.articleSearchResultSelector)) {
            return;
        }
        // remove existing search results popover
        this._clearSuggestResults();

        this.$emitter.publish('onBodyClick');
    }

    _handleArticleClickEvent(e) {
        e.preventDefault();

        this._searchField.value = DomAccess.getAttribute(e.currentTarget, this.options.searchResultArticleNumberDataAttribute);
        this._selectedArticleTitle.innerHTML = DomAccess.getAttribute(e.currentTarget, this.options.searchResultArticleTitleDataAttribute);
        this._selectedArticlePrice.innerHTML = DomAccess.getAttribute(e.currentTarget, this.options.searchResultArticlePriceFormattedDataAttribute);

        this._selectedArticleTitle.classList.remove('d-none');
        this._selectedArticlePrice.classList.remove('d-none');

        this._itemPrice =  DomAccess.getAttribute(e.currentTarget, this.options.searchResultArticlePriceDataAttribute);
        this._currencySymbol =  DomAccess.getAttribute(e.currentTarget, this.options.searchResultArticleCurrencySymbolDataAttribute);

        this._quantityField.value = 1;

        this._selectedArticlePrice.setAttribute(this.options.itemTotalDataAttribute, this._itemPrice);

        this._calculateTotalAmount();
    }

    _handleQuantityChange(e) {
        let quantity = e.currentTarget.value;
        let itemTotal =  this._itemPrice * quantity;
        if (!isNaN(itemTotal)) {
            this._selectedArticlePrice.setAttribute(this.options.itemTotalDataAttribute, itemTotal)

            this._selectedArticlePrice.innerHTML = `${this._currencySymbol + itemTotal.toFixed(2)}`;

            this._calculateTotalAmount();
        }
    }

    _calculateTotalAmount() {
        const elements = DomAccess.querySelectorAll(document, this.options.selectedArticlePriceSelector);

        let totalAmount = 0;

        elements.forEach(element => {
            const itemTotal = parseFloat(element.getAttribute(this.options.itemTotalDataAttribute));

            if (!isNaN(itemTotal)) {
                totalAmount += itemTotal;
            }
        });

        DomAccess.querySelector(document, this.options.totalAmountSelector).innerHTML = this._currencySymbol + totalAmount.toFixed(2);
    }

    _clearSelectedItem() {
        this._selectedArticleTitle.innerHTML = "";
        this._selectedArticlePrice.innerHTML = "";
        this._selectedArticlePrice.setAttribute(this.options.itemTotalDataAttribute, 0);

        this._selectedArticleTitle.classList.add('d-none');
        this._selectedArticlePrice.classList.add('d-none');

        this._calculateTotalAmount();
    }
}
