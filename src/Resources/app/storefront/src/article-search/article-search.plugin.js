import Plugin from 'src/plugin-system/plugin.class';
import DomAccess from 'src/helper/dom-access.helper';
import Debouncer from 'src/helper/debouncer.helper';
import HttpClient from 'src/service/http-client.service';

export default class ArticleSearchPlugin extends Plugin {
    static options = {
        articleSearchSelector: '.js-article-search-form',
        articleSearchInputFieldSelector: 'input[type=search]',
        articleSearchUrlDataAttribute: "data-article-search-url",

        articleSearchDelay: 250,
        articleSearchMinChars: 3,
    };
    
    init() {
        try {
            this._inputField = DomAccess.querySelector(this.el, this.options.articleSearchInputFieldSelector);
            this._articleSearchUrl = DomAccess.getAttribute(this.el, this.options.articleSearchUrlDataAttribute);
        } catch (e) {
            console.log(e);
            return;
        }

        this._client = new HttpClient();

        this._registerEvents();
    }

    _registerEvents() {
        this._inputField.addEventListener(
            'input',
            Debouncer.debounce(this._handleInputEvent.bind(this), this.options.articleSearchDelay),
            {
                capture: true,
                passive: true,
            },
        );
    }

    _handleInputEvent() {
        const value = this._inputField.value.trim();

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

            // attach search results to the DOM
            this._insertResponseHTML(response);
        });
    }

    _clearSuggestResults() {
        console.log('clear results');
    }

    _insertResponseHTML(response) {
        console.log('insert response html')
    }
}
