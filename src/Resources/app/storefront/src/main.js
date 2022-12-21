// import
import PluginManager from 'src/plugin-system/plugin.manager';
import DrivenProductConfiguratorPlugin from "./plugin/driven-product-configurator.plugin";

// register plugin
PluginManager.register(
    'DrivenProductConfiguratorPlugin',
    DrivenProductConfiguratorPlugin,
    '[data-driven-product-configurator]'
);