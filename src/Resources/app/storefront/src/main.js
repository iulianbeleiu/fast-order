// Import all necessary Storefront plugins
import ArticleSearchPlugin from './article-search/article-search.plugin';

// Register your plugin via the existing PluginManager
const PluginManager = window.PluginManager;

PluginManager.register('ArticleSearchPlugin', ArticleSearchPlugin, '[data-fast-order-search]');
